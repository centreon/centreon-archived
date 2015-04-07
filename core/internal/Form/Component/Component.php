<?php

/*
 * Copyright 2005-2014 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */
namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Di;

/**
 * Description of FormComponent
 *
 * @author lionel
 */
class Component
{
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
                        if (false === isset($validator['params']['charaters'])) {
                            break;
                        }
                        $rule['characters'] = $validator['params']['characters'];
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
