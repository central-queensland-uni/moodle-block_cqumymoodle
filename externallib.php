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
 * External function definitions.
 *
 * @package     block
 * @subpackage  cqumymoodle
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @copyright   2014 CQUniversity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/externallib.php");

class block_cqumymoodle_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_user_courses_parameters() {
        return new external_function_parameters(
            array(
                'field' => new external_value(
                    PARAM_ALPHA,
                    "The search field can be 'id' or 'idnumber' or 'username' or 'email'"
                ),
                'value' => new external_value(
                    PARAM_RAW,
                    "The value to match"
                )
            )
        );
    }

    /**
     * Get user courses for a unique field.
     *
     * @param string $field
     * @param string $value
     * @return array An array of arrays containing user courses.
     */
    public static function get_user_courses($field, $value) {
        global $CFG, $USER, $DB;
        require_once($CFG->dirroot . "/user/lib.php");

        $params = self::validate_parameters(
                self::get_user_courses_parameters(),
                array('field' => $field, 'value' => $value)
        );

        switch ($field) {
            case 'id':
                $paramtype = PARAM_INT;
                break;
            case 'idnumber':
                $paramtype = PARAM_RAW;
                break;
            case 'username':
                $paramtype = PARAM_RAW;
                break;
            case 'email':
                $paramtype = PARAM_EMAIL;
                break;
            default:
                throw new coding_exception(
                    'invalid field parameter',
                    "The search field '$field' is not supported, look at the web service documentation");
        }

        // Clean the values
        $cleanedvalue = clean_param($value, $paramtype);
        if ( $value != $cleanedvalue) {

            throw new invalid_parameter_exception("The field '$field' value is invalid: $value '(cleaned value: $cleanedvalue)");
        }

        // Retrieve the user
        $user = $DB->get_records_list('user', $field, array($cleanedvalue), 'id');
        $user = array_shift($user);

        $courses = enrol_get_users_courses($user->id, true, 'id, shortname, fullname, idnumber');

        // Finally append the full course link to the record
        $returnedcourses = array();

        foreach ($courses as $course) {

            $context = context_course::instance($course->id, IGNORE_MISSING);

            try {
                self::validate_context($context);
            } catch (Exception $e) {
                // Current user cannot access this course, we cannot disclose to them who is enrolled
                continue;
            }

            if ($user->id != $USER->id
                && !has_capability('moodle/course:viewparticipants', $context)
            ){
                // We need the capabilty to view participants
                continue;
            }

            $category = $DB->get_field('course_categories', 'name', array('id' => $course->category));

            $coursedetails = array(
                'id'        => $course->id,
                'shortname' => $course->shortname,
                'fullname'  => $course->fullname,
                'idnumber'  => $course->idnumber,
                'courselink'=> "$CFG->wwwroot/course/view.php?id=$course->id",
                'category'  => $category
            );

            $returnedcourses[] = $coursedetails;
        }

        return $returnedcourses;
    }

    /**
    * Returns description of method result value
    *
    * @return external_description
    */
    public static function get_user_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'        => new external_value(PARAM_INT, 'id of course'),
                    'shortname' => new external_value(PARAM_RAW, 'short name of course'),
                    'fullname'  => new external_value(PARAM_RAW, 'long name of course'),
                    'idnumber'  => new external_value(PARAM_RAW, 'id number of course'),
                    'courselink'=> new external_value(PARAM_RAW, 'fully qualified link to course'),
                    'category'  => new external_value(PARAM_RAW, 'the parent category of the course')
                ), 'List of courses'
            )
        );
    }
}
