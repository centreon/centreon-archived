<?php

use Centreon\Test\Behat\CentreonContext;

class TopCounterPollersContext extends CentreonContext
{
    /**
     * @When I click on the pollers icon and I click on the configuration button
     */
    public function iClickOnThePollersIconAndIClickOnTheConfigurationButton()
    {
        $this->visit('/');
        $this->assertFind('css', '[aria-label="Pollers status"]')->click();
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '[aria-label="Pollers configuration"]');
            },
            'The summary of pollers status is not open',
            10
        );
        $this->assertFind('css', '[aria-label="Pollers configuration"]')->click();
    }

    /**
     * @Then I see the list of pollers configuration
     */
    public function iSeeTheListOfPollersConfiguration()
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '[name="apply_configuration"]');
            },
            'The poller configuration page is not loaded',
            10
        );
    }
}
