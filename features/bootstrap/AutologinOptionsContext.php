<?php

use Centreon\Test\Behat\Administration\ParametersCentreonUiPage;
use Centreon\Test\Behat\Configuration\CurrentUserConfigurationPage;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\External\LoginPage;

class AutologinOptionsContext extends CentreonContext
{
    private $currentPage;

    /**
     * @Given user default page is a legacy page
     */
    public function userDefaultPageIsALegacyPage()
    {
        $this->currentPage = new CurrentUserConfigurationPage($this);
        $this->currentPage->setProperties([
            'default' => 'Administration > Sessions'
        ]);
        $this->currentPage->save();
    }

    /**
     * @Given one autologin key has been generated
     */
    public function oneAutologinKeyHasBeenGenerated()
    {
        $this->currentPage = new CurrentUserConfigurationPage($this);
        $this->currentPage->setProperties(array(
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
        // log out
        $this->visit('index.php?disconnect=1');
        $this->currentPage = new LoginPage($this, false);

        // log in with autologin
        $this->visit('main.php?autologin=1&useralias=admin&token=autolog&min=1');
        $this->currentPage = $this->getSession()->getPage();
    }

    /**
     * @Then Centreon default page is displayed without the menus and the header
     */
    public function centreonDefaultPageIsDisplayedWithoutTheMenusAndTheHeader()
    {
        $this->spin(
            function ($context) {
                $element = $this->currentPage->find(
                    'css',
                    '*[aria-label="Breadcrumb"]  a[href="main.php?p=504"]'
                );
                return !is_null($element);
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
                $element = $this->currentPage->find('css', 'select[name="host"]');
                return !is_null($element);
            },
            'The current page is not valid.',
            5
        );
    }
}
