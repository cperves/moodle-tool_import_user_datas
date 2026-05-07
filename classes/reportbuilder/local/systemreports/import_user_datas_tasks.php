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

namespace tool_import_user_datas\reportbuilder\local\systemreports;

use context_system;
use core_reportbuilder\local\report\action;
use core_reportbuilder\system_report;
use tool_import_user_datas\reportbuilder\local\entities\import_user_datas_task;

/**
 * system report for listing import user datas tasks
 */
class import_user_datas_tasks extends system_report {
    /**
     * @var user data task entity
     */
    private $importuserdatastaskentity;

    /**
     * initiaise entity
     * @return void
     * @throws \coding_exception
     */
    protected function initialise(): void {
        $this->importuserdatastaskentity = new import_user_datas_task();
        $entitymainalias = $this->importuserdatastaskentity->get_table_alias('tool_import_user_datas');
        $this->set_main_table('tool_import_user_datas', $entitymainalias);
        $this->add_entity($this->importuserdatastaskentity);
        $this->add_base_fields("{$entitymainalias}.id");
        $this->add_columns();
        $this->add_filters();
    }

    /**
     * return true if current user has convinient capabilities to view entity
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function can_view(): bool {
        return has_capability('moodle/site:config', context_system::instance());
    }

    /**
     * add columns to report entity
     * @return void
     * @throws \coding_exception
     */
    public function add_columns(): void {
        $entitityname = 'import_user_datas_task';

        $this->add_columns_from_entities([
            $entitityname . ':id',
            $entitityname . ':username',
            $entitityname . ':auth',
            $entitityname . ':firstname',
            $entitityname . ':lastname',
            $entitityname . ':email',
            $entitityname . ':status',
            $entitityname . ':timecreated',
            $entitityname . ':timemodified',
            $entitityname . ':timeprocessed',
        ]);
        $this->set_initial_sort_column($entitityname . ':id', SORT_DESC);
    }

    /**
     * add filters to report entity
     * @return void
     */
    protected function add_filters(): void {
        $entitityname = 'import_user_datas_task';
        $filters = [
            $entitityname . ':id',
            $entitityname . ':username',
            $entitityname . ':firstname',
            $entitityname . ':lastname',
            $entitityname . ':email',
            $entitityname . ':status',
            $entitityname . ':timecreated',
            $entitityname . ':timemodified',
            $entitityname . ':timeprocessed',
        ];

        $this->add_filters_from_entities($filters);
    }
}
