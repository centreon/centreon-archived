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
namespace Centreon\Core\Form\Custom;

class Custommacro extends Customobject
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        (isset($element['value']) ? $value = 'value="'.$element['value'].'" ' :  $value = '');
        
        if (!isset($element['label']) || (isset($element['label']) && empty($element['label']))) {
            $element['label'] = $element['name'];
        }
        
        if (!isset($element['placeholder']) || (isset($element['placeholder']) && empty($element['placeholder']))) {
            $placeholder = 'placeholder="'.$element['name'].'" ';
        }
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }
        
        // Load JsFile
        $tpl = \Centreon\Core\Di::getDefault()->get('template');
        $tpl->addJs('centreon-clone.js');
        
        $myJs = '';
        
        $myHtml = '<div id="'.$element['name'].'_controls">
                       <div id="'.$element['name'].'_add" class="clone-trigger">
                           <a id="'.$element['name'].'_add_link" class="addclone" style="padding-right:5px;cursor:pointer;">
                               '._("Add a new entry").' <i data-action="add" class="fa fa-plus-square"></i>
                           </a>
                       </div>
                   </div>';
        $myHtml .= '<ul id="'.$element['name'].'" class="clonable no-deco-list">
                        <li id="'.$element['name'].'_noforms_template">
                            <p class="muted">'._('Nothing here, use the "Add" button').'</p>
                        </li>
                        <li id="'.$element['name'].'_clone_template" class="clone_template" style="display:none;">
                            <hr style="margin:2;"/>
                            <div class="row clone-cell">
                                <div class="col-sm-1"><label class="label-controller">'._("Name").'</label></div>
                                <div class="col-sm-3"><input class="form-control" name="macro_name[]" /></div>
                                <div class="col-sm-1"><label class="label-controller">'._("Value").'</label></div>
                                <div class="col-sm-3"><input class="hidden-value form-control" name="macro_value[]" /></div>
                                <div class="col-sm-1"><label class="label-controller">'._("Hidden").'</label></div>
                                <div class="col-sm-1"><input class="hidden-value-trigger" type="checkbox" name="macro_hidden[]" /></div>
                                <div class="col-sm-2">
                                    <span class="clonehandle" style="cursor:move;"><i class="fa fa-arrows"></i><span>
                                    &nbsp;
                                    <span class="remove-trigger" style="cursor:pointer;"><i class="fa fa-times-circle"></i><span>
                                </div>
                            </div>
                            <input type="hidden" name="clone_order_'.$element['name'].'_#index#" id="clone_order_#index#" />
                        </li>
                    </ul>';
        
        return array(
            'html' => $myHtml,
            'js' => $myJs
        );
    }
}