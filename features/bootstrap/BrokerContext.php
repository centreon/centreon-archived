<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\BrokerConfigurationListingPage;
use Centreon\Test\Behat\PollerConfigurationExportPage;

class BrokerContext extends CentreonContext
{
    protected $page;

    /**
     *  @Given a daemon broker configuration
     */
    public function aDaemonBrokerConfiguration()
    {
        $this->page = new BrokerConfigurationListingPage($this);
        $this->page = $this->page->inspect('central-broker-master');
    }

    /**
     *  @When I update broker configuration file name
     */
    public function IUpdateBrokerConfigurationFileName()
    {
        $this->page->setProperties(
            array(
                'filename' => 'new-name.xml'
            )
        );
        $this->page->save();
    }

    /**
     *  @When I export configuration
     */
    public function IExportConfiguration()
    {
        $this->page = new PollerConfigurationExportPage($this);
        $this->page->setProperties(
            array(
                'pollers' => 'central',
                'generate_files' => true,
                'run_debug' => true,
                'move_files' => true,
                'restart_engine' => true,
                'restart_method' => true
            )
        );
        $this->page->export();
    }

    /**
     *  @Then the new configuration is applied
     */
    public function theNewConfigurationIsApplied()
    {
        $return = $this->container->execute('cat /etc/centreon-broker/watchdog.xml', 'web');
        if (!preg_match('/\/etc\/centreon-broker\/\/?new-name\.xml/', $return['output'])) {
            throw new \Exception('new-name.xml is not declared in watchdog.xml');
        }
    }

    /**
     *  @Then the monitoring is still working
     */
    public function theMonitoringIsStillWorking()
    {
        if (!$this->getPollingState()) {
            throw new \Exception('Poller is not running.');
        }
    }
}
