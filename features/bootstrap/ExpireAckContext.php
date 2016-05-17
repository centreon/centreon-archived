<?php
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\ConfigurationPollersPage;

/**
 * Defines application features from the specific context.
 */
class ExpireAckContext extends CentreonContext
{
    private $hostName;
    private $serviceName;
    
    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'ExpireAckTestHost';
        $this->serviceName = 'ExpireAckTestService';
    }

    /**
     * @Given a service with a host configured with expirations
     */
    public function aServiceConfiguredWithExpirations()
    {
        $hostPage = $this->getHostServiceConfigurationPage();
        $hostPage->toHostCreationPage($this->hostName);
        // TODO: configure expiration
        $hostPage->saveHost();
        $servicePage = $this->getServiceConfigurationPage();
        $servicePage->toServiceCreationPage($this->hostName, $this->serviceName);
        // Enable passive checks
        $servicePage->saveService();
    }

    /**
     * @Given In a critical state
     */
    public function inACriticalState()
    {
        $this->assertFind('named', array('id', 'nrestart'))->check();
    }

    /**
     * @Given Acknowledged
     */
    public function acknowledged()
    {
        $this->getSession()->getPage()->selectFieldOption('restart_mode', 'Reload');
    }

    /**
     * @When I wait the time limit set for expirations
     */
    public function iWaitTheTimeLimitSetForExpirations()
    {
        $this->getSession()->getPage()->selectFieldOption('restart_mode', 'Restart');
    }

    /**
     * @Then The acknowledgement disappears
     */
    public function theAcknowledgementDisappears()
    {
        $this->assertFind('named', array('id', 'exportBtn'))->click();
    }

    /**
     * @Then Centreon Engine is restarted
     */
    public function centreonEngineIsRestarted()
    {
        $this->spin(function($context) {
            return $context->getSession()->getPage()->has('named', array('id', 'progressPct'))
                   && $context->getSession()->getPage()->find('named', array('id', 'progressPct'))->getText() == '100%';
        });
    }

    /**
     * @Then Centreon Engine is reloaded
     */
    public function centreonEngineIsReloaded()
    {
        $this->spin(function($context) {
            return $context->getSession()->getPage()->has('named', array('id', 'progressPct'))
                   && $context->getSession()->getPage()->find('named', array('id', 'progressPct'))->getText() == '100%';
        });
    }
}
