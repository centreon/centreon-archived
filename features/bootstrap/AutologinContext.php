<?php

use Centreon\Test\Behat\Administration\ParametersCentreonUiPage;
use Centreon\Test\Behat\Configuration\CurrentUserConfigurationPage;
use Centreon\Test\Behat\CentreonContext;

class AutologinContext extends CentreonContext
{
    private $currentPage;

    /**
     * @Given the user with autologin enabled
     */
    public function theUserWithAutologinEnabled()
    {
        $this->currentPage = new ParametersCentreonUiPage($this);
        $this->currentPage->setProperties([
            'enable autologin' => true
        ]);

        $this->currentPage->save();
    }

    /**
     * @When the user generates autologin key
     */
    public function theUserGeneratesAutologinKey()
    {
        $this->currentPage = new CurrentUserConfigurationPage($this);
        $this->currentPage->setProperties([
            'default' => 'Monitoring > Status Details > Hosts',
            'autologin_key' => 'toto'
        ]);
        $this->currentPage->save();
        $this->iAmLoggedOut();
    }

    /**
     * @Then the user arrives on the configured page for its account
     */
    public function theUserArrivesOnTheConfiguredPageForItsAccount()
    {
        $this->visit('main.php?autologin=1&useralias=admin&token=toto', false);
        self::$lastUri = 'p=20202';
        $this->switchToIframe();
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', 'a[href="main.php?p=20202"]');
            }
        );
    }

    /**
     * @Then the user enters a topology and arrives at the linked page
     */
    public function theUserEntersATopologyAndArrivesAtTheLinkedPage()
    {
        $this->visit('main.php?p=60101&autologin=1&useralias=admin&token=toto', false);
        self::$lastUri = 'p=60101';
        $this->switchToIframe();
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', 'a[href="main.php?p=60101"]');
            }
        );
    }
}
