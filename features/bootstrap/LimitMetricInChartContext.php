<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Monitoring\GraphMonitoringPage;

class LimitMetricInChartContext extends CentreonContext
{
    private $hostName = 'MetricTestHostname';
    private $serviceName = 'MetricTestService';
    private $chartPage = null;


    /**
     * @When I display the chart in performance page
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
     * @Then a message says that the chart will not be displayed
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
        if ($message != "Too many metrics, the chart can't be displayed") {
            throw new \Exception('Message which says "too many metrics" does not exist');
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
