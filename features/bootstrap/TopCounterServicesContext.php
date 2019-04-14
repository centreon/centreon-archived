<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;

class TopCounterServicesContext extends CentreonContext
{

    /**
     * @Given I have a passive service
     */
    public function iHaveAPassiveService()
    {
        $page = new ServiceConfigurationPage($this);
        $page->setProperties(array(
            'hosts' => 'Centreon-Server',
            'description' => 'AcceptanceTestService',
            'templates' => 'generic-service',
            'check_command' => 'check_centreon_dummy',
            'check_period' => '24x7',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1
        ));
        $page->save();
        $this->restartAllPollers();
    }

    /**
     * @Given an ok service
     */
    public function anOkService()
    {
        $this->submitServiceResult(
            'Centreon-Server',
            'AcceptanceTestService',
            0,
            'Acceptance test output.'
        );
    }

    /**
     * @Given a Critical service
     */
    public function aCriticalService()
    {
        $this->submitServiceResult(
            'Centreon-Server',
            'AcceptanceTestService',
            2,
            'Acceptance test output.'
        );
    }

    /**
     * @Given a warning service
     */
    public function aWarningService()
    {
        $this->submitServiceResult(
            'Centreon-Server',
            'AcceptanceTestService',
            1,
            'Acceptance test output.'
        );
    }

    /**
     * @Given a unknown service
     */
    public function aUnknownService()
    {
        $this->submitServiceResult(
            'Centreon-Server',
            'AcceptanceTestService',
            3,
            'Acceptance test output.'
        );
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
     * @Then I see the list of services filtered by status :status
     */
    public function iSeeTheListOfServicesFilteredByStatus($status)
    {
        self::$lastUri = 'p=20201';
        $this->spin(
            function ($context) {
                $context->switchToIframe();
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
        $this->visit('/', false);
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    '[class*="wrap-right-services"] [class*="icon-services"]'
                );
            },
            'Home not load.',
            5
        );
        $this->assertFind('css', '[class*="wrap-right-services"] [class*="icon-services"]')->click();
    }

    /**
     * @Then I see the summary of services status
     */
    public function iSeeTheSummaryOfServicesStatus()
    {
        $this->spin(
            function ($context) {
                $element = $context->getSession()->getPage()->find('css', '[class*="services"] [class*="submenu"]');
                return $element->isVisible();
            },
            'The summary of services status is not open',
            10
        );
    }
}
