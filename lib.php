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
 * Contains logic class and interface for the grading evaluation plugin "Participation credit".
 *
 * @package    workshopeval_credit
 * @copyright  2013 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Defines the computation logic of the grading evaluation subplugin
 */
class workshop_credit_evaluation extends workshop_evaluation {

    /** @var workshop the parent workshop instance */
    protected $workshop;

    /**
     * Constructor
     *
     * @param workshop $workshop The workshop api instance
     * @return void
     */
    public function __construct(workshop $workshop) {
        $this->workshop = $workshop;
    }

    /**
     * Calculates the grades for assessment and updates 'gradinggrade' fields in 'workshop_assessments' table
     *
     * @param stdClass $settings settings for this round of evaluation
     * @param null|int|array $restrict if null, update all reviewers, otherwise update just grades for the given reviewers(s)
     *
     * @return void
     */
    public function update_grading_grades(stdclass $settings, $restrict=null) {
    }

}
