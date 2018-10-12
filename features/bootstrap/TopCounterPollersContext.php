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
        $this->assertFind('css', '.iconmoon.icon-poller')->click();
        $this->spin(
            function ($context) {
                $element = $context->getSession()->getPage()->find('css', '.submenu.pollers');
                return $element->isVisible();
            },
            'The summary of pollers status is not open',
            10
        );
        $this->assertFind('css', '.submenu.pollers .btn-green.submenu-top-button')->click();
    }

    /**
     * @Then I see the list of pollers configuration
     */
    public function iSeeTheListOfPollersConfiguration()
    {
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
