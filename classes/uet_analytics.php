<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 3/5/18
 * Time: 8:36 AM
 */

namespace mod_uetanalytics;

use stdClass;

class uet_analytics
{
    private $course;


    public function __construct($course)
    {
        if($course instanceof uet_course){
            $this->course = $course;
        }else{
            if(isset($course->id) ){
                $this->course = new uet_course($course->id);
            }else{
                $this->course = new uet_course($course);
            }
        }

    }


    public function getTimeOfSection($section)
    {
        $startdate = $this->course->getStartDateInt();
        $time = $startdate + $section * 7 * 24 * 3600;
        return $time;
    }

    public function getGrade($studentid)
    {
        global $DB;
        $param = [
            'userid' => $studentid,
            'courseid' => $this->course->getCourseId()
        ];
        $grade = $DB->get_record('uet_grade', $param);
        return $grade;
    }

    //get view post of student in section
    public function getViewPostInSection($section, $studentid)
    {
        global $DB;
        $time = $this->getTimeOfSection($section);
        $timestart = $time - 7 * 24 * 3600;
        $sql = 'AND userid = :userid';
        $params = [
            'courseid' => $this->course->getCourseId(),
            'stype' => 'activity',
            'userid' => $studentid,
            'edulevel' => 2,
            'action' => 'viewed',
            'timecreated' => $time,
            'timestart' => $timestart
        ];
        $view = $DB->get_record_sql("SELECT count(*) as view FROM {logstore_standard_log} WHERE courseid = :courseid 
                                                                                            AND action= :action
                                                                                            AND edulevel = :edulevel
                                                                                            AND timecreated >= :timestart
                                                                                            AND timecreated <= :timecreated $sql ", $params);
        $post_actions = '"submitted" ,"created", "deleted", "updated", "uploaded", "sent"';
        unset($params['action']);
        $post = $DB->get_record_sql("SELECT count(*) as post FROM {logstore_standard_log} WHERE courseid = :courseid 
                                                                                            AND edulevel = :edulevel
                                                                                            AND timecreated >= :timestart
                                                                                            AND timecreated <= :timecreated 
                                                                                            AND action IN($post_actions) $sql", $params);

        $result['view'] = isset($view->view) ? $view->view : 0;
        $result['post'] = isset($post->post) ? $post->post : 0;
        return $result;
    }

    //get total view post of student from start date to current section
    public function getTotalCurrentViewPost($studentid)
    {
        global $DB;
        $section = $this->course->getCurrentSection();
        $time = $this->getTimeOfSection($section);
        $params = [
            'courseid' => $this->course->getCourseId(),
            'stype' => 'activity',
            'userid' => $studentid,
            'edulevel' => 2,
            'action' => 'viewed',
            'timecreated' => $time
        ];
        $sql = 'AND userid = :userid';
        $view = $DB->get_record_sql("SELECT count(*) as view FROM {logstore_standard_log} WHERE courseid = :courseid 
                                                                                            AND action= :action
                                                                                            AND edulevel = :edulevel
                                                                                            AND timecreated <= :timecreated $sql ", $params);
        $post_actions = '"submitted" ,"created", "deleted", "updated", "uploaded", "sent"';
        unset($params['action']);
        $post = $DB->get_record_sql("SELECT count(*) as post FROM {logstore_standard_log} WHERE courseid = :courseid 
                                                                                            AND edulevel = :edulevel
                                                                                            AND timecreated <= :timecreated 
                                                                                            AND action IN($post_actions) $sql", $params);

        $result['view'] = isset($view->view) ? $view->view : 0;
        $result['post'] = isset($post->post) ? $post->post : 0;
        return $result;
    }

    //get view post forum of student in section
    public function getForumViewPostInSection($section, $studentid)
    {
        global $DB;
        $time = $this->getTimeOfSection($section);
        $params['courseid'] = $this->course->getCourseId();
        $params['userid'] = $studentid;
        $logtable = 'logstore_standard_log';
        $params['component'] = 'mod_forum';
        $params['action'] = "viewed";
        $params['timecreated'] = $time;
        $params['timestart'] = $time - 7 * 24 * 3600;
        $sql = 'AND userid = :userid';
        $a = $DB->get_record_sql("SELECT count(*) as view FROM {" . $logtable . "}
                                        WHERE  courseid=:courseid AND component = :component $sql AND action=:action AND timecreated >= :timestart AND timecreated <= :timecreated ", $params);
        $result["view"] = isset($a->view) ? $a->view : 0;
        $post_actions = '"submitted" ,"created", "deleted", "updated", "uploaded", "sent"';
        $a = $DB->get_record_sql("SELECT count(*) as post FROM {" . $logtable . "}
                                        WHERE courseid=:courseid $sql AND component = :component AND action IN ($post_actions) AND timecreated >= :timestart AND timecreated <= :timecreated ", $params);
        $result['post'] = isset($a->post) ? $a->post : 0;
        return $result;
    }

    //get view post forum of student from start date to current section
    public function getTotalCurrentForumViewPost($studentid)
    {
        global $DB;
        $section = $this->course->getCurrentSection();
        $time = $this->getTimeOfSection($section);
        $params['courseid'] = $this->course->getCourseId();
        $params['userid'] = $studentid;
        $logtable = 'logstore_standard_log';
        $params['component'] = 'mod_forum';
        $params['action'] = "viewed";
        $params['timecreated'] = $time;
        $a = $DB->get_record_sql("SELECT count(*) as view FROM {" . $logtable . "}
                                        WHERE  courseid=:courseid AND component = :component AND userid= :userid AND action=:action AND timecreated <= :timecreated ", $params);
        $result["view"] = isset($a->view) ? $a->view : 0;
        $post_actions = '"submitted" ,"created", "deleted", "updated", "uploaded", "sent"';
        $a = $DB->get_record_sql("SELECT count(*) as post FROM {" . $logtable . "}
                                        WHERE courseid=:courseid AND userid = :userid AND component = :component AND action IN ($post_actions) AND timecreated <= :timecreated ", $params);
        $result['post'] = isset($a->post) ? $a->post : 0;
        return $result;
    }

    // get all assignment submission from start date to section
    public function getAssignmentSubmissionInSection($section, $studentid)
    {
        global $DB;
        $submission = new stdClass();
        $params['userid'] = $studentid;
        $params['courseid'] = $this->course->getCourseId();
        $params['timemodified'] = $this->getTimeOfSection($section);
        $assigns = $this->getAssignmentInSection($section);
        $submission->total = count($assigns);
        $submission->submitted = 0;
        $submission->late = 0;
        $submission->not = 0;
        foreach ($assigns as $assign) {
            $params['assign'] = $assign->id;
            $submit = $DB->get_record_sql("SELECT id,userid,assignment,timemodified,status FROM {assign_submission}
                                              WHERE assignment = :assign AND userid = :userid AND timemodified <= :timemodified", $params);
            if ($submit) {
                if ($submit->status == 'submitted') {
                    $submission->submitted += 1;
                    $time = $assign->duedate - $submit->timemodified;
                    if ($time < 0) {
                        $submission->late += 1;
                    }
                } else {
                    $submission->not += 1;
                }
            } else {
                $submission->not += 1;
            }
        }
        return $submission;
    }

    //get number submitted in assignment $id
    public function getSubmissionInAssignment($id)
    {
        global $DB;
        $num = $DB->get_record_sql("SELECT COUNT(*) as num FROM {assign_submission} WHERE assignment=$id AND status='submitted'");
        if ($num) {
            return $num->num;
        } else {
            return 0;
        }
    }

    //get number module to $section
    public function getNumberModulesInSection($section)
    {
        global $DB;
        $params['courseid']= $this->course->getCourseId();
        $params['section'] = $section;
        $cm = $DB->get_record_sql('SELECT COUNT(*) as num FROM {course_modules} WHERE course = :courseid AND section <= :section',$params);
        return $cm->num;
    }

    //get number forum discussion to $section
    public function getNumberForumDiscussionsInSection($section)
    {
        global $DB;
        $time = $this->getTimeOfSection($section);
        $params['courseid'] = $this->course->getCourseId();
        $params['timemodified'] = $time;
        $forum = $DB->get_record_sql("SELECT COUNT(*) AS number FROM {forum_discussions} WHERE course = :courseid AND timemodified <= :timemodified", $params);
        return $forum->number;
    }

    //get all assignments from start date to $section
    public function getAssignmentInSection($section)
    {
        global $DB;
        $time = $this->getTimeOfSection($section);
        $params['courseid'] = $this->course->getCourseId();
        $params['duedate'] = $time;
        $assigns = $DB->get_records_sql("SELECT id,course,name,duedate,allowsubmissionsfromdate FROM {assign}
                                            WHERE course = :courseid AND duedate <= :duedate", $params);
        if (!$assigns) {
            return false;
        }
        return $assigns;
    }

    //predict student grade
    public function predict($studentid)
    {
        $week = $this->course->getCurrentSection();
        if ($week < 3) {
            return ['w7' => 0, 'w15' => 0];
        } elseif ($week >= 3 && $week < 6) {
            $week = 3;
        } elseif ($week >= 6 && $week < 7) {
            $week = 6;
        } elseif ($week >= 7 && $week < 10) {
            $week = 7;
        } elseif ($week >= 10 && $week < 13) {
            $week = 10;
        } elseif ($week >= 13 && $week < 15) {
            $week = 13;
        } else {
            $week = 15;
        }
        $row['week'] = $week;
        $stat = $this->getViewPostInSection($week, $studentid);
        $modules = $this->getNumberModulesInSection($week);
        $row['view'] = floatval($stat['view'] / $modules);
        $row['post'] = floatval($stat['post'] / $modules);
        $forum = $this->getForumViewPostInSection($week, $studentid);
        $nforum = $this->getNumberForumDiscussionsInSection($week);
        if (!$nforum) {
            $row['forumpost'] = 0;
            $row['forumview'] = 0;
        } else {
            $row['forumview'] = floatval($forum['view'] / $nforum);
            $row['forumpost'] = floatval($forum['post'] / $nforum);

        }
        $submission = $this->getAssignmentSubmissionInSection($week, $studentid);
        $row['successsubmission'] = floatval($submission->submitted / $submission->total);
        $response = $this->runPythonPredict($row);
        return $response;
    }

    // test predict by webservices
    public function curlRequest($postfield)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_URL, "http://localhost:5000/predict");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        return $server_output;
    }

    // predict grade by python
    public function runPythonPredict($data)
    {
        $d = implode(' ', $data);
        $cmd = "cd backend ; python3 -W ignore main.py " . $d . " 2>&1";
        $output = shell_exec($cmd);
        $output = json_decode($output);
        return $output;
    }

    // import grade for
    public function importGrade($course, $file)
    {
        global $CFG, $DB;
        require_once("$CFG->libdir/phpexcel/PHPExcel.php");
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $excel = $objReader->load($file);
        $sheet = $excel->getSheet(0);
        $lastRow = $sheet->getHighestRow();
        $grade = new stdClass();
        $grade->courseid = $course;
        for ($row = 2; $row <= $lastRow; $row++) {
            $studentNo = $sheet->getCell('C' . $row)->getValue();
            $userid = $DB->get_record('user', ['id' => $studentNo]);
            $grade->userid = $userid->id;
            $g = $DB->get_record('uet_grade', ['courseid' => $grade->courseid, 'userid' => $grade->userid]);
            if ($g) {
                $g->mid = $sheet->getCell('D' . $row)->getValue();
                $g->final = $sheet->getCell('E' . $row)->getValue();
                $DB->update_record('uet_grade', $g);

            } else {
                $grade->mid = $sheet->getCell('D' . $row)->getValue();
                $grade->final = $sheet->getCell('E' . $row)->getValue();
                $DB->insert_record('uet_grade', $grade);
            }
        }
    }

    public function getCourseAnalytics($section){
        global $DB;
        $time = $this->getTimeOfSection($section);
        $params['courseid'] = $this->course->getCourseId();
        $logtable = 'logstore_standard_log';
        $params['component'] = 'mod_forum';
        $params['action'] = "viewed";
        $params['timecreated'] = $time;
        $params['timestart'] = $time - 7 * 24 * 3600;
        $a = $DB->get_record_sql("SELECT count(*) as view FROM {" . $logtable . "}
                                        WHERE  courseid=:courseid AND component = :component  AND action=:action AND timecreated >= :timestart AND timecreated <= :timecreated ", $params);
        $result["forumview"] = isset($a->view) ? $a->view : 0;
        $a = $DB->get_record_sql("SELECT count(*) as view FROM {" . $logtable . "}
                                        WHERE  courseid=:courseid  AND action=:action AND timecreated >= :timestart AND timecreated <= :timecreated ", $params);
        $result["view"] = isset($a->view) ? $a->view : 0;
        $post_actions = '"submitted" ,"created", "deleted", "updated", "uploaded", "sent"';
        $a = $DB->get_record_sql("SELECT count(*) as post FROM {" . $logtable . "}
                                        WHERE courseid=:courseid  AND component = :component AND action IN ($post_actions) AND timecreated >= :timestart AND timecreated <= :timecreated ", $params);
        $result['forumpost'] = isset($a->post) ? $a->post : 0;
        $a = $DB->get_record_sql("SELECT count(*) as post FROM {" . $logtable . "}
                                        WHERE courseid=:courseid   AND action IN ($post_actions) AND timecreated >= :timestart AND timecreated <= :timecreated ", $params);
        $result['post'] = isset($a->post) ? $a->post : 0;
        return $result;
    }
}