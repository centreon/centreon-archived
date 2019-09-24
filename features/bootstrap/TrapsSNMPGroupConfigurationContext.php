<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\SnmpTrapGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\SnmpTrapGroupConfigurationListingPage;

class TrapsSNMPGroupConfigurationContext extends CentreonContext
{
    protected $currentPage;
    protected $initialProperties = array(
        'name' => 'trapGroupName',
        'traps' => array(
            '3com - secureViolation2',
            'Dell - adRebuildFailed'
        )
    );
    protected $updatedProperties = array(
        'name' => 'trapGroupNameChanged',
        'traps' => array(
            'Generic - coldStart',
            'HP - snTrapChasFanFailed'
        )
    );

    /**
     * @Given a trap group is configured
     */
    public function aTrapGroupIsConfigured()
    {
        $this->currentPage = new SnmpTrapGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a trap group
     */
    public function iChangeThePropertiesOfATrapGroup()
    {
        $this->currentPage = new SnmpTrapGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['name']);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the properties are updated
     */
    public function thePropertiesAreUpdated()
    {
        // No need to visit listing page, already loaded.
        $this->currentPage = new SnmpTrapGroupConfigurationListingPage($this, false);
        $this->currentPage = $this->currentPage->inspect($this->updatedProperties['name']);
        $object = $this->currentPage->getProperties();
        foreach ($this->updatedProperties as $key => $value) {
            $expected = is_array($value) ? implode(' ', $value) : $value;
            if ($expected != $object[$key]) {
                throw new \Exception(
                    'Property ' . $key . ' was not updated: got ' .
                    $object[$key] . ', expected ' . $expected
                );
            }
        }
    }

    /**
     * @When I duplicate a trap group
     */
    public function iDuplicateATrapGroup()
    {
        $this->currentPage = new SnmpTrapGroupConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new object has the same properties
     */
    public function theNewObjectHasTheSameProperties()
    {
        $this->updatedProperties = $this->initialProperties;
        $this->updatedProperties['name'] .= '_1';
        $this->thePropertiesAreUpdated();
    }

    /**
     * @When I delete a trap group
     */
    public function iDeleteATrapGroup()
    {
        $this->currentPage = new SnmpTrapGroupConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted object is not displayed in the list
     */
    public function theDeletedObjectIsNotDisplayedInTheList()
    {
        $this->currentPage = new SnmpTrapGroupConfigurationListingPage($this, false);
        $this->spin(
            function ($context) {
                $object = $context->currentPage->getEntries();
                return !array_key_exists($context->initialProperties['name'], $object);
            },
            'Service ' . $this->initialProperties['name'] . ' is not being deleted.'
        );
    }
}
