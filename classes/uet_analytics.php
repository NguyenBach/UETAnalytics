<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 3/5/18
 * Time: 8:36 AM
 */


class uet_analytics
{
    private $course;
    private $sections = [];
    private $students = [];
    private $assigns = [];
    private $forums = [];
    private $cm = [];
    private $moduleType = [];

    public function __construct($course)
    {
        $this->course = $course;
        $this->sections = $this->getSection(0);
        $this->students = $this->getStudentsInCourse();
        $this->assigns = $this->getAllAssigns();
        $this->forums = $this->getAllForums();
        $this->cm = $this->getCourseModules();
        $this->moduleType = $this->getModuleType();
    }

    private function getCourseId()
    {
        return $this->course->id;
    }

    public function getTimeOfSection($section)
    {
        $startdate = $this->course->startdate;
        $time = $startdate + $section * 7 * 24 * 3600;
        return $time;
    }

    public function getGrade($type, $studentid)
    {
        global $DB;
        if ($type == 'mid') {
            $section = intval(count($this->sections) / 2);
        } else {
            $section = count($this->sections);
        }
        $assigns = $this->getAssignmentInSection($section);
        $params['userid'] = $studentid;
        $params['courseid'] = $this->course->id;
        $grade = 0;
        foreach ($assigns as $assign) {
            $params['assignid'] = $assign->id;
            $item = $DB->get_record_sql("SELECT id,courseid,itemname FROM {grade_items}
                                              WHERE iteminstance = :assignid AND courseid=:courseid", $params);
            $params['itemid'] = $item->id;
            $item = $DB->get_record_sql("SELECT id,userid,itemid,finalgrade,aggregationweight FROM {grade_grades}
                                              WHERE userid=:userid AND itemid=:itemid ", $params);
            if (!$item) {
                $grade += 0;
            }
            $grade += $item->finalgrade * $item->aggregationweight;
        }
        return $grade;
    }

    public function getViewPostInSection($section, $studentid)
    {
        global $DB;
        $time = $this->getTimeOfSection($section);
        $timestart = $time - 7 * 24 * 3600;
        if (!$studentid) {
            $sql = '';
        } else {
            $sql = 'AND userid = :userid';
        }
        $params = [
            'courseid' => $this->course->id,
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

    public function getTotalViewPost($section, $studentid)
    {
        global $DB;
        $time = $this->getTimeOfSection($section);
        $params = [
            'courseid' => $this->course->id,
            'stype' => 'activity',
            'userid' => $studentid,
            'edulevel' => 2,
            'action' => 'viewed',
            'timecreated' => $time
        ];
        if (!$studentid) {
            $sql = '';
        } else {
            $sql = 'AND userid = :userid';
        }
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

    public function getForumViewPostInSection($section, $studentid)
    {
        global $DB;
        $time = $this->getTimeOfSection($section);
        $params['courseid'] = $this->course->id;
        $params['userid'] = $studentid;
        $logtable = 'logstore_standard_log';
        $params['component'] = 'mod_forum';
        $params['action'] = "viewed";
        $params['timecreated'] = $time;
        $params['timestart'] = $time - 7 * 24 * 3600;
        if (!$studentid) {
            $sql = '';
        } else {
            $sql = 'AND userid = :userid';
        }
        $a = $DB->get_record_sql("SELECT count(*) as view FROM {" . $logtable . "}
                                        WHERE  courseid=:courseid AND component = :component $sql AND action=:action AND timecreated >= :timestart AND timecreated <= :timecreated ", $params);
        $result["view"] = isset($a->view) ? $a->view : 0;
        $post_actions = '"submitted" ,"created", "deleted", "updated", "uploaded", "sent"';
        $a = $DB->get_record_sql("SELECT count(*) as post FROM {" . $logtable . "}
                                        WHERE courseid=:courseid $sql AND component = :component AND action IN ($post_actions) AND timecreated >= :timestart AND timecreated <= :timecreated ", $params);
        $result['post'] = isset($a->post) ? $a->post : 0;
        return $result;
    }

    public function getTotalFormViewPost($section, $studentid)
    {
        global $DB;
        $time = $this->getTimeOfSection($section);
        $params['courseid'] = $this->course->id;
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

    public function getAssignmentSubmissionInSection($section, $studentid)
    {
        global $DB;
        $submission = new stdClass();
        $params['userid'] = $studentid;
        $params['courseid'] = $this->getCourseId();
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

    public function getStudentsInCourse()
    {
        global $DB;
        $params['courseid'] = $this->getCourseId();
        $students = $DB->get_records_sql("  SELECT ra.userid FROM {role_assignments} ra JOIN {context} c ON ra.contextid = c.id
                                                                         WHERE c.instanceid = :courseid AND ra.roleid = 5", $params);
        return $students;
    }

    public function getSection($section)
    {
        global $DB;
        $params['courseid'] = $this->getCourseId();
        if ($section == 0) {
            $sql = '';
        } else {
            $sql = 'AND section <= ' . $section;
        }
        $sections = $DB->get_records_sql("SELECT id,course,section,name FROM {course_sections}
                                                 WHERE course =:courseid AND section != 0 $sql ", $params);
        return $sections;
    }

    public function getNumberModulesInSection($section)
    {
        $modules = 0;
        foreach ($this->cm as $cm) {
            if ($this->sections[$cm->section]->section <= $section) {
                $modules++;
            }
        }
        return $modules;
    }

    public function getNumberForumDiscussionsInSection($section)
    {
        global $DB;
        $time = $this->getTimeOfSection($section);
        $params['courseid'] = $this->getCourseId();
        $params['timemodified'] = $time;
        $forum = $DB->get_record_sql("SELECT COUNT(*) AS number FROM {forum_discussions} WHERE course = :courseid AND timemodified <= :timemodified", $params);
        return $forum->number;
    }

    public function getAssignmentInSection($section)
    {
        global $DB;
        $assigns = [];
        if ($section == 0) {
            return $this->assigns;
        }
        foreach ($this->cm as $cm) {
            if ($cm->module == $this->moduleType['assign']) {
                if ($this->sections[$cm->section]->section <= $section) {
                    $assigns[] = $this->assigns[$cm->instance];
                }
            }
        }
        return $assigns;
    }

    public function getAllAssigns()
    {
        global $DB;
        $courseid = $this->getCourseId();
        $params['courseid'] = $courseid;
        $assigns = $DB->get_records_sql("SELECT id,course,name,duedate,allowsubmissionsfromdate FROM {assign}
                                            WHERE course = :courseid", $params);
        if (!$assigns) {
            return false;
        }
        return $assigns;
    }

    public function getAllForums()
    {

    }

    public function getCourseModules()
    {
        global $DB;
        $params['courseid'] = $this->getCourseId();
        $cm = $DB->get_records_sql("SELECT * FROM {course_modules} WHERE course =:courseid", $params);
        if ($cm) return $cm;
        else return 0;
    }

    public function getModuleType()
    {
        global $DB;
        $modules = $DB->get_records_sql("SELECT * FROM {modules}");
        $m = [];
        foreach ($modules as $module) {
            $m[$module->name] = $module->id;
        }
        return $m;
    }

    public function predict($studentid)
    {
        $week = $this->getCurrentSection();
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

    public function getCurrentSection()
    {
        $now = strtotime('now');
        if ($this->course->format == 'weeks') {
            if ($now >= $this->course->enddate) {
                return count($this->sections);
            } else {
                $t = $now - $this->course->startdate;
                $w = intval($t / (7 * 24 * 3600)) + 1;
                return $w;
            }
        } else {
            $t = $now - $this->course->startdate;
            $w = intval($t / (7 * 24 * 3600)) + 1;
            return $w;
        }
    }

    public function runPythonPredict($data){
        $d = implode(' ',$data);
        $cmd ="cd backend ; python3 -W ignore main.py ".$d." 2>&1";
        $output = shell_exec($cmd);
        $output = json_decode($output);
        return $output;
    }
}