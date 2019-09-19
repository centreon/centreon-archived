<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ServiceDependencyConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceDependencyConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;

class ServiceDependencyConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $host = array(
        'name' => 'hostName',
        'alias' => 'hostAlias',
        'address' => 'host@localhost'
    );

    protected $initialProperties = array(
        'name' => 'serviceDependencyName',
        'description' => 'serviceDependencyDescription',
        'parent_relationship' => 0,
        'execution_fails_on_none' => 1,
        'execution_fails_on_ok' => 0,
        'execution_fails_on_warning' => 0,
        'execution_fails_on_unknown' => 0,
        'execution_fails_on_critical' => 0,
        'execution_fails_on_pending' => 0,
        'notification_fails_on_ok' => 1,
        'notification_fails_on_warning' => 1,
        'notification_fails_on_unknown' => 1,
        'notification_fails_on_critical' => 1,
        'notification_fails_on_pending' => 1,
        'notification_fails_on_none' => 0,
        'services' => 'Centreon-Server - Load',
        'dependent_services' => 'Centreon-Server - Memory',
        'dependent_hosts' => 'Centreon-Server',
        'comment' => 'serviceDependingComment'
    );

    protected $updatedProperties = array(
        'name' => 'serviceDependentNameChanged',
        'description' => 'serviceDependentDescriptionChanged',
        'parent_relationship' => 1,
        'execution_fails_on_ok' => 1,
        'execution_fails_on_warning' => 1,
        'execution_fails_on_unknown' => 1,
        'execution_fails_on_critical' => 1,
        'execution_fails_on_pending' => 1,
        'execution_fails_on_none' => 0,
        'notification_fails_on_none' => 1,
        'notification_fails_on_ok' => 0,
        'notification_fails_on_warning' => 0,
        'notification_fails_on_unknown' => 0,
        'notification_fails_on_critical' => 0,
        'notification_fails_on_pending' => 0,
        'services' => 'Centreon-Server - Ping',
        'dependent_services' => 'Centreon-Server - Disk-/home',
        'dependent_hosts' => 'hostName',
        'comment' => 'serviceDependingCommentChanged'
    );

    /**
     * @Given a service dependency is configured
     */
    public function aServiceDependencyIsConfigured()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host);
        $this->currentPage->save();
        $this->currentPage = new ServiceDependencyConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a service dependency
     */
    public function iChangeThePropertiesOfAServiceDependency()
    {
        $this->currentPage = new ServiceDependencyConfigurationListingPage($this);
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
                    $this->currentPage = new ServiceDependencyConfigurationListingPage($this);
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
     * @When I duplicate a service dependency
     */
    public function iDuplicateAServiceDependency()
    {
        $this->currentPage = new ServiceDependencyConfigurationListingPage($this);
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
                    $this->currentPage = new ServiceDependencyConfigurationListingPage($this);
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
     * @When I delete a service dependency
     */
    public function iDeleteAServiceDependency()
    {
        $this->currentPage = new ServiceDependencyConfigurationListingPage($this);
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
                $this->currentPage = new ServiceDependencyConfigurationListingPage($this);
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
