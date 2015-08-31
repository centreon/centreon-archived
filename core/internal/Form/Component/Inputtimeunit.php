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
use Centreon\Internal\Form\Component\Component;

/**
 * Component for a input with selection of time unit
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package Centreon
 * @subpackage Form
 * @version 3.0.0
 */
class Inputtimeunit extends Component
{
    /**
     * Generate and return the html for the element
     *
     * @param array $element The element to parse
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        file_put_contents("/tmp/debug_form", var_export($element, true));
        $tpl = Di::getDefault()->get('template');

        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }

        $element['clsTypeUnit'] = 'input-time-unit';

        $tpl->assign('element', $element);
        $tpl->addJs('component/centreon.inputWithUnit.js');
        
        return array(
            'html' => $tpl->fetch('file:[Core]/form/component/inputwithunit.tpl'),
            'js' => '$(function () {
                    $(".input-time-unit").centreonInputWithUnit();
                });'
        );
    }
}
