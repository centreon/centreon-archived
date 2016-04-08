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
class RestartCentreonEngineContext extends CentreonContext
{

    private $pollers_page;

    public function __construct()
    {
        parent::__construct();
        $this->pollers_page = new ConfigurationPollersPage($this);
    }

    /**
     * @Given I am on the Central poller page
     */
    public function iAmOnTheCentralPollerWebpage()
    {
        $this->visit('/main.php?p=60902&poller=1');
    }

    /**
     * @Given I check Restart Monitoring Engine
     */
    public function iCheckRestartMonitoringEngine()
    {
        $this->assertFind('named', array('id', 'nrestart'))->check();
    }

    /**
     * @Given I select the Method Restart
     */
    public function iSelectTheMethodRestart()
    {
        $this->getSession()->getPage()->selectFieldOption('restart_mode', 'Reload');
    }

    /**
     * @Given I select the Method Reload
     */
    public function iSelectTheMethodReload()
    {
        $this->getSession()->getPage()->selectFieldOption('restart_mode', 'Restart');
    }

    /**
     * @When I export Centreon Engine
     */
    public function iExportCentreonEngine()
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
