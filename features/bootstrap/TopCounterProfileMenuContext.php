<?php

use Centreon\Test\Behat\CentreonContext;

class TopCounterProfileMenuContext extends CentreonContext
{
    /**
     * @When I click to edit profile link
     */
    public function iClickToEditProfileLink()
    {
        $this->visit('/');
        $this->assertFind('css', '[aria-label="User Profile"]')->click();
        $this->spin(
            function ($context) {
                return $this->getSession()->getPage()->has('css', '[aria-label="Edit profile"]');
            },
            'Popin not opened',
            20
        );
        $this->assertFind('css', '[aria-label="Edit profile"]')->click();
    }

    /**
     * @Then I see my profile edit form
     */
    public function iSeeMyProfileEditForm()
    {
        $this->spin(
            function ($context) {
                return $this->getSession()->getPage()->has('css', 'input[name="contact_name"]');
            },
            'The edit profile page is not loaded',
            10
        );
    }

    /**
     * @When I click to logout link
     */
    public function iClickToLogoutLink()
    {
        $this->visit('/');
        $this->assertFind('css', '[aria-label="User Profile"]')->click();
        $this->spin(
            function ($context) {
                return $this->getSession()->getPage()->has('css', '[aria-label="Edit profile"]');
            },
            'Popin not opened',
            20
        );
        $this->assertFind('css', '[aria-label="Logout"]')->click();
    }

    /**
     * @Then I see the login page
     */
    public function iSeeTheLoginPage()
    {
        $this->spin(
            function ($context) {
                return $this->getSession()->getPage()->has('css', 'input[name="submitLogin"]');
            },
            'The login page is not loaded',
            10
        );
    }
}
