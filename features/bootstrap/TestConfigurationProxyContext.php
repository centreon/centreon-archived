<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ParametersCentreonUiPage;

class TestConfigurationProxyContext extends CentreonContext 
{
    private $page;
    private $wrongAddress = 'squad';
    private $wrongProxyPort = '9999';
    
    

    /**
     * @When I click on the test configuration button
     */
    public function IclickOnTheTestConfigurationButton()
    {
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
     * @Given a Centreon user on the Centreon UI page with a proxy url and port wrongly configured
     */
    public function aCentreonUserOnTheCentreonUIpageWithAproxyUrlAndPortWronglyConfigured()
    {   
        $this->page = new ParametersCentreonUiPage($this);
        $this->page->setProperties(array(
            'proxy_url'=> $this->wrongAddress,
            'proxy_port'=> $this->wrongProxyPort
        ));
    }
    
    /**
     * @Then a popin displays an error message
     */
    public function ApopinDisplaysAnErrorMessage()
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
