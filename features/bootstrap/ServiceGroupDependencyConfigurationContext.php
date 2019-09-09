<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ServiceGroupDependencyConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceGroupDependencyConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ServiceGroupConfigurationPage;

class ServiceGroupDependencyConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $serviceGroup1 = array(
        'name' => 'serviceGroup1Name',
        'description' => 'serviceGroup1Description'
    );

    protected $serviceGroup2 = array(
        'name' => 'serviceGroup2Name',
        'description' => 'serviceGroup2Description'
    );

    protected $serviceGroup3 = array(
        'name' => 'serviceGroup3Name',
        'description' => 'serviceGroup3Description'
    );

    protected $serviceGroup4 = array(
        'name' => 'serviceGroup4Name',
        'description' => 'serviceGroup4Description'
    );

    protected $serviceGroup5 = array(
        'name' => 'serviceGroup5Name',
        'description' => 'serviceGroup5Description'
    );

    protected $serviceGroup6 = array(
        'name' => 'serviceGroup6Name',
        'description' => 'serviceGroup6Description'
    );

    protected $initialProperties = array(
        'name' => 'serviceGroupDependencyName',
        'description' => 'serviceGroupDependencyDescription',
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
        'service_groups' => 'serviceGroup1Name',
        'dependent_service_groups' => 'serviceGroup2Name',
        'comment' => 'serviceGroupDependencyComment'
    );

    protected $updatedProperties = array(
        'name' => 'serviceGroupDependencyNameChanged',
        'description' => 'serviceGroupDependencyDescriptionChanged',
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
        'service_groups' => array(
            'serviceGroup3Name',
            'serviceGroup4Name'
        ),
        'dependent_service_groups' => array(
            'serviceGroup5Name',
            'serviceGroup6Name'
        ),
        'comment' => 'serviceGroupDependencyCommentChanged'
    );

    /**
     * @Given a service group dependency
     */
    public function aServiceGroupDependency()
    {
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup1);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup2);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup3);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup4);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup5);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup6);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupDependencyConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a service group dependency
     */
    public function iChangeThePropertiesOfAServiceGroupDependency()
    {
        $this->currentPage = new ServiceGroupDependencyConfigurationListingPage($this);
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
                    $this->currentPage = new ServiceGroupDependencyConfigurationListingPage($this);
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
     * @When I duplicate a service group dependency
     */
    public function iDuplicateAServiceGroupDependency()
    {
        $this->currentPage = new ServiceGroupDependencyConfigurationListingPage($this);
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
                    $this->currentPage = new ServiceGroupDependencyConfigurationListingPage($this);
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
     * @When I delete a service group dependency
     */
    public function iDeleteAServiceGroupDependency()
    {
        $this->currentPage = new ServiceGroupDependencyConfigurationListingPage($this);
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
                $this->currentPage = new ServiceGroupDependencyConfigurationListingPage($this);
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
