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

namespace CentreonAdministration\Repository;

use CentreonAdministration\Models\Options;
use Centreon\Internal\Form\Validators\Validator;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class OptionRepository extends Repository
{
    /**
     * 
     * @param type $submittedValues
     * @param type $group
     */
    public static function update($submittedValues, $origin = "", $route = "")
    {
        static::validateForm($submittedValues, $origin, $route);
        $currentOptionsList = Options::getOptionsKeysList();

        $optionsToSave = array();
        $optionsToUpdate = array();

        foreach ($submittedValues as $key => $value) {
            if (in_array($key, $currentOptionsList)) {
                $optionsToUpdate[$key]= $value;
            } else {
                $optionsToSave[$key]= $value;
            }
        }

        Options::update($optionsToUpdate);
        Options::insert($optionsToSave, 'default');
    }
    
    /**
     * 
     * @param type $group
     * @param array $options
     * @return array
     */
    public static function get($group = null, array $options = array())
    {
        $listOfOptions = Options::getList($group, $options);
        return $listOfOptions;
    }
    
    
}
