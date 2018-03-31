<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 3/11/18
 * Time: 8:38 AM
 */


class uet_user
{
    private $id;
    private $user;
    public function __construct($id)
    {
        global $DB;
        $this->id = $id;
        $this->user = $DB->get_record('user',array('id'=>$id));
    }
    public function getUser(){
        return $this->user;
    }
    public function getName(){
        $name = $this->user->lastname.' '.$this->user->firstname;
        return $name;
    }

    public function getEmail(){
        return $this->user->email;
    }

    public function getAddress(){
        return $this->user->city .' - '. $this->user->country;
    }

    public function getProfilePicture(){
        global $PAGE;
        $picture = new user_picture($this->user);
        return $picture->get_url($PAGE);
    }

    public function getNumberCourses(){
        return 0;
    }

    public function getLassAccess($courseid){
        global $DB;
        $access = $DB->get_record('user_lastaccess',array('userid'=>$this->user->id,'courseid'=>$courseid));
        $date = date_create($access->timeaccess);
        return date_format($date,'y-m-d H:m');
    }
    public function getLastIP(){
        return $this->user->lastip;
    }

    public function getUserId(){
        return $this->id;
    }

    public function isTeacher($context){
        if(is_primary_admin($this->id)){
            return true;
        }
        $roles = get_user_roles($context,$this->id);
        $role = [];
        foreach ($roles as $r){
            $role[] = $r->shortname;
        }
        if(in_array('teacher',$role)){
            return true;
        }else {
            return false;
        }
    }
}