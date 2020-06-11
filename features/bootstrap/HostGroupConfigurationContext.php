<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\HostGroupConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;

class HostGroupConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $host1 = array(
        'name' => 'host1Name',
        'alias' => 'host1Alias',
        'address' => 'host1@localhost'
    );

    protected $host2 = array(
        'name' => 'host2Name',
        'alias' => 'host2Alias',
        'address' => 'host2@localhost'
    );

    protected $initialProperties = array(
        'name' => 'hostGroupName',
        'alias' => 'hostGroupAlias',
        'hosts' => 'host2Name',
        'notes' => 'hostGroupNotes',
        'notes_url' => 'hostGroupNotesUrl',
        'action_url' => 'hostGroupActionUrl',
        'icon' => '',
        'map_icon' => '       centreon (png)',
        'geo_coordinates' => '2.3522219,48.856614',
        'rrd_retention' => 80,
        'comments' => 'hostGroupComments',
        'enabled' => 1
    );

    protected $duplicatedProperties = array(
        'name' => 'hostGroupName_1',
        'alias' => 'hostGroupAlias',
        'hosts' => 'host2Name',
        'notes' => 'hostGroupNotes',
        'notes_url' => 'hostGroupNotesUrl',
        'action_url' => 'hostGroupActionUrl',
        'icon' => '',
        'map_icon' => '       centreon (png)',
        'geo_coordinates' => '2.3522219,48.856614',
        'rrd_retention' => 80,
        'comments' => 'hostGroupComments',
        'enabled' => 1
    );

    protected $updatedProperties = array(
        'name' => 'hostGroupNameChanged',
        'alias' => 'hostGroupAliasChanged',
        'hosts' => 'host1Name',
        'notes' => 'hostGroupNotesChanged',
        'notes_url' => 'hostGroupNotesUrlchanged',
        'action_url' => 'hostGroupActionUrlChanged',
        'icon' => '       centreon (png)',
        'map_icon' => '',
        'geo_coordinates' => '2.3522219,48.856614',
        'rrd_retention' => 45,
        'comments' => 'hostGroupCommentsChanged',
        'enabled' => 0
    );

    /**
     * @Given an host group is configured
     */
    public function anHostGroupIsConfigured()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setproperties($this->host1);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host2);
        $this->currentPage->save();
        $this->currentPage = new HostGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a host group
     */
    public function iChangeThePropertiesOfAHostGroup()
    {
        $this->currentPage = new HostGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['name']);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then its properties are updated
     */
    public function itsPropertiesAreUpdated()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new HostGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedProperties as $key => $value) {
                        if ($value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                    }
                    return count($this->tableau) == 0;
                },
                "Some properties are not being updated : ",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some properties are not being updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @When I duplicate a host group
     */
    public function iDuplicateAHostGroup()
    {
        $this->currentPage = new HostGroupConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then a new host is created with identical properties
     */
    public function aNewHostIsCreatedWithIdenticalProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new HostGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->duplicatedProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->duplicatedProperties as $key => $value) {
                        if ($value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                    }
                    return count($this->tableau) == 0;
                },
                "Some properties are not being updated : ",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some properties are not being updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @When I delete the host group
     */
    public function iDeleteTheHostGroup()
    {
        $this->currentPage = new HostGroupConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the host group is not visible anymore
     */
    public function theHostGroupIsNotVisibleAnymore()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new HostGroupConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['name'] != $this->initialProperties['name'];
                }
                return $bool;
            },
            "The host group is not being deleted.",
            5
        );
    }
}
