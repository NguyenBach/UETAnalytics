<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 13/04/2018
 * Time: 15:03
 */
require_once('autoload.php');
use mod_uetanalytics\uet_analytics;
global $CFG;
require_login();

$course =  optional_param('course', 0, PARAM_INT);    // Course Module ID, or
$id = optional_param('mod', 0, PARAM_INT);    // Course Module ID, or

$PAGE->set_title("UET Import Grade");
$uet = new uet_analytics($COURSE);
echo $OUTPUT->header();
echo '<h1>Import Grade</h1>';
$form = new import_grade_form( null, null,  'post',  '',  null,  true, null, $course);
if ($form->is_cancelled()) {

}
if ($form->is_submitted()) {
    $data = $form->get_submitted_data();
    var_dump($data);
    $name = $form->get_new_filename('file');
    $fullpath = $CFG->dirroot . '/mod/uetanalytics/temp/' . $name;
    $success = $form->save_file('file', $fullpath, true);
    $uet->importGrade($data->courseid,$fullpath);
    echo 'ok';
}
$form->display();

echo $OUTPUT->footer();