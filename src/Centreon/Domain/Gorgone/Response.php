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

use Centreon\Domain\Gorgone\Interfaces\ResponseInterface;
use Centreon\Domain\Gorgone\Interfaces\ResponseRepositoryInterface;
use Centreon\Domain\Gorgone\Interfaces\CommandInterface;

/**
 * This class is design to represent a response of the Gorgone server and can be used to retrieve all action logs
 * based on the command sent.
 *
 * @package Centreon\Domain\Gorgone
 */
class Response implements ResponseInterface
{
    /**
     * @var CommandInterface Command sent to the Gorgone server
     */
    private $command;

    /**
     * @var string|null Error message
     */
    private $error;

    /**
     * @var string|null Message received by the Gorgone server based on the command sent
     */
    private $message;

    /**
     * @var string|null Token assigned by the Gorgone server to this response which must be equal to the
     * associated command.
     */
    private $token;

    /**
     * @var ActionLog[] Action logs based on the command sent to the Gorgone server.
     */
    private $actionLogs = [];

    /**
     * @var ResponseRepositoryInterface
     */
    private static $staticResponseRepository;

    /**
     * @var ResponseRepositoryInterface
     */
    private $responseRepository;

    /**
     * @param ResponseRepositoryInterface $responseRepository
     */
    public static function setRepository(ResponseRepositoryInterface $responseRepository): void
    {
        static::$staticResponseRepository = $responseRepository;
    }

    /**
     * Create a Gorgone server response.
     *
     * The response and associated action logs will be retrieved when calling
     * the getLastActionLog method.
     *
     * @param CommandInterface $command Command sent to the Gorgone server
     * @return ResponseInterface
     * @see ResponseInterface::getActionLogs()
     */
    public static function create(CommandInterface $command): ResponseInterface
    {
        return new Response(self::$staticResponseRepository, $command);
    }

    public function __construct(
        ResponseRepositoryInterface $responseRepository,
        CommandInterface $command
    ) {
        $this->command = $command;
        $this->responseRepository = $responseRepository;
    }

    /**
     * @return CommandInterface|null
     */
    public function getCommand(): ?CommandInterface
    {
        return $this->command;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return ActionLog[]
     * @throws \Exception
     * @see Response::$actionLogs
     */
    public function getActionLogs(): array
    {
        $this->actionLogs = [];
        $rawResponse = $this->responseRepository->getResponse($this->command);
        $jsonResponse = json_decode($rawResponse, true);
        $this->error = $jsonResponse['error'] ?? null;
        $this->token = (string) $jsonResponse['token'];

        if ($this->error === null) {
            foreach ($jsonResponse['data'] as $key => $responseData) {
                $this->actionLogs[$key] = ActionLog::create($responseData);
            }
        }
        $this->message = ((string) $jsonResponse['message'] ?? null);
        return $this->actionLogs;
    }

    /**
     * @return ActionLog|null
     * @throws \Exception
     */
    public function getLastActionLog(): ?ActionLog
    {
        $this->getActionLogs();
        return $this->actionLogs[count($this->actionLogs) - 1] ?? null;
    }


    /**
     * @inheritDoc
     * @see Response::$token
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     * @see Response::$error
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}
