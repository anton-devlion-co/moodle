<?php

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
 * Web service external functions and service definitions.
 *
 * @module     tool/customlang
 * @package    tool
 * @subpackage customlang
 * @copyright  2020 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
    'tool_customlang_translation_get_string' => array(
        'classname'   => 'tool_customlang_external',
        'methodname'  => 'translation_get_string',
        'classpath'   => '',
        'description' => 'Get string for translation',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => '',
        'loginrequired' => true,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'tool_customlang_translation_update_string' => array(
        'classname'   => 'tool_customlang_external',
        'methodname'  => 'translation_update_string',
        'classpath'   => '',
        'description' => 'Update translation',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => '',
        'loginrequired' => true,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Tool customlang services' => array(
        'functions' => array(
            'tool_customlang_translation_get_string',
            'tool_customlang_translation_update_string',
        ),
        'enabled'=>1,
        'shortname'=>'tool_customlang'
    )
);
