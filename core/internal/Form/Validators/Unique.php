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

namespace Centreon\Internal\Form\Validators;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Unique implements ValidatorInterface
{
    /**
     * 
     * @param type $value
     * @param type $module
     * @param type $objectName
     * @param type $id
     * @param type $fieldname
     * @return boolean
     */
    public function validate($value, $params = array())
    {
        /*$callableObject = '\\' . $module . '\Models\\'.ucwords($objectName);
        if ($callableObject::isUnique($value, $id)) {
            $result = array('success' => true);
        } else {
            $result = array(
                'success' => false,
                'error' => _("\"<i>$value</i>\" is already in use for another $objectName")
            );
        }
        return $result;*/
        
        return array('success' => true, 'error' => '');
    }
}
