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
use Centreon\Domain\Gorgone\Interfaces\GorgoneApiConnectionInterface;
use Centreon\Domain\Gorgone\Interfaces\GorgoneCommandInterface;
use Centreon\Domain\Gorgone\Interfaces\GorgoneCommandRepositoryInterface;
use Centreon\Domain\Gorgone\Interfaces\GorgoneResponseInterface;
use Centreon\Domain\Gorgone\Interfaces\GorgoneResponseRepositoryInterface;
use Centreon\Domain\Gorgone\Interfaces\GorgoneServiceInterface;
use Centreon\Domain\Option\Interfaces\OptionRepositoryInterface;

class GorgoneService implements GorgoneServiceInterface
{
    /**
     * @var GorgoneResponseRepositoryInterface
     */
    private $responseRepository;
    /**
     * @var GorgoneCommandRepositoryInterface
     */
    private $commandRepository;
    /**
     * @var OptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var bool Internal flag which indicates whether the connection
     * parameters for the Gorgone command and response repositories had been defined
     */
    private $isInitializedConnection = false;

    /**
     * @param GorgoneResponseRepositoryInterface $responseRepository
     * @param GorgoneCommandRepositoryInterface $commandRepository
     * @param OptionRepositoryInterface $optionRepository
     */
    public function __construct (
        GorgoneResponseRepositoryInterface $responseRepository,
        GorgoneCommandRepositoryInterface $commandRepository,
        OptionRepositoryInterface $optionRepository
    ) {
        $this->responseRepository = $responseRepository;
        $this->commandRepository = $commandRepository;
        $this->optionRepository = $optionRepository;
        GorgoneResponse::setRepository($responseRepository);
    }

    /**
     * @inheritDoc
     * @see GorgoneResponseInterface
     */
    public function send(GorgoneCommandInterface $command): GorgoneResponseInterface
    {
        if ($this->isInitializedConnection === false) {
            $this->initGorgoneConnection();
        }
        try {
            $responseToken = $this->commandRepository->send($command);
        } catch (\Throwable $ex) {
            throw new GorgoneException('Error when connecting to the Gorgon server');
        }
        $command->setToken($responseToken);
        return GorgoneResponse::create($command);
    }

    public function getResponseFromToken(int $pollerId, string $token): GorgoneResponseInterface
    {
        if ($this->isInitializedConnection === false) {
            $this->initGorgoneConnection();
        }
        $emptyCommand = new EmptyCommand($pollerId);
        $emptyCommand->setToken($token);
        return GorgoneResponse::create($emptyCommand);
    }

    /**
     * Defines the connection parameters for the Gorgone command and response repositories.
     */
    private function initGorgoneConnection (): void
    {
        /**
         * @see GorgoneApiConnectionInterface::DEFAULT_CONNECTION_PARAMETERS
         */
        $connectionDetails = $this->optionRepository->findSelectedOptions([
            'gorgone_api_address',
            'gorgone_api_port',
            'gorgone_api_username',
            'gorgone_api_password',
            'gorgone_api_ssl',
            'gorgone_api_allow_self_signed'
        ]);
        $this->commandRepository->defineConnectionParameters($connectionDetails);
        $this->responseRepository->defineConnectionParameters($connectionDetails);
        $this->isInitializedConnection = true;
    }
}
