<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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
declare(strict_types=1);

namespace Centreon\Domain\HostConfiguration\Exception;

/**
 * This class is designed to contain all exceptions for the context of the host template.
 *
 * @package Centreon\Domain\HostConfiguration\Exception
 */
class HostTemplateArgumentException extends \InvalidArgumentException
{
    /**
     * @param int $options
     * @return self
     */
    public static function badNotificationOptions(int $options): self
    {
        return new self(sprintf(_('Invalid notification option (%d)'), $options));
    }

    /**
     * @param int $options
     * @return self
     */
    public static function badStalkingOptions(int $options): self
    {
        return new self(sprintf(_('Invalid stalking option (%d)'), $options));
    }

    /**
     * @param string $snmpVersion
     * @return self
     */
    public static function badSnmpVersion(string $snmpVersion): self
    {
        return new self(sprintf(_('This SNMP version (%s) is not allowed'), $snmpVersion));
    }

    /**
     * @param int $activeChecksStatus
     * @return self
     */
    public static function badActiveChecksStatus(int $activeChecksStatus): self
    {
        return new self(sprintf(_('This active checks status (%d) is not allowed'), $activeChecksStatus));
    }

    /**
     * @param int $passiveChecksStatus
     * @return self
     */
    public static function badPassiveChecksStatus(int $passiveChecksStatus): self
    {
        return new self(sprintf(_('This passive checks status (%d) is not allowed'), $passiveChecksStatus));
    }

    /**
     * @param int $notificationStatus
     * @return self
     */
    public static function badNotificationStatus(int $notificationStatus): self
    {
        return new self(sprintf(_('This notifications status (%d) is not allowed'), $notificationStatus));
    }

    /**
     * @param int $notificationInterval
     * @return self
     */
    public static function badNotificationInterval(int $notificationInterval): self
    {
        return new self(
            sprintf(_('The notification interval must be greater than or equal to 0'), $notificationInterval)
        );
    }
}
