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
 */

namespace Centreon\Internal\Form\Component;

//use Centreon\Internal\Di;

/**
 * Html Checkobox element
 * 
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Checkbox extends Component
{
    /**
     * Return the HTML representation of the checkbox field
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {

        /*$tpl = Di::getDefault()->get('template');
        load CssFile
        $tpl->addCss('bootstrap-toggle.min.css');

         Load JsFile
        $tpl->addJs('bootstrap-toggle.min.js');
        $tpl->addJs('component/centreon.checkbox.js'); */


        (isset($element['html']) ? $value = $element['html'] :  $value = '');

        $values = explode(',', $element['html']);
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }
        
        $addClass = '';
        if (isset($element['label_mandatory']) && $element['label_mandatory'] == "1") {
            $addClass .= 'mandatory-field ';
        }
        
        $inputHtml = '';
        $myJs = '';
        $i = 1;
        
        foreach ($element['label_choices'] as $key => $choice) {
            $htmlSelected = '';
            if (in_array($choice, $values)) {
                $htmlSelected = 'checked=checked';
            }
            $inputHtml .= '<div class="checkbox checkbox-styled"><label class="label-controller" for="'.$element['id'] . $i . '">'.
                        '<input '.'id="'.$element['id']. $i . '" '.
                        'type="'.$element['label_type'].'" '.'name="'.$element['name'].'[]" '.
                        'value=' . $choice . ' '.$htmlSelected.' '.
                        '/><span>'.' '.$key.'</span>'.
                        '</label></div>';
            $i++;
        }
        
        return array(
            'html' => $inputHtml,
            'js' => $myJs
        );
    }
}
