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
 * CQU external functions and service definitions.
 *
 * @package     block
 * @subpackage  cqumymoodle
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @copyright   2014 CQUniversity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

    'cqu_get_user_courses' => array(
        'classname' => 'block_cqumymoodle_external',
        'methodname'    => 'get_user_courses',
        'classpath'     => 'blocks/cqumymoodle/externallib.php',
        'description'   => 'Get the list of courses where a user is enrolled in.',
        'type'          => 'read',
        'capabilities'  => 'moodle/user:update, moodle/course:useremail, moodle/course:viewparticipants,'
                . ' moodle/course:view, moodle/course:viewhiddencourses, moodle/user:viewdetails,'
                . ' moodle/user:viewhiddendetails, webservice/rest:use'
    ),
);
