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

namespace Centreon\Domain\PlatformTopology\Interfaces;

use Centreon\Domain\Repository\Interfaces\RepositoryExceptionInterface;

interface PlatformTopologyRepositoryExceptionInterface extends RepositoryExceptionInterface
{
    /**
     * Failure on authentication token retrieval
     * @param string $centralServerAddress
     * @return self
     */
    public static function failToGetToken(string $centralServerAddress): self;

    /**
     * Failure returned from the API calling the Central
     * @param string $details
     * @return self
     */
    public static function apiRequestOnCentralException(string $details): self;

    /**
     * Transport exception related to the client
     * @param string $details
     * @return self
     */
    public static function apiClientException(string $details): self;

    /**
     * Transport exception related to the redirection
     * @param string $details
     * @return self
     */
    public static function apiRedirectionException(string $details): self;

    /**
     * Transport exception related to the server
     * @param string $message concatenated message with the central response
     * @param string $details
     * @return self
     */
    public static function apiServerException(string $message, string $details): self;

    /**
     * Central response decoding failure
     * @param string $details
     * @return self
     */
    public static function apiDecodingResponseFailure(string $details): self;

    /**
     * Undetermined error when calling the central's API
     * @param string $details
     * @return self
     */
    public static function apiUndeterminedError(string $details): self;
}
