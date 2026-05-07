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
 *
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



namespace tool_import_user_datas;
use core_external\external_api;
use externallib_advanced_testcase;
use tool_import_user_datas_external;

defined('MOODLE_INTERNAL') || die();
global $CFG;

use core\exception\required_capability_exception;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/admin/tool/import_user_datas/externallib.php');

/**
 * externallib tests
 */
final class external_test extends externallib_advanced_testcase {
    /**
     * @var user
     */
    private $user;

    /**
     * test get_user_datas_and_preferences
     * @covers \tool_import_user_datas_external::get_user_datas_and_preferences
     * @covers \tool_import_user_datas_external::get_user_datas_and_preferences_returns
     * @return void
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public function test_get_user_datas_and_preferences(): void {
        global $CFG;
        require_once($CFG->dirroot . '/admin/tool/import_user_datas/externallib.php');
        $this->set_pref_datas_fields_config();
        $this->create_user();
        $this->set_user_prefs();
        $config = get_config('tool_import_user_datas');
        $result = tool_import_user_datas_external::get_user_datas_and_preferences(
            $this->user->username,
            'manual',
            $config->preferences,
            $config->user_datas,
        );
        $result = external_api::clean_returnvalue(
            tool_import_user_datas_external::get_user_datas_and_preferences_returns(),
            $result
        );
        $this->assertCount(2, $result['preferences']);
        $this->assertCount(3, $result['userdatas']);
        foreach ($result['preferences'] as $pref) {
            // Check we receive the expected preferences.
            $this->assertEquals(get_user_preferences($pref['name'], null, $this->user), $pref['value']);
        }
        foreach ($result['userdatas'] as $userdata) {
            // Check we receive the expected userdatas.
            $this->assertEquals($this->user->{$userdata['name']}, $userdata['value']);
        }
    }

    /**
     * Test get_user_datas_and_preferences without having correct permissions
     * @covers \tool_import_user_datas_external::get_user_datas_and_preferences
     * @covers \tool_import_user_datas_external::get_user_datas_and_preferences_returns
     * @return void
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public function test_get_user_datas_and_preferences_with_no_permission(): void {
        global $CFG;
        require_once($CFG->dirroot . '/admin/tool/import_user_datas/externallib.php');
        $this->set_pref_datas_fields_config();
        $this->create_user();
        $this->set_user_prefs();
        $config = get_config('tool_import_user_datas');
        $testuser = $this->getDataGenerator()->create_user();
        // Add first required capability.
        $this->setUser($testuser);
        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage(
            get_string(
                'nopermissions',
                'error',
                get_string(
                    'import_user_datas:get_user_preferences_and_datas_for_user',
                    'tool_import_user_datas'
                )
            )
        );
        \tool_import_user_datas_external::get_user_datas_and_preferences(
            $this->user->username,
            'manual',
            $config->preferences,
            $config->user_datas,
        );
    }

    /**
     * Create user for tests
     * @return void
     */
    private function create_user($triggeroncreate = false): void {
        set_config('trigger_on_create', $triggeroncreate, 'tool_import_user_datas');
        set_config('user_auth', 'manual', 'tool_import_user_datas');
        $datagenerator = $this->getDataGenerator();
        $userrecord = new \stdClass();
        $userrecord->autosubscribe = 0;
        $userrecord->city = 'Strasbourg';
        $userrecord->firstnamephonetic = 'fɜ:sneɪmefəʊnetɪk';
        $this->user = $datagenerator->create_user($userrecord);
    }

    /**
     * set user preferences for tests
     * @return void
     */
    private function set_user_prefs(): void {
        set_user_preferences(
            [
                'htmleditor' => 'tiny',
                'calendar_maxevents' => 1,
            ],
            $this->user
        );
    }

    /**
     * Set plugin config settings for tests
     * @return void
     */
    private function set_pref_datas_fields_config(): void {
        set_config(
            'preferences',
            'htmleditor;calendar_maxevents',
            'tool_import_user_datas'
        );
        set_config(
            'user_datas',
            'city;autosubscribe;firstnamephonetic',
            'tool_import_user_datas'
        );
    }

    /**
     * setuo tests
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->preventResetByRollback(); // Logging waits till the transaction gets committed.
        $this->setAdminUser();
    }
}
