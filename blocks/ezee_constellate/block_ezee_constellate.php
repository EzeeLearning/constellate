<?php

/**
 * Form for editing Ezee Constellate instances.
 *
 * @package   block_ezee_constellate
 * @copyright 2020, John Stainsby <john@ezeedigital.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include $CFG->dirroot . '/blocks/ezee_constellate/classes/db_query.php';

class block_ezee_constellate extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_ezee_constellate');
    }

    function has_config() {
        return true;
    }

    function get_content() {
        if ($this->content != null) {
            return $this->content;
        }

        global $CFG, $USER, $PAGE, $OUTPUT;

        //Add jquery and js files
        $PAGE->requires->jquery();
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_constellate/js/ezee_constellate.js'));
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_constellate/js/Chart.bundle.js'), true);

        //Get information from database
        $db_query = new db_query;
        $resultssummary = $db_query->dashboardTotals();
        $resultsstaff = $db_query->staffList();

        $resultscourses = $db_query->courseList();
        $coursesJSON = json_encode(array_values($resultscourses));

        $resultsactivity = $db_query->activityDates();
        $activityJSON = json_encode(array_values($resultsactivity));

        //Pass variables to js file
        $PAGE->requires->js_init_call('loadPercentageGraph', $resultssummary);
        $PAGE->requires->js_init_call('loadCourseGraph', array($coursesJSON));
        $PAGE->requires->js_init_call('loadDateGraph', array($activityJSON));

        //Setup and display block
        if ($this->content !== NULL) {
            return $this->content;
        }

        //Get settings
        $showactivity = get_config('block_ezee_constellate', 'showactivity');
        $graphDisplay = $showactivity ? "visible" : "hidden";
        $tableDisplay = $showactivity ? "hidden" : "visible";

        //Render content
        $templatecontext = (object)[
            'manager' => $USER->firstname . ' ' . $USER->lastname,
            'staffcount' => count($resultsstaff),
            'coursetotal' => array_values($resultssummary)[0]->totalcourses,
            'tableusers' => array_values($resultsstaff),
            'profileurl' => new moodle_url('/user/profile.php'),
            'logourl' => new moodle_url('/blocks/ezee_constellate/constellate.png'),
            'graphdisplay' => $graphDisplay,
            'tabledisplay' => $tableDisplay
        ];

        $this->content = new stdClass;
        $this->content->text = $OUTPUT->render_from_template('block_ezee_constellate/dashboard', $templatecontext);
        return $this->content;
    }
}