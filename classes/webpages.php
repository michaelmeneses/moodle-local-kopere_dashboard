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
 * @created    13/05/17 13:28
 * @package    local_kopere_dashboard
 * @copyright  2017 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_dashboard;

defined('MOODLE_INTERNAL') || die();

use local_kopere_dashboard\html\button;
use local_kopere_dashboard\html\data_table;
use local_kopere_dashboard\html\form;
use local_kopere_dashboard\html\inputs\input_checkbox;
use local_kopere_dashboard\html\inputs\input_html_editor;
use local_kopere_dashboard\html\inputs\input_select;
use local_kopere_dashboard\html\inputs\input_text;
use local_kopere_dashboard\html\table_header_item;
use local_kopere_dashboard\util\dashboard_util;
use local_kopere_dashboard\util\end_util;
use local_kopere_dashboard\util\header;
use local_kopere_dashboard\util\html;
use local_kopere_dashboard\util\mensagem;
use local_kopere_dashboard\util\server_util;
use local_kopere_dashboard\util\title_util;
use local_kopere_dashboard\vo\kopere_dashboard_menu;
use local_kopere_dashboard\vo\kopere_dashboard_webpages;

/**
 * Class webpages
 * @package local_kopere_dashboard
 */
class webpages {

    /**
     *
     */
    public function dashboard() {
        global $DB, $CFG;

        dashboard_util::start_page(get_string_kopere('webpages_title'), -1, 'webpages::settings', 'Páginas-estáticas');

        echo '<div class="element-box">';

        $menus = $DB->get_records('kopere_dashboard_menu', null, 'title ASC');

        button::add(get_string_kopere('webpages_menu_create'), 'webpages::edit_menu', '', true, false, true);

        if (!$menus) {
            button::help('webpages', get_string_kopere('webpages_menu_help'), 'Páginas-estáticas');
        } else {
            $table = new data_table();
            $table->add_header('#', 'id', table_header_item::TYPE_INT, null, 'width: 20px');
            $table->add_header(get_string_kopere('webpages_table_link'), 'link');
            $table->add_header(get_string_kopere('webpages_table_title'), 'title');

            $table->set_click_modal('open-ajax-table.php?webpages::edit_menu&id={id}', 'id');
            $table->print_header('', false);
            $table->set_row($menus);
            $table->close(false);
        }
        echo '</div>';

        if ($menus) {
            echo '<div class="element-box">';
            title_util::print_h3('webpages_title');
            button::add(get_string_kopere('webpages_page_create'), 'webpages::edit_page');

            $pages = $DB->get_records('kopere_dashboard_webpages', null, 'pageorder ASC');

            $table = new data_table();
            $table->add_header('#', 'id', table_header_item::TYPE_INT, null, 'width: 20px');
            $table->add_header(get_string_kopere('webpages_table_link'), 'link');
            $table->add_header(get_string_kopere('webpages_table_link'), 'title');
            $table->add_header(get_string_kopere('webpages_table_visible'), 'visible', table_header_item::RENDERER_VISIBLE);
            $table->add_header(get_string_kopere('webpages_table_order'), 'pageorder', table_header_item::TYPE_INT);

            $table->set_click_redirect('webpages::details&id={id}', 'id');
            $table->print_header('', false);
            $table->set_row($pages);
            $table->close(false);

            echo '</div>';
        }

        button::info(get_string_kopere('webpages_page_crash'), $CFG->wwwroot . '/admin/tool/replace/');

        dashboard_util::end_page();
    }

    /**
     *
     */
    public function details() {
        global $DB, $CFG;

        $id = optional_param('id', 0, PARAM_INT);
        /** @var kopere_dashboard_webpages $webpages */
        $webpages = $DB->get_record('kopere_dashboard_webpages', array('id' => $id));
        header::notfound_null($webpages, get_string_kopere('webpages_page_notfound'));

        dashboard_util::start_page(array(
            array('webpages::dashboard', get_string_kopere('webpages_title')),
            $webpages->title
        ), -1);
        echo '<div class="element-box">';

        $linkpagina = "{$CFG->wwwroot}/local/kopere_dashboard/?p={$webpages->link}";

        button::info(get_string_kopere('webpages_page_view'), $linkpagina, '', false);
        button::edit(get_string_kopere('webpages_page_edit'), 'webpages::edit_page&id=' . $webpages->id, 'margin-left-15', false);
        button::delete(get_string_kopere('webpages_page_delete'),
            'webpages::delete_page&id=' . $webpages->id, 'margin-left-15', false, false, true);

        $form = new form();
        $form->print_panel(get_string_kopere('webpages_table_link'), "<a target='_blank' href='$linkpagina'>$linkpagina</a>");
        $form->print_panel(get_string_kopere('webpages_table_title'), $webpages->title);
        $form->print_panel(get_string_kopere('webpages_table_link'), $webpages->link);
        if ($webpages->courseid) {
            $course = $DB->get_record('course', array('id' => $webpages->courseid));
            if ($course) {
                $form->print_panel(get_string_kopere('webpages_page_course'),
                    '<a href="?courses::details&courseid=' . $webpages->courseid . '">' . $course->fullname . '</a>');
            }
        }
        $form->print_panel(get_string_kopere('webpages_table_theme'), $this->theme_name($webpages->theme));
        $form->print_panel(get_string_kopere('webpages_table_text'), $webpages->text);
        $form->print_panel(get_string_kopere('webpages_table_visible'), $webpages->visible ? get_string('yes') : get_string('no'));

        echo '</div>';
        dashboard_util::end_page();

    }

    /**
     *
     */
    public function edit_page() {
        global $DB;

        $id = optional_param('id', 0, PARAM_INT);

        /** @var kopere_dashboard_webpages $webpages */
        $webpages = $DB->get_record('kopere_dashboard_webpages', array('id' => $id));
        if (!$webpages) {
            $webpages = kopere_dashboard_webpages::create_by_default();
            $webpages->theme = get_config('local_kopere_dashboard', 'webpages_theme');
            dashboard_util::start_page(array(
                array('webpages::dashboard', get_string_kopere('webpages_title')),
                get_string_kopere('webpages_page_new')
            ), -1);
        } else {
            $webpages = kopere_dashboard_webpages::create_by_object($webpages);
            dashboard_util::start_page(array(
                array('webpages::dashboard', get_string_kopere('webpages_title')),
                array('webpages::details&id=' . $webpages->id, $webpages->title),
                get_string_kopere('webpages_page_edit')
            ), -1);
        }

        echo '<div class="element-box">';

        $form = new form('webpages::edit_page_save');
        $form->create_hidden_input('id', $webpages->id);
        $form->add_input(
            input_text::new_instance()->set_title(get_string_kopere('webpages_page_title'))
                ->set_name('title')
                ->set_value($webpages->title)
                ->set_required()
        );
        $form->add_input(
            input_text::new_instance()->set_title(get_string_kopere('webpages_table_link'))
                ->set_name('link')
                ->set_value($webpages->link)
                ->set_required()
        );
        $form->add_input(
            input_select::new_instance()->set_title(get_string_kopere('webpages_page_menu'))
                ->set_name('menuid')
                ->set_values(self::list_menus())
                ->set_value($webpages->menuid));
        $form->add_input(
            input_select::new_instance()->set_title(get_string_kopere('webpages_table_theme'))
                ->set_name('theme')
                ->set_values(self::list_themes())
                ->set_value($webpages->theme));

        $form->add_input(
            input_html_editor::new_instance()->set_title(get_string_kopere('webpages_table_text'))
                ->set_name('text')
                ->set_value($webpages->text)
                ->set_required()
        );

        $form->add_input(
            input_checkbox::new_instance()->set_title(get_string_kopere('webpages_table_visible'))
                ->set_name('visible')
                ->set_checked($webpages->visible));

        $form->create_submit_input(get_string_kopere('webpages_page_save'));
        $form->close();

        ?>
        <script>
            $('#title').focusout(function () {
                var url = 'open-ajax-table.php?webpages::ajax_get_page_url';
                var postData = {
                    title : $(this).val(),
                    id    : $('#id').val()
                };
                $.post(url, postData, function (data) {
                    $('#link').val(data);
                    $('#theme').focus();
                }, 'text');
            });
        </script>
        <?php
        echo '</div>';
        dashboard_util::end_page();
    }

    /**
     *
     */
    public function edit_page_save() {
        global $DB;

        $webpages = kopere_dashboard_webpages::create_by_default();
        $webpages->id = optional_param('id', 0, PARAM_INT);

        if ($webpages->title == '' || $webpages->text == '') {
            mensagem::agenda_mensagem_warning(get_string_kopere('webpages_page_error'));
            $this->edit_page();
        } else {
            if ($webpages->id) {
                mensagem::agenda_mensagem_success(get_string_kopere('webpages_page_updated'));
                try {
                    $DB->update_record('kopere_dashboard_webpages', $webpages);
                    self::delete_cache();
                    header::location('webpages::details&id=' . $webpages->id);
                } catch (\dml_exception $e) {
                    mensagem::print_danger($e->error);
                }
            } else {
                mensagem::agenda_mensagem_success(get_string_kopere('webpages_page_created'));
                try {
                    $webpages->id = $DB->insert_record('kopere_dashboard_webpages', $webpages);

                    self::delete_cache();
                    header::location('webpages::details&id=' . $webpages->id);
                } catch (\dml_exception $e) {
                    mensagem::print_danger($e->error);
                }
            }
        }
    }

    /**
     *
     */
    public function delete_page() {
        global $DB, $CFG;

        $status = optional_param('status', '', PARAM_TEXT);
        $id = optional_param('id', 0, PARAM_INT);
        /** @var kopere_dashboard_webpages $webpages */
        $webpages = $DB->get_record('kopere_dashboard_webpages', array('id' => $id));
        header::notfound_null($webpages, get_string_kopere('webpages_page_notfound'));

        if ($status == 'sim') {
            $DB->delete_records('kopere_dashboard_webpages', array('id' => $id));

            self::delete_cache();
            mensagem::agenda_mensagem_success(get_string_kopere('webpages_page_deleted'));
            header::location('webpages::dashboard');
        }

        dashboard_util::start_page(array(
            array('webpages::dashboard', get_string_kopere('webpages_title')),
            array('webpages::details&id=' . $webpages->id, $webpages->title),
            get_string_kopere('webpages_page_delete')
        ), -1);

        echo "<p>" . get_string_kopere('webpages_page_delete_confirm', $webpages) . "</p>";
        button::delete(get_string('yes'), 'webpages::delete_page&status=sim&id=' . $webpages->id, '', false);
        button::add(get_string('no'), 'webpages::details&id=' . $webpages->id, 'margin-left-10', false);

        dashboard_util::end_page();
    }

    /**
     *
     */
    public function edit_menu() {
        global $DB;

        $id = optional_param('id', 0, PARAM_INT);

        /** @var kopere_dashboard_menu $webpages */
        $menus = $DB->get_record('kopere_dashboard_menu', array('id' => $id));
        if (!$menus) {
            $menus = kopere_dashboard_menu::create_by_default();
            $menus->theme = get_config('kopere_dashboard_menu', 'webpages_theme');
            if (!AJAX_SCRIPT) {
                dashboard_util::start_page(array(
                    array('webpages::dashboard', get_string_kopere('webpages_title')),
                    get_string_kopere('webpages_menu_new')
                ));
            } else {
                dashboard_util::start_popup(get_string_kopere('webpages_menu_new'), 'webpages::edit_menu_save');
            }
        } else {
            $menus = kopere_dashboard_menu::create_by_object($menus);

            if (!AJAX_SCRIPT) {
                dashboard_util::start_page(array(
                    array('webpages::dashboard', get_string_kopere('webpages_title')),
                    get_string_kopere('webpages_menu_edit')
                ), -1);
            } else {
                dashboard_util::start_popup(get_string_kopere('webpages_menu_edit'), 'webpages::edit_menu_save');
            }
        }

        echo '<div class="element-box">';

        if (!AJAX_SCRIPT) {
            $form = new form('webpages::edit_menu_save');
        } else {
            $form = new form();
        }
        $form->create_hidden_input('id', $menus->id);
        $form->add_input(
            input_text::new_instance()->set_title(get_string_kopere('webpages_menu_title'))
                ->set_name('title')
                ->set_value($menus->title)
                ->set_required()
        );

        $form->add_input(
            input_text::new_instance()->set_title(get_string_kopere('webpages_menu_link'))
                ->set_name('link')
                ->set_value($menus->link)
                ->set_required()
        );
        if (!AJAX_SCRIPT) {
            $form->create_submit_input(get_string_kopere('webpages_menu_save'));
        }
        $form->close();

        echo '</div>';

        ?>
        <script>
            $('#title').focusout(function () {
                var url = 'open-ajax-table.php?webpages::ajax_get_menu_url';
                var postData = {
                    title : $(this).val(),
                    id    : $('#id').val()
                };
                $.post(url, postData, function (data) {
                    $('#link').val(data).focus();
                }, 'text');
            });
        </script>
        <?php
        echo '</div>';

        if (!AJAX_SCRIPT) {
            dashboard_util::end_page();
        } else {
            if ($id) {
                dashboard_util::end_popup('webpages::delete_menu&id=' . $id);
            } else {
                dashboard_util::end_popup();
            }
        }
    }

    /**
     *
     */
    public function edit_menu_save() {
        global $DB;

        $menu = kopere_dashboard_menu::create_by_default();
        $menu->id = optional_param('id', 0, PARAM_INT);

        if ($menu->title == '') {
            mensagem::agenda_mensagem_warning(get_string_kopere('webpages_menu_error'));
        } else {
            if ($menu->id) {
                mensagem::agenda_mensagem_success(get_string_kopere('webpages_menu_updated'));
                $DB->update_record('kopere_dashboard_menu', $menu);
            } else {
                mensagem::agenda_mensagem_success(get_string_kopere('webpages_menu_created'));
                $menu->id = $DB->insert_record('kopere_dashboard_menu', $menu);
            }

            self::delete_cache();
            header::location('webpages::dashboard');
        }
    }

    /**
     *
     */
    public function delete_menu() {
        global $DB;

        $status = optional_param('status', '', PARAM_TEXT);
        $id = optional_param('id', 0, PARAM_INT);
        /** @var kopere_dashboard_menu $menu */
        $menu = $DB->get_record('kopere_dashboard_menu', array('id' => $id));
        header::notfound_null($menu, get_string_kopere('webpages_page_notfound'));

        if ($status == 'sim') {
            $DB->delete_records('kopere_dashboard_menu', array('id' => $id));

            self::delete_cache();
            mensagem::agenda_mensagem_success(get_string_kopere('webpages_menu_deleted'));
            header::location('webpages::dashboard');
        }

        dashboard_util::start_page(array(
            array('webpages::dashboard', get_string_kopere('webpages_menu_subtitle')),
            array('webpages::details&id=' . $menu->id, $menu->title),
            get_string_kopere('webpages_menu_delete')
        ));

        echo "<p>Deseja realmente excluir o menu <strong>{$menu->title}</strong>?</p>";
        button::delete(get_string('yes'), 'webpages::delete_menu&status=sim&id=' . $menu->id, '', false);
        button::add(get_string('no'), 'webpages::dashboard', 'margin-left-10', false);

        dashboard_util::end_page();
    }

    /**
     *
     */
    public function ajax_get_page_url() {
        global $DB;

        $title = optional_param('title', '', PARAM_TEXT);
        $id = optional_param('id', 0, PARAM_INT);

        if ($title == '') {
            end_util::end_script_show('');
        }

        $title = html::link($title);

        $sql
            = "SELECT *
                 FROM {kopere_dashboard_webpages}
                WHERE id    !=   :id
                  AND title LIKE :title";

        $webpages = $DB->get_record_sql($sql,
            array(
                'id' => $id,
                'title' => $title,
            ));
        if ($webpages) {
            end_util::end_script_show($title . '-2');
        } else {
            end_util::end_script_show($title);
        }
    }

    /**
     *
     */
    public function ajax_get_menu_url() {
        global $DB;

        $title = optional_param('title', '', PARAM_TEXT);
        $id = optional_param('id', 0, PARAM_INT);

        if ($title == '') {
            end_util::end_script_show();
        }

        $title = html::link($title);

        $sql
            = "SELECT *
                 FROM {kopere_dashboard_menu}
                WHERE id    !=   :id
                  AND title LIKE :title";

        $webpages = $DB->get_record_sql($sql,
            array(
                'id' => $id,
                'title' => $title,
            ));
        if ($webpages) {
            end_util::end_script_show($title . '-2');
        } else {
            end_util::end_script_show($title);
        }
    }

    /**
     * @param $theme
     * @return string
     */
    private function theme_name($theme) {
        $themes = self::list_themes();

        foreach ($themes as $t) {
            return $t['value'];
        }

        return '-';
    }

    /**
     * @return array
     */
    public static function list_menus() {
        global $DB;

        $menus = $DB->get_records('kopere_dashboard_menu', null, 'title ASC');

        $return = array();
        /** @var kopere_dashboard_menu $menu */
        foreach ($menus as $menu) {
            $return[] = array('key' => $menu->id, 'value' => $menu->title);
        }

        return $return;
    }

    /**
     * @return array
     */
    public static function list_themes() {
        $layouts = array(
            array(
                'key' => 'base',
                'value' => 'theme_base'
            ),
            array(
                'key' => 'standard',
                'value' => 'theme_standard'
            ),
            array(
                'key' => 'popup',
                'value' => 'theme_popup'
            ),
            array(
                'key' => 'frametop',
                'value' => 'theme_frametop'
            ),
            array(
                'key' => 'print',
                'value' => 'theme_print'
            ),
            array(
                'key' => 'report',
                'value' => 'theme_report'
            )
        );

        return $layouts;
    }

    /**
     *
     */
    public function settings() {
        ob_clean();
        dashboard_util::start_popup(get_string_kopere('webpages_page_settigs'), 'settings::save');

        $form = new form();

        $form->add_input(
            input_select::new_instance()->set_title(get_string_kopere('webpages_page_theme'))
                ->set_values($this->list_themes())
                ->set_value_by_config('webpages_theme'));

        $form->add_input(
            input_text::new_instance()->set_title(get_string_kopere('webpages_page_analytics'))
                ->set_value_by_config('webpages_analytics_id')
                ->set_description(get_string_kopere('webpages_page_analyticsdesc')));

        $form->close();

        dashboard_util::end_popup();
    }

    /**
     * @return string
     */
    public static function get_cache_dir() {
        $path = server_util::get_kopere_pathath(true) . 'cache';

        @mkdir($path);

        return $path . '/';
    }

    /**
     *
     */
    private static function delete_cache() {
        $caches = glob(self::get_cache_dir() . '*');
        foreach ($caches as $cache) {
            unlink($cache);
        }
    }
}