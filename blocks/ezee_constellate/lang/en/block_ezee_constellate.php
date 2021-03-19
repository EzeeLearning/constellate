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
 * Moodle language strings for plugin.
 *
 * @package    block_ezee_constellate
 * @copyright  2021 John Stainsby
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['ezee_constellate:addinstance'] = 'Add a new Ezee Constellate dashboard block';
$string['ezee_constellate:myaddinstance'] = 'Add a new Ezee Constellate block to the dashboard';
$string['ezee_constellate'] = '(new Ezee Constellate dashboard block)';
$string['pluginname'] = 'Ezee Constellate Dashboard';

$string['showactivity'] = 'Toggle Activity/Staff';
$string['showactivitydesc'] = 'Choose whether to show staff list table or staff activity graph on the dashboard by default.';

$string['learningplan'] = 'Align To Learning Plans';
$string['learningplandesc'] = 'Display staff completion and statistics based on learning plans rather than assigned courses.';

$string['staffmode'] = 'Show All Staff';
$string['staffmodedesc'] = 'Show all staff on the dashboard rather than just an individual manager\'s staff.';

$string['email'] = 'Order Email';
$string['emaildesc'] = 'Enter the email address used for your Ezee Constellate subscription to enable full access to the plugin beyond the 14 day free trial. This is the email you used when subscribing at www.ezeeconstellate.com';

$string['staff_zero'] = 'Staff completion percentage is {$a}%. None of your staff have completed courses, check your Constellate dashboard to improve your team\'s compliance.';
$string['staff_low'] = 'Staff completion percentage is {$a}%. Your team\'s course completion is low, check your Constellate dashboard to improve your team\'s compliance.';
$string['staff_medium'] = 'Staff completion percentage is {$a}%. Your team\'s compliance is improving, check your Constellate dashboard to keep it going.';
$string['staff_high'] = 'Staff completion percentage is {$a}%. Your team\'s compliance is good, keep checking your Constellate dashboard to maintain it.';

$string['privacy:metadata'] = 'The Ezee Constellate dashboard only displays existing data from within Moodle and does not transfer any user data to external systems.';