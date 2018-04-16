<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 19/04/2017
 * Time: 19:47
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_uetanalytics_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB,$PAGE;
        $mform = $this->_form;
        // General --------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $label = "Name: ";
        $mform->addElement('text', 'name', $label, array('size' => '64','value'=>"UET Learning Analytics"));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements("Introduction:");

        $mform->addElement('text', 'name', $label, array('size' => '64','value'=>"UET Learning Analytics"));


        $this->standard_coursemodule_elements();

        $this->add_action_buttons();

    }


}

