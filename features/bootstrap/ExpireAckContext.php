<?php
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\ConfigurationPollersPage;
use Centreon\Test\Behat\MonitoringServicesPage;
use Centreon\Test\Behat\MonitoringHostsPage;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\ServiceConfigurationPage;

/**
 * Defines application features from the specific context.
 */
class ExpireAckContext extends CentreonContext
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
        $hostPage->toHostCreationPage($this->hostName);
        $hostPage->switchToTab('Data Processing');
        $this->assertFindField('host_acknowledgement_timeout')->setValue(1);
        $hostPage->switchToTab('Host Configuration');
        $this->checkRadioButtonByValue('1', 'named', array('id_or_name', 'host_passive_checks_enabled[host_passive_checks_enabled]'));
        $this->checkRadioButtonByValue('0', 'named', array('id_or_name', 'host_active_checks_enabled[host_active_checks_enabled]'));
        $hostPage->saveHost();
        $this->configurationPollersPage->restartEngine();
    }

    /**
     * @Given a service configured with acknowledgement expiration
     */
    public function aServiceConfiguredWithAckExpiration()
    {
        $servicePage = new ServiceConfigurationPage($this);
        $servicePage->toServiceCreationPage($this->serviceHostName, $this->serviceName);
        $servicePage->switchToTab('Data Processing');
        $this->assertFindField('service_acknowledgement_timeout')->setValue(1);
        $servicePage->switchToTab('General Information');
        $this->checkRadioButtonByValue('1', 'named', array('id_or_name', 'service_passive_checks_enabled[service_passive_checks_enabled]'));
        $this->checkRadioButtonByValue('0', 'named', array('id_or_name', 'service_active_checks_enabled[service_active_checks_enabled]'));
        $servicePage->saveService();
        (new ConfigurationPollersPage($this))->restartEngine();
    }

    /**
     * @Given the host is down
     */
    public function theHostIsDown()
    {
        $this->submitHostResult($this->hostName, 'DOWN');
        $hostName = $this->hostName;
        $this->spin(function($ctx) use ($hostName) {
            return ((new MonitoringHostsPage($ctx))->getStatus($hostName)
                    == "DOWN");
        });
    }

    /**
     * @Given the service is in a critical state
     */
    public function serviceInACriticalState()
    {
        $hostName = $this->serviceHostName;
        $serviceName = $this->serviceName;
        (new MonitoringServicesPage($this))->listServices();
        $this->spin(function($ctx) use ($hostName, $serviceName) {
            try {
                (new MonitoringServicesPage($ctx))->getStatus($hostName, $serviceName);
                $found = TRUE;
            }
            catch (\Exception $e) {
                $found = FALSE;
            }
            return $found;
        });
        $this->submitServiceResult($hostName, $serviceName, 'CRITICAL');
        $this->spin(function($ctx) use ($hostName, $serviceName) {
            $status = (new MonitoringServicesPage($ctx))->getStatus($hostName, $serviceName);
            return ($status == 'CRITICAL');
        });
    }

    /**
     * @Given the host is acknowledged
     */
    public function hostAcknowledged()
    {
        $page = new MonitoringHostsPage($this);
        $page->addAcknowledgementOnHost(
          $this->hostName,
          'Unit test',
          true,
          true,
          true,
          false,
          false);
        $hostName = $this->hostName;
        $this->spin(function($ctx) use ($hostName) {
            return ((new MonitoringHostsPage($ctx))->isHostAcknowledged($hostName));
        });
    }

    /**
     * @Given the service is acknowledged
     */
    public function serviceAcknowledged()
    {
        $hostName = $this->serviceHostName;
        $serviceName = $this->serviceName;
        $page = new MonitoringServicesPage($this);
        $page->addAcknowledgementOnService(
            $hostName,
            $serviceName,
            'Unit test',
            true,
            true,
            true,
            false);
        $this->spin(function($ctx) use ($hostName, $serviceName) {
            return ((new MonitoringServicesPage($ctx))->isServiceAcknowledged($hostName, $serviceName));
        });
    }

    /**
     * @When I wait the time limit set for expiration
     */
    public function iWaitTheTimeLimitSetForExpiration()
    {
       $this->getSession()->wait(60000, '');
    }

    /**
     * @Then The host acknowledgement disappears
     */
    public function theHostAcknowledgementDisappears()
    {
       $hostName = $this->hostName;
       $this->spin(function($ctx) use ($hostName) {
         return !(new MonitoringHostsPage($ctx))->isHostAcknowledged(
           $hostName);
       },
       20);
    }


    /**
     * @Then The service acknowledgement disappears
     */
    public function theServiceAcknowledgementDisappears()
    {
       $hostName = $this->serviceHostName;
       $serviceName = $this->serviceName;
       $this->spin(function($ctx) use ($hostName, $serviceName) {
         return !(new MonitoringServicesPage($ctx))->isServiceAcknowledged(
           $hostName,
           $serviceName);
       },
       20);
    }
}
