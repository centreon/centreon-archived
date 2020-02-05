<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\MetaServiceDependencyConfigurationPage;
use Centreon\Test\Behat\Configuration\MetaServiceDependencyConfigurationListingPage;
use Centreon\Test\Behat\Configuration\MetaServiceConfigurationPage;

class MetaServiceDependencyConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $metaService1 = array(
        'name' => 'metaService1Name',
        'max_check_attempts' => 12
    );

    protected $metaService2 = array(
        'name' => 'metaService2Name',
        'max_check_attempts' => 3
    );

    protected $metaService3 = array(
        'name' => 'metaService3Name',
        'max_check_attempts' => 40
    );

    protected $metaService4 = array(
        'name' => 'metaService4Name',
        'max_check_attempts' => 9
    );

    protected $metaService5 = array(
        'name' => 'metaService5Name',
        'max_check_attempts' => 21
    );

    protected $metaService6 = array(
        'name' => 'metaService6Name',
        'max_check_attempts' => 4
    );

    protected $initialProperties = array(
        'name' => 'metaServiceDependencyName',
        'description' => 'metaServiceDependencyDescription',
        'parent_relationship' => 0,
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
        'meta_services' => array(
            'metaService1Name',
            'metaService2Name'
        ),
        'dependent_meta_services' => 'metaService3Name',
        'comment' => 'metaServiceDependencyComment'
    );

    protected $updatedProperties = array(
        'name' => 'metaServiceDependencyNameChanged',
        'description' => 'metaServiceDependencyDescriptionChanged',
        'parent_relationship' => 1,
        'execution_fails_on_pending' => 0,
        'execution_fails_on_none' => 1,
        'execution_fails_on_ok' => 0,
        'execution_fails_on_warning' => 0,
        'execution_fails_on_unknown' => 0,
        'execution_fails_on_critical' => 0,
        'notification_fails_on_ok' => 1,
        'notification_fails_on_warning' => 1,
        'notification_fails_on_unknown' => 1,
        'notification_fails_on_critical' => 1,
        'notification_fails_on_pending' => 1,
        'notification_fails_on_none' => 0,
        'meta_services' => 'metaService4Name',
        'dependent_meta_services' => array(
            'metaService5Name',
            'metaService6Name'
        ),
        'comment' => 'metaServiceDependencyCommentChanged'
    );

    /**
     * @Given a meta service dependency
     */
    public function aMetaServiceDependency()
    {
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService1);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService2);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService3);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService4);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService5);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService6);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceDependencyConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a meta service dependency
     */
    public function iChangeThePropertiesOfAMetaServiceDependency()
    {
        $this->currentPage = new MetaServiceDependencyConfigurationListingPage($this);
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
                    $this->currentPage = new MetaServiceDependencyConfigurationListingPage($this);
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
     * @When I duplicate a meta service dependency
     */
    public function iDuplicateAMetaServiceDependency()
    {
        $this->currentPage = new MetaServiceDependencyConfigurationListingPage($this);
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
                    $this->currentPage = new MetaServiceDependencyConfigurationListingPage($this);
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
     * @When I delete a meta service dependency
     */
    public function iDeleteAMetaServiceDependency()
    {
        $this->currentPage = new MetaServiceDependencyConfigurationListingPage($this);
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
                $this->currentPage = new MetaServiceDependencyConfigurationListingPage($this);
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
