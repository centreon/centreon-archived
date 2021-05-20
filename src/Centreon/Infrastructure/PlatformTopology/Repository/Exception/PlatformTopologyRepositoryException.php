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

namespace Centreon\Infrastructure\PlatformInformation\Repository\Exception;

use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryExceptionInterface;

class PlatformTopologyRepositoryException extends \Exception implements PlatformTopologyRepositoryExceptionInterface
{
    /**
     * @inheritDoc
     */
    public static function failToGetToken(string $centralServerAddress): PlatformTopologyRepositoryExceptionInterface
    {
        return new self(
            sprintf(
                _("Failed to get the auth token from Central : '%s''"),
                $centralServerAddress
            )
        );
    }

    /**
     * @inheritDoc
     */
    public static function apiRequestOnCentralException(string $details): PlatformTopologyRepositoryExceptionInterface
    {
        return new self(_("Request to the Central's API failed") . (' : ') . $details);
    }

    /**
     * @inheritDoc
     */
    public static function apiClientException(string $details): PlatformTopologyRepositoryExceptionInterface
    {
        return new self(_("API calling the Central returned a Client exception") . (' : ') . $details);
    }

    /**
     * @inheritDoc
     */
    public static function apiRedirectionException(string $details): PlatformTopologyRepositoryExceptionInterface
    {
        return new self(_("API calling the Central returned a Redirection exception") . (' : ') . $details);
    }

    /**
     * @inheritDoc
     */
    public static function apiServerException(
        string $message,
        string $details
    ): PlatformTopologyRepositoryExceptionInterface
    {
        return new self($message . (' : ') . $details);
    }

    /**
     * @inheritDoc
     */
    public static function apiDecodingResponseFailure(string $details): PlatformTopologyRepositoryExceptionInterface
    {
        return new self(_("Unable to decode Central's API response") . (' : ') . $details);
    }

    /**
     * @inheritDoc
     */
    public static function apiUndeterminedError(string $details): PlatformTopologyRepositoryExceptionInterface
    {
        return new self(_("Error from Central's register API") . (' : ') . $details);
    }
}
