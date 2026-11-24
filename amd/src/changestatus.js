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
 * Change status ajax lib
 *
 * @module     tool/import_user_datas
 * @copyright  2026 Université de Strasbourg  {@link <unistra.fr}
 * @author      Céline Pervès <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import notification from "core/notification";

/**
 * Change status of task
 * @param {int} id
 * @returns {Promise<void>}
 */
export const asyncchangestatus = async(id) => {
    // AJAX call to webservice.
    var selectelt = document.getElementById('menustatus_select_' + id);
    await new Promise(resolve => {
        return Ajax.call([{
            methodname: 'tool_import_user_datas_change_task_status',
            args: {
                'taskid': id,
                'status': selectelt.value,
            },
            done: result => {
                resolve(result);

            },
            fail: notification.exception
        }]);
    });
};