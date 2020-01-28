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

interface GorgoneResponseRepositoryInterface
{
    /**
     * Returns the response of the command sent.
     *
     * The command must have been sent because we will use the command token to retrieve the message.
     *
     * @param GorgoneCommandInterface $command Command sent to the Gorgone server
     * @return string Response message in JSON format
     * @throws \Exception
     */
    public function getResponse (GorgoneCommandInterface $command): string;

    /**
     * Defines the function or method that will be executed after the response is received.
     *
     * The response message will be passed as a parameter of the function or method.
     *
     * @param callable $responseSetter
     */
    public function defineResponseSetter (callable $responseSetter): void;
}
