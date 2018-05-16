<?php

use Centreon\Test\Behat\CentreonContext;

class TopCounterServicesContext extends CentreonContext
{
    /**
     * @When /^I click on the chip "([^"]+)"$/
     */
    public function iClickOnTheChip($chip)
    {
        $this->visit('/');
        $selector = '[aria-label="' . $chip . '"]';
        $this->assertFind('css', $selector)->click();
    }

    /**
     * @Then I see the list of services filtered by status :status
     */
    public function iSeeTheListOfServicesFilteredByStatus($status)
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '#statusFilter');
            },
            'Services listing page not loaded.',
            10
        );
        $value = $this->assertFind('css', '#statusFilter')->getValue();
        if ($value !== $status) {
            throw new \Exception('Bad status filter');
        }
    }

    /**
     * @When I click on the services icon
     */
    public function iClickOnTheServicesIcon()
    {
        $this->visit('/');
        $this->assertFind('css', '[aria-label="Services status"]')->click();
    }

    /**
     * @Then I see the summary of services status
     */
    public function iSeeTheSummaryOfServicesStatus()
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', 'a[title="all services list"]');
            },
            'The summary of services status is not open',
            10
        );
    }
}
