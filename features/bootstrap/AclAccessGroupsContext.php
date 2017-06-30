<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactGroupsConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationListingPage;

class AclAccessGroupsContext extends CentreonContext 
{
    protected $page;
    protected $firstContactName = 'firstContactName';
    protected $firstContactAlias = 'firstContactAlias';
    protected $secondContactName = 'secondContactName';
    protected $secondContactAlias = 'secondContactAlias';
    protected $contactGroupName = 'contactGroupName';
    protected $contactGroupAlias = 'contactGroupAlias';
    protected $accessGroupsName = 'accessGroupsName';
    protected $accessGroupsAlias = 'accessGroupsAlias';

    /**
     * @When one contact group exists including two non admin contacts
     */
    public function oneContactGroupExistsIncludingTwoNonAdminContacts()
    {
        $this->page = new ContactConfigurationPage($this);
        $this->page->setProperties(array(
            'alias' => $this->firstContactAlias,
            'name' => $this->firstContactName,
            'email' => 'test@centreon.com',
            'password' => 'firstContactPassword',
            'password2' => 'firstContactPassword',
            'admin' => 0
        ));
        $this->page->save();
        $this->page = new ContactConfigurationPage($this);
        $this->page->setProperties(array(
            'alias' => $this->secondContactAlias,
            'name' => $this->secondContactName,
            'email' => 'test2@centreon.com',
            'password' => 'secondContactPassword',
            'password2' => 'secondContactPassword',
            'admin' => 0
        ));
        $this->page->save();
        $this->page = new ContactGroupsConfigurationPage($this);
        $this->page->setProperties(array(
            'name' => $this->contactGroupName,
            'alias' => $this->contactGroupAlias
        ));
        $this->assertFind('css', 'span[class="select2-selection select2-selection--multiple"]')->click();
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', 'span ul li div[title="'.$this->firstContactName.'"]');
            },
             'The user: '.$this->firstContactName. ' does not exist or has not been found',
            5
        );
        $this->assertFind('css', 'span ul li div[title="'.$this->firstContactName.'"]')->click();
        $this->assertFind('css', 'span[class="select2-selection select2-selection--multiple"]')->click();
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', 'span ul li div[title="'.$this->secondContactName.'"]');
            },
            'The user: ' . $this->secondContactName . ' does not exist or has not been found',
            5
        );
        $this->assertFind('css', 'span ul li div[title="'.$this->secondContactName.'"]')->click();
        $this->page->save();
    }

    /**
     * @When the access group is saved with its properties
     */
    public function theAccessGroupIsSavedWithItsProperties()
    {
        $this->page = new ACLGroupConfigurationPage($this);
        $this->page->setProperties(array(
            'group_name' => $this->accessGroupsName,
            'group_alias' => $this->accessGroupsAlias,
            'contacts' => array($this->firstContactName, $this->secondContactName)
        ));
        $this->page->save();
    }

    /**
     * @Then all linked users have the access list group displayed in Centreon authentication tab
     */
    public function allLinkedUsersHaveTheAccessListGroupDisplayedInCentreonAuthenticationTab()
    {
        $this->page = new ContactConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->firstContactAlias);
        $this->assertFind('css', 'li#c2 a')->click();
        $value = $this->assertFind('css', 'span[title="'. $this->accessGroupsName.'"]')->getText();
        if ($value != $this->accessGroupsName) {
            
            throw new \Exception($this->firstContactAlias . ' have no access groups displayed');
        }
        
        $this->page = new ContactConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->secondContactAlias);
        $this->assertFind('css', 'li#c2 a')->click();
        $value = $this->assertFind('css', 'span[title="'. $this->accessGroupsName.'"]')->getText();
        if ($value != $this->accessGroupsName) {
            
            throw new \Exception($this->secondContactAlias . ' have no access groups displayed');
        }
    }

    /**
     * @When I add a new access group with linked contact group
     */
    public function iAddANewAccessGroupWithLinkedContactGroup()
    {
        throw new PendingException();
    }

    /**
     * @Then the Contact group has the access list group displayed in Relations informations
     */
    public function theContactGroupHasTheAccessListGroupDisplayedInRelationsInformations()
    {
        throw new PendingException();
    }

    /**
     * @Given one existing ACL access group
     */
    public function oneExistingAclAccessGroup()
    {
        throw new PendingException();
    }

    /**
     * @When I modify its properties
     */
    public function iModifyItsProperties()
    {
        throw new PendingException();
    }

    /**
     * @Then all modified properties are updated
     */
    public function allModifiedPropertiesAreUpdated()
    {
        throw new PendingException();
    }

    /**
     * @When I duplicate the access group
     */
    public function iDuplicateTheAccessGroup()
    {
        throw new PendingException();
    }

    /**
     * @Then a new access group appears with similar properties
     */
    public function aNewAccessGroupAppearsWithSimilarProperties()
    {
        throw new PendingException();
    }

    /**
     * @When I delete the access group
     */
    public function iDeleteTheAccessGroup()
    {
        throw new PendingException();
    }

    /**
     * @Then it does not exist anymore
     */
    public function itDoesNotExistAnymore()
    {
        throw new PendingException();
    }

    /**
     * @Given one existing enabled ACL access group
     */
    public function oneExistingEnabledAclAccessGroup()
    {
        throw new PendingException();
    }

    /**
     * @When I disable it
     */
    public function iDisableIt()
    {
        throw new PendingException();
    }

    /**
     * @Then its status is modified
     */
    public function itsStatusIsModified()
    {
        throw new PendingException();
    }
}
