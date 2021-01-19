<?php

class db_query {

    //Assigned staff and course totals for dashboard
    public function dashboardTotals() {
        global $DB;

        $sqlsummary = "SELECT COUNT(DISTINCT ra.contextId) AS TotalCourses, COUNT(DISTINCT ra.id) AS AssignedCourses,
        SUM(CASE WHEN gi.grademax >= gg.rawgrade || gi.gradepass >= gg.rawgrade AND gg.rawgrade IS NOT NULL THEN 1 ELSE 0 END) AS CompletedCourses             
        FROM mdl_course AS c
        JOIN mdl_context AS ctx ON c.id = ctx.instanceid
        JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id
        JOIN mdl_user AS u ON u.id = ra.userid
        LEFT OUTER JOIN mdl_grade_grades AS gg ON gg.userid = u.id
        LEFT OUTER JOIN mdl_grade_items AS gi ON gi.id = gg.itemid AND gi.courseid = c.id
        WHERE ra.roleid = 5";
        return $DB->get_records_sql($sqlsummary, []);
    }

    //Staff list for table
    public function staffList() {
        global $DB;
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

        //With query paramaters
        // $params = [ 'userid' => $USER->id ];
        //return $DB->get_records_sql($sqlstaff, $params);

        return $DB->get_records_sql($sqlstaff, []);
    }

    //Activity dates for chart
    function activityDates() {
        global $DB;
        
        $DB->execute("CREATE TEMPORARY TABLE temp_dates (Months INT, Logins INT NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (1, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (2, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (3, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (4, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (5, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (6, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (7, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (8, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (9, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (10, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (11, NULL)", []);
        $DB->execute("INSERT INTO temp_dates VALUES (12, NULL)", []);
        
        $DB->execute("UPDATE temp_dates SET Logins = (SELECT COUNT(*) FROM mdl_user WHERE FROM_UNIXTIME(lastlogin) > DATE_SUB(NOW(), INTERVAL 1 YEAR) AND MONTH(FROM_UNIXTIME(lastlogin)) = temp_dates.Months)", []);
        
        $results = $DB->get_records_sql("SELECT * FROM temp_dates", []);

        $DB->execute("DROP TEMPORARY TABLE temp_dates", []);

        return $results;
    }
    
}