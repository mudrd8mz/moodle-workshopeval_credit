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
 * Provides thes {@link workshopeval_credit_testcase} class.
 *
 * @package     workshopeval_credit
 * @category    test
 * @copyright   2013 David Mudrak <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/mod/workshop/locallib.php');
require_once($CFG->dirroot.'/mod/workshop/eval/credit/lib.php');


/**
 * Unit tests for the 'Participation credit' workshop evaluation method.
 *
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class workshopeval_credit_testcase extends advanced_testcase {

    /** @var workshop instance emulation */
    protected $workshop;

    /** @var testable_workshop_credit_evaluation */
    protected $eval;

    /**
     * Prepare the test environment.
     */
    protected function setUp() {
        global $CFG;

        parent::setUp();

        if ($CFG->version >= 2014051200) {
            // Since Moodle 2.7 we have data generators available.
            $this->setAdminUser();
            $course = $this->getDataGenerator()->create_course();
            $workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $course, 'evaluation' => 'best'));
            $cm = get_coursemodule_from_instance('workshop', $workshop->id, $course->id, false, MUST_EXIST);
            $this->workshop = new workshop($workshop, $cm, $course);

        } else {
            $cm = new stdClass();
            $course = new stdClass();
            $context = new stdClass();
            $workshop = (object)array('id' => 22, 'evaluation' => 'best');
            $this->workshop = new workshop($workshop, $cm, $course, $context);
        }

        $this->eval = new testable_workshop_credit_evaluation($this->workshop);
    }

    /**
     * Shut down the test environment.
     */
    protected function tearDown() {
        $this->workshop = null;
        $this->eval = null;
        parent::tearDown();
    }

    /**
     * With no assessments, no grades are to be calculated.
     */
    public function test_calculate_assessment_grades_empty() {
        $this->resetAfterTest(true);

        $this->assertEquals(array(), $this->eval->calculate_assessment_grades(array(), 'all'));
        $this->assertEquals(array(), $this->eval->calculate_assessment_grades(array(), 'proportional'));
        $this->assertEquals(array(), $this->eval->calculate_assessment_grades(array(), 'one'));
    }

    /**
     * Test the actual grading grade calculations.
     */
    public function test_calculate_assessment_grades() {
        $this->resetAfterTest(true);

        // Prepare a structure as returned by {@link workshop_credit_evaluation::make_assessments_map()}.
        $assessments = array(
            4 => array(
                102 => null,
                103 => null,
                104 => null,
            ),
            6 => array(
                104 => null,
                105 => 100.00000,
                106 => 12.12345,
                107 => 0.00000,
            ),
            8 => array(
                106 => null,
                107 => 50.00000,
                108 => null,
            ),
            23 => array(
                108 => 100.00000,
                109 => 100.00000,
                110 => 67.65356,
            ),
            0 => array(),
            -1 => null,
        );

        // Test that non-sense data are not returned.
        $grades = $this->eval->calculate_assessment_grades($assessments, 'one');
        $this->assertFalse(array_key_exists(0, $grades));
        $this->assertFalse(array_key_exists(-1, $grades));

        // Test 'All or nothing' mode.
        $grades = $this->eval->calculate_assessment_grades($assessments, 'all');
        $this->assertSame(0.00000, $grades[4]);
        $this->assertSame(0.00000, $grades[6]);
        $this->assertSame(0.00000, $grades[8]);
        $this->assertSame(100.00000, $grades[23]);

        // Test 'At least one' mode.
        $grades = $this->eval->calculate_assessment_grades($assessments, 'one');
        $this->assertSame(0.00000, $grades[4]);
        $this->assertSame(100.00000, $grades[6]);
        $this->assertSame(100.00000, $grades[8]);
        $this->assertSame(100.00000, $grades[23]);

        // Test 'Proportional' mode.
        $grades = $this->eval->calculate_assessment_grades($assessments, 'proportional');
        $this->assertSame(0.00000, $grades[4]);
        $this->assertSame(75.00000, $grades[6]);
        $this->assertSame(33.33333, $grades[8]);
        $this->assertSame(100.00000, $grades[23]);
    }
}


/**
 * Provides access to protected methods we want to test.
 *
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testable_workshop_credit_evaluation extends workshop_credit_evaluation {

    /**
     * Expose parent's protected method so that it can be tested by calling it directly.
     *
     * @param array $assessments
     * @param string $mode
     * @return array
     */
    public function calculate_assessment_grades(array $assessments, $mode) { // @codingStandardsIgnoreLine overriding expected
        return parent::calculate_assessment_grades($assessments, $mode);
    }
}
