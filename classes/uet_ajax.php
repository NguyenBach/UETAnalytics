<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 15/04/2018
 * Time: 21:59
 */

namespace mod_uetanalytics;

class uet_ajax
{
    public function ajax($action,$params){
        switch ($action){
            case 'messageinfo':
                $result = $this->messageInfo($params['from'],$params['to']); break;
            case 'sendmessage':
                $result = $this->sendMessage($params); break;
            case 'notify':
                $result = $this->notify($params); break;
            case 'student':
                $result = $this->student($params);
        }
        return $result;
    }

    public function messageInfo($from,$to){
        $from = new uet_user($from);
        $to = new uet_user($to);
        return json_encode(['from'=>$from->getName(),'to'=>$to->getName()]);
    }

    public function student($params){
        $student = new uet_student($params['studentid'],$params['courseid']);
        $student->setupStudent();
        return json_encode($student->toArray());
    }

    public function sendMessage($params){
        global $DB;
        $from = $DB->get_record('user',['id'=>$params['from']]);
        $to = $DB->get_record('user',['id'=>$params['to']]);
        $message = new \core\message\message();
        $message->component = 'moodle';
        $message->name = 'instantmessage';
        $message->userfrom = $from;
        $message->userto = $to;
        $message->subject = $params['msgsubject'];
        $message->fullmessage = $params['msgmessage'];
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = $params['msgmessage'];
        $message->notification = '0';
        $message->courseid = $params['courseid'];
        $messageid = message_send($message);
        return json_encode(['sent'=>$messageid]);
    }

    public function notify($params){
        global $DB;
        $now = time();
        $params['courseid'] = $params['courseid'];
        $params['userid'] = $params['to'];
        $params['timeend'] = $now;
        $notifications = $DB->get_record_sql('SELECT * FROM {uet_notification} WHERE courseid =:courseid AND userid=:userid AND timeend >= :timeend AND status = 1', $params);
        if($notifications){
            $notifications->status = 0;
            $DB->update_record('uet_notification',$notifications);
        }
        $notification = new \stdClass();
        $notification->userid = $params['to'];
        $notification->courseid = $params['courseid'];
        $notification->notification = $params['notification'];
        $notification->status = 1;
        $notification->timeend = time() + 7*24*3600;
        $id = $DB->insert_record('uet_notification',$notification);
        return json_encode(['sent'=>$id]);
    }
}