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
use Centreon\Domain\Gorgone\Interfaces\CommandRepositoryInterface;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Centreon\Infrastructure\Gorgone\Interfaces\ConfigurationLoaderApiInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * This class is designed to send commands to the Gorgone server using its API.
 *
 * @package Centreon\Infrastructure\Gorgone
 */
class CommandRepositoryAPI implements CommandRepositoryInterface
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
    public function send(CommandInterface $command): string
    {
        $isCertificateShouldBeVerify = $this->configuration->isSecureConnectionSelfSigned() === false;
        $options = [
            'body' => $command->getBodyRequest(),
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
                '%s://%s:%d/api/%s',
                $this->configuration->isApiConnectionSecure() ? 'https' : 'http',
                $this->configuration->getApiIpAddress(),
                $this->configuration->getApiPort(),
                $command->getUriRequest()
            );
            $response = $this->client->request($command->getMethod(), $uri, $options);
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Bad request', $response->getStatusCode());
            }
            $jsonResponse = json_decode($response->getContent(), true);
            if (!array_key_exists('token', $jsonResponse)) {
                $exceptionMessage = 'Token not found';
                if (array_key_exists('message', $jsonResponse)) {
                    if ($jsonResponse['message'] === 'Method not implemented') {
                        $exceptionMessage = 'The "autodiscovery" module of Gorgone is not loaded';
                    } else {
                        $exceptionMessage = $jsonResponse['message'];
                    }
                }
                throw new CommandRepositoryException($exceptionMessage);
            }
            return (string) $jsonResponse['token'];
        } catch (CommandRepositoryException $ex) {
            throw $ex;
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
