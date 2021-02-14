<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 
/**
 * @package   block_ezee_constellate
 * @copyright 2021, John Stainsby <john@ezeedigital.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();
 
$plugin->version = 2020100100;              // Version of the plugin in YYYYMMDDXX where XX is incrementing number
$plugin->requires = 2020061502;             // Specifies the minimum version number of Moodle core that this plugin requires
$plugin->component = 'block_ezee_constellate';   // Type and name of plugin e.g. quiz module = mod_quiz
$plugin->maturity = MATURITY_ALPHA;         // Stablity of plugin (MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC or MATURITY_STABLE)
$plugin->release = 'Testing_0_0';           // Release version name

//$plugin->supported = TODO;                / Available as of Moodle 3.9.0 or later.
//$plugin->incompatible = TODO;             // Available as of Moodle 3.9.0 or later.

// $plugin->dependencies = [                // Declare explicit dependency on other plugin(s) for this plugin to work
//     'mod_forum' => ANY_VERSION,
//     'mod_data' => TODO
// ];