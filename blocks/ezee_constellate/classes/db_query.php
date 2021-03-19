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
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Database queries for plugin
 *
 * @package    block_ezee_constellate
 * @copyright  2021 John Stainsby
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class db_query {

    /**
     * Get total number of courses, assigned courses and assigned learning plans for dashboard statistics
     *
     * @param bool      $allstaff Admin setting to switch between all staff and staff relating to a single user
     * @return array    List of courses and enrolments
     */
    public function dashboardtotals($allstaff) {
        global $DB, $USER;
        $params;

        if ($allstaff) {
            $params = [
                'userid1' => -1,
                'userid2' => -1,
                'userid3' => -1,
                'userid4' => -1
            ];
        } else {
            $params = [
                'userid1' => $USER->id,
                'userid2' => $USER->id,
                'userid3' => $USER->id,
                'userid4' => $USER->id
            ];
        }

        $sql = "SELECT TotalCourses, AssignedCourses, CompletedCourses, ROUND((CompletedCourses / AssignedCourses) * 100) AS CompletionPercentage, LearningPlans FROM (SELECT COUNT(DISTINCT CourseId) AS TotalCourses, COUNT(CourseId) AS AssignedCourses, SUM(CASE WHEN timecompleted IS NOT NULL THEN 1 ELSE 0 END) AS CompletedCourses, COUNT(DISTINCT PlanId) AS LearningPlans FROM (SELECT a.UserId, a.CourseId, cc.timecompleted, cp.id AS PlanId FROM ( SELECT u.id AS UserId, c.Id AS CourseId FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id JOIN {context} ctx ON ctx.id = ra.contextid JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = 50 WHERE :userid1 = -1 OR ra.contextid IN (SELECT contextid FROM {role_assignments} WHERE userid = :userid2) UNION SELECT u.id AS UserId, c.Id AS CourseId FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id JOIN {context} ctx ON ctx.id = ra.contextid JOIN {course_categories} cat ON cat.id = ctx.instanceid AND ctx.contextlevel = 40 JOIN {course} c ON c.category = cat.id WHERE :userid3 = -1 OR ra.contextid IN (SELECT contextid FROM {role_assignments} WHERE userid = :userid4)) a LEFT OUTER JOIN {course_completions} cc ON cc.course = a.CourseId AND cc.userid = a.UserId LEFT OUTER JOIN {competency_plan} cp ON cp.userid = a.UserId GROUP BY a.UserId, a.CourseId) g) s";

        return $DB->get_records_sql($sql, $params);
    }


    /**
     * Staff list including information and course or learning plan enrolments
     *
     * @param bool      $learningplan Whether to show learning plan information or course enrolments
     * @param bool      $allstaff Admin setting to switch between all staff and staff relating to a single user
     * @return array    List of user with learning plan or course enrolment information
     */
    public function stafflist($learningplan, $allstaff) {
        global $DB, $USER;
        $sql;
        $params;

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

        if ($learningplan) {
            $sql = "SELECT Id, FirstName, LastName, DisplayName, Email, IFNULL(LearningPlan, 'No Learning Plan') AS LearningPlan, PlanCompetencies, CompletedCompetencies, (CASE WHEN PlanCompetencies = 0 THEN 0 ELSE ROUND((CompletedCompetencies / PlanCompetencies) * 100) END) AS CompletionPercentage, LinkedCourses, CompletedCourses, (CASE WHEN PlanCompetencies = 0 OR ROUND((CompletedCompetencies / PlanCompetencies) * 100) BETWEEN 0 AND 50 THEN 'red' ElSE (CASE WHEN ROUND((CompletedCompetencies / PlanCompetencies) * 100) BETWEEN 50 AND 80 THEN 'orange' ElSE 'green' END) END) AS ProgressClass, Logins, CourseViews, ModuleViews FROM (SELECT u.id AS Id, u.firstname AS FirstName , u.lastname AS LastName, CONCAT(u.firstname, ' ', u.lastname) AS DisplayName, u.email AS Email, p.name AS LearningPlan, COUNT(DISTINCT pc.id) AS PlanCompetencies, IFNULL(ur.Completed, 0) AS CompletedCompetencies, COUNT(DISTINCT cc.id) AS LinkedCourses, COUNT(DISTINCT CASE WHEN ucc.proficiency = 1 THEN ucc.id ELSE 0 END) AS CompletedCourses, SUM(CASE WHEN l.action = 'loggedin' AND l.target = 'user' THEN 1 ELSE 0 END) AS Logins, SUM(CASE WHEN l.action = 'viewed' AND l.target = 'course' THEN 1 ELSE 0 END) AS CourseViews, SUM(CASE WHEN l.action = 'viewed' AND l.target = 'course_module' THEN 1 ELSE 0 END) AS ModuleViews FROM {user} u LEFT OUTER JOIN {competency_plan} p ON p.userid = u.id LEFT OUTER JOIN {competency_plancomp} pc ON pc.planid = p.id LEFT OUTER JOIN (SELECT up.userid, up.planid, COUNT(uc.id) AS Completed FROM {competency_usercomp} uc JOIN {competency_usercompplan} up ON up.userid = uc.userid AND up.competencyid = uc.competencyid WHERE uc.proficiency = 1 GROUP BY up.userid, up.planid) ur ON ur.userid = u.id AND ur.planid = p.id LEFT OUTER JOIN {competency_coursecomp} cc ON cc.competencyid = pc.competencyid LEFT OUTER JOIN {competency_usercompcourse} ucc ON ucc.userid = u.id AND ucc.competencyid = pc.competencyid AND ucc.courseid = cc.courseid LEFT OUTER JOIN {logstore_standard_log} l ON l.userid = u.id GROUP BY u.id, p.id) c ORDER BY CompletionPercentage DESC, CourseViews DESC, ModuleViews DESC, Logins DESC, LastName";
        } else {
            $sql = "SELECT s.Id, FirstName, LastName, DisplayName, Email, AssignedCourses, CompletedCourses, ROUND((CompletedCourses / AssignedCourses) * 100) AS CompletionPercentage, (CASE WHEN ROUND((CompletedCourses / AssignedCourses) * 100) BETWEEN 0 AND 50 THEN 'red' ElSE (CASE WHEN ROUND((CompletedCourses / AssignedCourses) * 100) BETWEEN 50 AND 80 THEN 'orange' ElSE 'green' END) END) AS ProgressClass, SUM(CASE WHEN l.action = 'loggedin' AND l.target = 'user' THEN 1 ELSE 0 END) AS Logins, SUM(CASE WHEN l.action = 'viewed' AND l.target = 'course' THEN 1 ELSE 0 END) AS CourseViews, SUM(CASE WHEN l.action = 'viewed' AND l.target = 'course_module' THEN 1 ELSE 0 END) AS ModuleViews FROM ( SELECT u.id, u.firstname AS FirstName , u.lastname AS LastName, CONCAT(u.firstname, ' ', u.lastname) AS DisplayName, u.email AS Email, COUNT(DISTINCT a.CourseId) AS AssignedCourses, SUM(CASE WHEN cc.timecompleted IS NOT NULL THEN 1 ELSE 0 END) AS CompletedCourses FROM (SELECT c.Id AS CourseId, ctx.Id AS ContextId FROM {context} ctx JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = 50 UNION SELECT c.Id AS CourseId, ctx.Id AS ContextId FROM {context} ctx JOIN {course_categories} cat ON cat.id = ctx.instanceid AND ctx.contextlevel = 40 JOIN {course} c ON c.category = cat.id) a JOIN {role_assignments} ra ON ra.contextid = a.ContextId JOIN {user} u ON u.id = ra.userid LEFT OUTER JOIN {course_completions} cc ON cc.course = a.CourseId AND cc.userid = u.id WHERE :userid1 = -1 OR ra.contextid IN (SELECT contextid FROM {role_assignments} WHERE userid = :userid2) GROUP BY u.id) s LEFT OUTER JOIN {logstore_standard_log} l ON l.userid = s.id GROUP BY s.Id ORDER BY CompletionPercentage DESC, CourseViews DESC, ModuleViews DESC, Logins DESC, LastName";
        }

        return $DB->get_records_sql($sql, $params);
    }


    /**
     * Get list of courses for course enrolments graph
     *
     * @param bool      $allstaff Admin setting to switch between all staff and staff relating to a single user
     * @return array    List of courses with enrolment numbers
     */
    public function courselist($allstaff) {
        global $DB, $USER;
        $params;

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

        $sql = "SELECT CourseName, COUNT(DISTINCT ra.userid) AS Users FROM (SELECT c.Id AS CourseId, c.shortname AS CourseName, ctx.Id AS ContextId FROM {context} ctx JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = 50 UNION SELECT c.Id AS CourseId, c.shortname AS CourseName, ctx.Id AS ContextId FROM {context} ctx JOIN {course_categories} cat ON cat.id = ctx.instanceid AND ctx.contextlevel = 40 JOIN {course} c ON c.category = cat.id) a JOIN {role_assignments} ra ON ra.contextid = a.ContextId WHERE :userid1 = -1 OR ra.contextid IN (SELECT contextid FROM {role_assignments} WHERE userid = :userid2) GROUP BY CourseId ORDER BY Users DESC LIMIT 10";

        return $DB->get_records_sql($sql, $params);
    }


    /**
     * Get monthly login and course activity stats for users
     *
     * @return array List of last 12 months with login and access totals
     */
    public function activitydates($allstaff) {
        global $DB, $USER;
        $params;

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

        $DB->execute("CREATE TEMPORARY TABLE temp_dates (`Month` INT, Logins INT NULL, Accesses INT NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (1, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (2, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (3, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (4, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (5, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (6, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (7, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (8, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (9, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (10, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (11, NULL, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (12, NULL, NULL)", []);

        $DB->execute("UPDATE temp_dates SET Logins = (SELECT COUNT(*) FROM {user} u JOIN {role_assignments} ra ON ra.userId = u.id WHERE (ra.ContextId IN (SELECT ContextId FROM {role_assignments} WHERE userid = :userid1) OR :userid2 = -1) AND FROM_UNIXTIME(lastlogin) > DATE_SUB(NOW(), INTERVAL 1 YEAR) AND MONTH(FROM_UNIXTIME(lastlogin)) = temp_dates.`Month`)", $params);

        $DB->execute("UPDATE temp_dates SET Accesses = (SELECT COUNT(*) FROM {user} u JOIN {role_assignments} ra ON ra.userId = u.id WHERE (ra.ContextId IN (SELECT ContextId FROM {role_assignments} WHERE userid = :userid1) OR :userid2 = -1) AND FROM_UNIXTIME(lastaccess) > DATE_SUB(NOW(), INTERVAL 1 YEAR) AND MONTH(FROM_UNIXTIME(lastaccess)) = temp_dates.`Month`)", $params);

        $results = $DB->get_records_sql("SELECT * FROM temp_dates", []);

        $DB->execute("DROP TEMPORARY TABLE temp_dates", []);

        return $results;
    }


    /**
     * Initialise plugin and check subscription
     *
     * @param string    $email Admin setting for subscription email
     * @return bool     Whether or not the plugin has an active subscription
     */
    public function config($email) {
        global $DB;

        $sql = "SELECT (CASE WHEN CURDATE() < DATE_ADD(timeinitial, INTERVAL duration DAY) THEN 1 ELSE 0 END) AS tt FROM {blocks_ezee_constellate}";
        $init = $DB->get_records_sql($sql, []);

        if ($init) {
            $tr = array_values($init)[0]->tt;
            if ($tr == 1) {
                return true;
            } else {
                if ($email) {
                    $url = "https://auth.ezeeconstellate.co.uk";
                    $data = array('email' => $email);
                    $options = array(
                        'http' => array(
                            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method'  => 'POST',
                            'content' => http_build_query($data)
                        )
                    );
                    $context  = stream_context_create($options);
                    $result = file_get_contents($url, false, $context);
                    if ($result === false) {
                        return false;
                    } else {
                        $obj = json_decode($result);
                        $status = $obj->subscription;
                        return $status;
                    }
                } else {
                    return false;
                }
            }
        } else {
            $DB->execute("INSERT INTO {blocks_ezee_constellate} VALUES (CURDATE(), 14)", []);
            return true;;
        }
    }
}