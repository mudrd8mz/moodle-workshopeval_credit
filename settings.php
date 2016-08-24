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
 * Site-level plugin configuration.
 *
 * @package     workshopeval_credit
 * @copyright   2013 David Mudrak <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$options = array(
    'all' => get_string('modeall', 'workshopeval_credit'),
    'proportional' => get_string('modeproportional', 'workshopeval_credit'),
    'one' => get_string('modeone', 'workshopeval_credit'),
);

$settings->add(new admin_setting_configselect('workshopeval_credit/defaultmode', get_string('mode', 'workshopeval_credit'),
    get_string('mode_desc', 'workshopeval_credit'), 'proportional', $options));
