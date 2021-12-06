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

namespace Centreon\Infrastructure\Gorgone\Interfaces;

interface ConfigurationLoaderApiInterface
{
    /**
     * Indicates whether the connection to the Gorgone server used a self-signed certificate
     *
     * @return bool
     * @throws \Exception
     */
    public function isSecureConnectionSelfSigned(): bool;

    /**
     * Returns the IP address of the Gorgone server
     *
     * @return string|null IP address
     * @throws \Exception
     */
    public function getApiIpAddress(): ?string;

    /**
     * Returns the connection port of the Gorgone server
     *
     * @return int|null Connection port
     * @throws \Exception
     */
    public function getApiPort(): ?int;

    /**
     * Returns the API password of the Gorgone server
     *
     * @return string|null API password of the Gorgone server
     * @throws \Exception
     */
    public function getApiPassword(): ?string;

    /**
     * Returns the delay before the connection timeout
     *
     * @return int Timeout (in seconds)
     * @throws \Exception
     */
    public function getCommandTimeout(): int;

    /**
     * Indicates whether the connection to the Gorgone server is secure
     *
     * @return bool
     * @throws \Exception
     */
    public function isApiConnectionSecure(): bool;

    /**
     * Returns the API username of the Gorgone server
     *
     * @return string|null API username of the Gorgone server
     * @throws \Exception
     */
    public function getApiUsername(): ?string;
}
