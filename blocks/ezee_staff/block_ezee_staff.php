<?php

/**
 * Form for editing Ezee Staff instances.
 *
 * @package   block_ezee_staff
 * @copyright 2020, John Stainsby <john@ezeedigital.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_ezee_staff extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_ezee_staff');
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $DB, $USER, $PAGE;

        //Setup and display block
        if ($this->content !== NULL) {
            return $this->content;
        }

        //Get settings
        $showcourses = get_config('block_ezee_staff', 'showcourses');

        //Build content
        $content = '';
        if ($showcourses) {
            $courses = $DB->get_records('course');
            foreach ($courses as $course) {
                $content .= $course->fullname . '<br>';
            }
        }
        else {
            $users = $DB->get_records('user');
            foreach ($users as $user) {
                $content .= $user->firstname . ' ' . $user->lastname . '<br>';
            }
        }

        //Render content
        $this->content = new stdClass;
        $this->content->text = $content;
        $this->content->footer = '';
        return $this->content;
    }

}