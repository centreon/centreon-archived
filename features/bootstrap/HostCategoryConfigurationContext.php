<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostCategoryConfigurationPage;
use Centreon\Test\Behat\Configuration\HostCategoryConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostTemplateConfigurationPage;

class HostCategoryConfigurationContext extends CentreonContext
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

    protected $hostTemplate1 = array(
        'name' => 'hostTemplate1Name',
        'alias' => 'hostTemplate2Alias'
    );

    protected $hostTemplate2 = array(
        'name' => 'hostTemplate2Name',
        'alias' => 'hostTemplate2Alias'
    );

    protected $initialProperties = array(
        'name' => 'hostCategoryName',
        'alias' => 'hostCategoryAlias',
        'hosts' => 'host1Name',
        'host_templates' => 'hostTemplate1Name',
        'severity' => 0,
        'enabled' => 1,
        'comments' => 'hostCategoryComment'
    );

    protected $duplicatedProperties = array(
        'name' => 'hostCategoryName_1',
        'alias' => 'hostCategoryAlias',
        'hosts' => 'host1Name',
        'host_templates' => 'hostTemplate1Name',
        'severity' => 0,
        'enabled' => 1,
        'comments' => 'hostCategoryComment'
    );

    protected $updatedProperties = array(
        'name' => 'hostCategoryNameChanged',
        'alias' => 'hostCategoryAliasChanged',
        'hosts' => 'host2Name',
        'host_templates' => 'hostTemplate2Name',
        'severity' => 1,
        'severity_level' => 3,
        'severity_icon' => '       centreon (png)',
        'enabled' => 0,
        'comments' => 'hostCategoryCommentChanged'
    );

    /**
     * @Given a host category is configured
     */
    public function aHostCategoryIsConfigured()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host1);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host2);
        $this->currentPage->save();
        $this->currentPage = new HostTemplateConfigurationPage($this);
        $this->currentPage->setProperties($this->hostTemplate1);
        $this->currentPage->save();
        $this->currentPage = new HostTemplateConfigurationPage($this);
        $this->currentPage->setProperties($this->hostTemplate2);
        $this->currentPage->save();
        $this->currentPage = new HostCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a host category
     */
    public function iChangeThePropertiesOfAHostCategory()
    {
        $this->currentPage = new HostCategoryConfigurationListingPage($this);
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
                    $this->currentPage = new HostCategoryConfigurationListingPage($this);
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
     * @When I duplicate a host category
     */
    public function iDuplicateAHostCategory()
    {
        $this->currentPage = new HostCategoryConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new host category has the same properties
     */
    public function theNewHostCategoryHasTheSameProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new HostCategoryConfigurationListingPage($this);
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
     * @When I delete a host category
     */
    public function iDeleteAHostCategory()
    {
        $this->currentPage = new HostCategoryConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted host is not displayed in the host list
     */
    public function theDeletedHostIsNotDisplayedInTheHostList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new HostCategoryConfigurationListingPage($this);
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
