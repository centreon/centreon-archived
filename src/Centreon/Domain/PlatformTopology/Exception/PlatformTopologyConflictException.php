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
 * @package Centreon\Domain\PlatformTopology\Exception
 */
class PlatformTopologyConflictException extends \Exception
{
    /**
     * Fail to found the platform on the central type parent
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function notFoundOnCentral(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("The platform '%s'@'%s' cannot be found on the Central."),
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
    public static function notTypeRemote(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("The platform: '%s'@'%s' is not declared as a 'remote'."),
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
    public static function addressConflict(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("Same address and parent_address for platform : '%s'@'%s'."),
                $name,
                $address
            )
        );
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function platformAlreadySaved(string $type, string $name, string $address): self
    {
        return new self(
            sprintf(
                _("A '%s': '%s'@'%s' is already saved"),
                $type,
                $name,
                $address
            )
        );
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function platformDoesNotMatchTheSavedOne(string $type, string $name, string $address): self
    {
        return new self(
            sprintf(
                _("The server type '%s' : '%s'@'%s' does not match the one configured in Centreon or is disabled"),
                $type,
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
    public static function platformNameOrAddressAlreadyExist(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("A platform using the name : '%s' or address : '%s' already exists"),
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
    public static function unableToLinkARemoteToAnotherRemote(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("Unable to link a 'remote': '%s'@'%s' to another remote platform"),
                $name,
                $address
            )
        );
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $address
     * @param string $parentType
     * @return self
     */
    public static function inconsistentTypeToLinkThePlatformTo(
        string $type,
        string $name,
        string $address,
        string $parentType
    ): self {
        return new self(
            sprintf(
                _("Cannot register the '%s' platform : '%s'@'%s' behind a '%s' platform"),
                $type,
                $name,
                $address,
                $parentType
            )
        );
    }
}
