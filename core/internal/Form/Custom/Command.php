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
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
class Command extends Customobject
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        // Select for Commands
        $commandSelect = Select::renderHtmlInput($element);
        
        $myHtml = '<div class="row"><div class="col-sm-12">'.$commandSelect['html'].'</div></div>';
        $myJs = $commandSelect['js'];
        
        $myHtml .='<div id="'.$element['name'].'_command_args" class="row"></div>';
        
        $commandArgumentsUrl = Di::getDefault()
                            ->get('router')
                            ->getPathFor('/centreon-configuration/command/[i:id]/arguments');
        
        $myJs .= ' '
            . '$("#'.$element['name'].'").on("change", function() { '
                . '$("#'.$element['name'].'_command_args").empty(); '
                . 'var commandId = $("#'.$element['name'].'").val(); '
                . 'var realCommandArgumentsUrl = "'.$commandArgumentsUrl.'"; '
                . 'var computedCommandArgumentsUrl = realCommandArgumentsUrl.replace("[i:id]", commandId);'
                . '$.ajax({ '
                    . 'url: computedCommandArgumentsUrl,'
                    . 'type: "GET", '
                    . 'dataType: "json" '
                . '})'
                . '.success(function(data, status, jqxhr) { '
                    . 'var argumentsHtml = ""; '
                    . '$.each(data, function(key, value){ '
                        . 'argumentsHtml += "<div class=\"row\"><div class=\"col-sm-3\">"+value.name+"</div>'
                        . '<div class=\"col-sm-4\">'
                            . '<input class=\"form-control\" type=\"text\" value=\""+value.value+"\">'
                            . '</div>'
                        . '<div class=\"col-sm-3\">"+value.example+"</div></div>" '
                    . '}); '
                    . '$("#'.$element['name'].'_command_args").append(argumentsHtml); '
                . '});'
            . '});';
        
        return array(
            'html' => $myHtml,
            'js' => $myJs
        );
    }
}
