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
 * Translation class
 *
 * @module     tool/customlang
 * @package    tool
 * @subpackage customlang
 * @copyright  2020 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class translation {

    public function __construct() {
    }

    /**
     * Get string for translation.
     *
     */
    public static function get_string($identifier) {
        global $DB;

        require_capability('tool/customlang:view', context_system::instance());

        list($stringid, $component) = explode('/', $identifier);
        if (!trim($component)) {
            $component = 'core';
        }
        list($plugintype, $pluginname) = core_component::normalize_component($component);
        if ($pluginname) {
            $component = $plugintype . "_" . $pluginname;
        } else {
            $component = $plugintype;
        }
        $lang = current_language();

        $query = "SELECT *
                FROM {tool_customlang}  tc
                LEFT JOIN {tool_customlang_components} tcc ON tcc.id = tc.componentid
                WHERE tc.stringid = ?
                    AND  tc.lang = ?
                    AND  tcc.name = ?
        ";
        $stringobj = $DB->get_record_sql($query, array($stringid, $lang, $component));
        $src = $stringobj->local ?? $stringobj->master ?? $stringobj->original;
        $string = '';
        if ($src) {
            $split = '/{\$a.*}/mU';
            preg_match_all($split, $src, $matches, PREG_SET_ORDER, 0);
            $plain = preg_split($split, $src);
            if (count($plain)) {
                foreach ($plain as $i=>$p) {
                    $string .= html_writer::span($p, 'translation-tool-partstring translation-tool-editable', ['contenteditable' => 'true']);
                    if (isset($matches[$i][0])) {
                        $string .= html_writer::span($matches[$i][0], 'translation-tool-partstring translation-tool-noneditable', ['contenteditable' => 'false']);
                    }
                }
            }
            $error = !(bool)$string;
        } else {
            $url = new \moodle_url('/admin/tool/customlang/index.php');
            $nostringtext = get_string('define_customizations', 'tool_customlang', $url->out());
            $string .= html_writer::span($nostringtext, 'translation-tool-partstring translation-tool-noneditable', ['contenteditable' => 'false']);
            $error = true;
        }


        return json_encode(array(
            "error" => $error,
            "string" => $string,
        ));
    }

    /**
     * Update string.
     *
     */
    public static function update_string($identifier, $string) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/customlang/locallib.php');

        require_capability('tool/customlang:edit', context_system::instance());

        $clearstring = html_entity_decode(preg_replace('/<span class=\"translation-tool-partstring.*>(.*)<\/span>/imU', '$1', $string));
        $clearstring = str_ireplace('&nbsp;', ' ', $clearstring);

        list($stringid, $component) = explode('/', $identifier);
        if (!trim($component)) {
            $component = 'core';
        }
        list($plugintype, $pluginname) = core_component::normalize_component($component);
        if ($pluginname) {
            $component = $plugintype . "_" . $pluginname;
        } else {
            $component = $plugintype;
        }
        $lang = current_language();

        $query = "SELECT tc.*
                FROM {tool_customlang}  tc
                LEFT JOIN {tool_customlang_components} tcc ON tcc.id = tc.componentid
                WHERE tc.stringid = ?
                    AND  tc.lang = ?
                    AND tcc.name = ?
        ";
        $stringobj = $DB->get_record_sql($query, array($stringid, $lang, $component));

        $stringobj->local = $clearstring;
        $stringobj->timecustomized = time();
        $stringobj->modified = 1;

        $res = $DB->update_record('tool_customlang', $stringobj);

        tool_customlang_utils::checkin($lang);

        $error = !(bool)$res;
        $response = !$error ? get_string('string_updated', 'tool_customlang') : get_string('something_wrong', 'tool_customlang');

        return json_encode(array(
            "error" => $error,
            "response" => $response
        ));
    }
}
