<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 3/11/18
 * Time: 8:42 AM
 */

namespace mod_uetanalytics;

class uet_course
{
    private $id;
    private $course;

    public function __construct($id)
    {
        global $DB;
        $this->id = $id;
        $this->course = $DB->get_record('course', array('id' => $id));
    }

    public function getCourse()
    {
        return $this->course;
    }

    public function getCourseId()
    {
        return $this->id;
    }

    public function getCourseFullName()
    {
        return $this->course->fullname;
    }

    public function getCourseShortName()
    {
        return $this->course->shortname;
    }

    public function getTeachers()
    {
        global $DB;
        $params['courseid'] = $this->getCourseId();
        $teachers = $DB->get_records_sql("  SELECT ra.userid FROM {role_assignments} ra JOIN {context} c ON ra.contextid = c.id
                                                                         WHERE c.instanceid = :courseid AND ra.roleid IN (1,2,3,4)", $params);
        return $teachers;
    }

    public function printTeachers()
    {
        $teacher = $this->getTeachers();
        $rs = '';
        foreach ($teacher as $t) {
            $t = new uet_user($t->userid);
            $rs .= ' - ' . $t->getName();
        }
        return $rs;
    }

    public function getSummary()
    {

        return $this->course->summary;

    }

    public function getStartDate()
    {
        $date = date_create($this->course->startdate);
        return date_format($date, 'y-m-d H:m');
    }

    public function getStartDateInt(){
        return $this->course->startdate;
    }

    public function getNumberStudent()
    {
        global $DB;
        $params['courseid'] = $this->getCourseId();
        $students = $DB->get_record_sql("  SELECT count(*) as number FROM {role_assignments} ra JOIN {context} c ON ra.contextid = c.id
                                                                         WHERE c.instanceid = :courseid AND ra.roleid = 5", $params);
        return $students->number;
    }

    public function getNumberModules()
    {
        global $DB;
        $modules = $DB->get_record_sql("SELECT COUNT(*) AS number FROM {course_modules} WHERE course = :courseid GROUP BY course", array('courseid' => $this->getCourseId()));
        if ($modules) {
            return $modules->number;
        } else {
            return 0;
        }
    }

    public function getSectionFormat()
    {
        $format = $this->course->format;
        $format = ucfirst(substr($format, 0, -1));
        return $format;
    }

    public function getNumberSection()
    {
        global $DB;
        $params['courseid'] = $this->getCourseId();
        $sections = $DB->get_records_sql("SELECT id,course,section,name FROM {course_sections}
                                                 WHERE course =:courseid ", $params);
        return count($sections)-1;
    }

    public function getCurrentSection()
    {
        $now = strtotime('now');
        if($this->course->format =='weeks'){
            if($now >= $this->course->enddate){
                return $this->getNumberSection();
            }else{
                $t = $now - $this->course->startdate;
                $w = intval($t/(7*24*3600))+1;
                return $w;
            }
        }
        else{
            $t = $now - $this->course->startdate;
            $w = intval($t/(7*24*3600))+1;
            return $w;
        }
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

    public function getStudents()
    {
        global $DB;
        $params['courseid'] = $this->getCourseId();
        $students = $DB->get_records_sql("  SELECT ra.userid FROM {role_assignments} ra JOIN {context} c ON ra.contextid = c.id
                                                                         WHERE c.instanceid = :courseid AND ra.roleid = 5", $params);
        return $students;
    }

    public function getAllAssigns()
    {
        global $DB;
        $params['courseid'] = $this->getCourseId();
        $assigns = $DB->get_records_sql("SELECT id,course,name,duedate,allowsubmissionsfromdate FROM {assign}
                                            WHERE course = :courseid", $params);
        if (!$assigns) {
            return false;
        }
        return $assigns;
    }

    public function getCourseModules()
    {
        global $DB;
        $params['courseid'] = $this->getCourseId();
        $cm = $DB->get_records_sql("SELECT * FROM {course_modules} WHERE course =:courseid", $params);
        if ($cm) return $cm;
        else return 0;
    }

    public function getTotalViewPostInSection($section){

    }

    public function getTotalCurrentForumViewPost(){

    }

    public function getTotalCurrentAssignSubmission(){

    }
}