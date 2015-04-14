<?php

/*
 * Copyright 2005-2015 CENTREON
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