<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\External\ListingPage;

class HostDuplicationCheckContext extends CentreonContext
{
    private $currentPage;

    private $initialProperties = array(
        'name' => 'hostName',
        'alias' => 'hostAlias',
        'address' => 'host@localhost',
        'enabled' => 1
    );

    private $updatedProperties = array(
        'name' => 'hostName_1',
        'alias' => 'hostAlias',
        'address' => 'host@localhost',
        'enabled' => 1
    );

    /**
     * @Given a host is created
     */
    public function aHostIsCreated()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I duplicate a host
     */
    public function whenIDuplicateAHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]')->check();
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the host properties are updated
     */
    public function theHostPropertiesAreUpdated()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->updatedProperties['name']);
        $object = $this->currentPage->getProperties();
        $tableau = array();
        foreach ($this->updatedProperties as $key => $value) {
            if ($value != $object[$key]) {
                $tableau[] = $key;
            }
        }
        if (count($tableau) > 0) {
            throw new \Exception("Some properties are not being updated : " . implode(', ', $tableau));
        }
    }
}
