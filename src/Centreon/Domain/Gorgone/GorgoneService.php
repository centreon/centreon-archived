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

use Centreon\Domain\Gorgone\Command\EmptyCommand;
use Centreon\Domain\Gorgone\Interfaces\CommandInterface;
use Centreon\Domain\Gorgone\Interfaces\CommandRepositoryInterface;
use Centreon\Domain\Gorgone\Interfaces\ResponseInterface;
use Centreon\Domain\Gorgone\Interfaces\ResponseRepositoryInterface;
use Centreon\Domain\Gorgone\Interfaces\GorgoneServiceInterface;
use Centreon\Infrastructure\Gorgone\CommandRepositoryException;

/**
 * This class is designed to send a command to the Gorgone server and retrieve the associated responses.
 *
 * @package Centreon\Domain\Gorgone
 */
class GorgoneService implements GorgoneServiceInterface
{
    /**
     * @var ResponseRepositoryInterface
     */
    private $responseRepository;
    /**
     * @var CommandRepositoryInterface
     */
    private $commandRepository;

    /**
     * @param ResponseRepositoryInterface $responseRepository
     * @param CommandRepositoryInterface $commandRepository
     */
    public function __construct(
        ResponseRepositoryInterface $responseRepository,
        CommandRepositoryInterface $commandRepository
    ) {
        $this->responseRepository = $responseRepository;
        $this->commandRepository = $commandRepository;
        Response::setRepository($responseRepository);
    }

    /**
     * @inheritDoc
     */
    public function send(CommandInterface $command): ResponseInterface
    {
        try {
            $responseToken = $this->commandRepository->send($command);
        } catch (CommandRepositoryException $ex) {
            throw new GorgoneException($ex->getMessage(), 0, $ex);
        } catch (\Throwable $ex) {
            throw new GorgoneException(_('Error when connecting to the Gorgone server'), 0, $ex);
        }
        $command->setToken($responseToken);
        return Response::create($command);
    }

    /**
     * @inheritDoc
     */
    public function getResponseFromToken(int $monitoringInstanceId, string $token): ResponseInterface
    {
        $emptyCommand = new EmptyCommand($monitoringInstanceId);
        $emptyCommand->setToken($token);
        return Response::create($emptyCommand);
    }
}
