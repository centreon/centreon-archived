<?php

use Centreon\Test\Behat\CentreonContext;

class TopCounterHostsContext extends CentreonContext
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
     * @Then I see the list of hosts filtered by status :status
     */
    public function iSeeTheListOfServicesFilteredByStatus($status)
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '#statusFilter');
            },
            'Hosts listing page not loaded.',
            10
        );
        $value = $this->assertFind('css', '#statusFilter')->getValue();
        if ($value !== $status) {
            throw new \Exception('Bad status filter');
        }
    }

    /**
     * @When I click on the hosts icon
     */
    public function iClickOnTheHostsIcon()
    {
        $this->visit('/');
        $this->assertFind('css', '[aria-label="Hosts status"]')->click();
    }

    /**
     * @Then I see the summary of hosts status
     */
    public function iSeeTheSummaryOfHostsStatus()
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', 'a[title="all hosts list"]');
            },
            'The summary of hosts status is not open',
            10
        );
    }
}
