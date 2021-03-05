<?php

/*
 *
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\PlatformTopology\Exception;

/**
 * This class is designed to represent a business exception in the 'Platform status' context.
 *
 * @package Centreon\Domain\PlatformTopology
 */
class PlatformTopologyException extends \Exception
{
    /**
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function errorWhenAddingThePlatform(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("Error when adding in topology the platform : '%s'@'%s'"),
                $name,
                $address
            )
        );
    }

    /**
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function missingMandatoryData(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("Platform : '%s'@'%s' mandatory data are missing. Please check the Remote Access form."),
                $name,
                $address
            )
        );
    }

    /**
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function platformNotLinkedToTheCentral(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("The platform: '%s'@'%s' is not linked to a Central. Please use the wizard first."),
                $name,
                $address
            )
        );
    }

    /**
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function missingCentralCredentials(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("Central's credentials are missing on: '%s'@'%s'. Please check the Remote Access form."),
                $name,
                $address
            )
        );
    }

    /**
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function missingCentralScheme(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("Central's protocol scheme is missing on: '%s'@'%s'. Please check the Remote Access form."),
                $name,
                $address
            )
        );
    }

    /**
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function missingCentralPort(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("Central's protocol port is missing on: '%s'@'%s'. Please check the Remote Access form."),
                $name,
                $address
            )
        );
    }

    /**
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function missingCentralPath(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("Central's path is missing on: '%s'@'%s'. Please check the Remote Access form."),
                $name,
                $address
            )
        );
    }

    /**
     * @return self
     */
    public static function unableToFindMonitoringServerName(): self
    {
        return new self(
            sprintf(_('Unable to find local monitoring server name'))
        );
    }

    /**
     * @return self
     */
    public static function unableToFindEngineConfiguration(): self
    {
        return new self(
            sprintf(_('Unable to find the Engine configuration'))
        );
    }

    /**
     * @param string $illegalCharacters
     * @param string $stringToCheck
     * @return self
     */
    public static function illegalCharacterFound(string $illegalCharacters, string $stringToCheck): self
    {
        return new self(
            sprintf(
                _("At least one illegal character in '%s' was found in platform's name: '%s'"),
                $illegalCharacters,
                $stringToCheck
            )
        );
    }

    /**
     * @param string $stringToCheck
     * @return self
     */
    public static function illegalRfcCharacterFound(string $stringToCheck): self
    {
        return new self(
            sprintf(
                _("At least one non RFC compliant character was found in platform's hostname: '%s'"),
                $stringToCheck
            )
        );
    }
}
