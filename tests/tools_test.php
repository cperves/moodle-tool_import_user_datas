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
 * Test tools class
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_import_user_datas;

use advanced_testcase;
use context_system;
use core\exception\required_capability_exception;

defined('MOODLE_INTERNAL') || die();
global $CFG;

/**
 * Tool test class
 */
final class tools_test extends advanced_testcase {

    /**
     * test tools::set_user_preferences_and_datas
     * @covers \tool_import_user_datas\tools::set_user_preferences_and_datas
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_set_user_preferences_and_datas(): void {
        global $DB;
        $datagenerator = $this->getDataGenerator();
        $user = $datagenerator->create_user();
        $userdatasandprefs = new \stdClass();
        $userdatasandprefs->preferences = [
            (object)['name' => 'htmleditor', 'value' => 'tiny'],
            (object)['name' => 'forum_useexperimentalui', 'value' => 1],
            (object)['name' => 'forum_markasreadonnotification', 'value' => 1],
            (object)['name' => 'timeformat', 'value' => '%I:%M %p'],
            (object)['name' => 'calendar_startwday', 'value' => 0],
            (object)['name' => 'calendar_maxevents', 'value' => 42],
            (object)['name' => 'calendar_lookahead', 'value' => 42],
            (object)['name' => 'calendar_persistflt', 'value' => 1],
            (object)['name' => 'core_contentbank_visibility', 'value' => 0],
            (object)['name' => 'message_blocknoncontacts', 'value' => \core_message\api::MESSAGE_PRIVACY_ONLYCONTACTS],
            (object)['name' => 'message_provider_moodle_instantmessage_enabled', 'value' => 'none'],
            (object)['name' => 'message_entertosend', 'value' => 0],
            (object)['name' => 'mailcharset', 'value' => 'EUC-JP'],
            (object)['name' => 'maxexternalblogsperuser', 'value' => 0],
        ];
        $userdatasandprefs->userdatas = [
            (object)['name' => 'autosubscribe', 'value' => 0],
            (object)['name' => 'city', 'value' => 'Strasbourg'],
            (object)['name' => 'firstnamephonetic', 'value' => "ˈfɜrstˌneɪm fəˈnɛtɪk"],
            (object)['name' => 'lastnamephonetic', 'value' => "ˈlæstˌneɪm fəˈnɛtɪk"],
            (object)['name' => 'middlename', 'value' => 'middlename'],
            (object)['name' => 'alternatename', 'value' => 'alternatename'],
            (object)['name' => 'maildigest', 'value' => 1],
            (object)['name' => 'trackforums', 'value' => 1],
            (object)['name' => 'lang', 'value' => 'en'],
            (object)['name' => 'calendartype', 'value' => 'gregorian'],
            (object)['name' => 'mailformat', 'value' => 1],
            (object)['name' => 'city', 'value' => 'Strasbourg'],
            (object)['name' => 'country', 'value' => 'FR'],
            (object)['name' => 'timezone', 'value' => 'Europe/Berlin'],
            (object)['name' => 'idnumber', 'value' => '42;-)'],
            (object)['name' => 'institution', 'value' => 'Université de Strasbourg'],
            (object)['name' => 'department' , 'value' => 'DIP'],
            (object)['name' => 'phone1', 'value' => '42'],
            (object)['name' => 'phone2', 'value' => '43'],
            (object)['name' => 'address', 'value' => 'somewhere over the rainbow'],
            (object)['name' => 'firstname', 'value' => 'Clémence'],
        ];
        tools::set_user_preferences_and_datas(
            $user->username,
            $userdatasandprefs
        );
        // Forced reloading of cache.
        unset($user->preference);
        check_user_preferences_loaded($user);
        $user = $DB->get_record('user', ['id' => $user->id]);
        $this->assertEquals(0, $user->autosubscribe);
        $this->assertEquals('Strasbourg', $user->city);
        $this->assertEquals("ˈfɜrstˌneɪm fəˈnɛtɪk", $user->firstnamephonetic);
        $this->assertEquals("ˈlæstˌneɪm fəˈnɛtɪk", $user->lastnamephonetic);
        $this->assertEquals('middlename', $user->middlename);
        $this->assertEquals('alternatename', $user->alternatename);
        $this->assertEquals(1, $user->maildigest);
        $this->assertEquals(1, $user->trackforums);
        $this->assertEquals('en', $user->lang);
        $this->assertEquals('gregorian', $user->calendartype);
        $this->assertEquals(1, $user->mailformat);
        $this->assertEquals('Strasbourg', $user->city);
        $this->assertEquals('FR', $user->country);
        $this->assertEquals('Europe/Berlin', $user->timezone);
        $this->assertEquals('42;-)', $user->idnumber);
        $this->assertEquals('Université de Strasbourg', $user->institution);
        $this->assertEquals('DIP', $user->department);
        $this->assertEquals('42', $user->phone1);
        $this->assertEquals('43', $user->phone2);
        $this->assertEquals('somewhere over the rainbow', $user->address);
        $this->assertNotEquals('Clémence', $user->firstname);

        // Check user preferences.
        $userpreferences  = get_user_preferences(null, null, $user);
        $this->assertEquals('tiny', $userpreferences['htmleditor']);
        $this->assertEquals('1', $userpreferences['forum_useexperimentalui']);
        $this->assertEquals('1', $userpreferences['forum_markasreadonnotification']);
        $this->assertEquals('%I:%M %p', $userpreferences['timeformat']);
        $this->assertEquals(0, $userpreferences['calendar_startwday']);
        $this->assertEquals(42, $userpreferences['calendar_maxevents']);
        $this->assertEquals(42, $userpreferences['calendar_lookahead']);
        $this->assertEquals(1, $userpreferences['calendar_persistflt']);
        $this->assertEquals(0, $userpreferences['core_contentbank_visibility']);
        $this->assertEquals(\core_message\api::MESSAGE_PRIVACY_ONLYCONTACTS,
            $userpreferences['message_blocknoncontacts']);
        $this->assertEquals('none', $userpreferences['message_provider_moodle_instantmessage_enabled']);
        $this->assertEquals(0, $userpreferences['message_entertosend']);
        $this->assertEquals('EUC-JP', $userpreferences['mailcharset']);
        $this->assertNotContainsEquals('maxexternalblogsperuser', array_keys($userpreferences));
    }

    /**
     * test set_user_preferences_and_datas with correct permissions
     * @covers \tool_import_user_datas\tools::set_user_preferences_and_datas
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_set_user_preferences_and_datas_with_permission(): void {
        $this->create_and_set_webservice_user();
        $this->test_set_user_preferences_and_datas();
    }

    /**
     * test set_user_preferences_and_datas without correct permissions
     * @covers \tool_import_user_datas\tools::set_user_preferences_and_datas
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_set_user_preferences_and_datas_without_permission(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage(
            get_string('nopermissions', 'error',
                get_string('import_user_datas:set_user_preferences_and_datas_for_user',
                    'tool_import_user_datas'
                )
            )
        );
        $this->test_set_user_preferences_and_datas();
    }

    /**
     * setUp tests
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->preventResetByRollback(); // Logging waits till the transaction gets committed.
        $this->setAdminUser();
        set_config('trigger_on_create', false, 'tool_import_user_datas');
        set_config(
            'preferences',
            'forum_useexperimentalui;forum_markasreadonnotification;htmleditor;'
            .'timeformat;calendar_startwday;calendar_maxevents;'
            .'calendar_lookahead;calendar_persistflt;'
            .'core_contentbank_visibility;'
            .'message_blocknoncontacts;message_provider_moodle_instantmessage_enabled;message_entertosend;'
            .'mailcharset',
            'tool_import_user_datas'
        );
        set_config(
            'user_datas',
            'firstnamephonetic;lastnamephonetic;middlename;alternatename;maildigest;autosubscribe;trackforums;'
            .'lang;calendartype;mailformat;'
            .'city;country;lang;timezone;idnumber;institution;department;phone1;phone2;address',
            'tool_import_user_datas'
        );

    }

    /**
     * function to create webservice user for tests
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function create_and_set_webservice_user(): void {
        global $DB;
        // Webservice settings.
        $systemcontext = context_system::instance();
        $wsuser = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $wsrole = $DB->get_record('role', ['id' => $roleid]);
        assign_capability('tool/import_user_datas:get_user_preferences_and_datas_for_user', CAP_ALLOW,
            $wsrole->id, $systemcontext->id, true);
        assign_capability('tool/import_user_datas:set_user_preferences_and_datas_for_user', CAP_ALLOW,
            $wsrole->id, $systemcontext->id, true);
        role_assign($wsrole->id, $wsuser->id, $systemcontext->id);
        // Add necessary capabilities for restore user.
        accesslib_clear_all_caches_for_unit_testing();
        $this->setUser($wsuser);
    }
}
