<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;

class HostConfigurationContext extends CentreonContext
{
    protected $currentPage;
    protected $hostName;
    protected $changedName;
    protected $hostAlias;
    protected $changedAlias;
    protected $hostAddress;
    protected $changedAddress;

    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'hostName';
        $this->changedName = 'hostNameChanged';
        $this->hostAlias = 'hostAlias';
        $this->changedAlias = 'hostAliasChanged';
        $this->hostAddress = 'local';
        $this->changedAddress = '10.30.2.105';
    }

    /**
     * @Given an host is configured
     */
    public function anHostIsConfigured()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => $this->hostName,
            'alias' => $this->hostAlias,
            'address' => $this->hostAddress
        ));
        $this->currentPage->save();
    }

    /**
     * @When I configure the name of an host
     */
    public function iConfigureTheIPAddressOfAnHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->hostName);
        $this->currentPage->setProperties(array(
            'name' => $this->changedName
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the name has changed on the Host page
     */
    public function theIPAddressHasChangedOnTheHostPage()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        try {
            $this->currentPage->getEntry($this->changedName);
        } catch (\Exception $e) {
            throw new \Exception('The name has not changed:    ' . $e->getMessage());
        }
    }

    /**
     * @When I configure the alias of an host
     */
    public function iConfigureTheAliasOfAnHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->hostName);
        $this->currentPage->setProperties(array(
            'alias' => $this->changedAlias
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the alias has changed on the Host page
     */
    public function theAliasHasChangedOnTheHostPage()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->hostName);
        $object = $this->currentPage->getProperties();
        if (!$object['alias'] == $this->changedAlias) {
            throw new \Exception("The alias was not changed.");
        }
    }

    /**
     * @When I configure the address of an host
     */
    public function iConfigureTheAddressOfAnHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->hostName);
        $this->currentPage->setProperties(array(
            'address' => $this->changedAlias
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the address has changed on the Host page
     */
    public function theAddressHasChangedOnTheHostPage()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->hostName);
        $object = $this->currentPage->getProperties();
        if (!$object['address'] == $this->changedAddress) {
            throw new \Exception("The address was not changed.");
        }
    }
}
