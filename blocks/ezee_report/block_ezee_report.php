<?php

/**
 * Form for editing Ezee Report instances.
 *
 * @package   block_ezee_report
 * @copyright 2020, John Stainsby <john@ezeedigital.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_ezee_report extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_ezee_report');
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $DB, $USER, $PAGE;

        //Prevent JS caching
        //$CFG->cachejs = false;

        //Add jquery and js files
        $PAGE->requires->jquery();
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_report/js/ezee_report.js'));
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_report/js/Chart.bundle.js'), true);

        //Summary totals
        $sqlsummary = "SELECT COUNT(DISTINCT ra.userid) AS TotalCourses,
        SUM(CASE 
        WHEN gi.grademax >= gg.rawgrade || gi.gradepass >= gg.rawgrade AND gg.rawgrade IS NOT NULL
            THEN 1
        ELSE 0
            END) AS CompletedCourses             
        FROM mdl_course AS c
        JOIN mdl_context AS ctx ON c.id = ctx.instanceid
        JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id
        JOIN mdl_user AS u ON u.id = ra.userid
        LEFT OUTER JOIN mdl_grade_grades AS gg ON gg.userid = u.id
        LEFT OUTER JOIN mdl_grade_items AS gi ON gi.id = gg.itemid AND gi.courseid = c.id
        WHERE ra.roleid = 5     
        ORDER BY TotalCourses";
        $resultssummary = $DB->get_records_sql($sqlsummary, []);

        //Build data object for charts (individual users)
        $sqluser = "SELECT u.id, u.firstname AS FirstName , u.lastname AS LastName, CONCAT(u.firstname, ' ', u.lastname) AS DisplayName,
        COUNT(c.Id) AS TotalCourses,
        SUM(CASE 
          WHEN gi.grademax >= gg.rawgrade || gi.gradepass >= gg.rawgrade AND gg.rawgrade IS NOT NULL
           THEN 1
          ELSE 0
        END) AS CompletedCourses
        FROM mdl_course AS c
        JOIN mdl_context AS ctx ON c.id = ctx.instanceid
        JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id
        JOIN mdl_user AS u ON u.id = ra.userid
        LEFT OUTER JOIN mdl_grade_grades AS gg ON gg.userid = u.id
        LEFT OUTER JOIN mdl_grade_items AS gi ON gi.id = gg.itemid AND gi.courseid = c.id
        WHERE ra.roleid = 5 
        GROUP BY u.id      
        ORDER BY LastName";
        $params = [
            'userid' => $USER->id
        ];
        $resultsuser = $DB->get_records_sql($sqluser, $params);
        $resultsuserJSON = json_encode(array_values($resultsuser));

        //Pass variables to js file
        $PAGE->requires->js_init_call('loadSummaryGraph', $resultssummary);
        $PAGE->requires->js_init_call('loadUserGraph', array($resultsuserJSON));


        //Setup and display block
        if ($this->content !== NULL) {
            return $this->content;
        }

        //Get settings
        $showcourses = get_config('block_ezee_report', 'showcourses');

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

        $staffCount = count($resultsuser);
        $courses = array_values($resultssummary)[0];

        //Render content
        $this->content = new stdClass;
        $this->content->text = '
        <h2>Manager: John Stainsby</h2>
        <h3>Staff: ' . $staffCount . '</h3>
        <h3>Assigned Courses: ' . $courses->totalcourses . '</h3>
        <hr>
        <br>
            <div style="width:50%;display:inline-block">
                <canvas id="SummaryChart"></canvas>
            </div>
            <div style="width:50%;float:right">
                <canvas id="UserChart"></canvas>
            </div>
            <div style="margin-top:50px;">
                <canvas id="TestChart"></canvas>
            </div>
        ';
        $this->content->footer = '';
        return $this->content;
    }

}