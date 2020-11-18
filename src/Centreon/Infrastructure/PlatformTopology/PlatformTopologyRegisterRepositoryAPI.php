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

namespace Centreon\Infrastructure\PlatformTopology;

use Centreon\Domain\Proxy\Proxy;
use Centreon\Application\ApiPlatform;
use Symfony\Component\HttpClient\HttpClient;
use Centreon\Domain\PlatformTopology\Platform;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Repository\RepositoryException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\PlatformTopology\PlatformConflictException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRegisterRepositoryInterface;

class PlatformTopologyRegisterRepositoryAPI implements PlatformTopologyRegisterRepositoryInterface
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var ApiPlatform
     */
    private $apiPlatform;

    /**
     * PlatformTopologyRegisterRepositoryAPI constructor.
     * @param HttpClientInterface $httpClient
     * @param ApiPlatform $apiPlatform
     */
    public function __construct(HttpClientInterface $httpClient, ApiPlatform $apiPlatform)
    {
        $this->httpClient = $httpClient;
        $this->apiPlatform = $apiPlatform;
    }

    /**
     * @inheritDoc
     */
    public function registerPlatformToParent(
        Platform $platform,
        PlatformInformation $platformInformation,
        Proxy $proxyService = null
    ): void {
        /**
         * Call the API on the n-1 server to register it too
         */
        try {
            // Central's API endpoints base path

            $baseApiEndpoint = $platformInformation->getApiScheme() . '://'
                . $platformInformation->getCentralServerAddress() . ':'
                . $platformInformation->getApiPort() . '/'
                . $platformInformation->getApiPath() . '/api/v'
                . ((string) $this->apiPlatform->getVersion()) . '/';

            // Enable specific options
            $optionPayload = [];
            // Enable proxy
            if (null !== $proxyService && !empty((string) $proxyService)) {
                $optionPayload['proxy'] = (string) $proxyService;
            }
            // On https scheme, the SSL verify_peer needs to be specified
            if ('https' === $platformInformation->getApiScheme()) {
                $optionPayload['verify_peer'] = $platformInformation->hasApiPeerValidation();
                $optionPayload['verify_host'] = $platformInformation->hasApiPeerValidation();
            }
            // Set the options for next http_client calls
            if (!empty($optionPayload)) {
                $this->httpClient = HttpClient::create($optionPayload);
            }

            // Central's API login payload
            $loginPayload = [
                'json' => [
                    "security" => [
                        "credentials" => [
                            "login" => $platformInformation->getApiUsername(),
                            "password" => $platformInformation->getApiCredentials()
                        ]
                    ]
                ]
            ];

            // Login on the Central to get a valid token
            $loginResponse = $this->httpClient->request(
                'POST',
                $baseApiEndpoint . 'login',
                $loginPayload
            );

            $token = $loginResponse->toArray()['security']['token'] ?? false;

            if (false === $token) {
                throw new RepositoryException(
                    sprintf(
                        _("Failed to get the auth token. Cannot register the platform : '%s'@'%s' on the Central"),
                        $platform->getName(),
                        $platform->getAddress()
                    )
                );
            }

            // Central's API register platform payload
            $registerPayload = [
                'json' => [
                    "name" => $platform->getName(),
                    "hostname" => $platform->getHostname(),
                    "type" => $platform->getType(),
                    "address" => $platform->getAddress(),
                    "parent_address" => $platform->getParentAddress()
                ],
                'headers' => [
                    "X-AUTH-TOKEN" => $token
                ]
            ];

            $registerResponse = $this->httpClient->request(
                'POST',
                $baseApiEndpoint . 'platform/topology',
                $registerPayload
            );

            // Get request status code and return the error message
            if (Response::HTTP_CREATED !== $registerResponse->getStatusCode()) {
                $errorMessage = sprintf(
                    _("The platform: '%s'@'%s' cannot be added to the Central linked to this Remote"),
                    $platform->getName(),
                    $platform->getAddress()
                );
                $returnedMessage = json_decode($registerResponse->getContent(false), true);

                if (!empty($returnedMessage)) {
                    $errorMessage .= "  /  " . _("Central's response => Code : ") .
                        implode(', ', $returnedMessage);
                }
                throw new PlatformConflictException(
                    $errorMessage
                );
            }
        } catch (TransportExceptionInterface $e) {
            throw new RepositoryException(
                _("Request to the Central's API failed") . (' : ') . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            throw new RepositoryException(
                _("API calling the Central returned a Client exception") . (' : ') . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            throw new RepositoryException(
                _("API calling the Central returned a Redirection exception") . (' : ') . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $message = _("API calling the Central returned a Server exception");
            if (!empty($optionPayload['proxy'])) {
                $message .= '. ' . _("Please check the 'Centreon UI' form and your proxy configuration");
            }
            throw new RepositoryException(
                $message . (' : ') . $e->getMessage()
            );
        } catch (DecodingExceptionInterface $e) {
            throw new RepositoryException(
                _("Unable to convert Central's API response") . (' : ') . $e->getMessage()
            );
        } catch (\Exception $e) {
            throw new RepositoryException(
                _("Error from Central's register API") . (' : ') . $e->getMessage()
            );
        }
    }
}
