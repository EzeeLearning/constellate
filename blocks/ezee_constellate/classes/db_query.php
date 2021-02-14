<?php

/**
 * Ezee Constellate dashboard
 *
 * @package   block_ezee_constellate
 * @copyright 2021, John Stainsby <john@ezeedigital.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class db_query {

    //Assigned staff and course totals for dashboard
    public function dashboardTotals($allStaff) {
        global $DB, $USER;
        $params;

        //Set parameter for current userid
        if ($allStaff) {
            $params = [
                'userid1' => -1,
                'userid2' => -1,
                'userid3' => -1,
                'userid4' => -1
            ];
        }
        else {
            $params = [
                'userid1' => $USER->id,
                'userid2' => $USER->id,
                'userid3' => $USER->id,
                'userid4' => $USER->id
            ];
        }

        $sql = 
        "SELECT TotalCourses, AssignedCourses, CompletedCourses, ROUND((CompletedCourses / AssignedCourses) * 100) AS CompletionPercentage, LearningPlans
        FROM (
            SELECT COUNT(DISTINCT CourseId) AS TotalCourses, COUNT(CourseId) AS AssignedCourses,
            SUM(CASE WHEN timecompleted IS NOT NULL THEN 1 ELSE 0 END) AS CompletedCourses,
            COUNT(DISTINCT PlanId) AS LearningPlans
            FROM (
                SELECT a.UserId, a.CourseId, cc.timecompleted, cp.id AS PlanId
                FROM (
                    SELECT u.id AS UserId, c.Id AS CourseId
                    FROM mdl_user u
                    JOIN mdl_role_assignments ra ON ra.userid = u.id
                    JOIN mdl_context ctx ON ctx.id = ra.contextid
                    JOIN mdl_course c ON c.id = ctx.instanceid AND ctx.contextlevel = 50   
                    WHERE :userid1 = -1 OR ra.contextid IN (SELECT contextid FROM mdl_role_assignments WHERE userid = :userid2) 
                    UNION  
                    SELECT u.id AS UserId, c.Id AS CourseId
                    FROM mdl_user u
                    JOIN mdl_role_assignments ra ON ra.userid = u.id
                    JOIN mdl_context ctx ON ctx.id = ra.contextid
                    JOIN mdl_course_categories cat ON cat.id = ctx.instanceid AND ctx.contextlevel = 40
                    JOIN mdl_course c ON c.category = cat.id
                    WHERE :userid3 = -1 OR ra.contextid IN (SELECT contextid FROM mdl_role_assignments WHERE userid = :userid4) 
                ) a
                LEFT OUTER JOIN mdl_course_completions cc ON cc.course = a.CourseId AND cc.userid = a.UserId
                LEFT OUTER JOIN mdl_competency_plan cp ON cp.userid = a.UserId
                GROUP BY a.UserId, a.CourseId
            ) g
        ) s";
        
        return $DB->get_records_sql($sql, $params);
    }


    //Staff list for table
    public function staffList($learningPlan, $allStaff) {
        global $DB, $USER;
        $sql;
        $params;

        if ($allStaff) {
            $params = [
                'userid1' => -1,
                'userid2' => -1
            ];
        }
        else {
            $params = [
                'userid1' => $USER->id,
                'userid2' => $USER->id
            ];
        }

        if ($learningPlan) {
            $sql = 
            "SELECT Id, FirstName, LastName, DisplayName, Email, IFNULL(LearningPlan, 'No Learning Plan') AS LearningPlan, PlanCompetencies, CompletedCompetencies, 
            (CASE WHEN PlanCompetencies = 0 THEN 0 ELSE ROUND((CompletedCompetencies / PlanCompetencies) * 100) END) AS CompletionPercentage, LinkedCourses, CompletedCourses,
            (CASE WHEN PlanCompetencies = 0 OR ROUND((CompletedCompetencies / PlanCompetencies) * 100) BETWEEN 0 AND 50 THEN 'red' ElSE (CASE WHEN ROUND((CompletedCompetencies / PlanCompetencies) * 100) BETWEEN 50 AND 80 THEN 'orange' ElSE 'green' END) END) AS ProgressClass, Logins, CourseViews, ModuleViews
            FROM (
                SELECT u.id AS Id, u.firstname AS FirstName , u.lastname AS LastName, CONCAT(u.firstname, ' ', u.lastname) AS DisplayName, u.email AS Email, p.name AS LearningPlan, COUNT(DISTINCT pc.id) AS PlanCompetencies, IFNULL(ur.Completed, 0) AS CompletedCompetencies,
                COUNT(DISTINCT cc.id) AS LinkedCourses,
                COUNT(DISTINCT CASE WHEN ucc.proficiency = 1 THEN ucc.id ELSE 0 END) AS CompletedCourses,
                SUM(CASE WHEN l.action = 'loggedin' AND l.target = 'user' THEN 1 ELSE 0 END) AS Logins,
                SUM(CASE WHEN l.action = 'viewed' AND l.target = 'course' THEN 1 ELSE 0 END) AS CourseViews,
                SUM(CASE WHEN l.action = 'viewed' AND l.target = 'course_module' THEN 1 ELSE 0 END) AS ModuleViews
                FROM mdl_user u
                LEFT OUTER JOIN mdl_competency_plan p ON p.userid = u.id
                LEFT OUTER JOIN mdl_competency_plancomp pc ON pc.planid = p.id
                LEFT OUTER JOIN (
                    SELECT up.userid, up.planid, COUNT(uc.id) AS Completed
                    FROM mdl_competency_usercomp uc
                    JOIN mdl_competency_usercompplan up ON up.userid = uc.userid AND up.competencyid = uc.competencyid
                    WHERE uc.proficiency = 1
                    GROUP BY up.userid, up.planid
                ) ur ON ur.userid = u.id AND ur.planid = p.id
                LEFT OUTER JOIN mdl_competency_coursecomp cc ON cc.competencyid = pc.competencyid
                LEFT OUTER JOIN mdl_competency_usercompcourse ucc ON ucc.userid = u.id AND ucc.competencyid = pc.competencyid AND ucc.courseid = cc.courseid
                LEFT OUTER JOIN mdl_logstore_standard_log l ON l.userid = u.id
                GROUP BY u.id, p.id
            ) c
            ORDER BY CompletionPercentage DESC, CourseViews DESC, ModuleViews DESC, Logins DESC, LastName";
        }
        else {
            $sql = 
            "SELECT s.Id, FirstName, LastName, DisplayName, Email, AssignedCourses, CompletedCourses,
            ROUND((CompletedCourses / AssignedCourses) * 100) AS CompletionPercentage,
            (CASE WHEN ROUND((CompletedCourses / AssignedCourses) * 100) BETWEEN 0 AND 50 THEN 'red' ElSE (CASE WHEN ROUND((CompletedCourses / AssignedCourses) * 100) BETWEEN 50 AND 80 THEN 'orange' ElSE 'green' END) END) AS ProgressClass,
            SUM(CASE WHEN l.action = 'loggedin' AND l.target = 'user' THEN 1 ELSE 0 END) AS Logins,
            SUM(CASE WHEN l.action = 'viewed' AND l.target = 'course' THEN 1 ELSE 0 END) AS CourseViews,
            SUM(CASE WHEN l.action = 'viewed' AND l.target = 'course_module' THEN 1 ELSE 0 END) AS ModuleViews
            FROM (
                SELECT u.id, u.firstname AS FirstName , u.lastname AS LastName, CONCAT(u.firstname, ' ', u.lastname) AS DisplayName, u.email AS Email,
                COUNT(DISTINCT a.CourseId) AS AssignedCourses,
                SUM(CASE WHEN cc.timecompleted IS NOT NULL THEN 1 ELSE 0 END) AS CompletedCourses
                FROM (
                    SELECT c.Id AS CourseId, ctx.Id AS ContextId
                    FROM mdl_context ctx
                    JOIN mdl_course c ON c.id = ctx.instanceid AND ctx.contextlevel = 50   
                    UNION  
                    SELECT c.Id AS CourseId, ctx.Id AS ContextId
                    FROM mdl_context ctx
                    JOIN mdl_course_categories cat ON cat.id = ctx.instanceid AND ctx.contextlevel = 40
                    JOIN mdl_course c ON c.category = cat.id
                ) a
                JOIN mdl_role_assignments ra ON ra.contextid = a.ContextId
                JOIN mdl_user u ON u.id = ra.userid
                LEFT OUTER JOIN mdl_course_completions cc ON cc.course = a.CourseId AND cc.userid = u.id
                WHERE :userid1 = -1 OR ra.contextid IN (SELECT contextid FROM mdl_role_assignments WHERE userid = :userid2) 
                GROUP BY u.id
            ) s
            LEFT OUTER JOIN mdl_logstore_standard_log l ON l.userid = s.id
            GROUP BY s.Id
            ORDER BY CompletionPercentage DESC, CourseViews DESC, ModuleViews DESC, Logins DESC, LastName";
        }

        return $DB->get_records_sql($sql, $params);
    }


    //Course list for chart
    public function courseList($allStaff) {
        global $DB, $USER;
        $params;

        if ($allStaff) {
            $params = [
                'userid1' => -1,
                'userid2' => -1
            ];
        }
        else {
            $params = [
                'userid1' => $USER->id,
                'userid2' => $USER->id
            ];
        }

        $sql = 
        "SELECT CourseName, COUNT(DISTINCT ra.userid) AS Users
        FROM (
            SELECT c.Id AS CourseId, c.shortname AS CourseName, ctx.Id AS ContextId
            FROM mdl_context ctx
            JOIN mdl_course c ON c.id = ctx.instanceid AND ctx.contextlevel = 50   
            UNION  
            SELECT c.Id AS CourseId, c.shortname AS CourseName, ctx.Id AS ContextId
            FROM mdl_context ctx
            JOIN mdl_course_categories cat ON cat.id = ctx.instanceid AND ctx.contextlevel = 40
            JOIN mdl_course c ON c.category = cat.id           
        ) a
        JOIN mdl_role_assignments ra ON ra.contextid = a.ContextId
        WHERE :userid1 = -1 OR ra.contextid IN (SELECT contextid FROM mdl_role_assignments WHERE userid = :userid2) 
        GROUP BY CourseId
        ORDER BY Users DESC
        LIMIT 10";

        return $DB->get_records_sql($sql, $params);
    }


    //Activity dates for chart
    function activityDates() {
        global $DB;
        
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
        
        $DB->execute("UPDATE temp_dates SET Logins = (SELECT COUNT(*) FROM mdl_user WHERE FROM_UNIXTIME(lastlogin) > DATE_SUB(NOW(), INTERVAL 1 YEAR) AND MONTH(FROM_UNIXTIME(lastlogin)) = temp_dates.`Month`)", []);
        
        $DB->execute("UPDATE temp_dates SET Accesses = (SELECT COUNT(*) FROM mdl_user WHERE FROM_UNIXTIME(lastaccess) > DATE_SUB(NOW(), INTERVAL 1 YEAR) AND MONTH(FROM_UNIXTIME(lastaccess)) = temp_dates.`Month`)", []);

        $results = $DB->get_records_sql("SELECT * FROM temp_dates", []);

        $DB->execute("DROP TEMPORARY TABLE temp_dates", []);

        return $results;
    }
    

    function config($orderid) {
        global $DB;

        $sql = "SELECT (CASE WHEN CURDATE() < DATE_ADD(timeinitial, INTERVAL duration DAY) THEN 1 ELSE 0 END) AS tt FROM mdl_blocks_ezee_constellate";
        $init = $DB->get_records_sql($sql, []);

        if ($init) {
            $tr = array_values($init)[0]->tt;
            if ($tr == 1) {
                return true;
            }
            else {
                if ($orderid) {
                    $url = "https://auth.ezeeconstellate.co.uk";
                    $data = array('orderid' => $orderid);   
                    $options = array(
                        'http' => array(
                            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method'  => 'POST',
                            'content' => http_build_query($data)
                        )
                    );
                    $context  = stream_context_create($options);
                    $result = file_get_contents($url, false, $context);
                    if ($result === FALSE) { 
                        return false;
                    }
                    else {
                        $obj = json_decode($result);
                        if ($obj->orderstatus == "FULFILLED")
                            return true;
                        else
                            return false;
                    }
                }
                else {
                    return false;
                }
                
            }
        }
        else {
            $DB->execute("INSERT INTO mdl_blocks_ezee_constellate VALUES (CURDATE(), 14)", []);         
            return true;;
        }
    }
}