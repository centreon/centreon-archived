<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostGroupDependencyConfigurationPage;
use Centreon\Test\Behat\Configuration\HostGroupDependencyConfigurationListingPage;

class HostGroupDependencyConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $initialProperties = array(
        'name' => 'hostGroupDependencyName',
        'description' => 'hostGroupDependencyAlias',
        'parent_relationship' => 0,
        'execution_fails_on_none' => 1,
        'execution_fails_on_ok' => 0,
        'execution_fails_on_down' => 0,
        'execution_fails_on_unreachable' => 0,
        'execution_fails_on_pending' => 0,
        'notification_fails_on_ok' => 1,
        'notification_fails_on_down' => 1,
        'notification_fails_on_unreachable' => 1,
        'notification_fails_on_pending' => 1,
        'notification_fails_on_none' => 0,
        'host_groups' => 'Firewall',
        'dependent_host_groups' => 'Windows-Servers',
        'comment' => 'hostGroupDependencyComment'
    );

    protected $updatedProperties = array(
        'name' => 'hostGroupDependencyNameChanged',
        'description' => 'hostGroupDependencyDescriptionChanged',
        'parent_relationship' => 1,
        'execution_fails_on_ok' => 1,
        'execution_fails_on_down' => 1,
        'execution_fails_on_unreachable' => 1,
        'execution_fails_on_pending' => 1,
        'execution_fails_on_none' => 0,
        'notification_fails_on_none' => 1,
        'notification_fails_on_ok' => 0,
        'notification_fails_on_down' => 0,
        'notification_fails_on_unreachable' => 0,
        'notification_fails_on_pending' => 0,
        'host_groups' => 'Unix-Servers',
        'dependent_host_groups' => 'Routers',
        'comment' => 'hostGroupDependencyCommentChanged'
    );

    /**
     * @Given a host group dependency is configured
     */
    public function aHostGroupDependencyIsConfigured()
    {
        $this->currentPage = new HostGroupDependencyConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a host group dependency
     */
    public function iChangeThePropertiesOfAHostGroupDependency()
    {
        $this->currentPage = new HostGroupDependencyConfigurationListingPage($this);
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
                    $this->currentPage = new HostGroupDependencyConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedProperties as $key => $value) {
                        if ($value != $object[$key]) {
                            if (is_array($value)) {
                                $value = implode(' ', $value);
                            }
                            if ($value != $object[$key]) {
                                $this->tableau[] = $key;
                            }
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
     * @When I duplicate a host group dependency
     */
    public function iDuplicateAHostGroupDependency()
    {
        $this->currentPage = new HostGroupDependencyConfigurationListingPage($this);
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
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new HostGroupDependencyConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['name'] . '_1');
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
                        if ($key != 'name' && $value != $object[$key]) {
                            if (is_array($value)) {
                                $value = implode(' ', $value);
                            }
                            if ($value != $object[$key]) {
                                $this->tableau[] = $key;
                            }
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
     * @When I delete a host group dependency
     */
    public function iDeleteAHostGroupDependency()
    {
        $this->currentPage = new HostGroupDependencyConfigurationListingPage($this);
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
        $this->spin(
            function ($context) {
                $this->currentPage = new HostGroupDependencyConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['name'] != $this->initialProperties['name'];
                }
                return $bool;
            },
            "The service is not being deleted.",
            5
        );
    }
}
