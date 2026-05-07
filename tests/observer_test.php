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
 * @subpackage test
 * observer test
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_import_user_datas;

use advanced_testcase;

/**
 * Observer tests
 */
final class observer_test extends advanced_testcase {

    /**
     * Test user creation
     * @covers \tool_import_user_datas\observers::user_created
     * @return void
     * @throws \dml_exception
     */
    public function test_user_created(): void {
        global $DB;
        set_config('trigger_on_create', '1', 'tool_import_user_datas');
        set_config('user_auth', 'manual', 'tool_import_user_datas');
        $tasks = $DB->get_records('tool_import_user_datas');
        $this->assertCount(0, $tasks);
        $this->getDataGenerator()->create_user();
        $tasks = $DB->get_records('tool_import_user_datas');
        $this->assertCount(1, $tasks);
    }

    /**
     * Test user creation 2 times
     * @covers \tool_import_user_datas\observers::user_created
     * @return void
     * @throws \dml_exception
     */
    public function test_user_created_2_times(): void {
        global $DB;
        set_config('trigger_on_create', '1', 'tool_import_user_datas');
        set_config('user_auth', 'manual', 'tool_import_user_datas');
        $tasks = $DB->get_records('tool_import_user_datas');
        $this->assertCount(0, $tasks);
        $this->getDataGenerator()->create_user(['username' => 'cigale', 'auth' => 'manual', 'mnethostid' => 1]);
        $tasks = $DB->get_records('tool_import_user_datas');
        $this->assertCount(1, $tasks);
        ob_start();
        $this->getDataGenerator()->create_user(['username' => 'cigale', 'auth' => 'manual', 'mnethostid' => 0]);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertStringStartsWith('username cigale with mnethostid = 0 not programmed because not same site.',
            $output
        );
        $tasks = $DB->get_records('tool_import_user_datas');
        $this->assertCount(1, $tasks);
    }

    /**
     * Test user creation deactivated
     * @covers \tool_import_user_datas\observers::user_created
     * @return void
     * @throws \dml_exception
     */
    public function test_user_created_not_triggered(): void {
        global $DB;
        set_config('trigger_on_create', '0', 'tool_import_user_datas');
        set_config('user_auth', 'manual', 'tool_import_user_datas');
        $tasks = $DB->get_records('tool_import_user_datas');
        $this->assertCount(0, $tasks);
        $this->getDataGenerator()->create_user();
        $tasks = $DB->get_records('tool_import_user_datas');
        $this->assertCount(0, $tasks);
    }

    /**
     * setUp tests
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->preventResetByRollback(); // Logging waits till the transaction gets committed.
    }
}
