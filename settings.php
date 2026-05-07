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
 * admin  tool import_user_datas settings
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
if ($hassiteconfig) {
    $plugin = core_plugin_manager::instance()->get_plugin_info('tool_import_user_datas');
    $myexternalfolder = new admin_category(
        'toolsimportuserdatasfolder',
        new lang_string('pluginname', 'tool_import_user_datas'),
        $plugin->is_enabled() === false
    );
    $ADMIN->add('tools', $myexternalfolder);
    $settings = new admin_settingpage('tool_import_user_datas', get_string('settings', 'tool_import_user_datas'));
    $ADMIN->add('toolsimportuserdatasfolder', $settings);
    $settings->add(new admin_setting_configcheckbox('tool_import_user_datas/activated',
        get_string('activated', 'tool_import_user_datas'),
        get_string('activated_desc', 'tool_import_user_datas'),
        0));
    $settings->add(
        new admin_setting_configtext(
            "tool_import_user_datas/remote_url",
            get_string('remote_url', 'tool_import_user_datas'),
            get_string('remote_url_desc', 'tool_import_user_datas'),
            ''
        )
    );
    $settings->add(
        new admin_setting_configtext(
            "tool_import_user_datas/remote_token",
            get_string('remote_token', 'tool_import_user_datas'),
            get_string('remote_token_desc', 'tool_import_user_datas'),
            ''
        )
    );
    $settings->add(
        new admin_setting_configtextarea(
            "tool_import_user_datas/user_auth",
            get_string('user_auth', 'tool_import_user_datas'),
            get_string('user_auth_desc', 'tool_import_user_datas'),
            ''
        )
    );
    $settings->add(
        new admin_setting_configtext(
            "tool_import_user_datas/preferences",
            get_string('preferences', 'tool_import_user_datas'),
            get_string('preferences_desc', 'tool_import_user_datas'),
            'forum_useexperimentalui;forum_markasreadonnotification;htmleditor;'
            .'timeformat;calendar_startwday;calendar_maxevents;'
            .'calendar_lookahead;calendar_persistflt;'
            .'core_contentbank_visibility;'
            .'message_blocknoncontacts;message_provider_moodle_instantmessage_enabled;message_entertosend;'
            .'mailcharset'
            , PARAM_TEXT
            )
    );
    $settings->add(
        new admin_setting_configtext(
            "tool_import_user_datas/user_datas",
            get_string('user_datas', 'tool_import_user_datas'),
            get_string('user_datas_desc', 'tool_import_user_datas'),
            'firstnamephonetic;lastnamephonetic;middlename;alternatename;maildigest;autosubscribe;'
            .'trackforums;lang;calendartype;mailformat;'
            .'city;country;lang;timezone;idnumber;institution;department;phone1;phone2;address',
            PARAM_TEXT
        )
    );
    $settings->add(new admin_setting_configcheckbox('tool_import_user_datas/trigger_on_create',
        get_string('trigger_on_create', 'tool_import_user_datas'),
        get_string('trigger_on_create_desc', 'tool_import_user_datas'),
        0));
    $settings->add(new admin_setting_configcheckbox('tool_import_user_datas/adhoctaks',
        get_string('adhoctaks', 'tool_import_user_datas'),
        get_string('adhoctaks_desc', 'tool_import_user_datas'),
        0));
    $setting = new admin_setting_configtext(
        "tool_import_user_datas/paging",
        get_string('paging', 'tool_import_user_datas'),
        get_string('paging_desc', 'tool_import_user_datas'),
        0,
        PARAM_INT
    );
    $settings->add(
        $setting
    );
    $setting->set_required_flag_options(admin_setting_flag::ENABLED, false);
    $ADMIN->add('toolsimportuserdatasfolder',
        new admin_externalpage(
            'import_user_datas_managment',
            get_string('managetasks', 'tool_import_user_datas'),
            "$CFG->wwwroot/admin/tool/import_user_datas/admin/index.php",
            'moodle/site:config'));
}
