<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ServiceConfigurationListingPage;

class ShowDisabledHostsServicesContext extends CentreonContext
{
    /**
     * @Given an host with configured services
     */
    public function anHostWithConfiguredServices()
    {
        $this->visit('/main.php?p=601');

        /* Wait page loaded */

        $host = $this->assertFind('css', 'tr.list_one > td:nth-child(2) > a')->getText();
        if ($host != 'Centreon-Server') {
            throw new \Exception('Host ' . $host . ' is not found');
        }
    }

    /**
     * @Given the host is disabled
     */
    public function theHostIsDisabled()
    {
        $bt_disabled = $this->assertFind('css', 'tr.list_one > td.ListColRight > a');
        $bt_disabled->click();
        sleep(5);
    }

    /**
     * @When I access to the menu of services configuration
     */
    public function iAccessToTheMenuOfServicesConfiguration()
    {
        $listingServices = new ServiceConfigurationListingPage($this);
        $listingServices->isPageValid();
    }

    /**
     * @When I activate the visibility filter of disabled hosts
     */
    public function iActivateTheVisibilityFilterOfDisabledHosts()
    {
        $this->getSession()->evaluateScript("document.getElementById('statusHostFilter').checked = true");
        $search = $this->assertFind('named', array('id_or_name', 'Search'));
        $search->click();
        sleep(2);
    }

    /**
     * @Then the services of disabled hosts are displayed
     */
    public function theServicesOfDisabledHostsAreDisplayed()
    {
        $service = $this->assertFind('css', 'tr:nth-child(9) > td:nth-child(3) > div > a')->getText();
        if ($service != 'Ping') {
            throw new \Exception('service ' . $service . ' is not found');
        }
    }
}
