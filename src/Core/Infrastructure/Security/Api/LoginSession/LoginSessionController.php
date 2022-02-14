<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\Security\Api\LoginSession;

use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Application\Security\UseCase\LoginSession\LoginSession;
use Core\Application\Security\UseCase\LoginSession\LoginSessionPresenterInterface;
use Core\Application\Security\UseCase\LoginSession\LoginSessionRequest;
use Centreon\Domain\Authentication\Model\Credentials;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Domain\Authentication\UseCase\Authenticate;
use Centreon\Domain\Authentication\UseCase\AuthenticateResponse;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

class LoginSessionController extends AbstractController
{
    /**
     * @param Request $request
     * @param string $providerConfigurationName
     * @param LoginSession $loginSession
     * @param AuthenticateResponse $response
     * @return Response
     */
    public function __invoke(
        Request $request,
        string $providerConfigurationName,
        LoginSession $loginSession,
        LoginSessionPresenterInterface $presenter,
        AuthenticateResponse $response,
        SessionInterface $session
    ): object {
        $this->validateDataSent($request, __DIR__ . '/LoginSessionSchema.json');

        $loginSessionRequest = $this->createLoginSessionRequest($request, $providerConfigurationName);

        try {
            $loginSession($presenter, $loginSessionRequest);
        } catch (AuthenticationException $e) {
            return $presenter->show();
            //$presenter->setResponseStatus(new UnauthorizedResponse());

            /*
            return $this->view(
                [
                    "code" => Response::HTTP_UNAUTHORIZED,
                    "message" => $e->getMessage(),
                    "password_is_expired" => true,
                ],
                Response::HTTP_UNAUTHORIZED
            );
            */
        }

        $presenter->setResponseHeaders(
            [
                Cookie::create('PHPSESSID', $session->getId()),
            ],
        );

        return $presenter->show();
    }

    /**
     * Create a DTO from HTTP Request or throw an exception if the body is incorrect.
     *
     * @param Request $request
     * @return LoginSessionRequest
     */
    private function createLoginSessionRequest(
        Request $request,
        string $providerConfigurationName,
    ): LoginSessionRequest {
        $requestData = json_decode((string) $request->getContent(), true);

        $loginSessionRequest = new LoginSessionRequest();
        $loginSessionRequest->providerConfigurationName = $providerConfigurationName;
        $loginSessionRequest->login = $requestData['login'];
        $loginSessionRequest->password = $requestData['password'];
        $loginSessionRequest->baseUri = $this->getBaseUri();
        $referer = $request->headers->get('referer');
        if ($referer !== null) {
            $loginSessionRequest->refererQueryParameters = parse_url($referer, PHP_URL_QUERY) ?: null;
        } else {
            $loginSessionRequest->refererQueryParameters = null;
        }
        $loginSessionRequest->clientIp = $request->getClientIp();

        return $loginSessionRequest;
    }

    /**
     * Validate the data sent.
     *
     * @param Request $request Request sent by client
     * @param string $jsonValidationFile Json validation file
     * @throws \InvalidArgumentException
     */
    private function validateDataSent(Request $request, string $jsonValidationFile): void
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
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new \InvalidArgumentException($message);
        }

        if ($request->getClientIp() === null) {
            throw new \InvalidArgumentException('Invalid address');
        }
    }
}
