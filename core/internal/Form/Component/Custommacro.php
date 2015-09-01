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
use CentreonConfiguration\Repository\CustomMacroRepository;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Custommacro extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        if (!isset($element['label']) || (isset($element['label']) && empty($element['label']))) {
            $element['label'] = $element['name'];
        }
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }
        
        // DefaultValue
        $functionCall = 'load' . ucfirst($element['label_object']) .'CustomMacro';
        $currentCustommacro = CustomMacroRepository::$functionCall($element['label_extra']['id']);

        if ($element['label_object'] == 'host') {
            $regex = '/^\$_HOST(.+)\$$/';
        } else {
            $regex = '/^\$_SERVICE(.+)\$$/';
        }

        foreach ($currentCustommacro as &$cm) {
            $cm['macro_name'] = preg_replace($regex, '$1', $cm['macro_name']);
        }

        $tpl = Di::getDefault()->get('template');

        $tpl->addJs('centreon-clone.js')
            ->addJs('component/custommacro.js');

        $tpl->assign('currentCustommacro', $currentCustommacro);
 
        return array(
            'html' => $tpl->fetch('file:[Core]/form/component/custommacro.tpl'),
        );
    }
}
