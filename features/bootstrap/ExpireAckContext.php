<?php
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\ConfigurationPollersPage;
use Centreon\Test\Behat\MonitoringServicesPage;
use Centreon\Test\Behat\MonitoringHostsPage;

/**
 * Defines application features from the specific context.
 */
class ExpireAckContext extends CentreonContext
{
    private $hostName;
    private $serviceName;
    private $configurationPollersPage;
    private $monitoringServicesPage;
    private $monitoringHostsPage;
    
    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'ExpireAckTestHost';
        $this->serviceName = 'ExpireAckTestService';
        $this->configurationPollersPage = new ConfigurationPollersPage($this);
        $this->monitoringServicesPage = new MonitoringServicesPage($this);
        $this->monitoringHostsPage = new MonitoringHostsPage($this);
    }

    /**
     * @Given a host configured with expirations
     */
    public function aHostConfiguredWithExpirations()
    {
        $hostPage = $this->getHostServiceConfigurationPage();
        $hostPage->toHostCreationPage($this->hostName);
        $hostPage->switchToTab('Data Processing');
        $this->assertFind('named', array('name', 'host_acknowledgement_timeout'))->setValue(1);
        $this->checkRadioButton('Yes', 'named', array('name', 'host_passive_checks_enabled[host_passive_checks_enabled]'));
        $this->checkRadioButton('No', 'named', array('name', 'host_active_checks_enabled[host_active_checks_enabled]'));
        $hostPage->saveHost();
        $this->configurationPollersPage->restartEngine();
    }
    
    /**
     * @Given a service associated with this host
     */
    public function aServiceAssociatedWithThisHost()
    {
        $servicePage = $this->getServiceConfigurationPage();
        $servicePage->toServiceCreationPage($this->hostName, $this->serviceName);
        $this->checkRadioButton('Yes', 'named', array('name', 'service_passive_checks_enabled[service_passive_checks_enabled]'));
        $this->checkRadioButton('No', 'named', array('name', 'service_active_checks_enabled[service_active_checks_enabled]'));
        $servicePage->saveService();
        $this->configurationPollersPage->restartEngine();
    }
    
    /**
     * @Given the host is in a critical state
     */
    public function hostInACriticalState()
    {
        $this->submitHostResult($this->hostName, 'DOWN');
    }

    /**
     * @Given the service is in a critical state
     */
    public function serviceInACriticalState()
    {
        $this->submitServiceResult($this->hostName, $this->service_name, 'CRITICAL');
    }

    /**
     * @Given the host is acknowledged
     */
    public function hostAcknowledged()
    {
        $this->monitoringServicesPage->addAcknowledgementOnHost(
          $this->hostName,
          'Unit test',
          true,
          true,
          true,
          false,
          false);
    }
    
    /**
     * @Given the service is acknowledged
     */
    public function serviceAcknowledged()
    {
       $this->monitoringServicesPage->addAcknowledgementOnService(
         $this->hostName,
         $this->serviceName,
         'Unit test',
         true,
         true,
         true,
         false);
    }

    /**
     * @When I wait the time limit set for expirations
     */
    public function iWaitTheTimeLimitSetForExpirations()
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
         return (new MonitoringHostsPage($ctx))->isHostAcknowledged(
           $hostName);
       }, 20);
    }

    
    /**
     * @Then The service acknowledgement disappears
     */
    public function theServiceAcknowledgementDisappears()
    {
       $hostName = $this->hostName;
       $serviceName = $this->serviceName;
       $this->spin(function($ctx) use ($hostName, $serviceName) {
         return (new MonitoringServicesPage($ctx))->isServiceAcknowledged(
           $hostName,
           $serviceName);
       }, 20);
    }
}
