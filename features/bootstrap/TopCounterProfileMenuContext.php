<?php

use Centreon\Test\Behat\CentreonContext;

class TopCounterProfileMenuContext extends CentreonContext
{
    /**
     * @When I click to edit profile link
     */
    public function iClickToEditProfileLink()
    {
        $this->visit('/', false);

        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '.iconmoon.icon-user');
            },
            'Home not load.',
            5
        );

        $this->assertFind('css', '.iconmoon.icon-user')->click();
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '.submenu-user-edit');
            },
            'Popin not opened',
            20
        );
        $this->assertFind('css', '.submenu-user-edit')->click();
    }

    /**
     * @Then I see my profile edit form
     */
    public function iSeeMyProfileEditForm()
    {
        self::$lastUri = 'p=50104';
        $this->spin(
            function ($context) {
                $context->switchToIframe();
                return $context->getSession()->getPage()->has('css', 'input[name="contact_name"]');
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
        $this->visit('/', false);
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '.iconmoon.icon-user');
            },
            'Home not load.',
            5
        );
        $this->assertFind('css', '.iconmoon.icon-user')->click();
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '.submenu-user-edit');
            },
            'Popin not opened',
            20
        );
        $this->assertFind('css', '.btn.btn-small.logout')->click();
    }

    /**
     * @Then I see the login page
     */
    public function iSeeTheLoginPage()
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', 'input[name="submitLogin"]');
            },
            'The login page is not loaded',
            10
        );
    }
}
