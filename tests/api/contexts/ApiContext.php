<?php

namespace Centreon\Tests\Api\Contexts;

use Behat\Behat\Context\Context;
use Symfony\Component\HttpClient\HttpClient;
use Centreon\Tests\Api\Contexts\JsonContextTrait;
use Centreon\Tests\Api\Contexts\RestContextTrait;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
class ApiContext implements Context
{
    use JsonContextTrait, RestContextTrait;

    /**
     * @var Symfony\Component\HttpClient\CurlHttpClient
     */
    protected $httpClient;

    /**
     * @var Symfony\Component\HttpClient\Response\CurlResponse
     */
    protected $httpResponse;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
        $this->httpResponse = $this->httpClient->request('GET', 'https://licenseapi.herokuapp.com/licenses');
    }

    /**
     * @return Symfony\Component\HttpClient\CurlHttpClient
     */
    protected function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return Symfony\Component\HttpClient\Response\CurlResponse
     */
    protected function getHttpResponse()
    {
        return $this->httpResponse;
    }
}
