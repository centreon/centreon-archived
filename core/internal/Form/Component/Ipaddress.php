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

class Ipaddress extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        (isset($element['html']) ? $value = 'value="'.$element['html'].'" ' :  $value = '');
        
        if (!isset($element['placeholder']) || (isset($element['placeholder']) && empty($element['placeholder']))) {
            $placeholder = 'placeholder="'.$element['name'].'" ';
        }
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }
        
        $addClass = '';
        $required = '';
        if (isset($element['label_mandatory']) && $element['label_mandatory'] == "1") {
            $addClass .= ' mandatory-field ';
            $required .= ' required';
        }
        
        $myJs = "";
        
        $myHtml = '<div id="'.$element['name'].'_ipaddress" class="inlineGroup">';
        
        $myHtml .= '<div class="Elem1"><input '.
                'id="'.$element['id'].'" '.
                'type="text" '.
                'name="'.$element['name'].'" '.
                $value.
                'class="form-control'.$addClass.'" '.
                $placeholder.
                $required.
                '/></div>';
                    
        $myHtml .= '<cite id="'.$element['name'].'_ipaddress_span"></cite>';
        
        $myHtml .= '<div id="'.$element['name'].'_resolve_dns_span" class="input-group-btn Elem2">'
            . '<button id="'.$element['name'].'_resolve_dns" class="btnC btnDefault" type="button"><h6>DNS</h6></button>'
            . '</div>';
        
        $myHtml .= '</div>';
        
        return array(
            'html' => $myHtml,
            'js' => $myJs
        );
    }
    
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function addValidation($element)
    {
        $validations = parent::addValidation($element);
        
        $resolveUrl = Di::getDefault()
                        ->get('router')
                        ->getPathFor('/validator/resolvedns');
        
        $validations['eventValidation']['extraJs'] = '$("#'.$element['name'].'_resolve_dns").on("click", function(){
                $.ajax({
                    url: "'.$resolveUrl.'",
                    type: "POST",
                    data: {"dnsname":$("#'.$element['name'].'").val()},
                    dataType: "json",
                    context: document.body
                })
                .success(function(data, status, jqxhr) {
                    alertClose();
                    if (data["success"]) {
                        $("#'.$element['name'].'").val(data["value"]);
                        $("#'.$element['name'].'").trigger("blur");
                    } else {
                        alertMessage(data["error"], "alert-danger");
                    }
                });
            });';
        
        $validations['eventValidation'][$element['name']] = array(
            'ipaddress' => array()
        );
        
        return $validations;
    }
}
