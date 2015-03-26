<?php

/*
 * Copyright 2005-2014 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */
namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Di;

/**
 * Description of FormComponent
 *
 * @author lionel
 */
class Component
{
    /**
     * Render Html for input field
     *
     * @param array
     * @return array array('html' => string, 'js' => string)
     */
    public static function renderHtmlInput(array $element)
    {
        
    }
    
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function addValidation($element)
    {
        $eventValidation = '';
        $submitValidation = '';
        
        if (isset($element['label_label']) && (!empty($element['label_label']))) {
            $label = $element['label_label'];
        } else {
            $label = $element['name'];
        }
        
        if (isset($element['label_validators'])) {
            foreach ($element['label_validators'] as $validator) {
                if (strstr(strtolower($validator['validator_action']), 'jquery') !== false) {

                } else {
                    $validatorRoute = Di::getDefault()
                        ->get('router')
                        ->getPathFor($validator['validator_action']);

                    $ajaxCall = '$.ajax({
                            url: "'.$validatorRoute.'",
                            type: "POST",
                            data: {
                                "value":$(this).val(),
                                "module":$("[name=\'module\']").val(),
                                "object":$("[name=\'object\']").val(),
                                "object_id":$("[name=\'object_id\']").val()
                            },
                            dataType: "json",
                            context: document.body
                        })';
                    $eventList = explode(',', trim($validator['events']));
                    foreach ($eventList as $event) {
                        if (!empty($event)) {
                            $eventValidation .= '$("#'.$element['name'].'").on ("'.$event.'" , function(){ '.
                               $ajaxCall.
                               '.success(function(data, status, jqxhr) {
                                    if (data["success"]) {
                                        alertClose();
                                        $(this).val(data["value"]);
                                    } else {
                                        alertClose();
                                        alertMessage("<b>'.$label.'</b> " + data["error"], "alert-danger");
                                    }
                               });
                            });';
                        }
                    }
                }
            }
        }
        
        return array(
            'submitValidation' => $submitValidation,
            'eventValidation' => $eventValidation
        );
    }
}
