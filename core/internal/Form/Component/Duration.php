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

use Centreon\Internal\Di;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Duration extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $element['placeholder'] = $element['name'];
        if (isset($element['label_label']) && (!empty($element['label_label']))) {
            $element['placeholder'] = $element['label_label'];
        }
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }
        
        $myJs = '$(".'.$element['name'].'_scale_values").on("click", function(){'
             . '$("#'.$element['id'].'_scale").val($(this).text()); '
             . '$("#'.$element['id'].'_scale_selector").text($(this).text());'
             . '});';

        $tpl = Di::getDefault()->get('template');

        $tpl->assign('element', $element);

        return array(
            'html' => $tpl->fetch('file:[Core]/form/component/duration.tpl'),
            'js' => $myJs
        );
    }
}
