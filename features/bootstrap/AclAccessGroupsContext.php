<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactGroupsConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactGroupConfigurationListingPage;
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
    protected $accessContactName = 'accessContactName';
    protected $accessContactAlias = 'accessContactAlias';
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
            'alias' => $this->contactGroupAlias,
            'contacts' => array($this->firstContactName, $this->secondContactName)
        ));
        $this->page->save();
    }

    /**
     * @When the access group is saved with its properties
     */
    public function theAccessGroupIsSavedWithItsProperties()
    {
        $this->page = new ACLGroupConfigurationPage($this);
        $this->page->setProperties(array(
            'group_name' => $this->accessContactName,
            'group_alias' => $this->accessContactAlias,
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
        $this->spin(
            function ($context) {
                if ($context->page->getProperty('acl_groups') !== $context->accessContactName) {
                    throw new \Exception($context->firstContactAlias . ' has no link access list groups displayed');
                }
                return true;
            },
            'Timeout',
            5
        );

        $this->page = new ContactConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->secondContactAlias);
        $this->spin(
            function ($context) {
                if ($context->page->getProperty('acl_groups') !== $context->accessContactName) {
                    throw new \Exception($context->secondContactAlias . ' has no link access list groups displayed');
                }
                return true;
            },
            'Timeout',
            5
        );
    }

    /**
     * @When I add a new access group with linked contact group
     */
    public function iAddANewAccessGroupWithLinkedContactGroup()
    {
        $this->oneContactGroupExistsIncludingTwoNonAdminContacts();
        $this->page = new ACLGroupConfigurationPage($this);
        $this->page->setProperties(array(
            'group_name' => 'accessGroupLinkedContactName',
            'group_alias' => 'accessGroupLinkedContactAlias',
            'contacts' => array($this->firstContactName, $this->secondContactName)
        ));
        $this->page->save();
        $this->page = new ACLGroupConfigurationPage($this);
        $this->page->setProperties(array(
            'group_name' => $this->accessGroupsName,
            'group_alias' => $this->accessGroupsAlias,
            'contactgroups' => $this->contactGroupName
        ));
        $this->page->save();
    }

    /**
     * @Then the contact group has the access group displayed in Relations informations
     */
    public function theContactGroupHasTheAccessListGroupDisplayedInRelationsInformations()
    {
        $this->page = new ContactGroupConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->contactGroupName);
        $this->spin(
            function ($context) {
                if ($context->page->getProperty('acl') !== $context->accessGroupsName) {
                    throw new \Exception($context->contactGroupName . ' has no Linked ACL groups displayed');
                }
                return true;
            },
            'Timeout',
            5
        );
        
    }

    /**
     * @Given one existing ACL access group
     */
    public function oneExistingAclAccessGroup()
    {
        $this->iAddANewAccessGroupWithLinkedContactGroup();
        $this->page = new ACLGroupConfigurationListingPage($this);
    }

    /**
     * @When I modify its properties
     */
    public function iModifyItsProperties()
    {
        $this->page = $this->page->inspect($this->accessGroupsName);
        $this->page->setProperties(array(
            'group_name' => 'newGroupName',
            'group_alias' => 'newGroupAlias'
        ));
        $this->page->save();
    }

    /**
     * @Then all modified properties are updated
     */
    public function allModifiedPropertiesAreUpdated()
    {
        $this->page = new ACLGroupConfigurationListingPage($this);
        $objet = $this->page->getEntries();
        if (!$objet['newGroupName'] && $objet['newGroupName']['description'] != 'newGroupAlias') {
            throw new \Exception('updates has not changed');
        }
    }

    /**
     * @When I duplicate the access group
     */
    public function iDuplicateTheAccessGroup()
    {
        $object = $this->page->getEntry($this->accessGroupsName);
        $this->page->selectMoreAction($object, 'Duplicate');
    }

    /**
     * @Then a new access group appears with similar properties
     */
    public function aNewAccessGroupAppearsWithSimilarProperties()
    {
        $objects = $this->page->getEntries();
        if ($objects['accessGroupsName_1']) {
            if ($objects['accessGroupsName_1']['description'] != $this->accessGroupsAlias) {
                throw new \Exception('properties has not been duplicated');
            }
        } else {
            throw new Exception('the duplication did not work');
        }
    }

    /**
     * @When I delete the access group
     */
    public function iDeleteTheAccessGroup()
    {
        $object = $this->page->getEntry($this->accessGroupsName);
        $this->page->selectMoreAction($object, 'Delete');
    }

    /**
     * @Then it does not exist anymore
     */
    public function itDoesNotExistAnymore()
    {
        $objects = $this->page->getEntries();
        if (key_exists($this->accessGroupsName, $objects)) {
            throw new Exception($this->accessGroupsName . ' is still existing');
        }
    }

    /**
     * @Given one existing enabled ACL access group
     */
    public function oneExistingEnabledAclAccessGroup()
    {
        $this->iAddANewAccessGroupWithLinkedContactGroup();
        $this->page = new ACLGroupConfigurationListingPage($this);
    }

    /**
     * @When I disable it
     */
    public function iDisableIt()
    {
        $this->page = $this->page->inspect($this->accessGroupsName);
        $this->page->setProperties(array('status' => 0));
        $this->page->save();
    }

    /**
     * @Then its status is modified
     */
    public function itsStatusIsModified()
    {
        $this->page = new ACLGroupConfigurationListingPage($this);
        $object = $this->page->getEntry($this->accessGroupsName);
        if ($object['status'] != 0) {
            throw new Exception($this->accessGroupsName . ' is still enabled');
        }
    }
}
