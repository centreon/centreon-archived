<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Monitoring\ServiceMonitoringDetailsPage;

/**
 * Defines application features from the specific context.
 */
class InfluxdbContext extends CentreonContext
{
    private $hostName = 'Centreon-Server';
    private $serviceName = 'InfluxdbTestService';

    /**
     * @Given I am logged in a Centreon server with InfluxDB
     */
    public function aCentreonServerWithInfluxDB()
    {
        $this->launchCentreonWebContainer('web_influxdb');
        $this->iAmLoggedIn();
        $this->container->execute('influx -execute "create database metrics"', 'influxdb');
    }

    /**
     * @Given Centreon Broker is configured to send data to an InfluxDB server
     */
    public function anInfluxdbOutputIsProperlyConfigured()
    {
        $this->visit('main.php?p=60909&o=c&id=1');
        $this->assertFind('css', 'li#c3 > a:nth-child(1)')->click();
        $this->assertFind('css', 'select#block_output')->selectOption('InfluxDB - Storage - InfluxDB');
        $this->assertFind('css', 'a#add_output')->click();
        sleep(5);
        $this->assertFind('named', array('id', 'output[3][name]'))->setValue('TestInfluxdb');
        $this->assertFind('named', array('id', 'output[3][db_host]'))->setValue('influxdb');
        $this->assertFind('css', 'input[name="output[3][db_port]"]')->setValue('8086');
        $this->assertFind('named', array('id', 'output[3][db_user]'))->setValue('root');
        $this->assertFind('named', array('id', 'output[3][db_name]'))->setValue('metrics');
        $this->assertFind('named', array('id', 'output[3][metrics_timeseries]'))->setValue('metric.$HOST$.$SERVICE$');
        $this->assertFind('named', array('id', 'output[3][status_timeseries]'))->setValue('status.$HOST$.$SERVICE$');

        // Metrics columns
        $this->assertFind(
            'css',
            '#metrics_column___3_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(1) > td:nth-child(2) input[value="true"]'
        )->getParent()->click();
        $this->assertFind(
            'css',
            '#metrics_column___3_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('$METRICID$');
        $this->assertFind(
            'css',
            '#metrics_column___3_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('metric_id');
        $this->assertFind('named', array('id', 'metrics_column___3_add'))->click();
        sleep(1);
        $this->assertFind(
            'css',
            '#metrics_column___3_template1 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('$TIME$');
        $this->assertFind(
            'css',
            '#metrics_column___3_template1 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('time');
        $this->assertFind('named', array('id', 'metrics_column___3_add'))->click();
        sleep(1);
        $this->assertFind(
            'css',
            '#metrics_column___3_template2 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('$VALUE$');
        $this->assertFind(
            'css',
            '#metrics_column___3_template2 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('value');

        // Status columns
        $this->assertFind(
            'css',
            '#status_column___3_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(1) > td:nth-child(2) input[value="true"]'
        )->getParent()->click();
        $this->assertFind(
            'css',
            '#status_column___3_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('$INDEXID$');
        $this->assertFind(
            'css',
            '#status_column___3_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('index_id');
        $this->assertFind('named', array('id', 'status_column___3_add'))->click();
        sleep(1);
        $this->assertFind(
            'css',
            '#status_column___3_template1 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('$TIME$');
        $this->assertFind(
            'css',
            '#status_column___3_template1 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('time');
        $this->assertFind('named', array('id', 'status_column___3_add'))->click();
        sleep(1);
        $this->assertFind(
            'css',
            '#status_column___3_template2 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > ' .
            'tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('$VALUE$');
        $this->assertFind(
            'css',
            '#status_column___3_template2 > td:nth-child(1) > table:nth-child(1) > ' .
            'tbody:nth-child(1) > tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)'
        )->setValue('value');

        $this->assertFind('css', '#validForm > p:nth-child(1) > input:nth-child(1)')->click();

        // Restart all pollers.
        $this->restartAllPollers();

        // Wait for the InfluxDB connection.
        $this->spin(
            function ($context) {
                $retval = $context->container->execute(
                    'cat /var/lib/centreon-broker/central-broker-master-stats.json',
                    'web',
                    false
                );
                if ($retval['exit_code'] === 0) {
                    $stats = json_decode($retval['output'], true);
                    return $stats['endpoint TestInfluxdb']['state'] == 'connected';
                }
                return false;
            },
            'Centreon Broker did not connect to InfluxDB.'
        );
    }

    /**
     * @Given a service is monitored by the Centreon platform
     */
    public function aServiceIsMonitoredByTheCentreonPlatform()
    {
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

        // Restart pollers.
        $this->restartAllPollers();

        // Wait for service monitoring data.
        $this->spin(
            function ($context) {
                $page = new ServiceMonitoringDetailsPage(
                    $context,
                    $context->hostName,
                    $context->serviceName
                );
                $properties = $page->getProperties();
                return !empty($properties['state']);
            }
        );
    }

    /**
     * @When new metric data is retrieved for the service
     */
    public function newMetricDataIsRetrievedForTheService()
    {
        $this->submitServiceResult($this->hostName, $this->serviceName, 'OK', 'OK', 'test=1s;5;10;0;10');

        $self = $this;
        $this->spin(
            function ($context) use ($self) {
                $page = new ServiceMonitoringDetailsPage($self, $self->hostName, $self->serviceName);
                $properties = $page->getProperties();
                if (!count($properties['perfdata'])) {
                    return false;
                }
                return true;
            },
            'Cannot get performance data of ' . $self->hostName . ' / ' . $self->serviceName . '.'
        );
    }

    /**
     * @Then it is saved in InfluxDB
     */
    public function thenItIsSavedInInfluxDB()
    {
        $self = $this;
        $this->spin(
            function ($context) use ($self) {
                $return = $context->container->execute('influx -database "metrics" -execute "SHOW SERIES"', 'influxdb');
                return preg_match('/status\.' . $self->hostName . '\.' . $self->serviceName . '/m', $return['output']);
            },
            "Cannot get metrics from InfluxDB."
        );
    }
}
