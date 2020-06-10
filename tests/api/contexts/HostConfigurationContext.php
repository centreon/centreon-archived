<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
class HostContext implements Context
{
    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var \Symfony\Component\HttpClient\CurlHttpClient|null
     */
    private $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    /**
     * @BeforeFeature
     */
    public static function prepare(BeforeFeatureScope $scope)
    {
        var_dump('before');
    }

    /**
     * @Given I create a host
     */
    public function iCallApi()
    {
        //$this->response = $this->client->request('GET', 'http://127.0.0.1/centreon/api/v2/login');
    }

    /**
     * @Then the host is properly created
     */
    public function aResponseIsReceived()
    {
        if ($this->response === null) {
            //throw new \RuntimeException('No response received');
        }
    }
}
