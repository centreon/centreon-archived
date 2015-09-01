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

namespace Centreon\Internal;


/**
 * Class for manage CSRF Token
 *
 * @version 3.0.0
 * @package Centreon
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 */
class Csrf
{
    private static $ignoreMethod = array(
        'GET',
        'OPTIONS',
        'HEAD'
    );

    private static $sessionTokenName = 'CSRF_TOKEN';

    private static $cookieName = 'XSRF-TOKEN';

    private static $headerNames = array('x-xsrf-token', 'x-csrf-token');

    /**
     * Check if a value match with the value in the session
     *
     * @param string $value The value to check
     * @param string $method The http method
     * @return boolean
     */
    public static function checkToken($value, $method = 'POST')
    {
        if (false === in_array(strtoupper($method), self::$ignoreMethod)) {
            if ($value != $_SESSION[self::$sessionTokenName]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Generate a new CSRF token an store it in session
     *
     * @return string
     */
    public static function generateToken()
    {
       
       $token = md5(uniqid(Di::getDefault()->get('config')->get('global', 'secret'), true));
       $_SESSION[self::$sessionTokenName] = $token;
       return $token;
    }

    /**
     * If the token must regenerate
     *
     * @param string $method The http method
     * @return boolean
     */
    public static function mustBeGenerate($method = 'POST')
    {
        if (false === isset($_SESSION[self::$sessionTokenName])) {
            return true;
        }
        return false;
    }

    /**
     * Return the cookie name
     *
     * @return string
     */
    public static function getCookieName()
    {
        return self::$cookieName;
    }

    /**
     * Return the list of possible header name for csrf
     *
     * @return array
     */
    public static function getHeaderNames()
    {
        return self::$headerNames;
    }
}
