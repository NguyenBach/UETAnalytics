<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 13/04/2018
 * Time: 14:57
 */

require_once('autoload.php');
require_once($CFG->libdir . '/formslib.php');

class import_grade_form extends moodleform
{
    private $course;

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, array $ajaxformdata = null, $courseid = 0)
    {
        $this->course = $courseid;
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    protected function definition()
    {
        global $CFG;
        $mform = $this->_form;
        $mform->addElement('hidden', 'courseid', $this->course);
        $mform->addElement('filepicker', 'file', "Grade file", null,
            array('maxbytes' => 10000, 'accepted_types' => ['xls', 'xlsx']));
        $this->add_action_buttons();
    }
}
