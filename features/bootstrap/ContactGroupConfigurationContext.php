<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactGroupsConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactGroupConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationPage;

class ContactGroupConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $initialProperties = array(
        'name' => 'contactGroupName',
        'alias' => 'contactGroupAlias',
        'status' => 0,
        'comments' => 'contactGroupComment'
    );

    protected $updatedProperties = array(
        'name' => 'contactGroupNameChanged',
        'alias' => 'contactGroupAliasChanged',
        'contacts' => 'contactName',
        'acl' => 'aclGroupName',
        'status' => 1,
        'comments' => 'contactGroupCommentChanged'
    );

    protected $aclGroup = array(
        'group_name' => 'aclGroupName',
        'group_alias' => 'aclGroupAlias'
    );

    protected $contact = array(
        'name' => 'contactName',
        'alias' => 'contactAlias',
        'email' => 'contact@localhost',
        'password' => 'pwd',
        'password2' => 'pwd',
        'admin' => 0
    );

    /**
     * @Given a contact group is configured
     */
    public function aContactGroupIsConfigured()
    {
        $this->currentPage = new ContactGroupsConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I update the contact group properties
     */
    public function iConfigureTheContactGroupProperties()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties($this->contact);
        $this->currentPage->save();
        $this->currentPage = new ACLGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->aclGroup);
        $this->currentPage->save();
        $this->currentPage = new ContactGroupConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['name']);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the contact group properties are updated
     */
    public function theContactGroupPropertiesAreUpdated()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ContactGroupConfigurationListingPage($this);
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
            throw new \Exception("Some properties are not being updated : " . implode(',', $this->tableau));
        }
    }
}
