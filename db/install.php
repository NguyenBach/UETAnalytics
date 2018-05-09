<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 19/04/2017
 * Time: 20:25
 */
defined('MOODLE_INTERNAL') || die;

function xmldb_uetanalytics_install() {
    global $CFG,$DB;
    $columns = $DB->get_columns('uet_dataset');
    $columns = array_keys($columns);
    $file = fopen($CFG->dirroot.'/mod/uetanalytics/backend/trainingdata.csv', 'r');
    while (($data = fgetcsv($file)) !== FALSE) {
        $row = new stdClass();
        foreach ($columns as $index => $column){
            $row->$column = doubleval($data[$index]);
        }
        $DB->insert_record('uet_dataset', $row);
    }
    fclose($file);
    mkdir('../mod/uetanalytics/backend/model',0777);
    $command = 'cd '.$CFG->dirroot.'/mod/uetanalytics/backend ; python3 model.py  2>&1';
    shell_exec($command);
}