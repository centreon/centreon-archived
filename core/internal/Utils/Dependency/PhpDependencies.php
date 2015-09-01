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

namespace Centreon\Internal\Utils\Dependency;

use Centreon\Internal\Exception\Module\MissingDependenciesException;

/**
 * Check for PHP Dependencies
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class PhpDependencies
{
    /**
     * 
     * @param array $dependencies
     * @param boolean $strict
     * @param boolean $fullScan
     * @return array
     * @throws Exception
     */
    public static function checkDependencies($dependencies = array(), $strict = true, $fullScan = true)
    {
        $status = true;
        $errors = array();
        
        $nbDependencies = count($dependencies);
        foreach ($dependencies as $dependency) {
            if (!extension_loaded($dependency)) {
                
                $message = 'Mandatory PHP module ' . $dependency . ' is not available';
                
                if ($strict) {
                    if (($nbDependencies == 0) || (!$fullScan)) {
                        throw new MissingDependenciesException($message, 1004);
                    }
                    $nbDependencies--;
                } else {
                    $status = false;
                    $errors[] = $message;
                }
            }
        }
        
        return array('success' => $status, 'errors' => $errors);
    }
}