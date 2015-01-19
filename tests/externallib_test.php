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
    protected $user;

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
                    'category'      => $this->categories[($i % 2 + 1)]->id
                )
            );
        }

        // Create user.
        $this->user = $this->getDataGenerator()->create_user(
            array(
                'idnumber'  => 123456,
                'email'     => 'test@test.com',
                'username'  => 'wwhite'
            )
        );

        foreach ($this->courses as $course) {
            $this->getDataGenerator()->enrol_user($this->user->id, $course->id);
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
    public function test_match_username() {

        self::setAdminUser();
        load_all_capabilities();
        $this->resetAfterTest(true);

        $courses = block_cqumymoodle_external::get_user_courses('username', $this->user->username);
        $this->assertEquals(count($courses), 5, "It should return 5 courses");
    }

    /**
     * @group blocks_cqumymoodle
     */
    public function test_match_email() {

        self::setAdminUser();
        load_all_capabilities();
        $this->resetAfterTest(true);

        $courses = block_cqumymoodle_external::get_user_courses('email', $this->user->email);
        $this->assertEquals(count($courses), 5, "It should return 5 courses");
    }

    /**
     * @group blocks_cqumymoodle
     */
    public function test_match_idnumber() {
        self::setAdminUser();
        load_all_capabilities();
        $this->resetAfterTest(true);

        $courses = block_cqumymoodle_external::get_user_courses('idnumber', $this->user->idnumber);
        $this->assertEquals(count($courses), 5, "It should return 5 courses");
    }
}
