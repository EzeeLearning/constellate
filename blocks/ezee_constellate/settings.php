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
 * Moodle admin settings for plugin
 *
 * @package    block_ezee_constellate
 * @copyright  2021 John Stainsby
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if ($ADMIN->fulltree) {

    // Choose whether to show staff list table or staff activity graph on the dashboard by default.
    $settings->add(new admin_setting_configcheckbox('block_ezee_constellate/showactivity',
        get_string('showactivity', 'block_ezee_constellate'),
        get_string('showactivitydesc', 'block_ezee_constellate'),
        0));

    // Display staff completion and statistics based on learning plans rather than assigned courses.
    $settings->add(new admin_setting_configcheckbox('block_ezee_constellate/learningplan',
        get_string('learningplan', 'block_ezee_constellate'),
        get_string('learningplandesc', 'block_ezee_constellate'),
        0));

    // Display all staff on the dashboard instead of just the current manager's staff.
    $settings->add(new admin_setting_configcheckbox('block_ezee_constellate/staffmode',
        get_string('staffmode', 'block_ezee_constellate'),
        get_string('staffmodedesc', 'block_ezee_constellate'),
        1));

    // Email address for subscription to plugin.
    $settings->add(new admin_setting_configtext('block_ezee_constellate/email',
        get_string('email', 'block_ezee_constellate'),
        get_string('emaildesc', 'block_ezee_constellate'), ''));
}