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
     * @var array<string, string> Connection parameters which will be used to initialise
     * the connection with the API of the Gorgone server
     */
    private $connectionParameters;

    /**
     * @var OptionServiceInterface
     */
    private $optionService;

    /**
     * @param OptionServiceInterface $optionService
     */
    public function __construct(OptionServiceInterface $optionService)
    {
        $this->client = new CurlHttpClient();
        $this->optionService = $optionService;
    }

    /**
     * @inheritDoc
     */
    public function send(CommandInterface $command): string
    {
        // Before to send command we retrieve the connection parameters to communicate with the Gorgone server
        if (empty($this->connectionParameters)) {
            $options = $this->optionService->findSelectedOptions([
                'gorgone_api_address',
                'gorgone_api_port',
                'gorgone_api_username',
                'gorgone_api_password',
                'gorgone_api_ssl',
                'gorgone_api_allow_self_signed'
            ]);
            foreach ($options as $option) {
                $this->connectionParameters[$option->getName()] = $option->getValue();
            }
        }
        $isAllowCertificateSelfSigned = $this->connectionParameters['gorgone_api_allow_self_signed'] === '0';
        $options = [
            'body' => $command->getBodyRequest(),
            'timeout' => 2,
            'verify_peer' => $isAllowCertificateSelfSigned,
            'verify_host' => $isAllowCertificateSelfSigned,
        ];
        if (!empty($this->connectionParameters['gorgone_api_username'])) {
            $options = array_merge(
                $options,
                [ 'auth_basic' => $this->connectionParameters['gorgone_api_username'] . ':'
                  . $this->connectionParameters['gorgone_api_password']]
            );
        }
        try {
            $uri = sprintf(
                '%s://%s:%d/api/%s',
                (($this->connectionParameters['gorgone_api_ssl'] === '1') ? 'https' : 'http'),
                $this->connectionParameters['gorgone_api_address'],
                $this->connectionParameters['gorgone_api_port'],
                $command->getUriRequest()
            );
            $response = $this->client->request('GET', $uri, $options);
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Bad request', $response->getStatusCode());
            }
            $jsonResponse = json_decode($response->getContent(), true);
            if (!array_key_exists('token', $jsonResponse)) {
                throw new \Exception('Token not found');
            }
            return (string) $jsonResponse['token'];
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
