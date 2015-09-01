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
class Email extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        (isset($element['html']) ? $value = 'value="'.$element['html'].'" ' :  $value = '');
        
        if (!isset($element['label']) || (isset($element['label']) && empty($element['label']))) {
            $element['label'] = $element['name'];
        }
        
        if (!isset($element['placeholder']) || (isset($element['placeholder']) && empty($element['placeholder']))) {
            $placeholder = 'placeholder="'.$element['name'].'" ';
        }
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }
        
        $addClass = '';
        $required = '';
        if (isset($element['label_mandatory']) && $element['label_mandatory'] == "1") {
            $addClass .= 'mandatory-field ';
            $required = ' required';
        }
        
        $myJs = "";
        
        $myHtml = '<div id="'.$element['name'].'_email" class="input-group input-group-sm">
                <span class="input-group-addon">@</span>';
        
        $myHtml .= '<input '.
                'id="'.$element['id'].'" '.
                'type="email" '.
                'name="'.$element['name'].'" '.
                $value.
                'class="form-control '.$addClass.'" '.
                $placeholder.
                $required .
                '/>';
                    
        $myHtml .= '<span id="'.$element['name'].'_email_span" class=""></span>'
            . '</div>';
        
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
        
        $validationUrl = Di::getDefault()
                            ->get('router')
                            ->getPathFor('/validator/email');
        
        $validations['eventValidation'] .= ' $("#'.$element['name'].'").on("blur", function() {
                    $.ajax({
                        url: "'.$validationUrl.'",
                        type: "POST",
                        data: {"email":$("#'.$element['name'].'").val()},
                        context: document.body
                    })
                    .success(function(data, status, jqxhr) {
                        if (data.success) {
                            $("#'
                            .$element['name']
                            .'_email").removeClass("has-error has-feedback");
                            $("#'
                            .$element['name']
                            .'_email_span").removeClass("glyphicon glyphicon-remove form-control-feedback");
                            $("#'
                            .$element['name']
                            .'_email").addClass("has-success has-feedback");
                            $("#'
                            .$element['name']
                            .'_email_span").addClass("glyphicon glyphicon-ok form-control-feedback");    
                        } else {
                            $("#'
                            .$element['name']
                            .'_email").removeClass("has-error has-feedback");
                            $("#'
                            .$element['name']
                            .'_email_span").removeClass("glyphicon glyphicon-ok form-control-feedback");
                            $("#'
                            .$element['name']
                            .'_email").addClass("has-error has-feedback");
                            $("#'
                            .$element['name']
                            .'_email_span").addClass("glyphicon glyphicon-remove form-control-feedback"); 
                        }
                    });
                });';
        
        return $validations;
    }
}
