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

/**
 * 
 *
 * @authors Lionel Assepo
 * @package Core
 * @subpackage Internals
 */
class Colorize
{
    /**
     * 
     * @param type $text
     * @param type $status
     * @return string
     */
    public static function colorizeMessage($text, $status = "success", $background = "black")
    {
        $colorizedMessage = chr(27);
        switch (strtolower($status)) {
            default:
            case "success":
                $colorizedMessage .= self::getColor("bgreen");
                break;
            case "primary":
                $colorizedMessage .= self::getColor("bblue");
                break;
            case "info":
                $colorizedMessage .= self::getColor("bcyan");
                break;
            case "warning":
                $colorizedMessage .= self::getColor("byellow");
                break;
            case "danger":
                $colorizedMessage .= self::getColor("bred");
                break;
        }
        $colorizedMessage .=  self::getBackground($background) . $text . self::resetColor();
        return $colorizedMessage;
    }
    
    /**
     * 
     * @param string $text
     * @param string $color
     * @param string $background
     * @param boolean $bold
     * @return string
     */
    public static function colorizeText($text, $color = "white", $background = "black", $bold = false)
    {
        if ($bold) {
            $color = 'b' . $color;
        }
        $colorizedText = chr(27) . self::getColor($color) . self::getBackground($background) . $text . self::resetColor();
        return $colorizedText;
    }
    
    /**
     * 
     * @return string
     */
    private static function resetColor()
    {
        return chr(27) . "[0m";
    }
    
    /**
     * 
     * @param string $color
     * @return string
     */
    private static function getColor($color)
    {
        $finalColor = "";
        switch (strtolower($color)) {
            default:
            case "white":
                $finalColor .= "[0;37m";
                break;
            case "bwhite":
                $finalColor .= "[1;37m";
                break;
            case "cyan":
                $finalColor .= "[0;36m";
                break;
            case "bcyan":
                $finalColor .= "[1;36m";
                break;
            case "purple":
                $finalColor .= "[0;35m";
                break;
            case "bpurple":
                $finalColor .= "[1;35m";
                break;
            case "blue":
                $finalColor .= "[0;34m";
                break;
            case "bblue":
                $finalColor .= "[1;34m";
                break;
            case "yellow":
                $finalColor .= "[0;33m";
                break;
            case "byellow":
                $finalColor .= "[1;33m";
                break;
            case "green":
                $finalColor .= "[0;32m";
                break;
            case "bgreen":
                $finalColor .= "[1;32m";
                break;
            case "red":
                $finalColor .= "[0;31m";
                break;
            case "bred":
                $finalColor .= "[1;31m";
                break;
            case "black":
                $finalColor .= "[0;30m";
                break;
            case "bblack":
                $finalColor .= "[1;30m";
                break;
        }
        return $finalColor;
    }
    
    /**
     * 
     * @param string $background
     * @return string
     */
    private static function getBackground($background)
    {
        $finalBackground = "";
        switch ($background) {
            default:
            case "black":
                $finalBackground . "[40m";
                break;
        }
        return $finalBackground;
    }
}