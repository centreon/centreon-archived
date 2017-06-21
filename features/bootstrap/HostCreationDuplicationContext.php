<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\External\ListingPage;

class HostCreationDuplicationContext extends CentreonContext
{
    private $hostName;
    private $hostAlias;
    private $hostAddress;

    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'hostName';
        $this->hostAlias = 'hostAlias';
        $this->hostAddress = 'local';
    }

    /**
     * @When I create a host
     */
    public function iCreateAHost()
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
     * @Then the new record is displayed in the hosts list
     */
    public function theNewRecordIsDisplayedInTheHostsList()
    {
        $this->spin(
            function($context){
	        $this->currentPage = new HostConfigurationListingPage($this);
                return $this->currentPage->getEntry($this->hostName);
            },
            "The new host is not displayed in the hosts list.",
            30
        );
    }

    /**
     * @Given a host is configured
     */
    public function aHostIsConfigured()
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
     * @When I duplicate a host
     */
    public function iDuplicateAHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->hostName);
        $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]')->check();
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new host is displayed in the hosts list
     */
    public function theNewHostIsDisplayedInTheHostsList()
    {
        $this->spin(
            function($context){
                $this->currentPage = new HostConfigurationListingPage($this);
                return $this->currentPage->getEntry($this->hostName . '_1');
            },
            "The duplicated host was not found.",
            30
        );
    }

    /**
     * @When I delete a host
     */
    public function iDeleteAHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->hostName);
        $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]')->check();
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted host is not displayed in the hosts list
     */
    public function theDeletedHostIsNotDisplayedInTheHostsList()
    {
        $this->spin(
            function($context){
                $this->currentPage = new HostConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach($object as $value){
                    $bool = $bool && $value['name'] != $this->hostName;
                }
                return $bool;
            },
            "The host was not deleted.",
            30
        );
    }
}
