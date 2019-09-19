<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Monitoring\MonitoringServicesPage;
use Centreon\Test\Behat\Monitoring\ServiceMonitoringDetailsPage;

class ServiceSubmitResultContext extends CentreonContext
{
    protected $page;
    protected $hostname = 'passiveHost';
    protected $hostservice = 'PassiveService';
    protected $checkoutput = 'Centreon test result';

    /**
     * @Given one passive service has been configured using arguments status and output exists
     */
    public function onePassiveServiceHasBeenConfiguredUsingArgumentsStatusAndOutputExists()
    {
        // Create host.
        $hostConfig = new HostConfigurationPage($this);
        $hostProperties = array(
            'name' => $this->hostname,
            'alias' => $this->hostname,
            'address' => 'localhost',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1
        );
        $hostConfig->setProperties($hostProperties);
        $hostConfig->save();

        // Create service.
        $serviceConfig = new ServiceConfigurationPage($this);
        $serviceProperties = array(
            'description' => $this->hostservice,
            'hosts' => $this->hostname,
            'templates' => 'generic-service',
            'check_command' => 'check_centreon_dummy',
            'check_period' => '24x7',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'is_volatile' => 0,
            'passive_checks_enabled' => 1
        );
        $serviceConfig->setProperties($serviceProperties);
        $serviceConfig->save();

        // Ensure service is monitored.
        $this->restartAllPollers();
    }

    /**
     * @When I submit some result to this service
     */
    public function iSubmitSomeResultToThisService()
    {

        $this->submitServiceResult($this->hostname, $this->hostservice, 2, $this->checkoutput);
    }

    /**
     * @Then the values are set as wanted in Monitoring > Status details page
     */
    public function theValuesAreSetAsWantedInMonitoringStatusDetailsPage()
    {
        try {
            $this->spin(
                function ($context) {
                    $this->page = new MonitoringServicesPage($this);
                    $result = $this->page->getPropertyFromAHostAndService(
                        $this->hostname,
                        $this->hostservice,
                        'status_information'
                    );
                    return ($result == $this->checkoutput);
                },
                "The result submitted is not set as wanted",
                15
            );
        } catch (\Exception $e) {
            throw new Exception('The result submitted is not set as wanted');
        }
    }
}
