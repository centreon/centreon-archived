<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Monitoring\MetricsConfigurationListingPage;
use Centreon\Test\Behat\Monitoring\MetricsConfigurationPage;

class VirtualMetricHandleContext extends CentreonContext
{
    protected $page;
    protected $vmName = 'vmtestname';
    protected $host = 'MetricTestHostname';
    protected $functionRPN = 'test10';
    protected $hostService = 'MetricTestService';
    protected $duplicatedVmName = 'vmtestname_1';


    /**
     * @When I add a virtual metric
     */
    public function iAddAVirtualMetric()
    {
        $this->page = new MetricsConfigurationListingPage($this);
        $this->assertFind('css', 'a[class*="btc bt_success"]')->click();
        $this->page = new MetricsConfigurationPage($this);
        $this->page->setProperties(array(
            'name' => $this->vmName,
            'linked-host_services' => $this->host . ' - ' . $this->hostService,
            'known_metrics' => $this->functionRPN,
        ));
        $this->page->setProperties(array('function' => $this->functionRPN));
        $this->page->save();
    }

    /**
     * @Then all properties are saved
     */
    public function allPropertiesAreSaved()
    {
        $this->page = new MetricsConfigurationListingPage($this);
        $data = $this->page->getEntry($this->vmName);
        if ($data['name'] != $this->vmName || $data['function'] != $this->functionRPN) {
            throw new \Exception('Some properties have not been saved');
        }
    }

    /**
     * @Given an existing virtual metric
     */
    public function anExistingVirtualMetric()
    {
        $this->iAddAVirtualMetric();
    }

    /**
     * @When I duplicate a virtual metric
     */
    public function iDuplicateAVirtualMetric()
    {
        $this->page = new MetricsConfigurationListingPage($this);
        $object = $this->page->getEntry($this->vmName);
        $this->page->selectMoreAction($object, 'Duplicate');
    }

    /**
     * @Then all properties are copied except the name
     */
    public function allPropertiesAreCopiedExceptTheName()
    {
        $objects = $this->page->getEntries();
        if (key_exists($this->duplicatedVmName, $objects)) {
            if ($objects[$this->duplicatedVmName]['function'] != $objects[$this->vmName]['function']
                || $objects[$this->duplicatedVmName]['def_type'] != $objects[$this->vmName]['def_type']) {
                throw new \Exception('Some properties of ' . $this->duplicatedVmName . ' virtual Metric have not '
                    . 'been duplicated');
            }
        } else {
            throw new \Exception($this->vmName . ' virtual Metric has not been duplicated');
        }
    }

    /**
     * @When I delete a virtual metric
     */
    public function iDeleteAVirtualMetric()
    {
        $this->page = new MetricsConfigurationListingPage($this);
        $object = $this->page->getEntry($this->vmName);
        $this->page->selectMoreAction($object, 'Delete');
    }

    /**
     * @Then the virtual metric disappears from the Virtual metrics list
     */
    public function theVirtualMetricDisappearsFromTheVirtualMetricsList()
    {
        $objects = $this->page->getEntries();
        if (key_exists($this->vmName, $objects)) {
            throw new \Exception($this->vmName . ' virtual Metric is still existing');
        }
    }
}
