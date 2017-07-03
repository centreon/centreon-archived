<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactGroupsConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactGroupConfigurationListingPage;
use Centreon\Test\Behat\External\ListingPage;

class ContactGroupCreationDuplicationContext extends CentreonContext
{
    protected $currentPage;
    protected $contactGroupName;
    protected $contactGroupAlias;

    public function __construct()
    {
        parent::__construct();
        $this->contactGroupName = 'contactGroupName';
        $this->contactGroupAlias = 'contactGroupAlias';
    }

    /**
     * @When I create a contact group
     */
    public function iCreateAContactGroup()
    {
        $this->aContactGroupIsConfigured();
    }

    /**
     * @Then the new record is displayed in the contact groups list
     */
    public function theNewRecordIsDisplayedInTheContactGroupList()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage->getEntry($this->contactGroupName);
    }

    /**
     * @Given a contact group is configured
     */
    public function aContactGroupIsConfigured()
    {
        $this->currentPage = new ContactGroupsConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => $this->contactGroupName,
            'alias' => $this->contactGroupAlias,
            'status' => 1
        ));
        $this->currentPage->save();
    }

    /**
     * @When I duplicate a contact group
     */
    public function iDuplicateAContactGroup()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->contactGroupName);
        $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]')->check();
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new contact group is displayed in the contact groups list
     */
    public function theNewContactGroupIsDisplayedInTheContactGroupList()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactGroupConfigurationListingPage($this);
                return $this->currentPage->getEntry($this->contactGroupName . '_1');
            },
            "The duplicated contact group was not found.",
            30
        );
    }

    /**
     * @When I delete a contact group
     */
    public function iDeleteAContactGroup()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->contactGroupName);
        $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]')->check();
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted contact group is not displayed in the contact groups list
     */
    public function theDeletedContactGroupIsNotDisplayedInTheUserList()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactGroupConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value){
                    $bool = $bool && $value['name'] != $this->contactGroupName;
                }
                return $bool;
            },
            "The contact group was not deleted.",
            30
        );
    }
}
