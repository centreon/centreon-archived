<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Monitoring\MetricsConfigurationListingPage;
use Centreon\Test\Behat\Monitoring\MetricsConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Monitoring\MonitoringHostsPage;
use Centreon\Test\Behat\Monitoring\MonitoringServicesPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationListingPage;
use Centreon\Test\Behat\Monitoring\ServiceMonitoringDetailsPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;

class VirtualMetricHandleContext extends CentreonContext
{
    protected $page;
    protected $vmName = 'vmtestname';
    protected $host = 'MetricTestHostname';
    protected $hostService = 'MetricTestService';
    


    /**
     * @When I add a virtual metric
     */
    public function iAddAVirtualMetric()
    {
        $this->page = new MetricsConfigurationListingPage($this);
        $this->assertFind('css', 'a[class="btc bt_success"]')->click();
        $this->page = new MetricsConfigurationPage($this);
        $this->page->setProperties(array(
            'name' => $this->vmName,
            'linked-host_services' => $this->host . ' - ' . $this->hostService,
            'function' => 'test1'
        ));
        $this->page->save();
    }

    /**
     * @Then all properties are saved
     */
    public function allPropertiesAreSaved()
    {
       $this->page = new MetricsConfigurationListingPage($this);
       $data = $this->page->getEntry($this->vmName);
       if ($data['name'] != $this->vmName || $data['function'] != 'test1') {
           throw new \Exception('some properties has not been saved');
       }
    }

    /**
     * @Given an existing virtual metric
     */
    public function anExistingVirtualMetric()
    {
        throw new PendingException();
    }

    /**
     * @When I modify a virtual metric
     */
    public function iModifyAVirtualMetric()
    {
        throw new PendingException();
    }

    /**
     * @Then all modified properties are updated
     */
    public function allModifiedPropertiesAreUpdated()
    {
        throw new PendingException();
    }

    /**
     * @When I duplicate a virtual metric
     */
    public function iDuplicateAVirtualMetric()
    {
        throw new PendingException();
    }

    /**
     * @Then all properties are copied except the name
     */
    public function allPropertiesAreCopiedExceptTheName()
    {
        throw new PendingException();
    }

    /**
     * @When I delete a virtual metric
     */
    public function iDeleteAVirtualMetric()
    {
        throw new PendingException();
    }

    /**
     * @Then the virtual metric disappears from the Virtual metrics list
     */
    public function theVirtualMetricDisappearsFromTheVirtualMetricsList()
    {
        throw new PendingException();
    }
}
