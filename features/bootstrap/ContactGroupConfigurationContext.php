<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactGroupsConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactGroupConfigurationListingPage;

class ContactGroupConfigurationContext extends CentreonContext
{
    protected $currentPage;
    protected $contactGroupName;
    protected $contactGroupAlias;
    protected $contactGroupContact;
    protected $contactGroupACL;
    protected $contactGroupComment;
    protected $changedName;
    protected $changedAlias;

    public function __construct()
    {
        parent::__construct();
        $this->contactGroupName = 'contactGroupName';
        $this->contactGroupAlias = 'contactGroupAlias';
        $this->contactGroupContact = 'Guest';
        $this->contactGroupACL = 'ALL';
        $this->contactGroupComment = 'contactGroupComment';
        $this->changedName = 'contactGroupNameChanged';
        $this->changedAlias = 'contactGroupAliasChanged';
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
            'status' => 0
        ));
        $this->currentPage->save();
    }

    /**
     * @When I configure the name of a contact group
     */
    public function iConfigureTheNameOfAContactGroup()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->contactGroupName);
        $this->currentPage->setProperties(array(
            'name' => $this->changedName
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the name has changed on the contact groups page
     */
    public function theNameHasChangedOnTheContactGroupsPage()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedName);
    }

    /**
     * @When I configure the alias of a contact group
     */
    public function iConfigureTheAliasOnAContactGroup()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->contactGroupName);
        $this->currentPage->setProperties(array(
            'alias' => $this->changedAlias
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the alias has changed on the contact groups page
     */
    public function theAliasHasChangedOnTheContactGroupsPage()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->contactGroupName);
        $object = $this->currentPage->getProperties();
        if ($object['alias'] != $this->changedAlias){
            throw new \Exception("The alias has not changed.");
        }
    }

    /**
     * @When I configure the status of a contact group
     */
    public function iConfigureTheStatusOfAContactGroup()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->contactGroupName);
        $this->currentPage->setProperties(array(
            'status' => 1
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the status has changed on the contact groups page
     */
    public function theStatusHasChangedOnTheContactGroupsPage()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->contactGroupName);
        $object = $this->currentPage->getProperties();
        if ($object['status'] != 1){
            throw new \Exception("The status has not changed.");
        }
    }

    /**
     * @When I configure the comment of a contact group
     */
    public function iConfigureTheCommentOfAContactGroup()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->contactGroupName);
        $this->currentPage->setProperties(array(
            'comments' => $this->contactGroupComment
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the comment has changed on the contact groups page
     */
    public function theCommentHasChangedOnTheContactGroupsPage()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->contactGroupName);
        $object = $this->currentPage->getProperties();
        if ($object['comments'] != $this->contactGroupComment){
            throw new \Exception("The comment has not changed.");
        }
    }
}
