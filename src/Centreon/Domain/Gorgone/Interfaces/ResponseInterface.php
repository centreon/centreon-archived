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

use Centreon\Domain\Gorgone\ActionLog;

/**
 * This interface is design to represent a response received by the Gorgone server.
 *
 * @package Centreon\Domain\Gorgone\Interfaces
 */
interface ResponseInterface
{
    public const STATUS_BEGIN = 0;
    public const STATUS_ERROR = 1;
    public const STATUS_OK = 2;

    /**
     * Return the command of this response.
     *
     * @return CommandInterface|null
     */
    public function getCommand(): ?CommandInterface;

    /**
     * Return the message of the response.
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Returns all the action logs received by the Gorgone server according to the command sent.
     *
     * To be sure that all actions logs have been received, the code of the last action log must be different to
     * GorgoneResponseInterface::STATUS_OK.
     *
     * @return ActionLog[]
     * @see ResponseInterface::STATUS_BEGIN for code when action begin
     * @see ResponseInterface::STATUS_ERROR for code when there is an error
     * @see ResponseInterface::STATUS_OK for code when the last action log has been received and its statut is OK
     */
    public function getActionLogs(): array;

    /**
     * Get the last action log received by the Gorgone server according to the command sent.
     *
     * When this method is called and the last action log has not been received,
     * a call is perform to the Gorgone server to retrieve the action logs.
     *
     * @return ActionLog|null
     */
    public function getLastActionLog(): ?ActionLog;

    /**
     * Get the token assigned by the Gorgone server to this response.
     *
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * Get the error message of the Gorgone server
     *
     * @return string|null
     */
    public function getError(): ?string;
}
