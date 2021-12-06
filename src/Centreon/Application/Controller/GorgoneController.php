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

use Centreon\Domain\Gorgone\Command\Thumbprint;
use Centreon\Domain\Gorgone\GorgoneException;
use Centreon\Domain\Gorgone\Interfaces\CommandInterface;
use Centreon\Domain\Gorgone\Interfaces\GorgoneServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class is designed to provide a single communication interface to interact with the Gorgone system.
 *
 * @package Centreon\Application\Controller
 */
class GorgoneController extends AbstractFOSRestController
{
    /**
     * @var GorgoneServiceInterface
     */
    private $gorgoneService;

    /**
     * GorgoneController constructor.
     *
     * @param GorgoneServiceInterface $gorgoneService
     */
    public function __construct(GorgoneServiceInterface $gorgoneService)
    {
        $this->gorgoneService = $gorgoneService;
    }

    /**
     * Entry point to send a command to a specific command
     *
     * @param int $pollerId Id of the poller for which this command is intended
     *
     * @param string $commandName Name of the Gorgone command
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function sendCommand(int $pollerId, string $commandName, Request $request): View
    {
        $requestBody = json_decode((string) $request->getContent(), false);
        if (!empty($requestBody)) {
            $validationFile = $this->findValidationFileByCommandName($commandName);
            if ($validationFile !== null) {
                $validator = new Validator();
                $validator->validate(
                    $requestBody,
                    (object) ['$ref' => $validationFile],
                    Constraint::CHECK_MODE_VALIDATE_SCHEMA
                );

                if (!$validator->isValid()) {
                    $message = '';
                    foreach ($validator->getErrors() as $error) {
                        $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
                    }
                    throw new GorgoneException($message);
                }
            }
        }

        $command = $this->createFromName(
            $commandName,
            $pollerId,
            !empty($request->getContent()) ? (string) $request->getContent() : null
        );
        $gorgoneResponse = $this->gorgoneService->send($command);

        return $this->view([
            'token' => $gorgoneResponse->getCommand() !== null
                ? $gorgoneResponse->getCommand()->getToken()
                : null
        ]);
    }

    /**
     * Entry point to get the response to a specific command
     *
     * @param int    $pollerId Id of the poller for which the command is intended
     * @param string $token    Token of the command attributed by the Gorgone server
     *
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
            'token' => $gorgoneResponse->getToken(),
            'data' => $gorgoneResponse->getActionLogs()
        ]));
    }

    /**
     * Check whether the command type exists or not.
     *
     * @param string $commandType Type of the command (ex: thumbprint, ...)
     * @param int $pollerId Id of the poller for which the command is intended
     * @param string|null $requestBody Request body to send in the command
     * @return CommandInterface
     */
    private function createFromName(string $commandType, int $pollerId, ?string $requestBody): CommandInterface
    {
        switch ($commandType) {
            case 'thumbprint':
                return new Thumbprint($pollerId);
            default:
                throw new \LogicException('Unrecognized Command');
        }
    }

    /**
     * Find the validation file based on the command name.
     *
     * Will be implemented with the future commands of Gorgone server.
     *
     * @param string $commandName Command name
     * @return string|null Return the path of the validation file or null if not found
     * @throws GorgoneException
     */
    private function findValidationFileByCommandName(string $commandName): ?string
    {
        throw new GorgoneException('Commands containing a request body are not allowed at the moment');
    }
}
