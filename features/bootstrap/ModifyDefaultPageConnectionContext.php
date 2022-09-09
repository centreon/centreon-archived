<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\Administration\ACLMenuConfigurationPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationPage;
use Centreon\Test\Behat\External\ListingPage;

class ModifyDefaultPageConnectionContext extends CentreonContext
{
    private $nonAdminName;
    private $nonAdminPwd;
    private $currentPage;
    private $expectedPage;

    public function __construct()
    {
        parent::__construct();
        $this->nonAdminName = "nonAdminContact";
        $this->nonAdminPwd = "Centreon!2021";
    }

    /**
     * @Given I have access to all menus
     */
    public function iHaveAccessToAllMenus()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => $this->nonAdminName,
            'alias' => $this->nonAdminName,
            'email' => "contact@localhost",
            'password' => $this->nonAdminPwd,
            'password2' => $this->nonAdminPwd,
            'admin' => 0
        ));
        $this->currentPage->save();
        $this->currentPage = new ACLGroupConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'group_name' => 'myACLGroupName',
            'group_alias' => 'myACLGroupAlias',
            'contacts' => $this->nonAdminName
        ));
        $this->currentPage->save();
        $this->currentPage = new ACLMenuConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'acl_name' => 'myACLMenuName',
            'acl_alias' => 'myACLMenuAlias',
            'acl_groups' => 'myACLGroupName',
            'menu_home' => 1,
            'menu_monitoring' => 1,
            'menu_reporting' => 1,
            'menu_configuration' => 1,
            'menu_administration' => 1
        ));
        $this->currentPage->save();
    }

    /**
     * @Given I have admin rights
     */
    public function iHaveAdminRights()
    {
        $this->iAmLoggedOut();
        $this->parameters['centreon_user'] = "admin";
        $this->parameters['centreon_password'] = "Centreon!2021";
        $this->iAmLoggedIn();
    }

    /**
     * @Given I have replaced the default page connection with Administration > Parameters
     */
    public function iHaveReplacedTheDefaultPageConnectionWithAdministrationParameters()
    {
        $this->visit('main.php?p=50104&o=c');
        $this->selectInList('select[name="default_page"]', 'Administration > Parameters > Centreon UI');
        $this->assertFindButton('submitC')->click();
    }

    /**
     * @Given I don't have admin rights
     */
    public function iDontHaveAdminRights()
    {
        $this->iAmLoggedOut();
        $this->parameters['centreon_user'] = $this->nonAdminName;
        $this->parameters['centreon_password'] = $this->nonAdminPwd;
        $this->iAmLoggedIn();
    }

    /**
     * @Given I have replaced the default page connection with Monitoring > Status Details > Hosts
     */
    public function iHaveReplacedTheDefaultPageConnectionWithMonitoringStatusDetailsHosts()
    {
        $this->visit('main.php?p=50104&o=c');
        $this->selectInList('select[name="default_page"]', 'Monitoring > Status Details > Hosts');
        $this->assertFindButton('submitC')->click();
    }

    /**
     * @When I log back to Centreon
     */
    public function iLogBackToCentreon()
    {
        $this->iAmLoggedOut();
        $this->iAmLoggedIn();
    }

    /**
     * @Then the active page is Administration > Parameters
     */
    public function theActivePageIsAdministrationParameters()
    {
        //The lastUri is a static variable used to check if the iFrame was correctly reloaded,
        //as we don't use the "visit" method, we need to set it directly here
        self::$lastUri = 'p=50110';
        $this->spin(
            function ($context) {
                $context->switchToIframe();
                return $context->getSession()->getPage()->has('css', 'input[name="oreon_path"]');
            },
            "The active page is not Administration > Parameters > Centreon UI.",
            5
        );
    }

    /**
     * @Then the active page is Monitoring > Status Details > Hosts
     */
    public function theActivePageIsMonitoringStatusDetailsHosts()
    {
        //The lastUri is a static variable used to check if the iFrame was correctly reloaded,
        //as we don't use the "visit" method, we need to set it directly here
        self::$lastUri = 'p=20202';
        $this->spin(
            function ($context) {
                $context->switchToIframe();
                return $context->getSession()->getPage()->has('css', '#host_search');
            },
            "The active page is not Monitoring > Status > Hosts.",
            5
        );
    }
}
