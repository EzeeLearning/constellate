<?php

require_once("$CFG->libdir/formslib.php");
 
class edit extends moodleform {

    //Add elements to form
    public function definition() {
        global $CFG;
        $mform = $this->_form;
 
        $mform->addElement('text', 'messagetext', 'Message text');
        $mform->setType('messagetext', PARAM_NOTAGS);
        $mform->setDefault('messagetext', 'Please enter message text');

        $choices = array(
            0 => get_string(core\output\notification::NOTIFY_INFO),
            1 => get_string(core\output\notification::NOTIFY_WARNING),
            2 => get_string(core\output\notification::NOTIFY_ERROR),
            3 => get_string(core\output\notification::NOTIFY_SUCCESS)
        );
        $mform->addElement('select', 'messagetype', 'Message type', $choices);
        $mform->setDefault('messagetype', 3);

        $this->add_action_buttons();
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

}