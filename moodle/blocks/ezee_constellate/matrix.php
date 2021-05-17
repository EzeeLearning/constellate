<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Matrix view
 *
 * @package    block_ezee_constellate
 * @copyright  2021 John Stainsby
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
$PAGE->set_url(new moodle_url('/blokcs/ezee_constellate/matrix.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Ezee Constellate - Matrix View');
$PAGE->requires->jquery();

echo $OUTPUT->header();

//Check which users to show
$allstaff = get_config('block_ezee_constellate', 'staffmode');

//Setup query parameters
if ($allstaff) {
    $params = [
        'userid1' => -1,
        'userid2' => -1
    ];
} else {
    $params = [
        'userid1' => $USER->id,
        'userid2' => $USER->id
    ];
}

//Get list of courses
$sqlcourses = "SELECT id, shortname AS coursename FROM {course}";
$resultscourses = $DB->get_records_sql($sqlcourses, []);

//Get list of users
$sqlusers = "SELECT DISTINCT u.id, u.firstname AS FirstName , u.lastname AS LastName, CONCAT(u.firstname, ' ', u.lastname) AS DisplayName, u.email AS Email, CASE WHEN a.userid IS NOT NULL THEN 1 ELSE 0 END AS EnrolledOnCourse FROM {user} u LEFT OUTER JOIN (SELECT ra.userid, ctx.id AS contextid FROM {context} ctx JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = 50 JOIN {role_assignments} ra ON ra.contextid = ctx.id UNION SELECT ra.userid, ctx.id AS contextid FROM {context} ctx JOIN {course_categories} cat ON cat.id = ctx.instanceid AND ctx.contextlevel = 40 JOIN {course} c ON c.category = cat.id JOIN {role_assignments} ra ON ra.contextid = ctx.id) a ON a.userid = u.id WHERE :userid1 = -1 OR a.contextid IN (SELECT contextid FROM {role_assignments} WHERE userid = :userid2)";
$resultsusers = $DB->get_records_sql($sqlusers, $params);

//Get user course completions
$sqlcompletions = "SELECT CONCAT(u.id, a.courseid) AS id, u.id AS userid, u.firstname AS FirstName , u.lastname AS LastName, CONCAT(u.firstname, ' ', u.lastname) AS DisplayName, u.email AS Email, a.courseid,
CASE WHEN cc.timecompleted IS NOT NULL THEN 'green' WHEN MAX(l.timecreated) IS NOT NULL THEN 'orange' ELSE 'red' END AS StatusColour, cc.timecompleted FROM {user} u INNER JOIN (SELECT ra.userid, ctx.id AS contextid, c.id as courseid, c.shortname AS coursename FROM {context} ctx JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = 50 JOIN {role_assignments} ra ON ra.contextid = ctx.id UNION SELECT ra.userid, ctx.id AS contextid, c.id as courseid, c.shortname AS coursename FROM {context} ctx JOIN {course_categories} cat ON cat.id = ctx.instanceid AND ctx.contextlevel = 40 JOIN {course} c ON c.category = cat.id JOIN {role_assignments} ra ON ra.contextid = ctx.id) a ON a.userid = u.id LEFT OUTER JOIN {course_completions} cc ON cc.course = a.CourseId AND cc.userid = u.id LEFT OUTER JOIN mdl_logstore_standard_log l ON l.courseid = a.courseid AND l.userid = u.id AND l.action = 'viewed' AND l.target = 'course_module' WHERE :userid1 = -1 OR a.contextid IN (SELECT contextid FROM {role_assignments} WHERE userid = :userid2) GROUP BY u.id, u.firstname, u.lastname, u.email, a.courseid, cc.timecompleted";
$resultscompletions = $DB->get_records_sql($sqlcompletions, $params);


// Render content
$templatecontext = (object)[
    'manager' => $USER->firstname . ' ' . $USER->lastname,
    'profileurl' => new moodle_url('/user/profile.php'),
    'courseurl' => new moodle_url('/course/view.php'),
    'tableusers' => array_values($resultsusers),
    'tablecourses'=> array_values($resultscourses),
    'tablecompletions'=> array_values($resultscompletions)
];

echo $OUTPUT->render_from_template('block_ezee_constellate/matrix', $templatecontext);

echo $OUTPUT->footer();