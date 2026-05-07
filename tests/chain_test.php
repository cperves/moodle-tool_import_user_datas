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

use advanced_testcase;
use core\task\manager;
use stdClass;
use tool_import_user_datas\tools;
use tool_import_user_datas\user_import_preferences_and_datas_task;

/**
 * All task chain test class
 */
final class chain_test extends advanced_testcase {

    /**
     * test all chain of import with a mocked response
     * @covers \tool_import_user_datas\user_import_preferences_and_datas_task
     * @return void
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function test_all_chain(): void {
        global $CFG, $DB;
        require_once($CFG->libdir . '/classes/task/manager.php');
        self::configure();
        $wsuser = $DB->get_record('user', ['username' => tools::TOOL_IMPORT_USER_DATAS_WS_USER]);
        $this->setUser($wsuser);
        $userrecord = new stdClass();
        $userrecord->autosubscribe = 0;
        $userrecord->city = 'Strasbourg';
        $userrecord->firstnamephonetic = 'fɜ:sneɪmefəʊnetɪk';
        $user = $this->getDataGenerator()->create_user($userrecord);
        set_user_preferences(
            [
                'htmleditor' => 'tiny',
                'calendar_maxevents' => 1,
            ],
            $user
        );
        user_import_preferences_and_datas_task::schedule_user_datas_import($user->username, $user->auth);
        $this->redirectMessages();
        ob_start();
        $task = manager::get_scheduled_task(
            '\tool_import_user_datas\task\perform_scheduled_preferences_import'
        );
        $mockedresponse = [
            'preferences' => [
                ['name' => 'htmleditor', 'value' => 'tiny'],
                ['name' => 'calendar_maxevents', 'value' => 1 ],
            ],
            'userdatas' => [
                ['name' => 'autosubscribe', 'value' => 0],
                ['name' => 'city', 'value' => 'Strasbourg'],
                ['name' => 'firstnamephonetic', 'value' => 'fɜ:sneɪmefəʊnetɪk'],
            ],
        ];
        \curl::mock_response(json_encode($mockedresponse));
        $task->execute();
        ob_end_clean();
    }

    /**
     * private configure option
     * @return void
     */
    private static function configure(): void {
        global $CFG;
        set_config('trigger_on_create', 1, 'tool_import_user_datas');
        set_config('user_auth', 1, 'tool_import_user_datas');
        // Install webservice.
        $token = tools::install_webservice();
        set_config('remote_url', $CFG->wwwroot, 'tool_import_user_datas');
        set_config('remote_token', $token, 'tool_import_user_datas');
        set_config('user_auth', 'manual', 'tool_import_user_datas');
        set_config('trigger_on_create', 0, 'tool_import_user_datas');
        set_config('activated', 1, 'tool_import_user_datas');
    }

    /**
     * setUp test
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->preventResetByRollback(); // Logging waits till the transaction gets committed.
    }
}
