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
 * adhoc task file  for import user datas
 * @package     tool_import_user_datas
 * @copyright   2025 Université de Strasbourg <www.unistra.fr
 * @author Céline Pervès<cperves@unistra.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_import_user_datas\task;

use core\task\adhoc_task;
use tool_import_user_datas\tools;

defined('MOODLE_INTERNAL') || die();
global $CFG;

/**
 * import user datas adhoc task class
 */
class perform_import_user_datas_task extends adhoc_task {
    /**
     * execute adhoc task
     * @return void
     * @throws \core\exception\moodle_exception
     */
    public function execute() {
        $data = $this->get_custom_data();
        tools::import_preferences_and_datas($data);
    }
}
