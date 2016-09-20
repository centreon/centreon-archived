<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\ServiceConfigurationPage;

/**
 * Defines application features from the specific context.
 */
class InfluxdbContext extends CentreonContext
{
  private $hostName = 'InfluxdbTestHost';
  private $serviceName = 'InlufxdbTestService';

    /**
     *  @Given I am logged in a Centreon server with Influxdb
     */
    public function aCentreonServerWithInfluxdb()
    {
        $this->launchCentreonWebContainer('web_influxdb');
        $this->iAmLoggedIn();
        $this->container->execute('influx -execute "create database metrics"', 'influxdb');
    }

    /**
     * @Given an Influxdb output is properly configured
     */
    public function anInfluxdbOutputIsProperlyConfigured()
    {
      $this->visit('main.php?p=60909&o=c&id=1');
      $this->assertFind('css', 'li#c4 > a:nth-child(1)')->click();
      file_put_contents('/tmp/test.png', $this->getSession()->getDriver()->getScreenshot());
      $this->assertFind('css', 'select#block_output')->selectOption('InfluxDB - Storage - InfluxDB');
      $this->assertFind('css', 'a#add_output')->click();
      sleep(5);
      $this->assertFind('named', array('id', 'output[4][name]'))->setValue('TestInfluxdb');
      $this->assertFind('named', array('id', 'output[4][db_host]'))->setValue('influxdb');
      $this->assertFind('css', 'input[name="output[4][db_port]"]')->setValue('8086');
      $this->assertFind('named', array('id', 'output[4][db_user]'))->setValue('root');
      $this->assertFind('named', array('id', 'output[4][db_name]'))->setValue('metrics');
      $this->assertFind('named', array('id', 'output[4][metrics_timeseries]'))->setValue('metric.$HOST$.$SERVICE$');
      $this->assertFind('named', array('id', 'output[4][status_timeseries]'))->setValue('status.$HOST$.$SERVICE$');


      // Metrics columns
      $this->assertFind('css', '#metrics_column___4_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(2) > input:nth-child(1)')->setValue('true');
      $this->assertFind('css', '#metrics_column___4_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)')->setValue('$METRICID$');
      $this->assertFind('css', '#metrics_column___4_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)')->setValue('metric_id');
      $this->assertFind('named', array('id', 'metrics_column___4_add'))->click();
      sleep(1);
      $this->assertFind('css', '#metrics_column___4_template1 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)')->setValue('$TIME$');
      $this->assertFind('css', '#metrics_column___4_template1 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)')->setValue('time');
      $this->assertFind('named', array('id', 'metrics_column___4_add'))->click();
      sleep(1);
      $this->assertFind('css', '#metrics_column___4_template2 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)')->setValue('$VALUE$');
      $this->assertFind('css', '#metrics_column___4_template2 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)')->setValue('value');
      
      // Status columns
      $this->assertFind('css', '#status_column___4_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(2) > input:nth-child(1)')->setValue('true');
      $this->assertFind('css', '#status_column___4_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)')->setValue('$INDEXID$');
      $this->assertFind('css', '#status_column___4_template0 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)')->setValue('index_id');
      $this->assertFind('named', array('id', 'status_column___4_add'))->click();
      sleep(1);
      $this->assertFind('css', '#status_column___4_template1 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)')->setValue('$TIME$');
      $this->assertFind('css', '#status_column___4_template1 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)')->setValue('time');
      $this->assertFind('named', array('id', 'status_column___4_add'))->click();
      sleep(1);
      $this->assertFind('css', '#status_column___4_template2 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(3) > td:nth-child(2) > input:nth-child(1)')->setValue('$VALUE$');
      $this->assertFind('css', '#status_column___4_template2 > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(4) > td:nth-child(2) > input:nth-child(1)')->setValue('value');

      $this->assertFind('css', '#validForm > p:nth-child(1) > input:nth-child(1)')->click();
    }

    /**
     *  @Given a passive service is configured
     */
    public function aPassiveServiceIsConfigured() {
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
    }

    /**
     * @Given Broker and Engine are restarted
     */
    public function andBrokerAndEngineAreRestarted()
    {
        $this->restartAllPollers();
    }

    /**
     * @When new metric data is discovered by the engine for the service
     */
    public function whenNewMetricDataIsDiscoveredByTheEngine()
    {
      sleep(5);
      $this->submitServiceResult($this->hostName, $this->serviceName, 'OK', '', 'test=1s;5;10;0;10');
      sleep(5);
    }


    /**
     * @Then it is saved in Influxdb
     */
    public function thenItIsSavedInInfluxdb()
    {
      $this->spin(function($context) {
        $return = $context->container->execute('influx -database "metrics" -execute "SHOW SERIES"', 'influxdb');
        return preg_match('/status\.InfluxdbTestHost\.InlufxdbTestService/m', $return['output']);
      });

    }
}
