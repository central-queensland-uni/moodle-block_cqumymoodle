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
 * CQU MyMoodle get user courses external PHPunit tests
 *
 * @package     block_cqumymoodle
 * @category    phpunit
 * @author      Marcus Boon<marcus@catalyst-au.net>
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/webservice/tests/helpers.php');

class block_cqumymoodle_external_testcase extends externallib_advanced_testcase {

    protected $categories;
    protected $courses;
    protected $student;
    protected $teacher;

    /**
     * @group blocks_cqumymoodle
     */
    protected function setUp() {
        global $CFG;
        include_once($CFG->dirroot.'/blocks/cqumymoodle/externallib.php');

        // Create categories.
        for ($i = 1; $i <= 2; $i++) {
            $this->categories[$i] = $this->getDataGenerator()->create_category(
                array('name' => 'Test Category '.$i)
            );
        }

        // Create courses.
        for ($i = 1; $i <= 5; $i++) {
            $this->courses[$i] = $this->getDataGenerator()->create_course(
                array(
                    'idnumber'      => ($i * 246),
                    'fullname'      => 'Test Course '.$i,
                    'shortname'     => 'testcourse'.$i.'2014',
                    'category'      => $this->categories[($i % 2 + 1)]->id,
                    'visible'       => ($i % 2) // Odd courses are visible.
                )
            );
        }

        // Create student.
        $this->student = $this->getDataGenerator()->create_user(
            array(
                'idnumber'  => 123456,
                'email'     => 'test@test.com',
                'username'  => 'wwhite'
            )
        );

        // Create teacher.
        $this->teacher = $this->getDataGenerator()->create_user(
            array(
                'idnumber'  => 543621,
                'email'     => 'test2@test.com',
                'username'  => 'yomrwhite'
            )
        );

        foreach ($this->courses as $course) {
            $this->getDataGenerator()->enrol_user($this->student->id, $course->id, 5);
            $this->getDataGenerator()->enrol_user($this->teacher->id, $course->id, 3);
        }
    }

    /**
     * @group blocks_cqumymoodle
     */
    public static function setAdminUser() {
        global $USER;
        parent::setAdminUser();
        // The logged in user needs email, country and city to do certain things.
        $USER->email    = 'admin@test.com';
        $USER->country  = 'AU';
        $USER->city     = 'Sydney';
    }

    /**
     * @group blocks_cqumymoodle
     */
    public function test_student_match_username() {

        self::setUser($this->student);
        $this->resetAfterTest(true);

        $courses = block_cqumymoodle_external::get_user_courses('username', $this->student->username);
        $this->assertEquals(3, count($courses), "It should return 3 courses");
    }

    /**
     * @group blocks_cqumymoodle
     */
    public function test_student_match_email() {

        self::setUser($this->student);
        $this->resetAfterTest(true);

        $courses = block_cqumymoodle_external::get_user_courses('email', $this->student->email);
        $this->assertEquals(3, count($courses), "It should return 3 courses");
    }

    /**
     * @group blocks_cqumymoodle
     */
    public function test_student_match_idnumber() {

        self::setUser($this->student);
        $this->resetAfterTest(true);

        $courses = block_cqumymoodle_external::get_user_courses('idnumber', $this->student->idnumber);
        $this->assertEquals(3, count($courses), "It should return 3 courses");
    }

    /**
     * @group blocks_cqumymoodle
     */
    public function test_teacher_hidden_courses() {

        self::setUser($this->teacher);
        $this->resetAfterTest(true);

        $courses = block_cqumymoodle_external::get_user_courses('idnumber', $this->teacher->idnumber);
        $this->assertEquals(5, count($courses), "It should return 5 courses");
    }
}
