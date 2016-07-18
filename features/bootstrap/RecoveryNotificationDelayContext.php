<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\CommandConfigurationPage;
use Centreon\Test\Behat\ConfigurationPollersPage;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\ServiceConfigurationPage;

class RecoveryNotificationDelayContext extends CentreonContext
{
    private $hostName;
    private $serviceHostName;
    private $serviceName;

    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'RecoveryNotificationDelayTestHost';
        $this->serviceHostName = 'Centreon-Server';
        $this->serviceName = 'RecoveryNotificationDelayTestService';
    }

    /**
     *  @Given a host configured with recovery notification delay
     */
    public function aHostConfiguredWithRecoveryNotificationDelay()
    {
        // Create notification command.
        $this->createNotificationCommand();

        // Create host.
        $page = new HostConfigurationPage($this);
        $page->setProperties(array(
            'name' => $this->hostName,
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1,
            'recovery_notification_delay' => 1));
        $page->save();
        (new ConfigurationPollersPage($this))->restartEngine();
    }

    /**
     *  @Given a service configured with recovery notification delay
     */
    public function aServiceConfiguredWithRecoveryNotificationDelay()
    {
        // Create notification command.
        $this->createNotificationCommand();

        // Create service.
        $page = new ServiceConfigurationPage($this);
        $page->setProperties(array(
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
            'recovery_notification_delay' => 1));
        $page->save();
        (new ConfigurationPollersPage($this))->restartEngine();
    }

    /**
     *  @Given the host is not UP
     */
    public function theHostIsNotUp()
    {
        $this->submitHostResult($this->hostName, 2, __FUNCTION__);
    }

    /**
     *  @Given the service is not OK
     */
    public function theServiceIsNotOK()
    {
        $this->submitServiceResult($this->serviceHostName, $this->serviceName, 2, __FUNCTION__);
    }

    /**
     *  @When the host recovers before the recovery notification delay
     */
    public function theHostRecoversBeforeTheRecoveryNotificationDelay()
    {
        $this->submitHostResult($this->hostName, 0, __FUNCTION__);
    }

    /**
     *  @When the service recovers before the recovery notification delay
     */
    public function theServiceRecoversBeforeTheRecoveryNotificationDelay()
    {
        $this->submitServiceResult($this->serviceHostName, $this->serviceName, 0, __FUNCTION__);
    }

    /**
     *  @When the host receives a new check result after the recovery notification delay
     */
    public function theHostReceivesANewCheckResult()
    {
        sleep(60);
        $this->submitHostResult($this->hostName, 0, __FUNCTION__);
    }

    /**
     *  @When the service receives a new check result after the recovery notification delay
     */
    public function theServiceReceivesANewCheckResult()
    {
        sleep(60);
        $this->submitServiceResult($this->serviceHostName, $this->serviceName, 0, __FUNCTION__);
    }

    /**
     *  @Then no recovery notification is sent
     */
    public function noRecoveryNotificationIsSent()
    {
        $retval = $this->execute('ls /tmp/acceptance_notification.tmp', 'web', false);
        if ($retval['exit_code'] == 0) {
            throw new \Exception('Notification was sent out.');
        }
    }

    /**
     *  @Then a recovery notification is sent
     */
    public function aRecoveryNotificationIsSent()
    {
        $retval = $this->execute('ls /tmp/acceptance_notification.tmp', 'web', false);
        if ($retval['exit_code'] != 0) {
            throw new \Exception('No notification was sent out.');
        }
    }

    private function createNotificationCommand()
    {
        $page = new CommandConfigurationPage($this);
        $page->setProperties(array(
            'command_name' => 'acceptance_notification_command',
            'command_line' => '/bin/touch /tmp/acceptance_notification.tmp'));
        $page->save();
    }
}

?>
