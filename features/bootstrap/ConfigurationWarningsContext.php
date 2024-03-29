<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\PollerConfigurationExportPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;

class ConfigurationWarningsContext extends CentreonContext
{
    /**
     * @Given a service with notifications enabled
     */
    public function aServiceWithNotificationsEnabled()
    {
        $page = new ServiceConfigurationPage($this);
        $page->setProperties(array(
            'hosts' => 'Centreon-Server',
            'description' => 'AcceptanceTestService',
            'check_command' => 'check_centreon_dummy',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1,
            'notifications_enabled' => 1
        ));
        $page->save();
    }

    /**
     * @Given the service has no notification period
     */
    public function theServiceHasNoNotificationPeriod()
    {
        // Nothing to do, services have no notification period by default.
    }

    /**
     * @When the configuration is exported
     */
    public function theConfigurationIsExported()
    {
        $page = new PollerConfigurationExportPage($this);
        $page->setProperties(array(
            'pollers' => 'all',
            'generate_files' => 1,
            'run_debug' => 1
        ));
        $page->export();
    }

    /**
     * @Then a warning message is printed
     */
    public function aWarningMessageIsPrinted()
    {
        $expectedWarningMessage = "Warning Notifier 'AcceptanceTestService' has no check time period defined!";

        $output = $this->assertFind('css', '#debug_1')->getText();

        if (str_contains($output, $expectedWarningMessage) === false) {
            throw new \Exception(
                "Configuration export debug does not contain expected warning message : $expectedWarningMessage"
            );
        }
    }
}
