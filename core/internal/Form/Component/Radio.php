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


/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Radio extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {

        (isset($element['html']) ? $value = $element['html'] :  $value = '');
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }
        
        $addClass = '';
        $required = '';
        if (isset($element['label_mandatory']) && $element['label_mandatory'] == "1") {
            $addClass .= 'mandatory-field';
            $required .= ' required';
        }
        
        $inputHtml = '';
        $myJs = '';
        $i = 1;


        if (isset($element['label_choices'])) {

            $inputHtml .='<div class="choiceGroup">';
            foreach ($element['label_choices'] as $key => $choice) {
                $htmlSelected = '';
                if ($value == $choice) {
                    $htmlSelected = 'checked=checked';
                }
                $inputHtml .= '<label class="label-controller radio-styled" for="'.$element['id'] . $i . '">'.
                            '<input '.'id="'.$element['id']. $i . '" '.
                            'type="'.$element['label_type'].'" '.'name="'.$element['name'].'" '.
                            'value=' . $choice . ' '.$htmlSelected.' '.
                            $required;
                if ($element['label_parent_field'] != '' && $element['label_parent_value'] != '') {
                    $inputHtml .= ' data-parentfield="' . $element['label_parent_field'] . '"';
                    $inputHtml .= ' data-parentvalue="' . $element['label_parent_value'] . '"';
                }
                $inputHtml .= ' /><span></span>'.' '.$key.
                            '</label>';
                $i++;
            }
            $inputHtml .='</div>';
        }
        
        return array(
            'html' => $inputHtml,
            'js' => $myJs
        );
    }
}
