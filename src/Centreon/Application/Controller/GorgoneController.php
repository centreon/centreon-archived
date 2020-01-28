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

namespace Centreon\Application\Controller;

use Centreon\Domain\Gorgone\Command\Internal\ThumbprintCommand;
use Centreon\Domain\Gorgone\Interfaces\CommandInterface;
use Centreon\Domain\Gorgone\Interfaces\ResponseRepositoryInterface;
use Centreon\Domain\Gorgone\Interfaces\ServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * This class is designed to provide a single communication interface to interact with the Gorgone system.
 *
 * @package Centreon\Application\Controller
 */
class GorgoneController extends AbstractFOSRestController
{
    /**
     * @var ServiceInterface
     */
    private $gorgoneService;

    public function __construct(ServiceInterface $gorgoneService)
    {
        $this->gorgoneService = $gorgoneService;
    }

    /**
     * Entry point to send a command to a specific command
     *
     * @Rest\Get(
     *     "/gorgone/pollers/{pollerId}/commands/{commandName}",
     *     condition="request.attributes.get('version') == 2.0")
     * @param string $commandName Name of the Gorgone command
     * @param int $pollerId Id of the poller for which this command is intended
     * @return View
     * @throws \Exception
     */
    public function sendCommand(int $pollerId, string $commandName): View
    {
        $command = $this->createFromName($commandName, $pollerId);
        $gorgoneResponse = $this->gorgoneService->send($command);
        return $this->view([
            'token' => $gorgoneResponse->getCommand()->getToken()
        ]);
    }

    /**
     * Entry point to get the response to a specific command
     *
     * @Rest\Get(
     *     "/gorgone/pollers/{pollerId}/responses/{token}",
     *     condition="request.attributes.get('version') == 2.0")
     * @param int $pollerId Id of the poller for which the command is intended
     * @param string $token Token of the command attributed by the Gorgone server
     * @return View
     */
    public function getResponses(int $pollerId, string $token): View
    {
        $gorgoneResponse = $this->gorgoneService->getResponseFromToken($pollerId, $token);
        // We force a call to read the Gorgon Server API responses
        $gorgoneResponse->getLastActionLog();
        $responseTemplate = ($gorgoneResponse->getError() !== null)
            ? ['error' => $gorgoneResponse->getError()]
            : [];
        return $this->view(array_merge($responseTemplate, [
            'message' => $gorgoneResponse->getMessage(),
            'token' =>$gorgoneResponse->getToken(),
            'data' => $gorgoneResponse->getActionLogs()
        ]));
    }

    /**
     * Check whether the command type exists or not.
     *
     * @param string $commandType Type of the command (ex: thumbprint, ...)
     * @param int $pollerId Id of the poller for which the command is intended
     * @return CommandInterface
     */
    private function createFromName(string $commandType, int $pollerId): CommandInterface
    {
        switch ($commandType) {
            case 'thumbprint':
                return new ThumbprintCommand($pollerId);
            default:
                throw new \LogicException('Unrecognized Command');
        }
    }
}
