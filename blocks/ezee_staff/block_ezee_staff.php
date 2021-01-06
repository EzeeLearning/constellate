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
        global $CFG, $DB, $USER, $PAGE, $OUTPUT;

        //Setup and display block
        if ($this->content !== NULL) {
            return $this->content;
        }

        //Get settings
        $showcourses = get_config('block_ezee_staff', 'showcourses');

        //Build content
        if ($showcourses) {
            $courses = $DB->get_records('course');
            foreach ($courses as $course) {
            }
        }
        else {
            $sqlstaff = "SELECT Id, FirstName, LastName, DisplayName, Email, AssignedCourses, CompletedCourses, ROUND((CompletedCourses / AssignedCourses) * 100) AS CompletionPercentage
            FROM (SELECT u.id, u.firstname AS FirstName , u.lastname AS LastName, CONCAT(u.firstname, ' ', u.lastname) AS DisplayName, u.email AS Email,
            COUNT(c.Id) AS AssignedCourses,
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
            GROUP BY u.id) s
            ORDER BY CompletionPercentage DESC, LastName";

            $resultsstaff = $DB->get_records_sql($sqlstaff, []);
        }


        //Render content
        $templatecontext = (object)[
            'users' => array_values($resultsstaff),
            'profileurl' => new moodle_url('/user/profile.php')
        ];
        
        $this->content = new stdClass;
        $this->content->text = $OUTPUT->render_from_template('block_ezee_staff/stafflist', $templatecontext);
        return $this->content;
    }

}