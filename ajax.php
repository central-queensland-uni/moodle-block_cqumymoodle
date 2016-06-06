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
 * Version details.
 *
 * @package     block_cqumymoodle
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @copyright   2014 CQUniversity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once("../../config.php");
require_once("$CFG->dirroot/blocks/moodleblock.class.php");
require_once("$CFG->dirroot/lib/filelib.php");
require_once('block_cqumymoodle.php');
require_once('locallib.php');

require_login();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);

// Close the session so even if the ajax takes ages then
// we don't stop the student from doing something else.
$blockid = required_param('id', PARAM_INT);
$instance = $DB->get_record('block_instances', array('id' => $blockid));
$block = block_instance('cqumymoodle', $instance);
echo $block->get_ajax_content();
