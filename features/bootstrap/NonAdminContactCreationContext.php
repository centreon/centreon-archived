<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\External\LoginPage;
use Centreon\Test\Behat\External\ListingPage;

class NonAdminContactCreationContext extends CentreonContext
{
    private $nonAdminName;
    private $nonAdminPassword;
    private $nonAdminAlias;
    private $nonAdminAddress;
    private $currentPage;

    public function __construct()
    {	
        parent::__construct();
        $this->nonAdminName = 'nonAdminName';
        $this->nonAdminPassword ='nonAdminPassword';
        $this->nonAdminAlias = 'nonAdminAlias';
        $this->nonAdminAddress = 'nonadmin@localhost';
    }

    /**
     * @When I have filled the contact form
     */
    public function iHaveFilledTtheContactForm()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => $this->nonAdminName,
            'alias' => $this->nonAdminAlias,
            'email' => $this->nonAdminAddress,
            'password' => $this->nonAdminPassword,
            'password2' => $this->nonAdminPassword,
            'admin' => 0
        ));
    }

    /**
     * @When clicked on the save button
     */
    public function clickedOnSaveButton()
    {
        $this->currentPage->save();
    }

    /**
     * @Then the new record is displayed in the users list
    */
    public function theNewRecordIsDisplayedInTheUserList()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage->getEntry($this->nonAdminAlias);
    }

    /**
     * @Given the new non admin user is created
     */
    public function theNewNonAdminUserIsCreated()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => $this->nonAdminName,
            'alias' => $this->nonAdminAlias,
            'email' => $this->nonAdminAddress,
            'password' => $this->nonAdminPassword,
            'password2' => $this->nonAdminPassword,
            'admin' => 0
        ));
        $this->currentPage->save();
    }

    /**
     * @When I fill login field and Password
     */
    public function iFillFieldAndPassword()
    {
        $this->iAmLoggedOut();
        $this->parameters['centreon_user'] = $this->nonAdminAlias;
        $this->parameters['centreon_password'] = $this->nonAdminPassword;
    }

    /**
     * @Then the contact is logged to Centreon Web
     */
    public function theContactIsLoggedToCentreonWeb()
    {
        $this->iAmLoggedIn();
    }

    /**
     * @Given a contact is configured
     */
    public function aContactIsConfigured()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => $this->nonAdminName,
            'alias' => $this->nonAdminAlias,
            'email' => $this->nonAdminAddress,
            'password' => $this->nonAdminPassword,
            'password2' => $this->nonAdminPassword,
            'admin' => 0
        ));
        $this->currentPage->save();
    }

   /**
     * @When I duplicate a contact
     */
    public function iDuplicateAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->nonAdminAlias);
        $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]')->check();
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new contact is displayed in the user list
     */
    public function theNewContactIsDisplayedInTheUserList()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                return $this->currentPage->getEntry($this->nonAdminAlias . '_1');
            },
            "The duplicated contact was not found.",
            30
        );
    }

    /**
     * @When I delete a contact
     */
    public function iDeleteAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->nonAdminAlias);
        $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]')->check();
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted contact is not displayed in the user list
     */
    public function theDeletedContactIsNotDisplayedInTheUserList()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach($object as $value){
                    $bool = $bool && $value['alias'] != $this->nonAdminAlias;
                }
                return $bool;
            },
            "The contact was not deleted.",
            30
        );
    }
}
