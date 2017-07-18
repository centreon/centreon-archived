<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Monitoring\MetricsConfigurationListingPage;
use Centreon\Test\Behat\Monitoring\MetricsConfigurationPage;

class VirtualMetricHandleContext extends CentreonContext 
{
    protected $page;
   
    
    /**
     * @When I add a virtual metric
     */
    public function iAddAVirtualMetric()
    {
        $this->page = new MetricsConfigurationListingPage($this);
        throw new \Exception('...');
    
    }

    /**
     * @Then all properties are saved
     */
    public function allPropertiesAreSaved()
    {
        throw new PendingException();
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
