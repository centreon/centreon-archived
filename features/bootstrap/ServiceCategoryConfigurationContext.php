<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ServiceCategoryConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceCategoryConfigurationListingPage;

class ServiceCategoryConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $initialProperties = array(
        'name' => 'serviceCategoryName',
        'description' => 'serviceCategoryDescription',
        'template' => 'generic-service',
        'severity' => 0,
        'status' => 1
    );

    protected $duplicatedProperties = array(
        'name' => 'serviceCategoryName_1',
        'description' => 'serviceCategoryDescription',
        'template' => 'generic-service',
        'severity' => 0,
        'status' => 1
    );

    protected $updatedProperties = array(
        'name' => 'serviceCategoryNameChanged',
        'description' => 'serviceCategoryDescriptionChanged',
        'template' => 'Ping-WAN',
        'severity' => 1,
        'level' => '3',
        'icon' => '       centreon (png)',
        'status' => 0
    );

    /**
     * @Given a service category is configured
     */
    public function aServiceCategoryIsConfigured()
    {
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a service category
     */
    public function iChangeThePropertiesOfAServiceCategory()
    {
        $this->currentPage = new ServiceCategoryConfigurationListingPage($this);
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
                    $this->currentPage = new ServiceCategoryConfigurationListingPage($this);
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
     * @When I duplicate a service category
     */
    public function iDuplicateAServiceCategory()
    {
        $this->currentPage = new ServiceCategoryConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new service category has the same properties
     */
    public function theNewServiceCategoryHasTheSameProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ServiceCategoryConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->duplicatedProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->duplicatedProperties as $key => $value) {
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
     * @When I delete a service category
     */
    public function iDeleteAServiceCategory()
    {
        $this->currentPage = new ServiceCategoryConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted service category is not displayed in the list
     */
    public function theDeletedServiceCategoryIsNotDisplayedInTheList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ServiceCategoryConfigurationListingPage($this);
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
