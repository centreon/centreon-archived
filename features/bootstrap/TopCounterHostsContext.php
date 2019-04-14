<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;

class TopCounterHostsContext extends CentreonContext
{

    /**
     * @Given an OK host
     */
    public function anOkHost()
    {

        $listPage = new HostConfigurationListingPage($this);
        $page = $listPage->inspect('Centreon-Server');
        $page->setProperties(array(
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1
        ));
        $page->save();
        $this->restartAllPollers();
        $this->submitHostResult('Centreon-Server', 0, 'acceptance');
    }

    /**
     * @Given a non-OK host
     */
    public function aNoOkHost()
    {

        $listPage = new HostConfigurationListingPage($this);
        $page = $listPage->inspect('Centreon-Server');
        $page->setProperties(array(
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1
        ));
        $page->save();
        $this->restartAllPollers();
        $this->submitHostResult('Centreon-Server', 1, 'acceptance');
    }

    /**
     * @Given an unreachable host
     */
    public function anUnreachableHost()
    {
        $listPage = new HostConfigurationListingPage($this);
        $page = $listPage->inspect('Centreon-Server');
        $page->setProperties(array(
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1
        ));
        $page->save();
        $this->restartAllPollers();
        $this->submitHostResult('Centreon-Server', 2, 'acceptance');
    }


    /**
     * @When /^I click on the chip "([^"]+)"$/
     */
    public function iClickOnTheChip($chip)
    {
        $this->visit('/', false);
        $selector = '#' . $chip;
        $this->spin(
            function ($context) use ($selector) {
                return $context->getSession()->getPage()->has(
                    'css',
                    $selector
                );
            },
            'Home not load.',
            5
        );
        $this->assertFind('css', $selector)->click();
    }

    /**
     * @Then I see the list of hosts filtered by status :status
     */
    public function iSeeTheListOfHostsFilteredByStatus($status)
    {
        self::$lastUri = 'p=20202';
        $this->spin(
            function ($context) {
                $context->switchToIframe();
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
        $this->visit('/', false);
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    '[class*="wrap-right-hosts"] [class*="icon-hosts"]'
                );
            },
            'Home not load.',
            5
        );
        $this->assertFind('css', '[class*="wrap-right-hosts"] [class*="icon-hosts"]')->click();
    }

    /**
     * @Then I see the summary of hosts status
     */
    public function iSeeTheSummaryOfHostsStatus()
    {
        $this->spin(
            function ($context) {
                $element = $context->getSession()->getPage()->find('css', '[class*="hosts"] [class*="submenu"]');
                return $element->isVisible();
            },
            'The summary of hosts status is not open',
            10
        );
    }
}
