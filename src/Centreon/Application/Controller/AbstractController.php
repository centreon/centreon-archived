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

use JsonSchema\Validator;
use Centreon\Domain\Log\LoggerTrait;
use JsonSchema\Constraints\Constraint;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * Abstraction over the FOSRestController
 *
 * @package Centreon\Application\Controller
 */
abstract class AbstractController extends AbstractFOSRestController
{
    use LoggerTrait;

    public const ROLE_API_REALTIME = 'ROLE_API_REALTIME';
    public const ROLE_API_REALTIME_EXCEPTION_MESSAGE = 'You are not authorized to access this resource';
    public const ROLE_API_CONFIGURATION = 'ROLE_API_CONFIGURATION';
    public const ROLE_API_CONFIGURATION_EXCEPTION_MESSAGE = 'You are not authorized to access this resource';

    public function denyAccessUnlessGrantedForApiConfiguration(): void
    {
        parent::denyAccessUnlessGranted(
            static::ROLE_API_CONFIGURATION,
            null,
            static::ROLE_API_CONFIGURATION_EXCEPTION_MESSAGE
        );
    }

    public function denyAccessUnlessGrantedForApiRealtime(): void
    {
        parent::denyAccessUnlessGranted(
            static::ROLE_API_REALTIME,
            null,
            static::ROLE_API_REALTIME_EXCEPTION_MESSAGE
        );
    }

    /**
     * Get current base uri
     *
     * @return string
     */
    protected function getBaseUri(): string
    {
        $baseUri = '';

        if (
            isset($_SERVER['REQUEST_URI'])
            && preg_match(
                '/^(.+)\/((api|widgets|modules|include|authentication)\/|main(\.get)?\.php).+/',
                $_SERVER['REQUEST_URI'],
                $matches
            )
        ) {
            $baseUri = $matches[1];
        }

        return $baseUri;
    }

    /**
     * Validate the data sent.
     *
     * @param Request $request Request sent by client
     * @param string $jsonValidationFile Json validation file
     * @throws \InvalidArgumentException
     */
    protected function validateDataSent(Request $request, string $jsonValidationFile): void
    {
        $receivedData = json_decode((string) $request->getContent(), true);
        if (!is_array($receivedData)) {
            throw new \InvalidArgumentException('Error when decoding your sent data');
        }
        $receivedData = Validator::arrayToObjectRecursive($receivedData);
        $validator = new Validator();
        $validator->validate(
            $receivedData,
            (object) [
                '$ref' => 'file://' . realpath(
                    $jsonValidationFile
                )
            ],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if (!$validator->isValid()) {
            $message = '';
            $this->error('Invalid request body');
            foreach ($validator->getErrors() as $error) {
                $message .= ! empty($error['property'])
                    ? sprintf("[%s] %s\n", $error['property'], $error['message'])
                    : sprintf("%s\n", $error['message']);
            }
            throw new \InvalidArgumentException($message);
        }
    }
}
