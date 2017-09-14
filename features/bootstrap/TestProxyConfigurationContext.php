<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ParametersCentreonUiPage;

class TestProxyConfigurationContext extends CentreonContext
{
    private $page;
    private $wrongProxyAddress = 'squad';
    private $wrongProxyPort = '9999';
    
    /**
     * @When I test the proxy configuration in the interface
     */
    public function ITestTheProxyConfigurationInTheInterface()
    {
        $this->visit('main.php?p=50110&o=general');
        $this->assertFind('css', 'input[name="test_proxy"]')->click();
    }
    
    /**
     * @Then a popin displays a successful connexion
     */
    public function APopinDisplaysASuccessfulConnexion()
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'span[class="msg-field success2"]'
                );
            },
            'The pop-in did not showed up'
        );
        $value = $this->assertFind('css', 'span[class="msg-field success2"]')->getText();
        if ($value !== 'Connection Successful') {
            throw new \Exception('The URL to reach failed');
        }
    }
    
    /**
     * @Given I am logged in a Centreon server with a wrongly configured proxy
     */
    public function IAmLoggedInAcentreonServerWithAwronglyConfiguredProxy()
    {
        $this->iAmLoggedInACentreonServer();
        $this->page = new ParametersCentreonUiPage($this);
        $this->page->setProperties(array(
            'proxy_url'=> $this->wrongProxyAddress,
            'proxy_port'=> $this->wrongProxyPort
        ));
        $this->page->save();
    }
    
    /**
     * @Then a popin displays an error message
     */
    public function aPopinDisplaysAnErrorMessage()
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'span[class="msg-field error"]'
                );
            },
            'The pop-in did not showed up'
        );
        $value = $this->assertFind('css', 'span[class="msg-field error"]')->getText();
        if ($value == 'Connection Successful') {
            throw new \Exception('The Proxy configuration is incorrect');
        }
    }
}
