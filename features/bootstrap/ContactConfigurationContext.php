<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\External\ListingPage;

class ContactConfigurationContext extends CentreonContext
{
    private $contactName;
    private $contactPassword;
    private $contactAlias;
    private $contactAddress;
    private $contactDN;
    private $contactHostNotifPeriod;
    private $contactServiceNotifPeriod;
    private $changedName;
    private $changedAlias;
    private $changedAddress;
    private $currentPage;

    public function __construct()
    {
        parent::__construct();
        $this->contactName = 'contactName';
        $this->contactPassword ='contactPassword';
        $this->contactAlias = 'contactAlias';
        $this->contactAddress = 'contact@localhost';
        $this->contactDN = 'contactDN';
        $this->contactHostNotifPeriod = 'workhours';
        $this->contactServiceNotifPeriod = 'nonworkhours';
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
            'name' => $this->contactName,
            'alias' => $this->contactAlias,
            'email' => $this->contactAddress,
            'password' => $this->contactPassword,
            'password2' => $this->contactPassword,
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
        $this->currentPage = $this->currentPage->inspect($this->contactAlias);
        $this->currentPage->setProperties(array(
            'name' => $this->changedName
        ));
        $this->currentPage->save();
    }

    /**
      * @When I configure the alias of a contact
      */
    public function iConfigureTheAliasOfAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->contactAlias);
        $this->currentPage->setProperties(array(
            'alias' => $this->changedAlias
        ));
        $this->currentPage->save();
    }

    /**
     * @When I configure the email of a contact
     */
    public function iConfigureTheAddressOfAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedAlias);
        $this->currentPage->setProperties(array(
            'email' => $this->changedAddress
        ));
        $this->currentPage->save();
    }

    /**
     * @When I configure the access of a contact
     */
    public function iConfigureTheAccessOfAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedAlias);
        $this->currentPage->setProperties(array(
            'access' => 1
        ));
        $this->currentPage->save();
    }

    /**
     * @When I make a contact be an admin
     */
    public function iMakeAContactBeAnAdmin()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedAlias);
        $this->currentPage->setProperties(array(
            'admin' => 0
        ));
        $this->currentPage->save();
    }

    /**
     * @When I configure the status of a contact
     */
    public function iConfigureTheStatusOfAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedAlias);
        $this->currentPage->setProperties(array(
            'status' => 1
        ));
        $this->currentPage->save();
    }

    /**
     * @When I configure the DN of a contact
     */
    public function iConfigureTheDNOfAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedAlias);
        $this->currentPage->setProperties(array(
            'dn' => $this->contactDN
        ));
        $this->currentPage->save();
    }

    /**
     * @When I configure the host_notif_period
     */
    public function iConfigureTheHost_notif_period()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedAlias);
        $this->currentPage->setProperties(array(
            'host_notification_period' => $this->contactHostNotifPeriod
        ));
        $this->currentPage->save();
    }

    /**
     * @When I configure the service_notif_period
     */
    public function iConfigureTheService_notif_period()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->changedAlias);
        $this->currentPage->setProperties(array(
            'service_notification_period' => $this->contactServiceNotifPeriod
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
                $object = $this->currentPage->getEntry($this->changedAlias);
                return $object['name'] == $this->changedName;
            },
            "The contact has not changed.",
            30
        );
    }

    /**
     * @Then the alias has changed on the contact page
     */
    public function theAliasHasChangedOnTheContactPage()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                return $this->currentPage->getEntry($this->changedAlias);
            },
            "The alias has not changed.",
            30
        );

    }

    /**
     * @Then the email has changed on the contact page
     */
    public function theAddressHasChangedOnTheContactPage()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->changedAlias);
                return $object['email'] == $this->changedAddress;
            },
            "The address has not changed.",
            30
        );

    }

    /**
     * @Then the access has changed on the contact page
     */
    public function theAccessHasChangedOnTheContactPage()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->changedAlias);
                return $object['access'] == 'Enabled';
            },
            "The access has not changed.",
            30
        );
    }

    /**
     * @Then the contact is now an admin
     */
    public function theContactIsNowAnAdmin()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->changedAlias);
                return $object['admin'] == 'No';
            },
            "The contact is not an admin.",
            30
        );
    }

    /**
     * @Then the status has changed on the contact page
     */
    public function theStatusHasChangedOnTheContactPage()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->changedAlias);
                return $object['status'] == 'ENABLED';
            },
            "The status has not changed.",
            30
        );
    }

    /**
     * @Then the DN has changed
     */
    public function theDNHasChanged()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $this->currentPage = $this->currentPage->inspect($this->changedAlias);
                $object = $this->currentPage->getProperties($this->changedAlias);
                return $object['dn'] == $this->contactDN;
            },
            "The DN has not changed.",
            30
        );
    }

    /**
     * @Then the host_notif_period has changed
     */
    public function theHost_notif_periodHasChanged()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->changedAlias);
                return $object['host_notification_period'] == $this->contactHostNotifPeriod . ' ()'; 
            },
            "The host_notification_period has not changed",
            30
        );
    }

    /**
     * @Then the service_notif_period has changed
     */
    public function theService_notif_periodHasChanged()
    {
        $this->spin(
            function($context){
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->changedAlias);
                return $object['service_notification_period'] == $this->contactServiceNotifPeriod . ' ()';
            },
            "The service_notification_period has not changed",
            30
        );
    }
}
