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

namespace Centreon\Infrastructure\Gorgone;

use Centreon\Domain\Gorgone\Interfaces\CommandInterface;
use Centreon\Domain\Gorgone\Interfaces\ResponseRepositoryInterface;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Centreon\Infrastructure\Gorgone\Interfaces\ConfigurationLoaderApiInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * This class is designed to retrieve command responses from the Gorgone server using its API.
 *
 * @package Centreon\Infrastructure\Gorgone
 */
class ResponseRepositoryAPI implements ResponseRepositoryInterface
{
    /**
     * @var HttpClientInterface Http client library that will be used to
     * communicate with the Gorgone server through its API.
     */
    private $client;

    /**
     * @var OptionServiceInterface
     */
    private $optionService;
    /**
     * @var ConfigurationLoaderApiInterface
     */
    private $configuration;

    /**
     * @param OptionServiceInterface $optionService
     * @param ConfigurationLoaderApiInterface $configuration
     */
    public function __construct(OptionServiceInterface $optionService, ConfigurationLoaderApiInterface $configuration)
    {
        $this->client = new CurlHttpClient();
        $this->optionService = $optionService;
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function getResponse(CommandInterface $command): string
    {
        $isCertificateShouldBeVerify = $this->configuration->isSecureConnectionSelfSigned() === false;
        $options = [
            'timeout' => $this->configuration->getCommandTimeout(),
            'verify_peer' => $isCertificateShouldBeVerify,
            'verify_host' => $isCertificateShouldBeVerify,
        ];
        if ($this->configuration->getApiUsername() !== null) {
            $options = array_merge(
                $options,
                [ 'auth_basic' => $this->configuration->getApiUsername() . ':'
                    . $this->configuration->getApiPassword()]
            );
        }
        try {
            $uri = sprintf(
                '%s://%s:%d/api/nodes/%d/log/%s',
                $this->configuration->isApiConnectionSecure() ? 'https' : 'http',
                $this->configuration->getApiIpAddress(),
                $this->configuration->getApiPort(),
                $command->getMonitoringInstanceId(),
                $command->getToken()
            );
            $response = $this->client->request('GET', $uri, $options);
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Request error', $response->getStatusCode());
            }
            return $response->getContent();
        } catch (\Throwable $ex) {
            throw new \Exception($ex->getMessage(), (int) $ex->getCode(), $ex);
        }
    }
}
