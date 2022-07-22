<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\PollerConfigurationExportPage;

/**
 * Defines application features from the specific context.
 */
class RestartCentreonEngineContext extends CentreonContext
{
    private $export_page;

    /**
     * @Given I am on the poller configuration export page
     */
    public function iAmOnTheCentralPollerWebpage()
    {
        $this->export_page = new PollerConfigurationExportPage($this);
    }

    /**
     * @Given I check Restart Monitoring Engine
     */
    public function iCheckRestartMonitoringEngine()
    {
        $this->export_page->setProperties(['restart_engine' => true]);
    }

    /**
     * @Given I select the method Restart
     */
    public function iSelectTheMethodRestart()
    {
        $this->export_page->setProperties(['restart_method' => PollerConfigurationExportPage::METHOD_RESTART]);
    }

    /**
     * @Given I select the method Reload
     */
    public function iSelectTheMethodReload()
    {
        $this->export_page->setProperties(['restart_method' => PollerConfigurationExportPage::METHOD_RELOAD]);
    }

    /**
     * @When I export Centreon Engine
     */
    public function iExportCentreonEngine()
    {
        $this->export_page->export();
    }

    /**
     * @Then Centreon Engine is restarted
     */
    public function centreonEngineIsRestarted()
    {
        $this->spin(
            fn($context) => $context->getSession()->getPage()->has('named', ['id', 'progressPct'])
                && $context->getSession()->getPage()->find('named', ['id', 'progressPct'])
                    ->getText() == '100%'
        );
    }

    /**
     * @Then Centreon Engine is reloaded
     */
    public function centreonEngineIsReloaded()
    {
        $this->spin(
            fn($context) => $context->getSession()->getPage()->has('named', ['id', 'progressPct'])
                && $context->getSession()->getPage()->find('named', ['id', 'progressPct'])
                    ->getText() == '100%'
        );
    }
}
