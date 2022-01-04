<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\External\LoginPage;

class SpecialCharactersInContactContext extends CentreonContext
{
    protected $nonAdminName = 'nonAdminName';
    protected $nonAdminPassword = 'nonAdminPassword!1';
    protected $nonAdminAlias = 'nonAdminalias';
    protected $nonAdminEmail = 'test@localhost.com';
    protected $page;
    protected $accentedAndSpeacialCharsAlias = 'guést@';

    /**
     * @Given one non admin contact has been created
     */
    public function oneNonAdminContactHasBeenCreated()
    {
        $this->page = new ContactConfigurationPage($this);
        $this->page->setProperties(array(
            'name' => $this->nonAdminName,
            'alias' => $this->nonAdminAlias,
            'email' => $this->nonAdminEmail,
            'password' => $this->nonAdminPassword,
            'password2' => $this->nonAdminPassword,
            'admin' => 0
        ));
        $this->page->save();
    }

    /**
     * @When I have changed the contact alias
     */
    public function iHaveChangedTheContactAlias()
    {
        $this->page = new ContactConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->nonAdminAlias);
        $this->page->setProperties(array('alias' => $this->accentedAndSpeacialCharsAlias));
        $this->page->save();
    }


    /**
     * @Then the new record is displayed in the users list with the new alias value
     */
    public function theNewRecordIsDisplayedInTheUsersListWithTheNewAliasValue()
    {
        $this->page = new ContactConfigurationListingPage($this);
        $this->page->getEntry($this->accentedAndSpeacialCharsAlias);
    }

    /**
     * @Given the contact alias contains an accent
     */
    public function theContactAliasContainsAnAccent()
    {
        $this->iHaveChangedTheContactAlias();
    }

    /**
     * @When I fill login field and Password
     */
    public function iFillLoginFieldAndPassword()
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
        $this->page = new LoginPage($this);
        $this->page->login($this->accentedAndSpeacialCharsAlias, $this->nonAdminPassword);
    }
}
