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
 * admin tool tool_import_user_datas webserices
 * an admin tool to import user preferences from one Moodle to another
 * @package tool_import_user_datas
 * @subpackage
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'tool_import_user_datas_get_user_datas' => [
        'classname' => 'tool_import_user_datas_external',
        'methodname' => 'get_user_datas_and_preferences',
        'classpath' => 'admin/tool/import_user_datas/externallib.php',
        'description' => 'Get user datas for a given username',
        'type' => 'read',
        'capabilities' => 'tool/import_user_datas:get_user_preferences_and_datas_for_user,
            tool/import_user_datas:set_user_preferences_and_datas_for_user,moodle/site:config',
    ],
    'tool_import_user_datas_change_task_status' => [
        'classname' => 'tool_import_user_datas_external',
        'methodname' => 'change_task_status',
        'description' => 'Change task status',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/site:config',
    ],
];
$services = [
    'admin tool import user preference webservice' => [
        'functions' => [
            'tool_import_user_datas_get_user_datas',
        ],
        'requiredcapability' => '',
        'restrictedusers' => 1,
        'enabled' => 1,
        'shortname' => 'wstoolimportuserdatas',
    ],
];


