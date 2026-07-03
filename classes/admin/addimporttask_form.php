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
 * form to add task
 *
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_import_user_datas\admin;
use coding_exception;
use core\exception\moodle_exception;
use moodleform;
use MoodleQuickForm_checkbox;
use MoodleQuickForm_radio;
use MoodleQuickForm_text;
use MoodleQuickForm_textarea;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

/**
 * Restore course for other user form
 */
class addimporttask_form extends moodleform {
    /**
     * form definition
     * @return void
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function definition() {
        global $CFG;
        $mform = &$this->_form;
        $mform->addElement('text', 'username', get_string('username'));
        $mform->setType('username', PARAM_TEXT);
        $mform->addRule(
            'username',
            get_string('required'),
            'required',
            null,
            'client'
        );
        $mform->addElement('text', 'auth', get_string('auth', 'tool_import_user_datas'));
        $mform->setType('auth', PARAM_TEXT);
        $mform->addRule(
            'auth',
            get_string('required'),
            'required',
            null,
            'client'
        );
        $mform->addHelpButton('auth', 'auth', 'tool_import_user_datas');
        $mform->addElement(
            'submit',
            'submit',
            get_string(
                'addimportusertask',
                'tool_import_user_datas'
            )
        );
    }

    /**
     * form validation
     * @param $data
     * @param $files
     * @return array
     * @throws \dml_exception
     * @throws coding_exception
     */
    public function validation($data, $files): array {
        global $DB;
        $errors = parent::validation($data, $files);
        if (!$DB->record_exists('user', ['username' => $data['username']])) {
            $errors['username'] = get_string(
                'user_not_exists',
                'tool_import_user_datas',
                (object)['username' => $data['username'], 'auth' => $data['auth']]
            );
        }
        if ($DB->record_exists('tool_import_user_datas', ['username' => $data['username']])) {
            $errors['username'] = get_string(
                'import_taskXuser_already_exists',
                'tool_import_user_datas',
                $data['username']
            );
        }
        return $errors;
    }


    /**
     * reset form
     * @return void
     */
    public function reset() {
        $mform = &$this->_form;
        foreach ($mform->_elements as $element) {
            if ($element instanceof MoodleQuickForm_text) {
                $element->setValue("");
            }
        }
    }
}
