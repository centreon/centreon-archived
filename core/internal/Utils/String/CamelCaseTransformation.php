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

namespace Centreon\Internal\Utils\String;

/**
 * Utils for CamelCase strings
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class CamelCaseTransformation
{
    /**
     * 
     */
    const REGEX = '/((?:^|[A-Z])[a-z]+)/';
    
    /**
     * 
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function camelCaseToCustom($string, $separator = "")
    {
        $matches = array();
        preg_match_all(self::REGEX, $string, $matches);
        return implode($separator, $matches[0]);
    }
    
    /**
     * 
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function customToCamelCase($string, $separator = "")
    {
        $stringExploded = ucwords(implode(' ', explode($separator, $string)));
        return str_replace(' ', '', $stringExploded);
    }
    
    /**
     * 
     * @param string $string
     * @return boolean
     */
    public static function isCamelCase($string)
    {
        $isCamelCase = false;
        if (preg_match(self::REGEX, $string) === 1) {
            $isCamelCase = true;
        }
        return $isCamelCase;
    }
}
