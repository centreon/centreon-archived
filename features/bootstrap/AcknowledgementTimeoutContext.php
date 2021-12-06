<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Monitoring\MonitoringServicesPage;
use Centreon\Test\Behat\Monitoring\MonitoringHostsPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;

/**
 * Defines application features from the specific context.
 */
class AcknowledgementTimeoutContext extends CentreonContext
{
    private $hostName;
    private $serviceName;

    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'ExpireAckTestHost';
        $this->serviceHostName = 'Centreon-Server';
        $this->serviceName = 'ExpireAckTestService';
    }

    /**
     * @Given a host configured with acknowledgement expiration
     */
    public function aHostConfiguredWithAckExpiration()
    {
        $hostPage = new HostConfigurationPage($this);
        $hostPage->setProperties(array(
            'name' => $this->hostName,
            'alias' => $this->hostName,
            'address' => 'localhost',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1,
            'acknowledgement_timeout' => 1
        ));
        $hostPage->save();
        $this->restartAllPollers();
    }

    /**
     * @Given a service configured with acknowledgement expiration
     */
    public function aServiceConfiguredWithAckExpiration()
    {
        $servicePage = new ServiceConfigurationPage($this);
        $servicePage->setProperties(array(
            'hosts' => $this->serviceHostName,
            'description' => $this->serviceName,
            'templates' => 'generic-service',
            'check_command' => 'check_centreon_dummy',
            'check_period' => '24x7',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1,
            'acknowledgement_timeout' => 1
        ));
        $servicePage->save();
        $this->restartAllPollers();
    }

    /**
     * @Given the host is down
     */
    public function theHostIsDown()
    {
        $this->submitHostResult($this->hostName, 'DOWN');
        $hostName = $this->hostName;
        $this->spin(
            function ($ctx) use ($hostName) {
                return ((new MonitoringHostsPage($ctx))->getStatus($hostName)
                    == "DOWN");
            }
        );
    }

    /**
     * @Given the service is in a critical state
     */
    public function serviceInACriticalState()
    {
        $hostName = $this->serviceHostName;
        $serviceName = $this->serviceName;
        (new MonitoringServicesPage($this))->listServices();
        $this->spin(
            function ($ctx) use ($hostName, $serviceName) {
                try {
                    (new MonitoringServicesPage($ctx))->getStatus($hostName, $serviceName);
                    $found = true;
                } catch (\Exception $e) {
                    $found = false;
                }
                return $found;
            }
        );
        $this->submitServiceResult($hostName, $serviceName, 'CRITICAL');
        $this->spin(
            function ($ctx) use ($hostName, $serviceName) {
                $status = (new MonitoringServicesPage($ctx))->getStatus($hostName, $serviceName);
                return ($status == 'CRITICAL');
            }
        );
    }

    /**
     * @Given the host is acknowledged
     */
    public function hostAcknowledged()
    {
        $page = new MonitoringHostsPage($this);
        $url = 'http://' . $this->container->getHost() . ':' . $this->container->getPort(80, 'web') .
            '/centreon/include/monitoring/external_cmd/cmdPopup.php';
        $page->addAcknowledgementOnHost(
            $this->hostName,
            'Unit test',
            true,
            true,
            true,
            false,
            false,
            $url
        );
        $hostName = $this->hostName;
        $this->spin(
            function ($ctx) use ($hostName) {
                return ((new MonitoringHostsPage($ctx))->isHostAcknowledged($hostName));
            }
        );
    }

    /**
     * @Given the service is acknowledged
     */
    public function serviceAcknowledged()
    {
        $hostName = $this->serviceHostName;
        $serviceName = $this->serviceName;
        $page = new MonitoringServicesPage($this);
        $url = 'http://' . $this->container->getHost() . ':' . $this->container->getPort(80, 'web') .
            '/centreon/include/monitoring/external_cmd/cmdPopup.php';
        $page->addAcknowledgementOnService(
            $hostName,
            $serviceName,
            'Unit test',
            true,
            true,
            true,
            false,
            $url
        );
        $this->spin(
            function ($ctx) use ($hostName, $serviceName) {
                return ((new MonitoringServicesPage($ctx))->isServiceAcknowledged($hostName, $serviceName));
            }
        );
    }

    /**
     * @When I wait the time limit set for expiration
     */
    public function iWaitTheTimeLimitSetForExpiration()
    {
        $this->getSession()->wait(60000);
    }

    /**
     * @Then The host acknowledgement disappears
     */
    public function theHostAcknowledgementDisappears()
    {
        $hostName = $this->hostName;
        $this->spin(
            function ($ctx) use ($hostName) {
                return !(new MonitoringHostsPage($ctx))->isHostAcknowledged(
                    $hostName
                );
            }
        );
    }

    /**
     * @Then The service acknowledgement disappears
     */
    public function theServiceAcknowledgementDisappears()
    {
        $hostName = $this->serviceHostName;
        $serviceName = $this->serviceName;
        $this->spin(
            function ($ctx) use ($hostName, $serviceName) {
                return !(new MonitoringServicesPage($ctx))->isServiceAcknowledged(
                    $hostName,
                    $serviceName
                );
            }
        );
    }
}
