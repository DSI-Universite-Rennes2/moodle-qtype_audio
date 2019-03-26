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
 * Version information for the audio 'question' type.
 *
 * @package   qtype_audio
 * @copyright 2019 Université Rennes 2 {@link https://www.univ-rennes2.fr}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// The full frankenstyle component name in the form of plugintype_pluginname.
$plugin->component = 'qtype_audio';

// Declares the maturity level of this plugin version, that is how stable it is.
$plugin->maturity  = MATURITY_ALPHA;

// Human readable version name that should help to identify each release of the plugin.
$plugin->release = '1.0.0';

// Specifies the minimum version number of Moodle core that this plugin requires.
$plugin->requires  = 2016011400;

// The version number of the plugin.
$plugin->version   = 2019032500;
