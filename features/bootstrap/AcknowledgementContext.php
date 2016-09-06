<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\ConfigurationPollersPage;
use Centreon\Test\Behat\MetaServiceConfigurationPage;
use Centreon\Test\Behat\MonitoringServicesPage;
use Centreon\Test\Behat\ServiceConfigurationPage;
use Centreon\Test\Behat\ServiceMonitoringDetailsPage;

class AcknowledgementContext extends CentreonContext
{
    /**
     *  @Given a non-OK service
     */
    public function aNonOKService()
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
        (new ConfigurationPollersPage($this))->restartEngine();
        $this->submitServiceResult(
            'Centreon-Server',
            'AcceptanceTestService',
            2,
            'Acceptance test output.'
        );
    }

    /**
     *  @Given a non-OK meta-service
     */
    public function aNonOKMetaService()
    {
        $page = new MetaServiceConfigurationPage($this);
        $page->setProperties(array(
            'name' => 'AcceptanceTestMetaService',
            'warning_level' => 0,
            'critical_level' => 0,
            'check_period' => '24x7',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1
        ));
        $page->save();
        (new ConfigurationPollersPage($this))->restartEngine();
        $this->spin(function ($context) {
            $page = new ServiceMonitoringDetailsPage(
                $context,
                '_Module_Meta',
                'meta_1'
            );
            $props = $page->getProperties();
            return $props['last_check'];
        },
        120);
    }

    /**
     *  @When I acknowledge the service
     */
    public function iAcknowledgeTheService()
    {
        $page = new MonitoringServicesPage($this);
        $page->addAcknowledgementOnService(
            'Centreon-Server',
            'AcceptanceTestService',
            'Acceptance test.',
            true,
            true,
            true,
            false
        );
    }

    /**
     *  @When I acknowledge the meta-service
     */
    public function iAcknowledgeTheMetaService()
    {
        $page = new MonitoringServicesPage($this);
        $page->addAcknowledgementOnService(
            '_Module_Meta',
            'meta_1',
            'Acceptance test.',
            true,
            true,
            true,
            false
        );
    }

    /**
     *  @Then the service is marked as acknowledged
     */
    public function theServiceIsMarkedAsAcknowledged()
    {
        $this->spin(function ($context) {
            $page = new MonitoringServicesPage($context);
            return $page->isServiceAcknowledged(
                'Centreon-Server',
                'AcceptanceTestService'
            );
        });
    }

    /**
     *  @Then the meta-service is marked as acknowledged
     */
    public function theMetaServiceIsMarkedAsAcknowledged()
    {
        $this->spin(function ($context) {
            $page = new MonitoringServicesPage($context);
            return $page->isServiceAcknowledged(
                '_Module_Meta',
                'meta_1'
            );
        });
    }
}
