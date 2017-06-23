<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\External\ListingPage;

class HostDuplicationCheckContext extends CentreonContext
{
    private $hostName;
    private $hostAlias;
    private $hostAddress;
    private $duplicatedHost;

    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'hostName';
        $this->hostAlias = 'hostAlias';
        $this->hostAddress = 'host@localhost';
        $this->duplicatedHost = $this->hostName . '_1';
    }

    /**
     * @Given a host is created
     */
    public function aHostIsCreated()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => $this->hostName,
            'alias' => $this->hostAlias,
            'address' => $this->hostAddress,
            'status' => 1
        ));
        $this->currentPage->save();
    }

    /**
     * @When I duplicate a host
     */
    public function whenIDuplicateAHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->hostName);
        $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]')->check();
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the host was correctly duplicated
     */
    public function theirNameAreTheSame()
    {
        $this->spin(
            function($context){
                $this->currentPage = new HostConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->duplicatedHost);
                return $object['name'] == $this->duplicatedHost;
            },
            "The host was not duplicated or the new name is not correct.",
            30
        );
    }

    /**
     * @Then their alias are the same
     */
    public function theirAliasAreTheSame()
    {
        $this->spin(
            function($context){
                $this->currentPage = new HostConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->duplicatedHost);
                return $object['alias'] == $this->hostAlias;
            },
            "The alias has changed during the duplication.",
            30
        );
    }

    /**
     * @Then their address are the same
     */
    public function theirEmailsAreTheSame()
    {
        $this->spin(
            function($context){
                $this->currentPage = new HostConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->duplicatedHost);
                return $object['ip_address'] == $this->hostAddress;
            },
            "The address has changed during the duplication.",
            30
        );
    }

    /**
     * @Then their status are the same
     */
    public function theirStatusAreTheSame()
    {
        $this->spin(
            function($context){
                $this->currentPage = new HostConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->duplicatedHost);
                return $object['status'] == 'ENABLED';
            },
            "The status has changed during the duplication.",
            30
        );
    }
}
