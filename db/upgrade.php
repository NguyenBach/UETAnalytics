<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 13/04/2018
 * Time: 09:52
 */

function xmldb_uetanalytics_upgrade($oldversion) {
    global $DB,$CFG;
    $dbman = $DB->get_manager();
    $file = $CFG->dirroot.'/mod/uetanalytics/db/install.xml';
    if ($oldversion < 2018041302) {
        $dbman->install_one_table_from_xmldb_file($file,'uet_grade');
    }
    if ($oldversion < 2018041600) {
        $dbman->install_one_table_from_xmldb_file($file,'uet_notification');
    }
    return true;
}
