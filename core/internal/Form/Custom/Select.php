<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */
namespace Centreon\Internal\Form\Custom;

use Centreon\Internal\Di;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Select extends Customobject
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $tpl = Di::getDefault()->get('template');
        // Load CssFile
        $tpl->addCss('select2.css')
            ->addCss('select2-bootstrap.css');

        // Load JsFile
        $tpl->addJs('jquery.select2/select2.min.js');

        if (isset($element['label_defaultValuesRoute'])) {
            $element['label_defaultValuesRoute'] = Di::getDefault()
                            ->get('router')
                            ->getPathFor($element['label_defaultValuesRoute'], $element['label_extra']);
        }
        if (isset($element['label_listValuesRoute'])) {
            $element['label_listValuesRoute'] = Di::getDefault()
                            ->get('router')
                            ->getPathFor($element['label_listValuesRoute'], $element['label_extra']);
        }
        
        $addClass = '';
        if (isset($element['label_mandatory']) && $element['label_mandatory'] == "1") {
            $addClass .= 'mandatory-field ';
        }
        $addJs = '';
        if (isset($element['label_ordered']) && $element['label_ordered']) {
            $addJs = '$("#'
                .$element['name']
                .'").on("change", function() { $("#'
                .$element['name']
                .'_val").html($("#'
                .$element['name']
                .'").val());});';

            $addJs .= '$("#'.$element['name'].'").select2("container").find("ul.select2-choices").sortable({
                    containment: "parent",
                    start: function() { $("#'.$element['name'].'").select2("onSortStart"); },
                    update: function() { $("#'.$element['name'].'").select2("onSortEnd"); }
                  });'."\n";
        }

        if (!isset($element['label_multiple'])) {
            $element['label_multiple'] = 0;
        }

        $myHtml = '<input '
            . 'class="form-control input-sm '
            . $addClass
            . '" id="'.$element['name']
            . '" name="' . $element['name']
            . '" style="width: 100%;" type="hidden" value=" " />';
        $myJs = ''
            . '$("#'.$element['name'].'").select2({'
                . 'placeholder:"'.$element['label_label'].'", '
                . 'multiple:'.(int)$element['label_multiple'].', '
                . 'allowClear: true, '
                . 'formatResult: select2_formatResult, '
                . 'formatSelection: select2_formatSelection, ';
        if (isset($element['label_selectData'])) {
            $myJs .= 'data: { results: '.$element['label_selectData'].' },';
        } elseif (isset($element['label_defaultValuesRoute'])) {
            $myJs .= ''
                . 'ajax: {'
                    .'data: function(term, page) {'
                        .'return { '
                            .'q: term, '
                        .'};'
                    .'},'
                    .'dataType: "json", '
                    .'url:"'.$element['label_defaultValuesRoute'].'", '
                    .'results: function (data){ '
                        .'return {results:data, more:false}; '
                    .'}'
                .'},';
        }
        $initCallback = '';
        if (isset($element['label_initCallback']) && $element['label_initCallback'] != '') {
            $initCallback = '
                if ( typeof ' . $element['label_initCallback'] . ' === "function" ) {
                    ' . $element['label_initCallback'] . '(data);
                } else {
                    callFunction = "' . $element['label_initCallback'] . '(data)";
                    eval(callFunction);
                }
            ';
        }

        if (isset($element['label_selectDefault']) && $element['label_selectDefault'] != "[]") {
            $myJs .= ''
                .'initSelection: function(element, callback) {'
                    .'var data = '.$element['label_selectDefault'].';'
                    .'callback(data);'
                    .'id = $(element).val();'
                    .'if (data.id) {'
                    .'  $(element).val(data.id);'
                    .'}'
                    .$initCallback
                .'}';
        } elseif (isset($element['label_listValuesRoute'])) {
            $myJs .= ''
                .'initSelection: function(element, callback) { '
                    .'var id=$(element).val();'
                    .'if (id == " ") {
                        $.ajax("'.$element['label_listValuesRoute'].'", {
                            dataType: "json"
                        }).done(function(data) {
                            if (data.length > 0 || data.id) {
                                callback(data);
                                id = $(element).val();
                                if (data.id) {
                                    $(element).val(data.id);
                                }
                                if (id.match(/^,/)) {
                                    $(element).val(id.substring(1, id.length));
                                } ' . $initCallback . '
                            }
                        });
                     }
                },';
        }
        $myJs .= ''
            .'});'."\n";
        
        $myJs .= $addJs;
        return array(
            'html' => $myHtml,
            'js' => $myJs
        );
    }
}
