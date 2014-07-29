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
 * Form for editing HTML block instances.
 *
 * @package     block
 * @subpackage  cqumymoodle
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @copyright   2014 CQUniversity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cqumymoodle_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $CFG;

        // Fields for configuring the CQUMyMoodle block
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Set the block title
        $mform->addElement(
            'text',
            'config_title',
            get_string('configtitle', 'block_cqumymoodle'),
            array('size' => 40)
        );
        $mform->setType('config_title', PARAM_TEXT);

        // Set whether we should display the course fullname
        $mform->addElement(
            'advcheckbox',
            'config_displayfullname',
            get_string('displayfullname', 'block_cqumymoodle')
        );
        $mform->setType('config_displayfullname', PARAM_INT);

        // Set whether we should display the course shortname
        $mform->addElement(
            'advcheckbox',
            'config_displayshortname',
            get_string('displayshortname', 'block_cqumymoodle')
        );
        $mform->setType('config_displayshortname', PARAM_INT);

        // Set whether we should display the course shortname
        $mform->addElement(
            'advcheckbox',
            'config_displaycategory',
            get_string('displaycategory', 'block_cqumymoodle')
        );
        $mform->setType('config_displaycategory', PARAM_INT);

        // Set the endpoint to call
        $mform->addElement(
            'text',
            'config_endpoint',
            get_string('configendpoint', 'block_cqumymoodle'),
            array('size' => 40)
        );
        $mform->addRule('config_endpoint', null, 'required', null, 'client');
        $mform->setType('config_endpoint', PARAM_URL);

        // Set whether or not to use SSL
        $mform->addElement(
            'advcheckbox',
            'config_ssl',
            get_string('configssl', 'block_cqumymoodle')
        );

        // Set which user field to match on the external system
        $umarr = array();
        $umarr[] =& $mform->createElement('radio', 'config_usermatch', '', get_string('username'), 0);
        $umarr[] =& $mform->createElement('radio', 'config_usermatch', '', get_string('email'), 1);
        $umarr[] =& $mform->createElement('radio', 'config_usermatch', '', get_string('idnumber'), 2);
        $mform->addGroup(
            $umarr,
            'usermatch',
            get_string('usermatch', 'block_cqumymoodle'),
            array(' '),
            false
        );
        $mform->addRule('usermatch', null, 'required', null, 'client');

        // Set whether we are connecting to an external moodle
        $mform->addElement(
            'advcheckbox',
            'config_ismoodle',
            get_string('configismoodle', 'block_cqumymoodle')
        );

        // Set the webservice token
        $mform->addElement(
            'text',
            'config_token',
            get_string('configtoken', 'block_cqumymoodle')
        );
        $mform->setType('config_token', PARAM_TEXT);
        $mform->disabledIf('config_token', 'config_ismoodle', 'notchecked');

    }

    function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $endpoint = $this->block->config->endpoint;
            if (empty($endpoint)) {
                $currentendpoint = '';
            } else {
                $currentendpoint = $endpoint;
            }
            $defaults->config_endpoint = $currentendpoint;
        } else {
            $endpoint = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        // have to delete text here, otherwise parent::set_data will empty content
        // of editor
        unset($this->block->config->endpoint);
        parent::set_data($defaults);
        // restore $endpoint
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        $this->block->config->endpoint = $endpoint;
        if (isset($title)) {
            // Reset the preserved title
            $this->block->config->title = $title;
        }
    }
}
