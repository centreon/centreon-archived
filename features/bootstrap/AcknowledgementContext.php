<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\MetaServiceConfigurationPage;
use Centreon\Test\Behat\Monitoring\MonitoringServicesPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Monitoring\ServiceMonitoringDetailsPage;

class AcknowledgementContext extends CentreonContext
{
    /**
     * @Given a non-OK service
     */
    public function aNonOKService()
    {
        $page = new ServiceConfigurationPage($this);
        $page->setProperties(
            array(
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
            )
        );
        $page->save();
        $this->restartAllPollers();
        $this->submitServiceResult(
            'Centreon-Server',
            'AcceptanceTestService',
            2,
            'Acceptance test output.'
        );
    }

    /**
     * @Given a non-OK meta-service
     */
    public function aNonOKMetaService()
    {
        $page = new MetaServiceConfigurationPage($this);
        $page->setProperties(
            array(
                'name' => 'AcceptanceTestMetaService',
                'warning_level' => 0,
                'critical_level' => 0,
                'check_period' => '24x7',
                'max_check_attempts' => 1,
                'normal_check_interval' => 1,
                'retry_check_interval' => 1
            )
        );
        $page->save();
        $this->restartAllPollers();

        $page = new MonitoringServicesPage($this);
        $this->spin(
            function ($context) use ($page) {
                $page->scheduleImmediateCheckForcedOnService('_Module_Meta', 'meta_1');
                return true;
            },
            'Could not schedule check.'
        );

        $this->spin(
            function ($context) {
                $page = new ServiceMonitoringDetailsPage(
                    $context,
                    '_Module_Meta',
                    'meta_1'
                );
                $props = $page->getProperties();
                return $props['last_check'] && $props['state'] != 'PENDING';
            },
            'Could not open meta-service monitoring details page.',
            120
        );
    }

    /**
     * @When I acknowledge the service
     */
    public function iAcknowledgeTheService()
    {
        $page = new MonitoringServicesPage($this);

        $url = 'http://' . $this->container->getHost() . ':' . $this->container->getPort(80, 'web') .
        '/centreon/include/monitoring/external_cmd/cmdPopup.php';
        $page->addAcknowledgementOnService(
            'Centreon-Server',
            'AcceptanceTestService',
            'Acceptance test.',
            true,
            true,
            true,
            false,
            $url
        );
    }

    /**
     * @When I acknowledge the meta-service
     */
    public function iAcknowledgeTheMetaService()
    {
        $page = new MonitoringServicesPage($this);
        $url = 'http://' . $this->container->getHost() . ':' . $this->container->getPort(80, 'web') .
            '/centreon/include/monitoring/external_cmd/cmdPopup.php';
        $page->addAcknowledgementOnService(
            '_Module_Meta',
            'meta_1',
            'Acceptance test.',
            true,
            true,
            true,
            false,
            $url
        );
    }

    /**
     * @Then the service is marked as acknowledged
     */
    public function theServiceIsMarkedAsAcknowledged()
    {
        $this->spin(
            function ($context) {
                $page = new MonitoringServicesPage($context);
                return $page->isServiceAcknowledged(
                    'Centreon-Server',
                    'AcceptanceTestService'
                );
            }
        );
    }

    /**
     * @Then the meta-service is marked as acknowledged
     */
    public function theMetaServiceIsMarkedAsAcknowledged()
    {
        $this->spin(
            function ($context) {
                $page = new MonitoringServicesPage($context);
                return $page->isServiceAcknowledged(
                    '_Module_Meta',
                    'meta_1'
                );
            }
        );
    }
}
