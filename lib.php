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

    /** @var recently used settings in this workshop */
    protected $settings;

    /**
     * Constructor
     *
     * @param workshop $workshop The workshop api instance
     * @return void
     */
    public function __construct(workshop $workshop) {
        $this->workshop = $workshop;
        $this->settings = array('mode' => 'proportional'); // TODO load the most recent setting used in this workshop
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
        global $DB;

        $assessments = $this->make_assessments_map($restrict);
        $grades = $this->calculate_assessment_grades($assessments, $settings->mode);

        foreach ($assessments as $reviewerid => $submissiongrades) {
            foreach ($submissiongrades as $submissionid => $notused) {
                $DB->set_field('workshop_assessments', 'gradinggrade', $grades[$reviewerid],
                    array('submissionid' => $submissionid, 'reviewerid' => $reviewerid));
            }
        }
    }

    /**
     * Returns an instance of the form to define evaluation settings.
     *
     * @return workshop_credit_evaluation_settings_form
     */
    public function get_settings_form(moodle_url $actionurl=null) {

        $customdata['workshop'] = $this->workshop;
        $customdata['current'] = $this->settings;
        $attributes = array('class' => 'evalsettingsform credit');

        return new workshop_credit_evaluation_settings_form($actionurl, $customdata, 'post', '', $attributes);
    }

    /// Internal methods ///////////////////////////////////////////////////////

    /**
     * Prepares an overview of assessments allocated to reviewers in this workshop.
     *
     * Returned structure is a two-dimensional array with keys [reviewerid][submissionid].
     * The item in this array is the grade given by the reviewer for the submission. The grade
     * is either null (not graded yet) or a decimal number from 0.00000 to 100.00000.
     *
     * @param null|int|array $restrict if null, returns info for all reviewers, just given reviewers(s)
     * @return array
     */
    protected function make_assessments_map($restrict) {
        global $DB;

        $assessments = array();

        $sql = "SELECT a.submissionid, a.reviewerid, a.grade
                  FROM {workshop_submissions} s
                  JOIN {workshop_assessments} a ON a.submissionid = s.id
                 WHERE s.workshopid = :workshopid AND s.example = 0";

        $params = array('workshopid' => $this->workshop->id);

        if (is_null($restrict)) {
            // Update all reviewers - no more conditions.
        } else if (!empty($restrict)) {
            list($usql, $uparams) = $DB->get_in_or_equal($restrict, SQL_PARAMS_NAMED);
            $sql .= " AND a.reviewerid $usql";
            $params = array_merge($params, $uparams);
        } else {
            throw new coding_exception('Empty value is not a valid $restrict parameter value.');
        }

        $rs = $DB->get_recordset_sql($sql, $params);

        foreach ($rs as $r) {
            $assessments[$r->reviewerid][$r->submissionid] = $r->grade;
        }

        $rs->close();

        return $assessments;
    }

    /**
     * Calculates grades for assessments.
     *
     * @param array $assessments the structure returned by {@link self::make_assessments_map()}
     * @param string $mode mode evaluation (all, proportional, one)
     * @return array of (int)reviewerid => (float)gradinggrade
     */
    protected function calculate_assessment_grades(array $assessments, $mode) {

        $allocated = array(); // Number of allocated assessments per reviewer.
        $finished = array(); // Number of actually graded assessments per reviewer.
        $grades = array(); // Suggested grade for assessment (0.00000 - 100.00000)

        foreach ($assessments as $reviewerid => $submissiongrades) {
            if (!is_array($submissiongrades) or empty($submissiongrades)) {
                continue;
            }
            if (!isset($allocated[$reviewerid])) {
                $allocated[$reviewerid] = count($submissiongrades);
            }
            if (!isset($finished[$reviewerid])) {
                $finished[$reviewerid] = 0;
            }
            foreach ($submissiongrades as $submissionid => $grade) {
                if (!is_null($grade)) {
                    $finished[$reviewerid]++;
                }
            }
        }

        foreach (array_keys($allocated) as $reviewerid) {
            $grades[$reviewerid] = null;

            if ($mode === 'all') {
                if ($allocated[$reviewerid] == $finished[$reviewerid]) {
                    $grades[$reviewerid] = 100.00000;
                } else {
                    $grades[$reviewerid] = 0.00000;
                }

            } else if ($mode === 'one') {
                if ($finished[$reviewerid] > 0.00000) {
                    $grades[$reviewerid] = 100.00000;
                } else {
                    $grades[$reviewerid] = 0.00000;
                }

            } else if ($mode === 'proportional') {
                $grades[$reviewerid] = round(100 * $finished[$reviewerid] / $allocated[$reviewerid], 5);

            } else {
                throw new coding_exception('Unknown evaluation mode', $mode);
            }
        }

        return $grades;
    }
}


/**
 * Represents the settings form for this plugin.
 */
class workshop_credit_evaluation_settings_form extends workshop_evaluation_settings_form {

    /**
     * Defines specific fields for this evaluation method.
     */
    protected function definition_sub() {
        $mform = $this->_form;

        $pluginconfig = get_config('workshopeval_credit');
        $current = $this->_customdata['current'];

        $options = array(
            'all' => get_string('modeall', 'workshopeval_credit'),
            'proportional' => get_string('modeproportional', 'workshopeval_credit'),
            'one' => get_string('modeone', 'workshopeval_credit'),
        );
        $mform->addElement('select', 'mode', get_string('mode', 'workshopeval_credit'), $options);
        $mform->addHelpButton('mode', 'mode', 'workshopeval_credit');
        $mform->setDefault('mode', isset($pluginconfig->defaultmode) ? $pluginconfig->defaultmode : 'proportional');

        $this->set_data($current);
    }
}
