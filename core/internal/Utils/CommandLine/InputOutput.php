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

namespace Centreon\Internal\Utils\CommandLine;

use Centreon\Internal\Utils\CommandLine\Colorize;

/**
 * Utils to Prompt and display in COmmand line
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class InputOutput
{
    /**
     * 
     * @param string $message
     * @param type $conditions
     * @return type
     */
    public static function prompt($message = "", $conditions = null)
    {
        $success = false;
        
        $message = Colorize::colorizeText($message, "blue");
        
        $promptMessage = $message;
        while (!$success) {
            echo $promptMessage . " => ";
            $userAnswer = trim(fgets(STDIN));

            if (isset($conditions)) {
                $conditions($userAnswer, $result);
                if ($result['success']) {
                    $success = true;
                } else {
                    $promptMessage = Colorize::colorizeText($result['message'], "red") . "\n" . $message;
                }
            } else {
                $success = true;
            }
        }
        return $userAnswer;
    }
    
    /**
     * 
     * @param string $message
     * @param boolean $withEndOfLine
     * @param string $color
     */
    public static function display($message, $withEndOfLine = true, $color = null)
    {
        $endOfLine = "";
        
        if (isset($color)) {
            $message = Colorize::colorizeText($message, $color);
        }
        
        if ($withEndOfLine) {
            $endOfLine .= "\n";
        }
        
        echo $message . $endOfLine;
    }
}
