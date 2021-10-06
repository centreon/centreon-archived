<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\MonitoringServer\Repository;

use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Log\LoggerTrait;
use DateTime;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationTokenServiceInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerConfigurationRepositoryInterface;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\Exception\TimeoutException;
use Centreon\Infrastructure\MonitoringServer\Repository\Exception\MonitoringServerConfigurationRepositoryException;

/**
 * This class is designed to represent the API repository to manage the generation/move/reload of the monitoring
 * server configuration.
 *
 * @package Centreon\Infrastructure\MonitoringServer\Repository
 */
class MonitoringServerConfigurationRepositoryApi implements MonitoringServerConfigurationRepositoryInterface
{
    use LoggerTrait;

    /**
     * @var ContactInterface
     */
    private $contact;
    /**
     * @var AuthenticationTokenServiceInterface
     */
    private $authenticationTokenService;
    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $serverUri;

    /**
     * @var int
     */
    private $timeout = 60;

    /**
     * @param AuthenticationTokenServiceInterface $authenticationTokenService
     * @param ContactInterface $contact
     * @param HttpClientInterface $httpClient
     */
    public function __construct(
        AuthenticationTokenServiceInterface $authenticationTokenService,
        ContactInterface $contact,
        HttpClientInterface $httpClient
    ) {
        $this->contact = $contact;
        $this->authenticationTokenService = $authenticationTokenService;
        $this->httpClient = $httpClient;
    }

    /**
     * To be used by the dependency injector to increase the timeout limit.
     *
     * @param int $timeout
     * @return MonitoringServerConfigurationRepositoryApi
     */
    public function setTimeout(int $timeout): MonitoringServerConfigurationRepositoryApi
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @throws \Assert\AssertionFailedException
     */
    private function initUri(): void
    {
        if ($this->serverUri === null) {
            $serverScheme = $_SERVER['REQUEST_SCHEME'] ?: 'http';
            Assertion::notEmpty($_SERVER['SERVER_NAME']);
            Assertion::notEmpty($_SERVER['REQUEST_URI']);
            $prefixUri = explode('/', $_SERVER['REQUEST_URI'])[1];
            // ex: http://localhost:80/centreon
            $this->serverUri = $serverScheme . '://' . $_SERVER['SERVER_NAME'] . '/' . $prefixUri;
        }
    }

    /**
     * @inheritDoc
     */
    public function generateConfiguration(int $monitoringServerId): void
    {
        $this->callHttp('generateFiles.php', 'generate=true&debug=true&poller=' . $monitoringServerId);
    }

    /**
     * @inheritDoc
     */
    public function moveExportFiles(int $monitoringServerId): void
    {
        $this->callHttp('moveFiles.php', 'poller=' . $monitoringServerId);
    }

     /**
     * @inheritDoc
     */
    public function reloadConfiguration(int $monitoringServerId): void
    {
        $this->callHttp('restartPollers.php', 'mode=1&poller=' . $monitoringServerId);
    }

    /**
     * @param string $filePath
     * @param string $payloadBody
     * @throws RepositoryException
     * @throws TimeoutException
     * @throws AuthenticationException
     */
    private function callHttp(string $filePath, string $payloadBody): void
    {
        try {
            $this->initUri();
            $fullUriPath = $this->serverUri . '/include/configuration/configGenerate/xml/' . $filePath;

            $optionPayload = [
                'proxy' => null,
                'no_proxy' => '*',
            ];

            // On https scheme, the SSL verify_peer needs to be specified
            $optionPayload['verify_peer'] = $_SERVER['REQUEST_SCHEME'] === 'https';
            $optionPayload['verify_host'] = $optionPayload['verify_peer'];

            $authenticationTokens = $this->authenticationTokenService->findByContact($this->contact);
            if ($authenticationTokens === null) {
                throw AuthenticationException::authenticationTokenNotFound();
            }
            $providerToken = $authenticationTokens->getProviderToken();
            if (
                $providerToken->getExpirationDate() === null
                || $providerToken->getExpirationDate()->getTimestamp() < (new DateTime())->getTimestamp()
            ) {
                throw AuthenticationException::authenticationTokenExpired();
            }
            $optionPayload['headers'] = ['X-AUTH-TOKEN' => $providerToken->getToken()];
            $optionPayload['body'] = $payloadBody;
            $optionPayload['timeout'] = $this->timeout;

            $response = $this->httpClient->request('POST', $fullUriPath, $optionPayload);
            if ($response->getStatusCode() !== 200) {
                throw MonitoringServerConfigurationRepositoryException::apiRequestFailed($response->getStatusCode());
            }

            $xml = $response->getContent();
            if (!empty($xml)) {
                if (($element = simplexml_load_string($xml)) !== false) {
                    if ((string) $element->statuscode !== '0') {
                        throw new RepositoryException((string) $element->error);
                    }
                }
            } else {
                throw MonitoringServerConfigurationRepositoryException::responseEmpty();
            }
        } catch (RepositoryException | AuthenticationException $ex) {
            throw $ex;
        } catch (\Assert\AssertionFailedException $ex) {
            throw MonitoringServerConfigurationRepositoryException::errorWhenInitializingApiUri();
        } catch (\Throwable $ex) {
            if ($ex instanceof TransportException && strpos($ex->getMessage(), 'timeout') > 0) {
                throw MonitoringServerConfigurationRepositoryException::timeout($ex);
            }
            throw MonitoringServerConfigurationRepositoryException::unexpectedError($ex);
        }
    }
}
