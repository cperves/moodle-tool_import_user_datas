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
 * Privacy provider tests
 * @package     tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_import_user_datas;

use core\exception\coding_exception;
use tool_import_user_datas\user_import_preferences_and_datas_task;
use tool_import_user_datas_tools;
use context_course;
use context_user;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_userlist;
use tool_import_user_datas\privacy\provider;
use core_privacy\tests\provider_testcase;
use core_user;
use stdClass;

/**
 * Unit tests for the privacy API implementation.
 */
final class privacy_provider_test extends provider_testcase {

    /**
     * Tests get_contexts_for_userid function.
     * @covers \tool_import_user_datas\privacy\provider::get_contexts_for_userid
     * Function that get the list of contexts that contain user information for the specified user.
     * @throws coding_exception
     */
    public function test_user_contextlist(): void {
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);
        $usercontext = context_user::instance($user1->id);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        $contextlist = provider::get_contexts_for_userid($user1->id);
        $this->assertCount(1, $contextlist);
        $contextlist = $contextlist->get_contexts();
        $this->assertContains($usercontext, $contextlist);
    }

    /**
     * Test export_context_data_for_user function.
     * @covers \tool_import_user_datas\privacy\provider::export_user_data
     * Function that Export all data within a context for a component for the specified user.
     * @throws coding_exception
     */
    public function test_export_context_data_for_user(): void {
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);
        $usercontext = context_user::instance($user1->id);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        $this->export_context_data_for_user($user1->id, $usercontext, 'tool_import_user_datas');
        $writer = writer::with_context($usercontext);
        $data = $writer->get_data([get_string('pluginname', 'tool_import_user_datas')]);
        $this->assertTrue($writer->has_any_data());
        $this->assertInstanceOf('stdClass', $data);
        $this->assertTrue(property_exists($data, 'import_user_datas_tasks'));
        $this->assertCount(1, $data->import_user_datas_tasks);
        foreach ($data->import_user_datas_tasks as $importuserdatastask) {
            $this->assertEquals($user1->username, $importuserdatastask->username);
        }
    }

    /**
     * Test export_all_data_for_user function.
     * @covers \tool_import_user_datas\privacy\provider::export_user_data
     * funciton that export all data for a component for the specified user.
     * @throws coding_exception
     */
    public function test_export_all_data_for_user(): void {
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);
        $usercontext = context_user::instance($user1->id);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        $this->export_all_data_for_user($user1->id, 'tool_import_user_datas');
        $writer = writer::with_context($usercontext);
        $data = $writer->get_data([get_string('pluginname', 'tool_import_user_datas')]);
        $this->assertTrue($writer->has_any_data());
        $this->assertInstanceOf('stdClass', $data);
        $this->assertTrue(property_exists($data, 'import_user_datas_tasks'));
        $this->assertCount(1, $data->import_user_datas_tasks);
        foreach ($data->import_user_datas_tasks as $importuserdatastask) {
            $this->assertEquals($user1->username, $importuserdatastask->username);
        }
    }

    /**
     * Test delete_data_for_all_users_in_context function.
     * @covers \tool_import_user_datas\privacy\provider::delete_data_for_all_users_in_context
     * Function that delete all data for all users in the specified context
     * @throws coding_exception
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        $this->assertCount(1, $DB->get_records('tool_import_user_datas'));
        $usercontext = context_user::instance($user1->id);
        provider::delete_data_for_all_users_in_context($usercontext);
        $this->assertCount(0, $DB->get_records('tool_import_user_datas'));
    }

    /**
     * Test delete_data_for_users function.
     * @covers \tool_import_user_datas\privacy\provider::delete_data_for_users
     * Function that Delete multiple users within a single context.
     * @throws coding_exception
     */
    public function test_delete_data_for_all_users(): void {
        global $DB;
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $usercontext = context_user::instance($user1->id);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        $userapproveduserlist = new approved_userlist($usercontext, 'tool_import_user_datas', [$user1->id]);
        $this->assertCount(1, $DB->get_records('tool_import_user_datas'));
        provider::delete_data_for_users($userapproveduserlist);
        $this->assertCount(0, $DB->get_records('tool_import_user_datas'));
    }

    /**
     * Test delete_data_for_user function.
     * @covers \tool_import_user_datas\privacy\provider::delete_data_for_users
     * Function that delete all user data for the specified user, in the specified contexts.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_delete_data_for_user(): void {
        global $DB;
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $usercontext = context_user::instance($user1->id);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        $userapproveduserlist = new approved_userlist($usercontext, 'tool_import_user_datas', [$user1->id]);
        $this->assertCount(1, $DB->get_records('tool_import_user_datas'));
        provider::delete_data_for_users($userapproveduserlist);
        $this->assertCount(0, $DB->get_records('tool_import_user_datas'));
    }
}
