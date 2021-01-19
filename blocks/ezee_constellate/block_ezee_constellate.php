<?php

/**
 * Form for editing Ezee Constellate instances.
 *
 * @package   block_ezee_constellate
 * @copyright 2020, John Stainsby <john@ezeedigital.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_ezee_constellate extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_ezee_constellate');
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $DB, $USER, $PAGE, $OUTPUT;

        //Prevent JS caching
        //$CFG->cachejs = false;

        //Add jquery and js files
        $PAGE->requires->jquery();
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_constellate/js/ezee_constellate.js'));
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_constellate/js/Chart.bundle.js'), true);

        //Summary totals
        $sqlsummary = "SELECT COUNT(DISTINCT ra.contextId) AS TotalCourses, COUNT(DISTINCT ra.id) AS AssignedCourses,
        SUM(CASE WHEN gi.grademax >= gg.rawgrade || gi.gradepass >= gg.rawgrade AND gg.rawgrade IS NOT NULL THEN 1 ELSE 0 END) AS CompletedCourses             
        FROM mdl_course AS c
        JOIN mdl_context AS ctx ON c.id = ctx.instanceid
        JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id
        JOIN mdl_user AS u ON u.id = ra.userid
        LEFT OUTER JOIN mdl_grade_grades AS gg ON gg.userid = u.id
        LEFT OUTER JOIN mdl_grade_items AS gi ON gi.id = gg.itemid AND gi.courseid = c.id
        WHERE ra.roleid = 5";
        $resultssummary = $DB->get_records_sql($sqlsummary, []);

        //Staff list for table
        $sqlstaff = "SELECT Id, FirstName, LastName, DisplayName, Email, AssignedCourses, CompletedCourses, ROUND((CompletedCourses / AssignedCourses) * 100) AS CompletionPercentage
        FROM (SELECT u.id, u.firstname AS FirstName , u.lastname AS LastName, CONCAT(u.firstname, ' ', u.lastname) AS DisplayName, u.email AS Email, COUNT(DISTINCT ra.id) AS AssignedCourses,
        SUM(CASE  WHEN gi.grademax >= gg.rawgrade || gi.gradepass >= gg.rawgrade AND gg.rawgrade IS NOT NULL THEN 1 ELSE 0 END) AS CompletedCourses
        FROM mdl_course AS c
        JOIN mdl_context AS ctx ON c.id = ctx.instanceid
        JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id
        JOIN mdl_user AS u ON u.id = ra.userid
        LEFT OUTER JOIN mdl_grade_grades AS gg ON gg.userid = u.id
        LEFT OUTER JOIN mdl_grade_items AS gi ON gi.id = gg.itemid AND gi.courseid = c.id
        WHERE ra.roleid = 5 
        GROUP BY u.id) s
        ORDER BY CompletionPercentage DESC, LastName";
        $params = [ 'userid' => $USER->id ];
        $resultsstaff = $DB->get_records_sql($sqlstaff, $params);
        $resultstaffJSON = json_encode(array_values($resultsstaff));

        //Pass variables to js file
        $PAGE->requires->js_init_call('loadPercentageGraph', $resultssummary);
        $PAGE->requires->js_init_call('loadUserGraph', array($resultstaffJSON));
        $PAGE->requires->js_init_call('loadDateGraph', array($resultstaffJSON));

        //Setup and display block
        if ($this->content !== NULL) {
            return $this->content;
        }

        //Get settings
        $showactivity = get_config('block_ezee_constellate', 'showactivity');
        $graphDisplay = "hidden";
        $tableDisplay = "";

        //Build content
        if ($showactivity) {
            $graphDisplay = "";
            $tableDisplay = "hidden";
        }

        $staffCount = count($resultsstaff);
        $courses = array_values($resultssummary)[0];

        //Render content
        $templatecontext = (object)[
            'manager' => $USER->firstname . ' ' . $USER->lastname,
            'staffcount' => $staffCount,
            'coursetotal' => $courses->totalcourses,
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