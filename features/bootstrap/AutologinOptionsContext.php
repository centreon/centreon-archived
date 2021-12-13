<?php

use Centreon\Test\Behat\Administration\ParametersCentreonUiPage;
use Centreon\Test\Behat\Configuration\CurrentUserConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\External\LoginPage;

class AutologinOptionsContext extends CentreonContext
{
    private $currentPage;

    /**
     * @Given one autologin key has been generated
     */
    public function oneAutologinKeyHasBeenGenerated()
    {
        $this->currentPage = new CurrentUserConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'default' => 'Configuration > Hosts',
            'autologin_key' => 'autolog'
        ));
        $this->currentPage->save();
    }

    /**
     * @Given the autologin option is enabled
     */
    public function theAutologinOptionIsEnabled()
    {
        $this->currentPage = new ParametersCentreonUIPage($this);
        $this->currentPage->setProperties(array(
            'enable autologin' => true
        ));
        $this->currentPage->save();
    }

    /**
     * @When I type the autologin url with the fullscreen option in my web browser
     */
    public function iTypeTheAutologinUrlWithTheFullscreenOptionInMyWebBrowser()
    {
        $this->iAmLoggedOut();

        // log in with autologin
        $this->visit('main.php?autologin=1&useralias=admin&token=autolog&min=1', false);
        self::$lastUri = 'p=60101';
        $this->switchToIframe();
    }

    /**
     * @Then Centreon default page is displayed without the menus and the header
     */
    public function centreonDefaultPageIsDisplayedWithoutTheMenusAndTheHeader()
    {
        $this->spin(
            function ($context) {
                new HostConfigurationListingPage($context, false);
                return true;
            },
            'The current page is not valid.',
            5
        );
    }

    /**
     * @When I type the autologin url with the option page :arg1
     */
    public function iTypeTheAutologinUrlWithTheOptionPage($arg1 = 30701)
    {
        $this->visit('main.php?p=30701&autologin=1&useralias=admin&token=autolog');
        $this->currentPage = $this->getSession()->getPage();
    }

    /**
     * @Then Reporting > Dashboards > Hosts page is displayed
     */
    public function reportingDashboardsHostsPageIsDisplayed()
    {
        $this->spin(
            function ($context) {
                $element = $this->currentPage->find('css', 'select[name="hostElement"]');
                return !is_null($element);
            },
            'The current page is not valid.',
            5
        );
    }
}
