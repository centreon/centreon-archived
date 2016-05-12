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

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Given A service configured with expirations
     */
    public function aServiceConfiguredWithExpirations()
    {
        $this->visit('/main.php?p=60902&poller=1');
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
