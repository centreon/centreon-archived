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

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Customarguments extends Component
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
        
        $myJs = "";
        
        $myHtml = '<div class="row">
                    <div class="col-sm-5"><input class="form-control" name="" /></div>
                    <div class="col-sm-2" style="text-align:center;"><i class="fa fa-arrow-left"></i></div>
                    <div class="col-sm-5"><input class="form-control" name="" /></div>
                   </div>';
        
        return array(
            'html' => $myHtml,
            'js' => $myJs
        );
    }
}
