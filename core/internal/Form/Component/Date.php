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
 * @author kevin duret <kduret@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Date extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        if (!isset($element['html'])) {
            $element['html'] = '';
        }

        if (!isset($element['placeholder']) || (isset($element['placeholder']) && empty($element['placeholder']))) {
            $element['placeholder'] = $element['label_label'];
        }        
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }

        $tpl = Di::getDefault()->get('template');

        $tpl->assign('element', $element);
        
        return array(
            'html' => $tpl->fetch('file:[Core]/form/component/date.tpl'),
            'js' => '$("input.date").daterangepicker({
                singleDatePicker: true,
                timePicker: true,
                timePickerIncrement: 5,
                timePicker12Hour: false,
                format: \'YYYY-MM-DD HH:mm\'
             });'
        );
    }
}
