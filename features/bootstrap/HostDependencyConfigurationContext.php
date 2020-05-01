<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostDependencyConfigurationPage;
use Centreon\Test\Behat\Configuration\HostDependencyConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;

class HostDependencyConfigurationContext extends CentreonContext
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

    protected $host3 = array(
        'name' => 'host3Name',
        'alias' => 'host3Alias',
        'address' => 'host3@localhost'
    );

    protected $initialProperties = array(
        'name' => 'hostDependencyName',
        'description' => 'hostDependencyDescription',
        'parent_relationship' => 0,
        'execution_fails_on_none' => 1,
        'execution_fails_on_up' => 0,
        'execution_fails_on_down' => 0,
        'execution_fails_on_unreachable' => 0,
        'execution_fails_on_pending' => 0,
        'notification_fails_on_ok' => 1,
        'notification_fails_on_down' => 1,
        'notification_fails_on_unreachable' => 1,
        'notification_fails_on_pending' => 1,
        'notification_fails_on_none' => 0,
        'hosts' => 'Centreon-Server',
        'dependent_hosts' => 'host1Name',
        'dependent_services' => 'Centreon-Server - Memory',
        'comment' => 'hostDependencyComment'
    );

    protected $updatedProperties = array(
        'name' => 'hostDependencyNameChanged',
        'description' => 'hostDependencyDescriptionChanged',
        'parent_relationship' => 1,
        'execution_fails_on_up' => 1,
        'execution_fails_on_down' => 1,
        'execution_fails_on_unreachable' => 1,
        'execution_fails_on_pending' => 1,
        'execution_fails_on_none' => 0,
        'notification_fails_on_none' => 1,
        'notification_fails_on_ok' => 0,
        'notification_fails_on_down' => 0,
        'notification_fails_on_unreachable' => 0,
        'notification_fails_on_pending' => 0,
        'hosts' => 'host2Name',
        'dependent_hosts' => 'host3Name',
        'dependent_services' => 'Centreon-Server - Load',
        'comment' => 'hostDependencyCommentChanged'
    );

    /**
     * @Given a host dependency is configured
     */
    public function aHostDependencyIsConfigured()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host1);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host2);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host3);
        $this->currentPage->save();
        $this->currentPage = new HostDependencyConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a host dependency
     */
    public function iChangeThePropertiesOfAHostDependency()
    {
        $this->currentPage = new HostDependencyConfigurationListingPage($this);
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
                    $this->currentPage = new HostDependencyConfigurationListingPage($this);
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
     * @When I duplicate a host dependency
     */
    public function iDuplicateAHostDependency()
    {
        $this->currentPage = new HostDependencyConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new host dependency has the same properties
     */
    public function theNewHostDependencyHasTheSameProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new HostDependencyConfigurationListingPage($this);
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
     * @When I delete a host dependency
     */
    public function iDeleteAHostDependency()
    {
        $this->currentPage = new HostDependencyConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted host dependency is not displayed in the list
     */
    public function theDeletedHostDependencyIsNotDisplayedInTheList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new HostDependencyConfigurationListingPage($this);
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
