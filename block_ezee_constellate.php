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

require $CFG->dirroot . '/blocks/ezee_constellate/classes/ezee_constellate_db.php';

/**
 * Block plugin to show dashboad with course information on staff.
 *
 * @package   block_ezee_constellate
 * @copyright 2021 John Stainsby
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ezee_constellate extends block_base
{
    /**
     * Block init function
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_ezee_constellate');
    }

    /**
     * This line tells Moodle that the block has a settings.php file
     *
     * @return bool     True
     */
    public function has_config() {
        return true;
    }
    
    /**
     * Get block content
     */
    public function get_content() {
        if ($this->content != null) {
            return $this->content;
        }

        global $CFG, $USER, $OUTPUT;

        // Get information from database.
        $page = $this->page;
        $dbquery = new ezee_constellate_db;
        $email = get_config('block_ezee_constellate', 'email');
        $config = $dbquery->config($email);
        $output;

        if ($config === true) {
            // Add jquery and js files.
            $page->requires->jquery();
            $page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_constellate/js/Chart.bundle.min.js'), true);
            $page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/ezee_constellate/js/ezee_constellate.js'));

            $planmode = get_config('block_ezee_constellate', 'learningplan');
            $staffmode = get_config('block_ezee_constellate', 'staffmode');

            $resultssummary = $dbquery->dashboardtotals($staffmode);
            $resultvalues = array_values($resultssummary)[0];

            $resultsstaff = $dbquery->stafflist($planmode, $staffmode);

            $resultscourses = $dbquery->courselist($staffmode);
            $coursesjson = json_encode(array_values($resultscourses));

            $resultsactivity = $dbquery->activitydates($staffmode);
            $activityjson = json_encode(array_values($resultsactivity));

            // Pass variables to js file.
            $page->requires->js_init_call('loadPercentageGraph', $resultssummary);
            $page->requires->js_init_call('loadCourseGraph', array($coursesjson));
            $page->requires->js_init_call('loadDateGraph', array($activityjson));

            // Show notifications panel above dashboard with overall percentage.
            $percentage = $resultvalues->completionpercentage > 100 ? 100 : $resultvalues->completionpercentage;
            $type;
            $message;
            if ($percentage == 0) {
                $type = \core\output\notification::NOTIFY_ERROR;
                $message = get_string('staff_zero', 'block_ezee_constellate', $percentage);
            } else if ($percentage > 0 && $percentage < 50) {
                $type = \core\output\notification::NOTIFY_WARNING;
                $message = get_string('staff_low', 'block_ezee_constellate', $percentage);
            } else if ($percentage >= 50 && $percentage < 80) {
                $type = \core\output\notification::NOTIFY_INFO;
                $message = get_string('staff_medium', 'block_ezee_constellate', $percentage);
            } else {
                $type = \core\output\notification::NOTIFY_SUCCESS;
                $message = get_string('staff_high', 'block_ezee_constellate', $percentage);
            }
            \core\notification::add($message, $type);

            // Check site admin settings.
            $showactivity = get_config('block_ezee_constellate', 'showactivity');
            $graphdisplay = $showactivity ? "visible" : "hidden";
            $tabledisplay = $showactivity ? "hidden" : "visible";

            // Render content.
            $templatecontext = (object)[
                'manager' => $USER->firstname . ' ' . $USER->lastname,
                'staffcount' => count($resultsstaff),
                'coursetotal' => array_values($resultssummary)[0]->totalcourses,
                'learningplans' => array_values($resultssummary)[0]->learningplans,
                'tableusers' => array_values($resultsstaff),
                'profileurl' => new moodle_url('/user/profile.php'),
                'planurl' => new moodle_url('/admin/tool/lp/plans.php'),
                'graphdisplay' => $graphdisplay,
                'tabledisplay' => $tabledisplay
            ];

            $output = $OUTPUT->render_from_template('block_ezee_constellate/dashboard', $templatecontext);

            $planmode = get_config('block_ezee_constellate', 'learningplan');
            if ($planmode) {
                $output .= $OUTPUT->render_from_template('block_ezee_constellate/stafflearningplan', $templatecontext);
            } else {
                $output .= $OUTPUT->render_from_template('block_ezee_constellate/staffenrolled', $templatecontext);
            }

            $output .= $OUTPUT->render_from_template('block_ezee_constellate/footer', $templatecontext);
        } else {
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