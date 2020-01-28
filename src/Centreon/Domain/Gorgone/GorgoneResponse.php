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

namespace Centreon\Domain\Gorgone;

use Centreon\Domain\Gorgone\Interfaces\GorgoneResponseInterface;
use Centreon\Domain\Gorgone\Interfaces\GorgoneResponseRepositoryInterface;
use Centreon\Domain\Gorgone\Interfaces\GorgoneCommandInterface;

/**
 * This class is design to represent a response of the Gorgone server and can be used to retrieve all action logs
 * based on the command sent.
 *
 * @package Centreon\Domain\Gorgone
 */
class GorgoneResponse implements GorgoneResponseInterface
{
    /**
     * @var GorgoneCommandInterface Command sent to the Gorgone server
     */
    private $command;

    /**
     * @var string|null Error message
     */
    private $error;

    /**
     * @var string Message received by the Gorgone server based on the command sent
     */
    private $message;

    /**
     * @var string Token assigned by the Gorgone server to this response which must be equal to the
     * associated command.
     */
    private $token;

    /**
     * @var ActionLog[] Action logs based on the command sent to the Gorgone server.
     */
    private $actionLogs = [];

    /**
     * @var GorgoneResponseRepositoryInterface
     */
    static private $staticResponseRepository;

    /**
     * @var GorgoneResponseRepositoryInterface
     */
    private $responseRepository;

    /**
     * @param GorgoneResponseRepositoryInterface $responseRepository
     */
    static public function setRepository(GorgoneResponseRepositoryInterface $responseRepository): void
    {
        static::$staticResponseRepository = $responseRepository;
    }

    /**
     * Create a Gorgone server response.
     *
     * The response and associated action logs will be retrieved when calling
     * the getLastActionLog method.
     *
     * @param GorgoneCommandInterface $command Command sent to the Gorgone server
     * @return GorgoneResponseInterface
     * @see GorgoneResponseInterface::getActionLogs()
     */
    static public function create(GorgoneCommandInterface $command): GorgoneResponseInterface
    {
        return new GorgoneResponse(self::$staticResponseRepository, $command);
    }

    public function __construct (GorgoneResponseRepositoryInterface $responseRepository, GorgoneCommandInterface $command) {
        $this->command = $command;
        $this->responseRepository = $responseRepository;
    }

    /**
     * @return GorgoneCommandInterface|null
     */
    public function getCommand (): ?GorgoneCommandInterface
    {
        return $this->command;
    }

    /**
     * @return string
     */
    public function getMessage (): string
    {
        return $this->message;
    }

    /**
     * @return ActionLog[]
     * @see GorgoneResponse::$actionLogs
     */
    public function getActionLogs (): array
    {
        return $this->actionLogs;
    }

    /**
     * @return ActionLog|null
     * @throws \Exception
     */
    public function getLastActionLog(): ?ActionLog
    {
        if (empty($this->actionLogs)) {
            $rawResponse = $this->responseRepository->getResponse($this->command);
            $jsonResponse = json_decode($rawResponse, true);
            $this->error = $jsonResponse['error'] ?? null;
            $this->token = (string) $jsonResponse['token'];

            if ($this->error === null) {
                $this->actionLogs = [];
                foreach ($jsonResponse['data'] as $key => $responseData) {
                    $this->actionLogs[$key] = ActionLog::create($responseData);
                }
            }
            $this->message = ((string) $jsonResponse['message'] ?? null);
        }
        return $this->actionLogs[count($this->actionLogs) - 1] ?? null;
    }


    /**
     * @inheritDoc
     * @see GorgoneResponse::$token
     */
    public function getToken (): string
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     * @see GorgoneResponse::$error
     */
    public function getError (): ?string
    {
        return $this->error;
    }
}
