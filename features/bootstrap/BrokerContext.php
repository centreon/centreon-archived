<?php

use Centreon\Test\Behat\BrokerConfigurationListingPage;
use Centreon\Test\Behat\CentreonContext;

class BrokerContext extends CentreonContext
{
    /**
     *  @Given a daemon broker configuration
     */
    public function aDaemonBrokerConfiguration()
    {
        $page = new BrokerConfigurationListingPage($this);
        var_dump($page->getEntries());
    }

    /**
     *  @When I update broker configuration file name
     */
    public function IUpdateBrokerConfigurationFileName()
    {

    }

    /**
     *  @When I export configuration
     */
    public function IExportConfiguration()
    {

    }

    /**
     *  @Then the new configuration is applied
     */
    public function theNewConfigurationIsApplied()
    {

    }

    /**
     *  @Then the monitoring is still working
     */
    public function theMonitoringIsStillWorking()
    {

    }
}
