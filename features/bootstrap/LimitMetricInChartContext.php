<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\ServiceConfigurationPage;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\MonitoringServicesPage;
use Centreon\Test\Behat\ServiceMonitoringDetailsPage;

class LimitMetricInChartContext extends CentreonContext
{
    private $hostName = 'LimitMetricInChartTestHost';
    private $serviceName = 'LimitMetricInChartTestService';
  
    /**
     * @Given a service with several metrics
     */
    public function aServiceWithSeveralMetrics()
    {
        $hostConfig = new HostConfigurationPage($this);
        $hostProperties = array(
            'name' => $this->hostName,
            'alias' => $this->hostName,
            'address' => 'localhost',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => "0",
            'passive_checks_enabled' => "1"
        );
        $hostConfig->setProperties($hostProperties);
        $hostConfig->save();

        $serviceConfig = new ServiceConfigurationPage($this);
        $serviceProperties = array(
            'description' => $this->serviceName,
            'hosts' => $this->hostName,
            'templates' => 'generic-service',
            'check_command' => 'check_centreon_dummy',
            'check_period' => '24x7',
            'active_checks_enabled' => "0",
            'passive_checks_enabled' => "1"
        );
        $serviceConfig->setProperties($serviceProperties);
        $serviceConfig->save();
        
        $this->restartAllPollers();
        
        sleep(5);
        $this->submitServiceResult($this->hostName, $this->serviceName, 'OK', '', 'test=1s;5;10;0;10;5;10;0;10;5;10;0;10;5;10;0;10');
        sleep(5);
    }
    
    /**
     * @When i display the chart in the popin
     */
    public function iDisplayTheChartInThePopin()
    {
        
    }
    
    /**
     * @When i display the chart in performance page
     */
    public function iDisplayTheChartInPerformancePage()
    {
        
    }
    
    /**
     * @When i display the chart in service details page
     */
    public function iDisplayTheChartInServiceDetailsPage()
    {
        $serviceMonitoringDetail = new ServiceMonitoringDetailsPage($this, $this->hostName, $this->serviceName);
        $serviceMonitoringDetail->find();
    }
    
    /**
     * @Then a message says that the chart will not be displayed
     */
    public function aMessageSaisThatTheChartWillNotBeDisplayed()
    {
        
    }
    
    /**
     * @Then a message says that the chart will not be displayed and a button is available to display the chart
     */
    public function aMessageSaisThatTheChartWillNotBeDisplayedAndAButtonIsAvailableToDisplayTheChart()
    {
        
    }
}
