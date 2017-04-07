<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Monitoring\ServiceMonitoringDetailsPage;
use Centreon\Test\Behat\Monitoring\GraphMonitoringPage;

class LimitMetricInChartContext extends CentreonContext
{
    private $hostName = 'LimitMetricInChartTestHost';
    private $serviceName = 'LimitMetricInChartTestService';
    private $chartPage = null;

    /**
     *  @Given a service with several metrics
     */
    public function aServiceWithSeveralMetrics()
    {
        // Create host.
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

        // Create service.
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

        // Ensure service is monitored.
        $this->restartAllPollers();
        sleep(7);

        // Send multiple perfdata.
        $perfdata = '';
        for ($i = 0; $i < 20 ; $i++) {
            $perfdata .= 'test' . $i . '=1s ';
        }
        $this->submitServiceResult($this->hostName, $this->serviceName, 'OK', 'OK', $perfdata);

        // Ensure perfdata were processed.
        $this->spin(
            function($context) {
                $page = new ServiceMonitoringDetailsPage(
                    $context,
                    $context->hostName,
                    $context->serviceName
                );
                $properties = $page->getProperties();
                if (count($properties['perfdata']) < 20) {
                    return false;
                }
                return true;
            },
            'Cannot get performance data of ' . $this->hostName . ' / ' . $this->serviceName
        );
    }

    /**
     *  @When I display the chart in performance page
     */
    public function iDisplayTheChartInPerformancePage()
    {
        $this->spin(
            function ($context) {
                $context->chartPage = new GraphMonitoringPage($context);
                $context->chartPage->setFilterbyChart($context->hostName, $context->serviceName);
                $context->spin(
                    function ($context) {
                        return $context->chartPage->hasChart(
                            $context->hostName,
                            $context->serviceName
                        );
                    },
                    'Chart does not exist.',
                    20
                );
                return true;
            },
            'Chart ' . $this->hostName . ' - ' . $this->serviceName . ' does not exist.'
        );
    }

    /**
     *  @Then a message says that the chart will not be displayed
     */
    public function aMessageSaysThatTheChartWillNotBeDisplayed()
    {
        $chart = $this->chartPage->getChart($this->hostName, $this->serviceName);
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '.c3-empty');
            }
        );
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
