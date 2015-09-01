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
