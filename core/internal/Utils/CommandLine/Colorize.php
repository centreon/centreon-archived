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