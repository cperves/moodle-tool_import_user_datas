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

use core_reportbuilder\system_report_factory;
use tool_import_user_datas\admin\addimporttask_form;
use tool_import_user_datas\reportbuilder\local\systemreports\import_user_datas_tasks;
use tool_import_user_datas\user_import_preferences_and_datas_task;

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup(
    'import_user_datas_managment',
    '',
    [],
    new moodle_url('/admin/tool/impor_user_datas/admin/index.php', [])
);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managetasks', 'tool_import_user_datas'));
$addimporttaskform = new addimporttask_form();
if ($data = $addimporttaskform->get_data()) {
    $return = user_import_preferences_and_datas_task::schedule_user_datas_import($data->username, $data->auth);
    if ($return) {
        echo $OUTPUT->notification(
            get_string(
                'task_added_success',
                'tool_import_user_datas',
                (object)['username' => $data->username, 'auth' => $data->auth]
            ),
            'success'
        );
        $addimporttaskform->reset();
    }
}
$addimporttaskform->display();
$report = system_report_factory::create(import_user_datas_tasks::class, context_system::instance());
echo $report->output();
$PAGE->requires->js_call_amd('tool_import_user_datas/changestatus');

echo $OUTPUT->footer();
