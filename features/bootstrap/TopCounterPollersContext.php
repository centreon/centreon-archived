<?php

use Centreon\Test\Behat\CentreonContext;

class TopCounterPollersContext extends CentreonContext
{
    /**
     * @When I click on the pollers icon and I click on the configuration button
     */
    public function iClickOnThePollersIconAndIClickOnTheConfigurationButton()
    {
        $this->visit('/', false);
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '[class*="iconmoon"][class*="icon-poller"]');
            },
            'Home not load.',
            5
        );
        $this->assertFind('css', '[class*="iconmoon"][class*="icon-poller"]')->click();
        $this->spin(
            function ($context) {
                $element = $context->getSession()->getPage()->find('css', '[class*="submenu"][class*="pollers"]');
                return $element->isVisible();
            },
            'The summary of pollers status is not open',
            10
        );
        $this->assertFind(
            'css',
            '[class*="submenu"][class*="pollers"] [class*="btn-green"][class*="submenu-top-button"]'
        )->click();
    }

    /**
     * @Then I see the list of pollers configuration
     */
    public function iSeeTheListOfPollersConfiguration()
    {
        self::$lastUri = 'p=60901';
        $this->spin(
            function ($context) {
                $context->switchToIframe();
                return $context->getSession()->getPage()->has('css', '[name="apply_configuration"]');
            },
            'The poller configuration page is not loaded',
            10
        );
    }
}
