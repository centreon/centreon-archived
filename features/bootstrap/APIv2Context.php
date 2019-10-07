<?php
/*
 ** Copyright 2019 Centreon
 **
 ** All rights reserved.
 */

use Centreon\Test\Behat\CentreonAPIContext;
use GuzzleHttp\Client;
use Behat\Gherkin\Node\PyStringNode;

class APIv2Context extends CentreonAPIContext
{

    /**
     * Instantiate the http client for testing
     *
     * @Given Authenticate with username '$username' and password '$password'
     * @throws \Exception
     */
    public function authentication($username, $password)
    {
        $base_url = $this->getMinkParameter('api_base');

        if (is_null($base_url)) {
            throw new \Exception('Unable to find a running container with Centreon Web');
        }

        $config = [
            'base_url' => $base_url . '/api/latest',
        ];

        $this->setClient(new Client($config));
        $this->authenticateToApi2($username, $password);

        $config['headers'] = [
            'X-AUTH-TOKEN' => $this->getAuthToken(),
            'Content-Type' => 'application/json'
        ];
        $this->setClient(new Client($config));
    }

    /**
     * @throws \Exception
     */
    private function authenticateToApi2(string $username, string $password)
    {
        $response = $this->getClient()
            ->post('/login', [
            'form_params' => [
                'username' => $username,
                'password' => $password
            ]
        ]);
        $responseObj = json_decode($response->getBody());
        if (empty($responseObj->authToken)){
            throw new \Exception('Could not get authentication token from API.');
        }
        $this->setAuthToken($responseObj->authToken);
    }
}
