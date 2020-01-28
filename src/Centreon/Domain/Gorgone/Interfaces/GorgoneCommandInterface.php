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

namespace Centreon\Domain\Gorgone\Interfaces;

interface GorgoneCommandInterface
{
    /**
     * Returns the token assigned by Gorgone for this command.
     *
     * @return string Token
     */
    public function getToken(): string;

    /**
     * Defines the token assigned for this command.
     *
     * @param string $token Token
     */
    public function setToken(string $token): void;

    /**
     * Returns the uri associated to this command.
     *
     * @return string Uri of the command
     */
    public function getUriRequest(): string;

    /**
     * Returns the body of the request that will be sent to the Gorgone server.
     *
     * @return string Body of the request
     */
    public function getBodyRequest(): string;

    /**
     * Returns the poller id for which this command is intended.
     *
     * @return int Poller id
     */
    public function getPollerId(): int;

    /**
     * @return string Retrieve the internal name of the command
     */
    public function getName(): string;
}
