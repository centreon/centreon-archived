<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\External\ListingPage;

class ContactConfigurationContext extends CentreonContext
{
    private $nonAdminName;
    private $nonAdminPassword;
    private $nonAdminAlias;
    private $nonAdminAddress;
    private $nonAdminDN;
    private $nonAdminServiceNotifCommand;
    private $changedName;
    private $changedAlias;
    private $changedAddress;
    private $currentPage;

    public function __construct()
    {
        parent::__construct();
        $this->nonAdminName = 'nonAdminName';
        $this->nonAdminPassword ='nonAdminPassword';
        $this->nonAdminAlias = 'nonAdminAlias';
        $this->nonAdminAddress = 'nonadmin@localhost';
        $this->nonAdminDN = 'nonAdminDN';
        $this->nonAdminServiceNotifCommand = 'host-notify-by-email';
        $this->changedName = 'changedName';
        $this->changedAlias = 'changedAlias';
        $this->changedAddress = 'contact@localhost';
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
     * @When I configure the name of a contact
     */
    public function iConfigureTheNameOfAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->nonAdminAlias);
        $this->currentPage->setProperties(array(
            'name' => $this->changedName
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the name has changed on the contact page
     */
    public function theNameHasChangedOnTheContactPage()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->nonAdminAlias);
                return $object['name'] == $this->changedName;
            },
            "The contact has not changed.",
            30
        );
    }

   /**
     * @When I configure the alias of a contact
     */
    public function iConfigureTheAliasOfAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->nonAdminAlias);
        $this->currentPage->setProperties(array(
            'alias' => $this->changedAlias
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the alias has changed on the contact page
     */
    public function theAliasHasChangedOnTheContactPage()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->spin(
            function($context){
                return $this->currentPage->getEntry($this->changedAlias);
            },
            "The alias has not changed.",
            30
        );

    }

    /**
     * @When I configure the email of a contact
     */
    public function iConfigureTheAddressOfAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->nonAdminAlias);
        $this->currentPage->setProperties(array(
            'email' => $this->changedAddress
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the email has changed on the contact page
     */
    public function theAddressHasChangedOnTheContactPage()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->nonAdminAlias);
                return $object['email'] == $this->changedAddress;
            },
            "The address has not changed.",
            30
        );

    }

   /**
     * @When I make a contact be an admin
     */
    public function iMakeAContactBeAnAdmin()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->nonAdminAlias);
        $this->currentPage->setProperties(array(
            'admin' => 1
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the contact is now an admin
     */
    public function theContactIsNowAnAdmin()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->nonAdminAlias);
                return $object['admin'] == 'Enabled';
            },
            "The contact is not an admin.",
            30
        );

    }

    /**
     * @When I configure the DN of a contact
     */
    public function iConfigureTheDNOfAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->nonAdminAlias);
        $this->currentPage->setProperties(array(
            'dn' => $this->nonAdminDN
        ));
        $this->currentPage->save();
    }

    /**
     * @Then the DN has changed
     */
    public function theDNHasChanged()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $this->currentPage = $this->currentPage->inspect($this->nonAdminAlias);
                $object = $this->currentPage->getProperties($this->nonAdminAlias);
                return $object['dn'] == $this->nonAdminDN;
            },
            "The DN has not changed.",
            30
        );
    }
}
