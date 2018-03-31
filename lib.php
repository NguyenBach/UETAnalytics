<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 19/04/2017
 * Time: 19:46
 */

function uetanalytics_add_instance($instance){
    global $DB;
    $instance->timecreated = time();
    $id = $DB->insert_record('uetanalytics',$instance);

    return $id;
}

function uetanalytics_update_instance($instance){

}

function uetanalytics_delete_instance($id){
    global $DB;

    return true;
}