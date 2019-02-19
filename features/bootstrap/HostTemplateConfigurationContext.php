<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostTemplateConfigurationPage;
use Centreon\Test\Behat\Configuration\HostTemplateConfigurationListingPage;

class HostTemplateConfigurationContext extends CentreonContext
{
    protected $page;
    protected $hostName = 'AcceptanceHost';
    protected $parentHostTemplate = 'parent';

    /**
     * @Given an host inheriting from an host template
     */
    public function anHostInheritingFromAnHostTemplate()
    {


        $this->page = new HostConfigurationPage($this);
        $this->page->setProperties(array(
            'name' => $this->hostName,
            'alias' => $this->hostName,
            'address' => 'localhost',
            //'templates' => array('generic-host')
        ));
        $this->page->save();
    }

    /**
     * @Given an host template inheriting from an host template
     */
    public function anHostTemplateInheritingFromAnHostTemplate()
    {
        $this->page = new HostTemplateConfigurationPage($this);
        $this->page->setProperties(array(
            'name' => $this->hostName,
            'alias' => $this->hostName,
            //'templates' => array('generic-host')
        ));
        $this->page->save();
    }


    /**
     * @When I configure the host
     */
    public function iConfigureTheHost()
    {
        $this->page = new HostConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->hostName);
    }

    /**
     * @When I configure the host template
     */
    public function iConfigureTheHostTemplate()
    {
        $this->page = new HostTemplateConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->hostName);
    }

    /**
     * @Then I can configure directly its parent template
     */
    public function iCanConfigureDirectlyItsParentTemplate()
    {
        // Click on template edition select2
        $this->assertFind('css', '#tpSelect ~ .select2-container')->click();
    }
}
