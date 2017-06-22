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
    protected $contactGroupComment;
    protected $changedName;
    protected $changedAlias;
    protected $changedComment;

    public function __construct()
    {
        parent::__construct();
        $this->contactGroupName = 'contactGroupName';
        $this->contactGroupAlias = 'contactGroupAlias';
        $this->contactGroupContact = 'Guest';
        $this->contactGroupComment = 'contactGroupComment';
        $this->changedName = 'contactGroupNameChanged';
        $this->changedAlias = 'contactGroupAliasChanged';
        $this->changedComment = 'contactGroupCommentChanged';
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
            'status' => 0,
            'comments' => $this->contactGroupComment
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
        $this->spin(
            function($context){
                $this->currentPage = new ContactGroupConfigurationListingPage($this);
                return $this->currentPage->inspect($this->changedName);
            },
            "The name has not changed.",
            30
        );
    }

    /**
     * @When I configure the alias of a contact group
     */
    public function iConfigureTheAliasOnAContactGroup()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedName);
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
        $this->spin(
            function($context){
                $this->currentPage = new ContactGroupConfigurationListingPage($this);
                $this->currentPage = $this->currentPage->inspect($this->changedName);
                $object = $this->currentPage->getProperties();
                return $object['alias'] == $this->changedAlias;
            },
            "The alias has not changed.",
            30
        );
    }

    /**
     * @When I configure the status of a contact group
     */
    public function iConfigureTheStatusOfAContactGroup()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedName);
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
        $this->spin(
            function($context){
                $this->currentPage = new ContactGroupConfigurationListingPage($this);
                $this->currentPage = $this->currentPage->inspect($this->changedName);
                $object = $this->currentPage->getProperties();
                return $object['status'] == 1;
            },
            "The status has not changed.",
            30
        );
    }

    /**
     * @When I configure the comment of a contact group
     */
    public function iConfigureTheCommentOfAContactGroup()
    {
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedName);
        $this->currentPage->setProperties(array(
            'comments' => $this->changedComment
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the comment has changed on the contact groups page
     */
    public function theCommentHasChangedOnTheContactGroupsPage()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactGroupConfigurationListingPage($this);
                $this->currentPage = $this->currentPage->inspect($this->changedName);
                $object = $this->currentPage->getProperties();
                return $object['comments'] == $this->changedComment;
            },
            "The comment has not changed.",
            30
        );
    }
}
