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

namespace Centreon\Domain\PlatformTopology;

use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\Proxy\Proxy;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\Exception\EntityNotFoundException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Service intended to register a new server to the platform topology
 *
 * @package Centreon\Domain\PlatformTopology
 */
class PlatformTopologyService implements PlatformTopologyServiceInterface
{
    /**
     * @var PlatformTopologyRepositoryInterface
     */
    private $platformTopologyRepository;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var PlatformInformationServiceInterface
     */
    private $platformInformation;

    /**
     * @var ProxyServiceInterface
     */
    private $proxy;

    /**
     * PlatformTopologyService constructor.
     * @param PlatformTopologyRepositoryInterface $platformTopologyRepository
     * @param HttpClientInterface $httpClient
     * @param PlatformInformationServiceInterface $platformInformationService
     * @param ProxyServiceInterface $proxy
     */
    public function __construct(
        PlatformTopologyRepositoryInterface $platformTopologyRepository,
        HttpClientInterface $httpClient,
        PlatformInformationServiceInterface $platformInformationService,
        ProxyServiceInterface $proxy
    ) {
        $this->platformTopologyRepository = $platformTopologyRepository;
        $this->httpClient = $httpClient;
        $this->platformInformation = $platformInformationService;
        $this->proxy = $proxy;
    }

    /**
     * @inheritDoc
     */
    public function addPlatformToTopology(PlatformTopology $platformTopology): void
    {
        /**
         * Search for already registered central or remote top level server on this platform
         * As only top level platform do not need parent_address and only one should be registered
         */
        if (PlatformTopology::TYPE_CENTRAL === $platformTopology->getType()) {
            // New unique Central top level platform case
            $this->checkForAlreadyRegisteredPlatformType(PlatformTopology::TYPE_CENTRAL);
            $this->checkForAlreadyRegisteredPlatformType(PlatformTopology::TYPE_REMOTE);
            $this->setMonitoringServerId($platformTopology, true);
        } elseif (PlatformTopology::TYPE_REMOTE === $platformTopology->getType()) {
            // Cannot add a Remote behind another Remote
            $isLocalhost = false;
            $this->checkForAlreadyRegisteredPlatformType(PlatformTopology::TYPE_REMOTE);
            if (null === $platformTopology->getParentAddress()) {
                // New unique Remote top level platform case
                $this->checkForAlreadyRegisteredPlatformType(PlatformTopology::TYPE_CENTRAL);
                $isLocalhost = true;
            }
            $this->setMonitoringServerId($platformTopology, $isLocalhost);
        }

        $this->checkForAlreadyRegisteredSameNameOrAddress($platformTopology);
        /**
         * @var PlatformTopology|null $registeredParentInTopology
         */
        $registeredParentInTopology = $this->findParentPlatformAndSetId($platformTopology);

        /**
         * The top level platform is defined as a Remote
         * Getting data and calling the register request on the Central
         */
        if (
            null !== $registeredParentInTopology
            && true === $registeredParentInTopology->isLinkedToAnotherServer()
        ) {
            /**
             * Getting platform information's data
             * @var PlatformInformation|null $foundPlatformInformation
             */
            $foundPlatformInformation = $this->platformInformation->getInformation();

            if (null === $foundPlatformInformation) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Platform : '%s'@'%s' mandatory data are missing. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (false === $foundPlatformInformation->isRemote()) {
                throw new PlatformTopologyConflictException(
                    sprintf(
                        _("The platform: '%s'@'%s' is not declared as a 'remote'."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getAuthorizedMaster()) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("The platform: '%s'@'%s' is not linked to a Central. Please use the wizard first."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (
                null === $foundPlatformInformation->getApiUsername()
                || null === $foundPlatformInformation->getApiCredentials()
            ) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Central's credentials are missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getApiScheme()) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Central's protocol scheme is missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getApiPort()) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Central's protocol port is missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getApiPath()) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Central's path is missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }

            /**
             * Getting platform's proxy data
             * @var Proxy|null $proxy
             */
            $proxy = $this->proxy->getProxy();

            /**
             * Call the API on the n-1 server to register it too
             */
            try {
                // Central's API endpoints base path
                $baseApiEndpoint = $foundPlatformInformation->getApiScheme() . '://' .
                    $foundPlatformInformation->getAuthorizedMaster() . ':' .
                    $foundPlatformInformation->getApiPort() . '/' .
                    $foundPlatformInformation->getApiPath() . '/api/v2.0/';

                // Enable specific options
                $optionPayload = [];
                // Enable proxy
                if (null !== $proxy && !empty((string) $proxy)) {
                    $optionPayload['proxy'] = (string) $proxy;
                }
                // SSL verify_peer
                if ($foundPlatformInformation->hasApiPeerValidation()) {
                    $optionPayload['verify_peer'] = true;
                    $optionPayload['verify_host'] = true;
                }

                // Central's API login payload
                $loginPayload = [
                    'json' => [
                        "security" => [
                            "credentials" => [
                                "login" => $foundPlatformInformation->getApiUsername(),
                                "password" => $foundPlatformInformation->getApiCredentials()
                            ]
                        ]
                    ]
                ];

                // Add specific options
                if (!empty($optionPayload)) {
                    $loginPayload = array_merge($loginPayload, $optionPayload);
                }

                // Login on the Central to get a valid token
                $loginResponse = $this->httpClient->request(
                    'POST',
                    $baseApiEndpoint . 'login',
                    $loginPayload
                );

                $token = $loginResponse->toArray()['security']['token'] ?? false;

                if (false === $token) {
                    throw new PlatformTopologyException(
                        sprintf(
                            _("Failed to get the auth token. Cannot register the platform : '%s'@'%s' on the Central"),
                            $platformTopology->getName(),
                            $platformTopology->getAddress()
                        )
                    );
                }

                // Central's API register platform payload
                $registerPayload = [
                    'json' => [
                        "name" => $platformTopology->getName(),
                        "type" => $platformTopology->getType(),
                        "address" => $platformTopology->getAddress(),
                        "parent_address" => $platformTopology->getParentAddress()
                    ],
                    'headers' => [
                        "X-AUTH-TOKEN" => $token
                    ]
                ];

                // Add specific options
                if (!empty($optionPayload)) {
                    $registerPayload = array_merge($registerPayload, $optionPayload);
                }

                $registerResponse = $this->httpClient->request(
                    'POST',
                    $baseApiEndpoint . 'platform/topology',
                    $registerPayload
                );

                // Get request status code and return the error message
                if (Response::HTTP_CREATED !== $registerResponse->getStatusCode()) {
                    $errorMessage = sprintf(
                        _("The platform: '%s'@'%s' cannot be added to the Central linked to this Remote"),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    );
                    $returnedMessage = json_decode($registerResponse->getContent(false), true);

                    if (!empty($returnedMessage)) {
                        $errorMessage .= "  /  " . _("Central's response => Code : ") . implode(', ', $returnedMessage);
                    }
                    throw new PlatformTopologyConflictException(
                        $errorMessage
                    );
                }
            } catch (TransportExceptionInterface $e) {
                throw new PlatformTopologyException(
                    _("Request to the Central's API failed") . (' : ') . $e->getMessage()
                );
            } catch (ClientExceptionInterface $e) {
                throw new PlatformTopologyException(
                    _("API calling the Central returned a Client exception") . (' : ') . $e->getMessage()
                );
            } catch (RedirectionExceptionInterface $e) {
                throw new PlatformTopologyException(
                    _("API calling the Central returned a Redirection exception") . (' : ') . $e->getMessage()
                );
            } catch (ServerExceptionInterface $e) {
                $message = _("API calling the Central returned a Server exception");
                if (!empty($optionPayload['proxy'])) {
                    $message .= '. ' . _("Please check the 'Centreon UI' form and your proxy configuration");
                }
                throw new PlatformTopologyException(
                    $message . (' : ') . $e->getMessage()
                );
            } catch (DecodingExceptionInterface $e) {
                throw new PlatformTopologyException(
                    _("Unable to convert Central's API response") . (' : ') . $e->getMessage()
                );
            } catch (\Exception $e) {
                throw new PlatformTopologyException(
                    _("Error from Central's register API") . (' : ') . $e->getMessage()
                );
            }
        }

        /*
         * Insert the platform into 'platform_topology' table
         */
        try {
            // add the new platform
            $this->platformTopologyRepository->addPlatformToTopology($platformTopology);
        } catch (\Exception $ex) {
            throw new PlatformTopologyException(
                sprintf(
                    _("Error when adding in topology the platform : '%s'@'%s'"),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }
    }

    /**
     * Used when parent_address is null, to check if this type of platform is already registered
     *
     * @param string $type platform type to find
     * @throws PlatformTopologyConflictException
     */
    private function checkForAlreadyRegisteredPlatformType(string $type): void
    {
        $foundAlreadyRegisteredPlatformByType = $this->platformTopologyRepository->findPlatformTopologyByType($type);
        if (null !== $foundAlreadyRegisteredPlatformByType) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("A '%s': '%s'@'%s' is already registered"),
                    $type,
                    $foundAlreadyRegisteredPlatformByType->getName(),
                    $foundAlreadyRegisteredPlatformByType->getAddress()
                )
            );
        }
    }

    /**
     * Search for platforms nagios_server ID and set it as serverId
     *
     * @param PlatformTopology $platformTopology
     * @param bool $isLocalhost
     * @throws PlatformTopologyConflictException
     * @throws \Exception
     */
    private function setMonitoringServerId(PlatformTopology $platformTopology, bool $isLocalhost): void
    {
        $foundServerInNagiosTable = $this->platformTopologyRepository->findMonitoringIdFromName(
            $platformTopology->getName(),
            $isLocalhost
        );

        if (null === $foundServerInNagiosTable) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("The server type '%s' : '%s'@'%s' does not match the one configured in Centreon or is disabled"),
                    $platformTopology->getType(),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }
        $platformTopology->setServerId($foundServerInNagiosTable->getId());
    }

    /**
     * Search for already registered platforms using same name or address
     *
     * @param PlatformTopology $platformTopology
     * @throws PlatformTopologyConflictException
     */
    private function checkForAlreadyRegisteredSameNameOrAddress(PlatformTopology $platformTopology): void
    {
        $isAlreadyRegistered = $this->platformTopologyRepository->isPlatformAlreadyRegisteredInTopology(
            $platformTopology->getAddress(),
            $platformTopology->getName()
        );

        if (true === $isAlreadyRegistered) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("A platform using the name : '%s' or address : '%s' already exists"),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }
    }

    /**
     * Search for parent platform ID in topology
     *
     * @param PlatformTopology $platformTopology
     * @return PlatformTopology|null
     * @throws EntityNotFoundException
     * @throws PlatformTopologyConflictException
     */
    private function findParentPlatformAndSetId(PlatformTopology $platformTopology): ?PlatformTopology
    {
        if (null === $platformTopology->getParentAddress()) {
            return null;
        }

        $registeredParentInTopology = $this->platformTopologyRepository->findPlatformTopologyByAddress(
            $platformTopology->getParentAddress()
        );
        if (null === $registeredParentInTopology) {
            throw new EntityNotFoundException(
                sprintf(
                    _("No parent platform was found for : '%s'@'%s'"),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }

        // Check parent consistency
        if (
            !in_array(
                $registeredParentInTopology->getType(),
                [PlatformTopology::TYPE_REMOTE, PlatformTopology::TYPE_CENTRAL],
                false
            )
        ) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("Cannot register the '%s' platform : '%s'@'%s' behind a '%s' platform"),
                    $platformTopology->getType(),
                    $platformTopology->getName(),
                    $platformTopology->getAddress(),
                    $registeredParentInTopology->getType()
                )
            );
        }

        $platformTopology->setParentId($registeredParentInTopology->getId());

        // A platform behind a remote needs to send the data to the Central too
        if (
            null === $registeredParentInTopology->getParentId()
            && $registeredParentInTopology->getType() === PlatformTopology::TYPE_REMOTE
        ) {
            $registeredParentInTopology->setLinkedToAnotherServer(true);
            return $registeredParentInTopology;
        }
        return null;
    }
}
