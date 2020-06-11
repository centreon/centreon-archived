<?php

namespace Centreon\Tests\Api\Contexts;

use Behat\Gherkin\Node\PyStringNode;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Centreon\Tests\Api\Contexts\Asserter;

Trait RestContextTrait
{
    //use Asserter;

    public function locatePath($path)
    {
        //$startUrl = rtrim($this->getMinkParameter('base_url'), '/') . '/';
        $startUrl = '/';

        return 0 !== strpos($path, 'http') ? $startUrl . ltrim($path, '/') : $path;
    }





    /**
     * @return Symfony\Component\HttpClient\CurlHttpClient
     */
    //abstract protected function getHttpClient();

    /**
     * @return Symfony\Component\HttpClient\Response\CurlResponse
     */
    //abstract protected function getHttpResponse();

    /**
     * Sends a HTTP request
     *
     * @Given I send a :method request to :url
     */
    public function iSendARequestTo($method, $url, PyStringNode $body = null, $files = [])
    {
        return $this->getHttpClient()->request(
            $method,
            $this->locatePath($url),
            [],
            $files,
            $body !== null ? $body->getRaw() : null
        );
    }

    /**
     * Sends a HTTP request with a some parameters
     *
     * @Given I send a :method request to :url with parameters:
     */
    public function iSendARequestToWithParameters($method, $url, TableNode $data)
    {
        $files = [];
        $parameters = [];

        foreach ($data->getHash() as $row) {
            if (!isset($row['key']) || !isset($row['value'])) {
                throw new \Exception("You must provide a 'key' and 'value' column in your table node.");
            }

            if (is_string($row['value']) && substr($row['value'], 0, 1) == '@') {
                $files[$row['key']] = rtrim($this->getMinkParameter('files_path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.substr($row['value'],1);
            }
            else {
                $parameters[$row['key']] = $row['value'];
            }
        }

        return $this->getHttpClient()->request(
            $method,
            $this->locatePath($url),
            $parameters,
            $files
        );
    }

    /**
     * Sends a HTTP request with a body
     *
     * @Given I send a :method request to :url with body:
     */
    public function iSendARequestToWithBody($method, $url, PyStringNode $body)
    {
        return $this->iSendARequestTo($method, $url, $body);
    }

    /**
     * Checks, whether the response content is equal to given text
     *
     * @Then the response should be equal to
     * @Then the response should be equal to:
     */
    public function theResponseShouldBeEqualTo(PyStringNode $expected)
    {
        $expected = str_replace('\\"', '"', $expected);
        $actual   = $this->getHttpResponse()->getContent();
        $message = "Actual response is '$actual', but expected '$expected'";
        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * Checks, whether the response content is null or empty string
     *
     * @Then the response should be empty
     */
    public function theResponseShouldBeEmpty()
    {
        $actual = $this->getHttpResponse()->getContent();
        $message = "The response of the current page is not empty, it is: $actual";
        $this->assertTrue(null === $actual || "" === $actual, $message);
    }

    /**
     * Checks, whether the header name is equal to given text
     *
     * @Then the header :name should be equal to :value
     */
    public function theHeaderShouldBeEqualTo($name, $value)
    {
        $actual = $this->getHttpResponse()->getHeaders()[$name];
        $this->assertEquals(strtolower($value), strtolower($actual),
            "The header '$name' should be equal to '$value', but it is: '$actual'"
        );
    }

    /**
    * Checks, whether the header name is not equal to given text
    *
    * @Then the header :name should not be equal to :value
    */
    public function theHeaderShouldNotBeEqualTo($name, $value) {
        $actual = $this->getHttpResponse()->getHeaders()[$name];
        if (strtolower($value) == strtolower($actual)) {
            throw new ExpectationException(
                "The header '$name' is equal to '$actual'",
                $this->getSession()->getDriver()
            );
        }
    }

    public function theHeaderShouldBeContains($name, $value)
    {
        trigger_error(
            sprintf('The %s function is deprecated since version 3.1 and will be removed in 4.0. Use the %s::theHeaderShouldContain function instead.', __METHOD__, __CLASS__),
            E_USER_DEPRECATED
        );
        $this->theHeaderShouldContain($name, $value);
    }

    /**
     * Checks, whether the header name contains the given text
     *
     * @Then the header :name should contain :value
     */
    public function theHeaderShouldContain($name, $value)
    {
        $actual = $this->getHttpResponse()->getHeaders()[$name];
        $this->assertContains($value, $actual,
            "The header '$name' should contain value '$value', but actual value is '$actual'"
        );
    }

    /**
     * Checks, whether the header name doesn't contain the given text
     *
     * @Then the header :name should not contain :value
     */
    public function theHeaderShouldNotContain($name, $value)
    {
        $this->assertNotContains($value, $this->getHttpResponse()->getHeaders()[$name],
            "The header '$name' contains '$value'"
        );
    }

    /**
     * Checks, whether the header not exist
     *
     * @Then the header :name should not exist
     */
    public function theHeaderShouldNotExist($name)
    {
        $this->not(function () use($name) {
            $this->theHeaderShouldExist($name);
        }, "The header '$name' exists");
    }

    protected function theHeaderShouldExist($name)
    {
        return isset($this->$this->getHttpResponse()->getHeaders()[$name]);
    }

    /**
     * @Then the header :name should match :regex
     */
    public function theHeaderShouldMatch($name, $regex)
    {
        $actual = $this->getHttpResponse()->getHeaders()[$name];

        $this->assertEquals(
            1,
            preg_match($regex, $actual),
            "The header '$name' should match '$regex', but it is: '$actual'"
        );
    }

    /**
     * @Then the header :name should not match :regex
     */
    public function theHeaderShouldNotMatch($name, $regex)
    {
        $this->not(
            function () use ($name, $regex) {
                $this->theHeaderShouldMatch($name, $regex);
            },
            "The header '$name' should not match '$regex'"
        );
    }

   /**
     * Checks, that the response header expire is in the future
     *
     * @Then the response should expire in the future
     */
    public function theResponseShouldExpireInTheFuture()
    {
        $date = new \DateTime($this->getHttpResponse()->getHeaders()['Date'][0]);
        $expires = new \DateTime($this->getHttpResponse()->getHeaders()['Expires'][0]);

        $this->assertSame(1, $expires->diff($date)->invert,
            sprintf('The response doesn\'t expire in the future (%s)', $expires->format(DATE_ATOM))
        );
    }

    /**
     * Add an header element in a request
     *
     * @Then I add :name header equal to :value
     */
    public function iAddHeaderEqualTo($name, $value)
    {
        $this->request->setHttpHeader($name, $value);
    }

    /**
     * @Then the response should be encoded in :encoding
     */
    public function theResponseShouldBeEncodedIn($encoding)
    {
        $content = $this->getHttpReponse()->getContent();
        if (!mb_check_encoding($content, $encoding)) {
            throw new \Exception("The response is not encoded in $encoding");
        }

        $this->theHeaderShouldContain('Content-Type', "charset=$encoding");
    }

    /**
     * @Then print last response headers
     */
    public function printLastResponseHeaders()
    {
        $text = '';
        $headers = $this->getHttpResponse()->getHeaders();

        foreach ($headers as $name => $value) {
            $text .= $name . ': '. $value . "\n";
        }
        echo $text;
    }


    /**
     * @Then print the corresponding curl command
     */
    public function printTheCorrespondingCurlCommand()
    {
        $method = $this->request->getMethod();
        $url = $this->request->getUri();

        $headers = '';
        foreach ($this->request->getServer() as $name => $value) {
            if (substr($name, 0, 5) !== 'HTTP_' && $name !== 'HTTPS') {
                $headers .= " -H '$name: $value'";
            }
        }

        $data = '';
        $params = $this->request->getParameters();
        if (!empty($params)) {
            $query = http_build_query($params);
            $data = " --data '$query'" ;
        }

        echo "curl -X $method$data$headers '$url'";
    }
}
