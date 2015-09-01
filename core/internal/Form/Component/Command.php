<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Di;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Command extends Component
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
