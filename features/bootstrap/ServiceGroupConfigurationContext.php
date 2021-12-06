<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ServiceGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceGroupConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostGroupServiceConfigurationPage;

class ServiceGroupConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $hostGroupService1 = array(
        'description' => 'hostGroupServiceDescription1',
        'hosts' => 'Windows-Servers',
        'check_command' => 'check_http'
    );

    protected $hostGroupService2 = array(
        'description' => 'hostGroupServiceDescription2',
        'hosts' => 'Firewall',
        'check_command' => 'check_https'
    );

    protected $initialProperties = array(
        'name' => 'serviceGroupName',
        'description' => 'serviceGroupDescription',
        'hosts' => 'Centreon-Server - Memory',
        'host_groups' => 'Windows-Servers - hostGroupServiceDescription1',
        'service_templates' => 'generic-host - Ping-LAN',
        'geo_coordinates' => '2.3522219,48.856614',
        'enabled' => 1,
        'comments' => 'serviceGroupComments'
    );

    protected $updatedProperties = array(
        'name' => 'serviceGroupNameChanged',
        'description' => 'serviceGroupDescriptionChanged',
        'hosts' => 'Centreon-Server - Load',
        'host_groups' => 'Firewall - hostGroupServiceDescription2',
        'service_templates' => 'Servers-Linux - SNMP-Linux-Swap',
        'geo_coordinates' => '2.3522219,48.856614',
        'enabled' => 1,
        'comments' => 'serviceGroupCommentsChanged'
    );

    /**
     * @Given a service group is configured
     */
    public function aServiceGroupIsConfigured()
    {
        $this->currentPage = new HostGroupServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->hostGroupService1);
        $this->currentPage->save();
        $this->currentPage = new HostGroupServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->hostGroupService2);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a service group
     */
    public function iChangeThePropertiesOfAServiceGroup()
    {
        $this->currentPage = new ServiceGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['name']);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the properties are updated
     */
    public function thePropertiesAreUpdated()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ServiceGroupConfigurationListingPage($this);
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
     * @When I duplicate a service group
     */
    public function iDuplicateAServiceGroup()
    {
        $this->currentPage = new ServiceGroupConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new service group has the same properties
     */
    public function theNewServiceGroupHasTheSameProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ServiceGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['name'] . '_1');
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
                        if ($key != 'name' && $value != $object[$key]) {
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
     * @When I delete a service group
     */
    public function iDeleteAServiceGroup()
    {
        $this->currentPage = new ServiceGroupConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted service group is not displayed in the service group list
     */
    public function theDeletedServiceGroupIsNotDisplayedInTheServiceGroupList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ServiceGroupConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['name'] != $this->initialProperties['name'];
                }
                return $bool;
            },
            "The host category is not being deleted.",
            5
        );
    }
}
