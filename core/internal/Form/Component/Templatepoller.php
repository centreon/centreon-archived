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
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Templatepoller extends Select
{
    /**
     * Render the element in html
     *
     * @param array $element The information of field
     * @return array The html and extra information
     */
    public static function renderHtmlInput(array $element)
    {
        $info = parent::renderHtmlInput($element);
        $info['css'] = 'col-sm-7';
        $info['extrahtml'] = '<div class="col-sm-1">
            <button class="btn btn-sm" disabled><i class="fa fa-gear fa-btn-inactive"></i></button>
            </div>
            <div class="col-sm-1">
            <button class="btn btn-sm" disabled><i class="fa fa-database fa-btn-inactive"></i></button>
            </div>';
        return $info;
    }
}
