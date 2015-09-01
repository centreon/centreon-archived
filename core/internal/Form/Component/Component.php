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
use Centreon\Internal\Utils\String\CamelCaseTransformation;

/**
 * Description of FormComponent
 *
 * @author lionel
 */
class Component
{
    
    protected static $jsFile = array();

    /**
     * Render Html for input field
     *
     * @param array
     * @return array array('html' => string, 'js' => string)
     */
    public static function renderHtmlInput(array $element)
    {
        
    }

    /**
     *
     * @param type $componentName
     * @return type
     */
    public static function parseComponentName($componentName)
    {
        $call = "";
        $parsedComponent = explode('.', $componentName);

        if ((count($parsedComponent) == 1) || $parsedComponent[0] === 'core') {
            $call .= '\\Centreon\\Internal\\Form\\Component\\' . ucfirst($componentName);
        } else {
            $call .= '\\' . CamelCaseTransformation::customToCamelCase($parsedComponent[0], '-')
                . '\\Forms\\Components\\';
            for ($i = 1; $i < count($parsedComponent); $i++) {
                $call .= ucfirst($parsedComponent[$i]);
            }
        }

        return $call;
    }
    
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function addValidation($element)
    {
        $eventValidation = '';
        $submitValidation = '';
        
        if (isset($element['label_label']) && (!empty($element['label_label']))) {
            $label = $element['label_label'];
        } else {
            $label = $element['name'];
        }
        
        $rules = array();
        
        if (isset($element['label_validators'])) {
            $remote = false;
            foreach ($element['label_validators'] as $validator) {
                $rule = null;
                switch ($validator['rules']) {
                    case 'remote':
                        if ($remote) {
                            // @todo log warning message more than one remote
                            break;
                        }
                        $rule = array(
                            'action' => $validator['validator_action']
                        );
                        $remote = true;
                        break;
                    case 'size':
                        if (false === isset($validator['params']['minlength'])
                            && false === isset($validator['params']['maxlength'])) {
                            // @todo log Warning bad format
                            break;
                        }
                        if (isset($validator['params']['minlength'])) {
                            $rule['minlength'] = $validator['params']['minlength'];
                        }
                        if (isset($validator['params']['maxlength'])) {
                            $rule['maxlength'] = $validator['params']['maxlength'];
                        }
                        break;
                    case 'forbiddenChar':
                        if (false === isset($validator['params']['characters'])) {
                            break;
                        }
                        $rule['characters'] = $validator['params']['characters'];
                    case 'equalTo':
                        if (false === isset($validator['params']['equalfield'])) {
                            break;
                        }
                        $rule['equalfield'] = $validator['params']['equalfield'];
                    case 'authorizedValues':
                        if (false === isset($validator['params']['values'])) {
                            break;
                        }
                        $rule['values'] = $validator['params']['values'];    
                    default:
                        // @todo log warning rules not found
                        break;
                }
                if (false === is_null($rule)) {
                    $rules = array_merge($rules, array(
                        $validator['rules'] => $rule
                    ));
                }
            }
        }
        
        if ((isset($element['parent_fields']) && $element['parent_fields'] != '')
            && (isset($element['parent_value']) && $element['parent_value'] != '')
            && (isset($element['child_mandatory']) && $element['child_mandatory'] == 1)) {
                
            $rules['required'] = array(
                'parent_id' => $element['parent_fields'],
                'parent_value' => $element['parent_value']
            );
        }
        
        return array(
            'submitValidation' => $submitValidation,
            'eventValidation' => array($element['name'] => $rules)
        );
    }
}
