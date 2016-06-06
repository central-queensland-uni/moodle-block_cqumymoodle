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
 * Custom functions for the cqumymoodle block.
 *
 * @package     block_cqumymoodle
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @copyright   2014 CQUniversity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 *  Get the JSON feed
 *
 * @param string $endpoint Fully qualified URL of the endpoint
 * @param bool   $ssl      Whether we should connect via SSL
 * @param string $token    The webservice token to connect to external service
 * @param int    $uid      Unique identifier for the user we want
 * @param string $uidtype  Which field we are matching on
 * @param bool   $ismoodle Assume the external service is another Moodle site
 *
 * @return mixed bool|array Array of links or false if nothing found
 */
function block_cqumymoodle_get_courses_json($endpoint, $ssl = false, $token = null, $uid, $uidtype, $ismoodle = true) {

    $restformat = 'json'; // Only works in Moodle 2.2 and above.
    $params = "field=".urlencode($uidtype)."&value=".urlencode($uid)."&";

    // Add the http bits to the call.
    if (strpos($endpoint, 'http') === false) {
        $endpoint = 'http'.($ssl ? 's' : '').'://'.$endpoint;
    }

    if (!empty($token) && $uid) {

        $wstoken = $token;
        $wsfunction = 'cqu_get_user_courses';

        $serverurl = $endpoint.'?wstoken='.$wstoken.'&';
        $serverurl .= 'wsfunction='.$wsfunction.'&';
    } else if (!$ismoodle) {

        $serverurl = $endpoint."?";
    } else {

        return false;
    }

    $incjson = block_cqumymoodle_curl_wrapper($serverurl, $restformat, $params);
    $courses = json_decode($incjson);

    if (!empty($courses)) {

        return $courses;
    }

    return false;
}

/**
 *  CURL wrapper
 *
 * @param string $serverurl The fully qualified endpoint with tokens and functions to call
 * @param string $restformat What return format we want it in
 * @param string $params The parameters we are passing through
 *
 * @return mixed bool|string False if error, JSON string on success
 */
function block_cqumymoodle_curl_wrapper($serverurl, $restformat, $params) {
    global $CFG;

    // Make the rest call.
    if ($CFG->version >= 2013111800) {
        header('Content-Type: text/plain');
    }

    $curloptions = array(
        'CURLOPT_CONNECTTIMEOUT' => 5, // 5 seconds should be plenty.
        'CURLOPT_TIMEOUT'        => 10 // 10 seconds because name resolution can take up to 3 secs.
    );
    $curl = new curl();
    $curl->setopt($curloptions);
    $restformat = ($restformat == 'json') ? '&moodlewsrestformat='.$restformat : '';

    try {

        $response = $curl->post($serverurl.$restformat, $params);
        return $response;
    } catch (Exception $e) {

        debugging("Error contacting external service, error returned was: ".$e->getMessage());
    }

    return false;
}
