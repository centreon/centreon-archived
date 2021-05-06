<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\BrokerConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Monitoring\ServiceMonitoringDetailsPage;

class BrokerContext extends CentreonContext
{
    protected $page;

    /**
     * @Given a daemon broker configuration
     */
    public function aDaemonBrokerConfiguration()
    {
        $this->page = new BrokerConfigurationListingPage($this);
        $this->page = $this->page->inspect('central-broker-master');
    }

    /**
     * @Given a configured passive service
     */
    public function aConfiguredPassiveService()
    {
        $this->page = new ServiceConfigurationPage($this);
        $this->page->setProperties(array(
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
        $this->page->save();
    }

    /**
     * @When I update broker configuration file name
     */
    public function IUpdateBrokerConfigurationFileName()
    {
        $this->page->setProperties(
            array(
                'filename' => 'new-name.json'
            )
        );
        $this->page->save();
    }

    /**
     * @When I export configuration and restart centreon-broker
     */
    public function IExportConfiguration()
    {
        $this->reloadAllPollers();
        $this->container->execute('service cbd restart', 'web');
    }

    /**
     * @Then the new configuration is applied
     */
    public function theNewConfigurationIsApplied()
    {
        $return = $this->container->execute('cat /etc/centreon-broker/watchdog.json', 'web');
        if (!preg_match('/\/etc\/centreon-broker\/\/?new-name\.json/', $return['output'])) {
            throw new \Exception('new-name.json is not declared in watchdog.json');
        }
    }

    /**
     * @Then the monitoring is still working
     */
    public function theMonitoringIsStillWorking()
    {
        $this->submitServiceResult(
            'Centreon-Server',
            'AcceptanceTestService',
            0,
            'Acceptance test output.',
            'test=1s'
        );

        $this->spin(
            function ($context) {
                $page = new ServiceMonitoringDetailsPage(
                    $context,
                    'Centreon-Server',
                    'AcceptanceTestService'
                );
                $props = $page->getProperties();
                return $props['last_check'];
            },
            'Configured passive service is not monitored. Maybe engine or broker are not properly reloaded',
            70
        );
    }
}
