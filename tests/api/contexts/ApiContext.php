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

namespace Centreon\Tests\Api\Contexts;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Response\CurlResponse;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpClient\HttpClient;
use Centreon\Tests\Api\Contexts\JsonContextTrait;
use Centreon\Tests\Api\Contexts\RestContextTrait;

/**
 * This context class contains the main definitions of the steps used by contexts to validate API
 */
class ApiContext implements Context
{
    use JsonContextTrait, RestContextTrait;

    /**
     * @var CurlHttpClient
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $httpHeaders = [];

    /**
     * @var string
     */
    protected $token;

    /**
     * @var CurlResponse
     */
    protected $httpResponse;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
    }

    /**
     * @return CurlHttpClient
     */
    protected function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param CurlHttpClient $httpClient
     * @return void
     */
    protected function setHttpClient(CurlHttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return array
     */
    protected function getHttpHeaders()
    {
        $httpHeaders = $this->httpHeaders;

        if (isset($this->token)) {
            $httpHeaders['X-AUTH-TOKEN'] = $this->token;
        }

        return $httpHeaders;
    }

    /**
     * @param array $httpHeaders
     * @return void
     */
    protected function setHttpHeaders(array $httpHeaders)
    {
        $this->httpHeaders = $httpHeaders;
    }

    /**
     * @return string
     */
    protected function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return void
     */
    protected function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return CurlResponse
     */
    protected function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * @param CurlResponse $httpResponse
     * @return void
     */
    protected function setHttpResponse(CurlResponse $httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    /**
     * Log in API
     *
     * @Given I am logged in
     */
    public function iAmLoggedIn()
    {
        $this->setHttpHeaders(['Content-Type' => 'application/json']);
        $response = $this->iSendARequestToWithBody(
            'POST',
            'http://10.30.2.72/centreon/api/latest/login',
            '{
                "security": {
                    "credentials": {
                        "login": "admin",
                        "password": "centreon"
                    }
                }
            }'
        );

        $response = json_decode($response->getContent(), true);
        $this->setToken(
            $response['security']['token']
        );
    }

    /**
     * Validate response following json format file
     *
     * @Then the response should use :type centreon JSON format
     */
    public function theResponseShouldUseCentreonJsonFormat(string $type)
    {
        $this->theResponseCodeShouldBe(200);
        $this->theResponseShouldBeFormattedLikeJsonFormat("monitoring/service/" . $type . ".json");
    }
}
