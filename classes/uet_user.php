<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 3/11/18
 * Time: 8:38 AM
 */

namespace mod_uetanalytics;

class uet_user
{
    private $id;
    private $user;


    public function __construct($user)
    {
        global $DB;
        if(isset($user->id) && is_int($user->id)){
            $this->id = $user->id;
            $this->user = $user;
        }else{
            $this->id = $user;
            $this->user = $DB->get_record('user', array('id' => $user));
        }
    }


    public function getUser()
    {
        return $this->user;
    }

    public function getName()
    {
        $name = $this->user->lastname . ' ' . $this->user->firstname;
        return $name;
    }

    public function getEmail()
    {
        return $this->user->email;
    }

    public function getAddress()
    {
        return $this->user->city . ' - ' . $this->user->country;
    }

    public function getProfilePicture()
    {
        global $PAGE;
        $picture = new \user_picture($this->user);
        return $picture->get_url($PAGE);
    }

    public function getNumberCourses()
    {
        return 0;
    }

    public function getLastAccess($courseid)
    {
        global $DB;
        $access = $DB->get_record('user_lastaccess', array('userid' => $this->user->id, 'courseid' => $courseid));
        return date( 'y-m-d H:m',$access->timeaccess);
    }

    public function getLastIP()
    {
        return $this->user->lastip;
    }

    public function getUserId()
    {
        return $this->id;
    }

    public function isTeacher($context)
    {
        if (is_primary_admin($this->id)) {
            return true;
        }
        $roles = get_user_roles($context, $this->id);
        $role = [];
        foreach ($roles as $r) {
            $role[] = $r->shortname;
        }
        if (in_array('teacher', $role) || in_array('coursecreator',$role) || in_array('editingteacher',$role)) {
            return true;
        } else {
            return false;
        }
    }
}