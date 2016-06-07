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
 * Custom course list block for CQU
 *
 * @package     block_cqumymoodle
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @copyright   2014 CQUniversity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_cqumymoodle extends block_base {

    /** @var string The name of the block */
    public $blockname = null;

    /**
     * Set the initial properties for the block
     * @return void
     */
    public function init() {

        $this->blockname    = get_class($this);
        $this->title        = get_string('blocktitle', $this->blockname);
    }

    /**
     * Can we set config options for the block?
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * Should we hide the blocks header?
     * @return bool
     */
    public function hide_header() {
        return false;
    }

    /**
     * Should we allow multiple instances of this block?
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Set the applicable formats for this block to call
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    public function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) :
                format_string(get_string('newcqumymoodleblock', 'block_cqumymoodle'));
    }

    /**
     * Allow the user to configure a block instance?
     * @return bool
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Allow the instance to be hidden?
     * @return bool
     */
    public function instance_can_be_hidden() {
        return true;
    }

    /**
     * Find out if an instance can be docked
     * @return bool
     */
    public function instance_can_be_docked() {
        return (
            parent::instance_can_be_docked() &&
            (empty($this->config->enabledock) ||
            $this->config->enabledock == 'yes')
        );
    }

    /**
     * Get the contents of the block via ajax
     *
     * @return string $html
     */
    public function get_ajax_content() {
        global $CFG, $USER;
        $config = $this->config;

        if (class_exists('cache')) {
            $cache = cache::make('block_cqumymoodle', 'remote');
            $key = $USER->id . '-' . $this->instance->id;
            $html = $cache->get($key);
        } else {
            $html = null;
        }

        if ($html) {
            return $html;
        }

        $html = '';
        $html .= html_writer::start_tag('div', array('class' => 'content block_cqumymoodle'));

        if (isset($config->endpoint) && $config->endpoint !== '') {

            $endpoint = $config->endpoint;
            $ssl = $config->ssl;
            $ismoodle = $config->ismoodle;
            $token = isset($config->token) ? $config->token : null;
            $showfullname = isset($config->displayfullname) ? $config->displayfullname : false;
            $showshortname = isset($config->displayshortname) ? $config->displayshortname : false;
            $showcategory = isset($config->displaycategory) ? $config->displaycategory : false;

            $usermatch = $config->usermatch;
            switch ($usermatch) {
                case 0:
                    $id = $USER->username;
                    $idtype = 'username';
                    break;
                case 1:
                    $id = $USER->email;
                    $idtype = 'email';
                    break;
                case 2:
                    $id = $USER->idnumber;
                    $idtype = 'idnumber';
                    break;
                default:
                    debugging("User field match not set for block_cqumymoodle!", DEBUG_DEVELOPER);
            }

            // Do stuff!
            $courses = block_cqumymoodle_get_courses_json($endpoint, $ssl, $token, $id, $idtype, $ismoodle);
            if (!empty($courses)) {

                if (isset($courses->exception)) {
                    return "Exception: " . $courses->message;
                }

                $html .= html_writer::start_tag('ul');

                $categories = array();

                // Process courses.
                foreach ($courses as $course) {

                    if ($showfullname && $showshortname) {
                        $coursename = "$course->shortname : $course->fullname";
                    } else if ($showfullname) {
                        $coursename = $course->fullname;
                    } else { // If not specified we just show the shortname.
                        $coursename = $course->shortname;
                    }

                    $linkattrs = null;
                    if (isset($course->visible) && $course->visible != 1) {
                        $linkattrs['class'] = 'dimmed';
                    }

                    $categories[$course->category][] = html_writer::link(
                        $course->courselink,
                        $coursename,
                        $linkattrs
                    );
                }

                // Process the categories array to spit out the course info.
                foreach ($categories as $category => $courses) {

                    // If we show the category, add it to html.
                    if ($showcategory) {
                        $attributes['class'] = 'cqumymoodle_category';
                        $html .= html_writer::start_tag('li', $attributes);
                        $html .= html_writer::tag('h3', $category);
                        $html .= html_writer::end_tag('li');
                        $html .= html_writer::start_tag('ul');
                    }

                    // Of course we want to add the courses.
                    foreach ($courses as $course) {

                        $html .= html_writer::tag(
                            'li',
                            $course,
                            array('class' => 'cqumymoodle_courses')
                        );
                    }

                    // Make sure we close the tag if we add category.
                    if ($showcategory) {
                        $html .= html_writer::end_tag('ul');
                    }
                }
                $html .= html_writer::end_tag('ul');
            } else {
                $html .= html_writer::tag('span', get_string('nodata', 'block_cqumymoodle'));
            }
        } else {
            $html .= html_writer::tag('span', get_string('noendpointset', 'block_cqumymoodle'));
        }

        $html .= html_writer::end_tag('div');   // Close content div.

        if (class_exists('cache')) {
            $cache->set($key, $html);
        }

        return $html;
    }

    /**
     * Get the contents of the block
     * @return object $this->content
     */
    public function get_content() {
        global $CFG, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        require_once($CFG->dirroot.'/blocks/cqumymoodle/locallib.php');

        $this->content = new stdClass;
        $this->content->footer = '';

        $html = '<!-- Start block cqumymoodle -->';
        $html .= html_writer::start_tag('div', array('class' => 'outer-container block_cqumymoodle'));

        if (class_exists('cache')) {
            $cache = cache::make('block_cqumymoodle', 'remote');
            $key = $USER->id . '-' . $this->instance->id;
            $chunk = $cache->get($key);
        } else {
            $chunk = null;
        }

        if ($chunk) {

            // If it in the cache render it directly.
            $html .= $chunk;
        } else {

            // If not then load it in ajax.
            $id = $this->instance->id;
            $html .= <<<EOT
<div id='cqumymoodle$id'> Loading .... </div>
<script>
(function(){

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            document.getElementById("cqumymoodle$id").innerHTML = xmlhttp.responseText;
        }
    };
    xmlhttp.open("GET", '/blocks/cqumymoodle/ajax.php?id=$id', true);
    xmlhttp.send();

})();
</script>
EOT;
        }

        $html .= html_writer::end_tag('div');   // Close outer-container div.
        $html .= '<!-- End block_cqumymoodle -->';

        $this->content->text = $html;
    }

    /**
     * Serialize and store config data
     * @return void
     */
    public function instance_config_save($data, $nolongerused = false) {

        // This is so we can customise the data if need be.
        $config = clone($data);

        if (strpos($config->endpoint, 'https') !== false) {
            $config->ssl = 1;
        } else if (strpos($config->endpoint, 'http') !== false) {
            $config->ssl = 0;
        }

        // Call parent and save the data.
        parent::instance_config_save($config, $nolongerused);
    }
}
