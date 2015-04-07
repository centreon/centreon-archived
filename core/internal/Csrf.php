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

    private static $expireTime = 900;

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

    /**
     * Return the expire time in seconds
     *
     * @return int
     */
    public static function getExpireTime()
    {
        return time() + self::$expireTime;
    }
}
