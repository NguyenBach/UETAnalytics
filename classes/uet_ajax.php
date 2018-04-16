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
            case 'messageinfo': $result = $this->messageInfo($params['from'],$params['to']); break;
            case 'sendmessage':
                $result = $this->sendMessage($params);
        }
        return $result;
    }

    public function messageInfo($from,$to){
        $from = new uet_user($from);
        $to = new uet_user($to);
        return json_encode(['from'=>$from->getName(),'to'=>$to->getName()]);
    }

    public function studentinfo($studentid){

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
}