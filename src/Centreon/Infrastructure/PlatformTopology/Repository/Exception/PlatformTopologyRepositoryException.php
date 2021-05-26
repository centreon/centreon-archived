<?php

/*
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

namespace Centreon\Infrastructure\PlatformTopology\Repository\Exception;

use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryExceptionInterface;
use Centreon\Domain\Repository\RepositoryException;

class PlatformTopologyRepositoryException
extends RepositoryException implements
    PlatformTopologyRepositoryExceptionInterface
{
    /**
     * Failure on authentication token retrieval
     * @param string $centralServerAddress
     * @return PlatformTopologyRepositoryException
     */
    public static function failToGetToken(string $centralServerAddress): self
    {
        return new self(
            sprintf(
                _("Failed to get the auth token from Central : '%s''"),
                $centralServerAddress
            )
        );
    }

    /**
     * Failure returned from the API calling the Central
     * @param string $details
     * @return PlatformTopologyRepositoryException
     */
    public static function apiRequestOnCentralException(string $details): self
    {
        return new self(_("Request to the Central's API failed") . ' : ' . $details);
    }

    /**
     * Transport exception related to the client
     * @param string $details
     * @return PlatformTopologyRepositoryException
     */
    public static function apiClientException(string $details): self
    {
        return new self(_("API calling the Central returned a Client exception") . ' : ' . $details);
    }

    /**
     * Transport exception related to the redirection
     * @param string $details
     * @return PlatformTopologyRepositoryException
     */
    public static function apiRedirectionException(string $details): self
    {
        return new self(_("API calling the Central returned a Redirection exception") . ' : ' . $details);
    }

    /**
     * Transport exception related to the server
     * @param string $message concatenated message with the central response
     * @param string $details
     * @return PlatformTopologyRepositoryException
     */
    public static function apiServerException(string $message, string $details): self
    {
        return new self($message . ' : ' . $details);
    }

    /**
     * Central response decoding failure
     * @param string $details
     * @return PlatformTopologyRepositoryException
     */
    public static function apiDecodingResponseFailure(string $details): self
    {
        return new self(_("Unable to decode Central's API response") . ' : ' . $details);
    }

    /**
     * Undetermined error when calling the central's API
     * @param string $details
     * @return PlatformTopologyRepositoryException
     */
    public static function apiUndeterminedError(string $details): self
    {
        return new self(_("Error from Central's register API") . ' : ' . $details);
    }
}
