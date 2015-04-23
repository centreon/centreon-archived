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
 * Utils class for status
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Utils
 */
class Status
{
    const SERVICE_OK = 0;
    const SERVICE_WARNING = 1;
    const SERVICE_CRITICAL = 2;
    const SERVICE_UNKNOWN = 3;
    const SERVICE_PENDING = 4;

    const HOST_UP = 0;
    const HOST_DOWN = 1;
    const HOST_UNREACHABLE = 2;
    const HOST_PENDING = 4;

    const EVENT_OK = 0;
    const EVENT_WARNING = 1;
    const EVENT_CRITICAL = 2;
    const EVENT_UNKNOWN = 3;
    const EVENT_PENDING = 4;
    const EVENT_INFORMATION = 5;

    const TYPE_SERVICE = 1;
    const TYPE_HOST = 2;
    const TYPE_EVENT = 3;

    private static $status = array(
        self::TYPE_SERVICE => array(
            self::SERVICE_OK => 'Ok',
            self::SERVICE_WARNING => 'Warning',
            self::SERVICE_CRITICAL => 'Critical',
            self::SERVICE_UNKNOWN => 'Unknown',
            self::SERVICE_PENDING => 'Pending'
        ),
        self::TYPE_HOST => array(
            self::HOST_UP => 'Up',
            self::HOST_DOWN => 'Down',
            self::HOST_UNREACHABLE => 'Unreachable',
            self::HOST_PENDING => 'Pending'
        ),
        self::TYPE_EVENT => array(
            self::EVENT_OK => 'Ok',
            self::EVENT_WARNING => 'Warning',
            self::EVENT_CRITICAL => 'Critical',
            self::EVENT_UNKNOWN => 'Unknown',
            self::EVENT_PENDING => 'Pending',
            self::EVENT_INFORMATION => 'Information'
        )
    );

    /** 
     * Convert a status number to string
     *
     * \Centreon\Utils\Status::numToString(
     *   1,
     *   \Centreon\Utils\Status::TYPE_SERVICE
     * );
     *
     * @param int $numStatus The number status
     * @param int $typeStatus The type of the status
     * @return string
     * @throws \OutOfBoundsException
     */
    public static function numToString($numStatus, $typeStatus, $translate = false)
    {
        if (false === isset(self::$status[$typeStatus]) ||
            false === isset(self::$status[$typeStatus][$numStatus])) {
            throw new \OutOfBoundsException("Status type or status number does not exists");
        }
        if ($translate) {
            return _(self::$status[$typeStatus][$numStatus]);
        }
        return self::$status[$typeStatus][$numStatus];
    }

    /**
     * Convert a status string to number
     *
     * @param string $textStatus The text status
     * @param int $typeStatus The type of the status
     * @return int
     * @throws \OutOfBoundsException
     */
    public static function stringToNum($textStatus, $typeStatus)
    {
        if (false === isset(self::$status[$typeStatus])) {
            throw new \OutOfBoundsException("Status type does not exists");
        }
        $key = array_search($textStatus, self::$status[$typeStatus]);
        if (false === $key) {
            throw new \OutOfBoundsException("Status text does not exists");
        }
        return $key;
    }
}
