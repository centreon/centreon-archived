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
use Centreon\Test\Behat\GraphMonitoringPage;

class LimitMetricInChartContext extends CentreonContext
{
    private $hostName = 'LimitMetricInChartTestHost';
    private $serviceName = 'LimitMetricInChartTestService';
    private $chartPage = null;
  
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

        $perfdata = '';
        for ($i = 0; $i < 20 ; $i++) {
            $perfdata .= 'test' . $i . '=1s ';
        }
        
        sleep(5);
        $this->submitServiceResult($this->hostName, $this->serviceName, 'OK', 'OK', $perfdata);
        sleep(10);
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
        $this->chartPage = new GraphMonitoringPage($this);
        $this->chartPage->setFilterbyChart($this->hostName, $this->serviceName);
        sleep(3);

        if (!$this->chartPage->hasChart($this->hostName, $this->serviceName)) {
            throw new \Exception('Chart ' . $this->hostName . ' - ' . $this->serviceName . ' does not exist.');
        }
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
    public function aMessageSaysThatTheChartWillNotBeDisplayed()
    {
        $chart = $this->chartPage->getChart($this->hostName, $this->serviceName);
        $message = $this->assertFindIn($chart, 'css', '.c3-empty')->getText();
        if ($message != "Too much metrics, the chart can't be displayed") {
            throw new \Exception('Message which says "too much metrics" does not exist');
        }
    }

    /**
     * @Then a button is available to display the chart
     */
    public function aButtonIsAvailableToDisplayTheChart()
    {
        $chart = $this->chartPage->getChart($this->hostName, $this->serviceName);
        $this->assertFindButtonIn($chart, 'Display Chart');
    }
}
