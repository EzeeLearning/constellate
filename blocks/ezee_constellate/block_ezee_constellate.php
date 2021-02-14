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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
 
/**
 * This is a one-line short description of the file.
 *
 * @package    block_ezee_constellate
 * @copyright  2021 John Stainsby
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        
        // Get information from database
        $db_query = new db_query;
        $orderid = get_config('block_ezee_constellate', 'orderid');
        $init_config = $db_query->config($orderid);
        $output;
        
        if ($init_config === true) {
            // Add jquery and js files
            $PAGE->requires->jquery();
            $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_constellate/js/Chart.bundle.min.js'), true);
            $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_constellate/js/ezee_constellate.js'));

            $planMode = get_config('block_ezee_constellate', 'learningplan');
            $staffMode = get_config('block_ezee_constellate', 'staffmode');
    
            $resultssummary = $db_query->dashboardTotals($staffMode);
            $resultvalues = array_values($resultssummary)[0];
    
            $resultsstaff = $db_query->staffList($planMode, $staffMode);
    
            $resultscourses = $db_query->courseList($staffMode);
            $coursesJSON = json_encode(array_values($resultscourses));
    
            $resultsactivity = $db_query->activityDates();
            $activityJSON = json_encode(array_values($resultsactivity));
    
            // Pass variables to js file
            $PAGE->requires->js_init_call('loadPercentageGraph', $resultssummary);
            $PAGE->requires->js_init_call('loadCourseGraph', array($coursesJSON));
            $PAGE->requires->js_init_call('loadDateGraph', array($activityJSON));
    
            // Notifications
            $percentage = $resultvalues->completionpercentage > 100 ? 100 : $resultvalues->completionpercentage;
            $type;
            $message;
            if ($percentage == 0) {
                $type = \core\output\notification::NOTIFY_ERROR;
                $message = "Staff completion percentage is " . $percentage . "%. " . get_string('staff_zero', 'block_ezee_constellate');
            }
            elseif ($percentage > 0 && $percentage < 50) {
                $type = \core\output\notification::NOTIFY_WARNING;
                $message = "Staff completion percentage is " . $percentage . "%. " . get_string('staff_low', 'block_ezee_constellate');
            }
            elseif ($percentage >= 50 && $percentage < 80) {
                $type = \core\output\notification::NOTIFY_INFO;
                $message = "Staff completion percentage is " . $percentage . "%. " . get_string('staff_medium', 'block_ezee_constellate');
            }
            else {
                $type = \core\output\notification::NOTIFY_SUCCESS;
                $message = "Staff completion percentage is " . $percentage . "%. " . get_string('staff_high', 'block_ezee_constellate');
            }
            \core\notification::add($message, $type);
    
            // Check site admin settings
            $showactivity = get_config('block_ezee_constellate', 'showactivity');
            $graphDisplay = $showactivity ? "visible" : "hidden";
            $tableDisplay = $showactivity ? "hidden" : "visible";
    
            // Render content
            $templatecontext = (object)[
                'manager' => $USER->firstname . ' ' . $USER->lastname,
                'staffcount' => count($resultsstaff),
                'coursetotal' => array_values($resultssummary)[0]->totalcourses,
                'learningplans' => array_values($resultssummary)[0]->learningplans,
                'tableusers' => array_values($resultsstaff),
                'profileurl' => new moodle_url('/user/profile.php'),
                'planurl' => new moodle_url('/admin/tool/lp/plans.php'),
                'graphdisplay' => $graphDisplay,
                'tabledisplay' => $tableDisplay
            ];
    
            $output = $OUTPUT->render_from_template('block_ezee_constellate/dashboard', $templatecontext);
    
            $planMode = get_config('block_ezee_constellate', 'learningplan');
            if ($planMode) {
                $output .= $OUTPUT->render_from_template('block_ezee_constellate/stafflearningplan', $templatecontext);
            }
            else {
                $output .= $OUTPUT->render_from_template('block_ezee_constellate/staffenrolled', $templatecontext);
            }
    
            $output .= $OUTPUT->render_from_template('block_ezee_constellate/footer', $templatecontext);
        }
        else {
            $templatecontext = (object)[
                'manager' => $USER->firstname . ' ' . $USER->lastname,
                'logourl' => new moodle_url('/blocks/ezee_constellate/constellate.png'),
            ];
            $output = $OUTPUT->render_from_template('block_ezee_constellate/info', $templatecontext);
        }
        
        $this->content = new stdClass;
        $this->content->text = $output;
        return $this->content;
    }
}