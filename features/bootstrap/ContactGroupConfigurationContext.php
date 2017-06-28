<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactGroupsConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactGroupConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationPage;

class ContactGroupConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $initialProperties = (array(
        'name' => 'contactGroupName',
        'alias' => 'contactGroupAlias',
        'status' => 0,
        'comments' => 'contactGroupComment'
    ));

    protected $updatedProperties = (array(
        'name' => 'contactGroupNameChanged',
        'alias' => 'contactGroupAliasChanged',
        'contacts' => 'contactAlias',
        'acl' => 'ACLGroupName',
        'status' => 1,
        'comments' => 'contactGroupCommentChanged'
    ));

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
        $this->currentPage->setProperties(array(
            'name' => $this->updatedProperties['contacts'],
            'alias' => $this->updatedProperties['contacts'],
            'email' => "contact@localhost",
            'password' => 'pwd',
            'password2' => 'pwd',
            'admin' => 0
        ));
        $this->currentPage->save();
        $this->currentPage = new ACLGroupConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'group_name' => $this->updatedProperties['acl'],
            'group_alias' => $this->updatedProperties['acl']
        ));
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
            function($context) {
                $this->currentPage = new ContactGroupConfigurationListingPage($this);
                $this->currentPage = $this->currentPage->inspect($this->updatedProperties['name']);
                $object = $this->currentPage->getProperties();
                foreach($this->updatedProperties as $key => $value) {
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
