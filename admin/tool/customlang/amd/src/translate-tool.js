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
 * Javascript for the translate tool.
 *
 * @module     tool/customlang
 * @package    tool
 * @subpackage customlang
 * @copyright  2020 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.7
 */

define([
    'jquery',
    'core/str',
    'core/ajax',
    'theme_boost/popover',
], function ($, Str, Ajax) {
    let strings;
    let attrelems = [];
    let attrpairs = [];
    let attrexists = [];

    function makeIdent(attr) {
        return attr.replace("/", "_");
    }

    function addNewElem(attr, elem) {
        if (!attrexists.includes(elem)) {
            attrexists.push(elem);
            attrpairs.push([attr, makeIdent(attr), elem]);
            attrelems.push(makeIdent(attr));
        }
    }

    function showPopover(el, txt, error) {
        let stringidentifier = el.data('string');
        $('.edit-translation').popover('dispose');
        $('.edit-translation').popover('dispose');
        let popoverTemplate = `<div class="popover popover-translation-block">
                <div class="arrow"></div>
                <div class="">
                    <button type="button" class="close popover-translation-close">&times;</button>
                    <h3 class="popover-header"></h3>
                </div>
                <div class="popover-body"></div>`;
        if (!error) {
            popoverTemplate += `
                    <div class="popover-footer text-center"><button type="button" class="popover-translation-close btn btn-default mr-1">${strings[1]}</button><button type="button" class="popover-translation-update btn btn-primary mr-1" data-string="${stringidentifier}">${strings[2]}</button></div>`;
        }
        popoverTemplate += `</div>`;
        let content = `<div class="popover-translation-wrapper"><div class="popover-translation-string">${txt}</div></div>`;
        $(el).popover({
            content: content,
            template: popoverTemplate,
            placement: "top",
            title: strings[3],
            html: true,
            sanitize: false
        });
        el.popover('show');
    }

    function bindEvents() {
        $(document).on('click', '.edit-translation', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let button = $(this);
            let identifier = button.data('string');
            Ajax.call([{
                methodname: "tool_customlang_translation_get_string",
                args: {
                    identifier: identifier
                },
                done: function (res) {
                    var result = JSON.parse(res);
                    showPopover(button, result.string, result.error);
                },
                fail: Notification.exception
            }]);
        });
        $(document).on('click', '.popover-translation-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.edit-translation').popover('dispose');
        });
        $(document).on('click', '.popover-translation-update', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let editblock = $(this).parents('.popover');
            let content = editblock.find('.popover-translation-string').html();
            let identifier = $(this).data('string');
            Ajax.call([{
                methodname: "tool_customlang_translation_update_string",
                args: {
                    string: content,
                    identifier: identifier
                },
                done: function (res) {
                    var result = JSON.parse(res);
                    if (result.error == false) {
                        editblock.find('.popover-body').html(result.response);
                        editblock.find('.popover-footer').slideUp();
                    }
                },
                fail: Notification.exception
            }]);
        });
    }

    function initPage() {
        $(document).ready(function () {
            // Scan page for language strings.
            let pagehtml = $('body').html().replace(/<(style|script)(\s|.)*?>(\s|.)*?<\/(style|script)>/gmi, ''); // Remove script and style tags.
            const regex = /{((.*?)\/(.*?))}/gm;
            let m;
            while ((m = regex.exec(pagehtml)) !== null) {
                // This is necessary to avoid infinite loops with zero-width matches
                if (m.index === regex.lastIndex) {
                    regex.lastIndex++;
                }
                let containstree = $(':contains(' + m[0] + ')');

                if (containstree.length == 0) { // Match tag attribute value
                    let regexcontain = new RegExp("(?![aria-label])([a-zA-Z0-9_-]+)=\"(((?!\").)+?){" + m[1] + "}\"", "gm"); // Simple pattern, without string, just def {/}.
                    let cont = pagehtml.match(regexcontain);
                    if (cont && cont.length) {
                        cont.forEach((el) => {
                            addNewElem(m[1], el);
                        });
                    }
                } else { // Match text.
                    let lowest = containstree.filter(':not(:has(*))'); // Find all the lowest elements in the tree. May be several ones.
                    let lowest0 = containstree[containstree.length - 1];
                    lowest.push(lowest0);
                    lowest.each(function(ind, currentelement) {
                        if (!$(currentelement).hasClass('addedtrtool_' + makeIdent(m[1]))) {
                            // Add edit button.
                            let regexcurrent = new RegExp("{" + m[1] + "}", "gm");
                            let newelem = m[0] + '<span class="edit-translation" data-string="' + m[1] + '">'+strings[0]+'</span>';
                            $(currentelement).html($(currentelement).html().replace(regexcurrent, newelem));
                            $(currentelement).addClass('addedtrtool addedtrtool_' + makeIdent(m[1]));
                        }
                    });
                }
            }
            attrpairs.forEach(function(elem){
                let identifier = elem[2];
                let q = $("[" + identifier + "]:visible");
                if (q.length) {
                    q.each(function(ind, element) {
                        $(element).after('<span class="edit-translation" data-string="' + elem[0] + '">'+strings[0]+'</span>');
                        $(element).addClass('addedtrtool addedtrtool_' + elem[1]);
                    });
                }
            });
            bindEvents();
        });
    };

    return {
        init: function () {
            Str.get_strings([
                {
                    key:        'edit',
                    component:  'tool_customlang'
                },
                {
                    key:        'cancel',
                    component:  'tool_customlang'
                },
                {
                    key:        'update',
                    component:  'tool_customlang'
                },
                {
                    key:        'translate_string',
                    component:  'tool_customlang'
                }
            ])
            .then(function(s) {
                strings = s;
                initPage();
            })
            .catch();
        }
    };
});