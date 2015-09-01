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

namespace Centreon\Internal\Utils;

/**
 * Utils for Array in Centreon
 *
 * @authors Lionel Assepo
 * @package Centreon
 * @subpackage Core
 */
class CentreonArray
{
    /**
    * 
    * @param array $array
    * @return int
    */
   public static function arrayDepth(array $array)
   {
       $max_depth = 1;

       foreach ($array as $value) {
           if (is_array($value)) {
               $depth = arrayDepth($value) + 1;

               if ($depth > $max_depth) {
                   $max_depth = $depth;
               }
           }
       }

       return $max_depth;
   }

   /**
    * Inserts values after specific key.
    *
    * @param array $array
    * @param sting/integer $position
    * @param array $values
    * @return array
    * @throws \Exception
    */
   public static function insertAfter(array &$array, $position, array $values)
   {
       // enforce existing position
       if (!isset($array[$position]))
       {
           throw new \Exception(strtr('Array position does not exist (:1)', array(':1' => $position)));
       }

       // offset
       $offset = 0;

       // loop through array
       foreach ($array as $key => $value)
       {
           // increase offset
           ++$offset;

           // break if key has been found
           if ($key == $position)
           {
               break;
           }
       }

       $array = array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, null, true);

       return $array;
   }
}