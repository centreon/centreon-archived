<?php
/*
 ** Copyright 2019 Centreon
 **
 ** All rights reserved.
 */

use Centreon\Test\Behat\CentreonAPIContext;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class APIv2Context extends CentreonAPIContext
{
    const HEADER_AUTH_TOKEN = 'X-AUTH-TOKEN';

    /**
     * @Given Exchange user identity token for admin user
     */
    public function authenticationWithAdmin()
    {
        $this->authentication('admin', 'centreon');
    }

    /**
     * @Given Exchange user identity token for username :username and password :password
     */
    public function authentication(string $username, string $password)
    {
        $baseUrl = $this->getMinkParameter('api_base');

        if (is_null($baseUrl)) {
            throw new \Exception('Unable to find a running container with Centreon Web');
        }

        $config = [
            'base_uri' => $baseUrl,
        ];

        $this->setClient(new Client($config));
        $this->authenticateToApi2($username, $password);

        $config[RequestOptions::HEADERS] = [
            static::HEADER_AUTH_TOKEN => $this->getAuthToken(),
            'Content-Type' => 'application/json'
        ];
        $this->setClient(new Client($config));
    }

    /**
     * @param string $username
     * @param string $password
     * @throws \Exception
     */
    private function authenticateToApi2(string $username, string $password)
    {
        $response = $this->getClient()
            ->post($this->getMinkParameter('api_base') . '/api/latest/login', [
            RequestOptions::JSON => [
                'security' => [
                    'credentials' => [
                        'login' => $username,
                        'password' => $password,
                    ],
                ],
            ],
        ]);

        $data = json_decode($response->getBody());

        if (empty($data->security->token)){
            throw new \Exception('Could not get authentication token from API.');
        }

        $this->setAuthToken((string)$data->security->token);
    }
}
