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
namespace tool_import_user_datas\reportbuilder\local\entities;

use core\output\actions\component_action;
use core\output\select_menu;
use core\output\single_select;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;
use moodle_url;
use tool_import_user_datas\user_import_preferences_and_datas_task;

/**
 * import user datas entities for listing tasks
 */
class import_user_datas_task extends base {
    /**
     * Database tables that this entity uses
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'tool_import_user_datas',
            'user',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('import_user_datas_entity', 'tool_import_user_datas');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
        $tablealias = $this->get_table_alias('tool_import_user_datas');
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }
        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }
        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        global $PAGE;
        // Loading amd without explicite functionname not works.
        $jscode =
            "require(['tool_import_user_datas/changestatus'],
                function(changestatus) {
                    asyncchangestatus = function(id) {
                        changestatus.asyncchangestatus(id);
                    }
                }
            );";
        $PAGE->requires->js_amd_inline($jscode);
        $tablealias = $this->get_table_alias('tool_import_user_datas');
        $useralias = $this->get_table_alias('user');
        $columns[] = (
            new column(
                'id',
                new lang_string('id', 'tool_import_user_datas'),
                $this->get_entity_name()
            ))
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$tablealias}.id")
            ->set_is_sortable(true);
        $columns[] = (
            new column(
                'username',
                new lang_string('username', 'tool_import_user_datas'),
                $this->get_entity_name()
            ))
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.username")
            ->set_is_sortable(true);
        $columns[] = (
        new column(
            'auth',
            new lang_string('auth', 'tool_import_user_datas'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.auth")
            ->set_is_sortable(true);
        $columns[] = (
            new column(
                'firstname',
                new lang_string('firstname', 'tool_import_user_datas'),
                $this->get_entity_name()
            ))
            ->add_join($this->get_user_join())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$useralias}.firstname")
            ->set_is_sortable(true);
        $columns[] = (
            new column(
                'lastname',
                new lang_string('lastname', 'tool_import_user_datas'),
                $this->get_entity_name()
            ))
            ->add_join($this->get_user_join())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$useralias}.lastname")
            ->set_is_sortable(true);
        $columns[] = (
            new column(
                'email',
                new lang_string('email', 'tool_import_user_datas'),
                $this->get_entity_name()
            ))
            ->add_join($this->get_user_join())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$useralias}.email")
            ->set_is_sortable(true);
        $columns[] = (
        new column(
            'status',
            new lang_string('status', 'tool_import_user_datas'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$tablealias}.status, {$tablealias}.id")
            ->set_is_sortable(true)
            ->set_callback(static function (?string $value, \stdClass $row): string {
                global $OUTPUT;
                $url = new moodle_url('/admin/tool/import_user_datas/WS');
                $options = [
                    user_import_preferences_and_datas_task::STATUS_SHEDULED =>
                        new lang_string('scheduledstatus', 'tool_import_user_datas'),
                    user_import_preferences_and_datas_task::STATUS_INPROGRESS =>
                        new lang_string('inprogressstatus', 'tool_import_user_datas'),
                    user_import_preferences_and_datas_task::STATUS_PERFORMED =>
                        new lang_string('performedstatus', 'tool_import_user_datas'),
                    user_import_preferences_and_datas_task::STATUS_ERROR =>
                        new lang_string('errorstatus', 'tool_import_user_datas'),
                ];
                $selectmenu = \html_writer::select(
                    $options,
                    'status_select_' . $row->id,
                    $value,
                    null,
                    ['onchange' => 'asyncchangestatus(' . $row->id . ')']
                );
                return $selectmenu;
            });
        $columns[] = (
            new column(
                'timecreated',
                new lang_string('timecreated', 'tool_import_user_datas'),
                $this->get_entity_name()
            ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$tablealias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'])
            ->add_callback(fn($value) => $value ?: get_string('never'));
        $columns[] = (
            new column(
                'timemodified',
                new lang_string('timemodified', 'tool_import_user_datas'),
                $this->get_entity_name()
            ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$tablealias}.timemodified")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'])
            ->add_callback(fn($value) => $value ?: get_string('never'));
        $columns[] = (
            new column(
                'timeprocessed',
                new lang_string('timeprocessed', 'tool_import_user_datas'),
                $this->get_entity_name()
            ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$tablealias}.timeprocessed")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'])
            ->add_callback(fn($value) => $value ?: get_string('never'));
        return $columns;
    }

    /**
     * return user datatable join
     * @return string
     * @throws \coding_exception
     */
    private function get_user_join(): string {

        // If the user table is already joined, we don't need to do that again.
        if ($this->has_table_join_alias('user')) {
            return '';
        }

        $tablealias = $this->get_table_alias('tool_import_user_datas');
        $useralias = $this->get_table_alias('user');

        return "INNER JOIN {user} {$useralias} ON {$useralias}.username = {$tablealias}.username";
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        global $DB;

        $tablealias = $this->get_table_alias('tool_import_user_datas');
        $useralias = $this->get_table_alias('user');

        $filters[] =
            (new filter(
                number::class,
                'id',
                new lang_string('id', 'tool_import_user_datas'),
                $this->get_entity_name(),
                "{$tablealias}.id"
            ))
                ->add_joins($this->get_joins());
        $filters[] =
            (new filter(
                text::class,
                'username',
                new lang_string('username', 'tool_import_user_datas'),
                $this->get_entity_name(),
                "{$tablealias}.username"
            ))
                ->add_joins($this->get_joins());
        $filters[] =
            (new filter(
                select::class,
                'status',
                new lang_string('status', 'tool_import_user_datas'),
                $this->get_entity_name(),
                "{$tablealias}.status"
            ))
                ->add_joins($this->get_joins())
                ->set_options_callback(
                    static function (): array {
                        $status = [
                            user_import_preferences_and_datas_task::STATUS_SHEDULED =>
                                new lang_string('scheduledstatus', 'tool_import_user_datas'),
                            user_import_preferences_and_datas_task::STATUS_INPROGRESS =>
                                new lang_string('inprogressstatus', 'tool_import_user_datas'),
                            user_import_preferences_and_datas_task::STATUS_PERFORMED =>
                                new lang_string('performedstatus', 'tool_import_user_datas'),
                            user_import_preferences_and_datas_task::STATUS_ERROR =>
                                new lang_string('errorstatus', 'tool_import_user_datas'),

                        ];
                        return $status;
                    }
                );
        $filters[] =
            (new filter(
                text::class,
                'firstname',
                new lang_string('firstname', 'tool_import_user_datas'),
                $this->get_entity_name(),
                "{$useralias}.firstname"
            ))
                ->add_joins($this->get_joins());
        $filters[] =
            (new filter(
                text::class,
                'lastname',
                new lang_string('lastname', 'tool_import_user_datas'),
                $this->get_entity_name(),
                "{$useralias}.lastname"
            ))
                ->add_joins($this->get_joins());
        $filters[] =
            (new filter(
                text::class,
                'email',
                new lang_string('lastname', 'tool_import_user_datas'),
                $this->get_entity_name(),
                "{$useralias}.email"
            ))
                ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('timecreated', 'tool_import_user_datas'),
            $this->get_entity_name(),
            "{$tablealias}.timecreated"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('timemodified', 'tool_import_user_datas'),
            $this->get_entity_name(),
            "{$tablealias}.timemodified"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timeprocessed',
            new lang_string('timeprocessed', 'tool_import_user_datas'),
            $this->get_entity_name(),
            "{$tablealias}.timeprocessed"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }
}
