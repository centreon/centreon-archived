<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\ContactConfigurationListingPage;
use Centreon\Test\Behat\CommandConfigurationPage;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\ServiceConfigurationPage;

class RecoveryNotificationDelayContext extends CentreonContext
{
    private $hostName;
    private $serviceName;

    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'RecoveryNotificationDelayTestHost';
        $this->serviceName = 'RecoveryNotificationDelayTestService';
    }

    /**
     *  @Given a host configured with recovery notification delay
     */
    public function aHostConfiguredWithRecoveryNotificationDelay()
    {
        // Create notification command.
        $this->createNotificationCommand();

        // Update notifications on admin contact
        $this->updateContactNotification();

        // Create host.
        $this->createHostWithRecoveryDelay();

        // Restart all pollers.
        $this->restartAllPollers();
    }

    /**
     *  @Given a service configured with recovery notification delay
     */
    public function aServiceConfiguredWithRecoveryNotificationDelay()
    {
        // Create notification command.
        $this->createNotificationCommand();

        // Update notifications on admin contact
        $this->updateContactNotification();

        // Create service.
        $this->createHostWithRecoveryDelay();
        $this->createServiceWithRecoveryDelay();

        // Restart all pollers.
        $this->restartAllPollers();
    }

    /**
     *  @Given the host is UP
     */
    public function theHostIsUp()
    {
        $this->submitHostResult($this->hostName, 0, __FUNCTION__);
    }

    /**
     *  @Given the host is not UP
     */
    public function theHostIsNotUp()
    {
        $this->submitHostResult($this->hostName, 1, __FUNCTION__);
        sleep(5);
        $this->execute('rm -f /tmp/acceptance_notification.tmp', 'web', false);
    }

    /**
     *  @Given the service is not OK
     */
    public function theServiceIsNotOK()
    {
        $this->submitServiceResult($this->hostName, $this->serviceName, 2, __FUNCTION__);
        sleep(5);
        $this->execute('rm -f /tmp/acceptance_notification.tmp', 'web', false);
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
        $this->submitServiceResult($this->hostName, $this->serviceName, 0, __FUNCTION__);
    }

    /**
     *  @When the host receives a new check result after the recovery notification delay
     */
    public function theHostReceivesANewCheckResult()
    {
        sleep(65);
        $this->submitHostResult($this->hostName, 0, __FUNCTION__);
    }

    /**
     *  @When the service receives a new check result after the recovery notification delay
     */
    public function theServiceReceivesANewCheckResult()
    {
        sleep(65);
        $this->submitServiceResult($this->hostName, $this->serviceName, 0, __FUNCTION__);
    }

    /**
     *  @Then no recovery notification is sent
     */
    public function noRecoveryNotificationIsSent()
    {
        sleep(10);
        $retval = $this->execute('ls /tmp/acceptance_notification.tmp 2>/dev/null', 'web', false);
        if ($retval['exit_code'] == 0) {
            throw new \Exception('Notification was sent out.');
        }
    }

    /**
     *  @Then a recovery notification is sent
     */
    public function aRecoveryNotificationIsSent()
    {
        sleep(10);
        $retval = $this->execute('ls /tmp/acceptance_notification.tmp 2>/dev/null', 'web', false);
        if ($retval['exit_code'] != 0) {
            throw new \Exception('No notification was sent out.');
        }
    }

    private function createNotificationCommand()
    {
        $page = new CommandConfigurationPage($this, true, 1);
        $page->setProperties(array(
            'command_name' => 'acceptance_notification_command',
            'command_line' => 'touch /tmp/acceptance_notification.tmp'));
        $page->save();
    }

    private function updateContactNotification()
    {
        $page = new ContactConfigurationListingPage($this);
        $contact = $page->inspect('admin');
        $contact->setProperties(array(
            'notifications_enabled' => 1,
            'host_notify_on_recovery' => 1,
            'host_notify_on_down' => 1,
            'host_notification_command' => 'acceptance_notification_command',
            'service_notify_on_recovery' => 1,
            'service_notify_on_critical' => 1,
            'service_notification_command' => 'acceptance_notification_command'
        ));
        $contact->save();
    }

    public function createHostWithRecoveryDelay()
    {
        $page = new HostConfigurationPage($this);
        $page->setProperties(array(
            'name' => $this->hostName,
            'alias' => $this->hostName,
            'address' => 'localhost',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1,
            'notifications_enabled' => 1,
            'notify_on_recovery' => 1,
            'notify_on_down' => 1,
            'recovery_notification_delay' => 1,
            'cs' => 'admin_admin'
        ));
        $page->save();
    }

    public function createServiceWithRecoveryDelay()
    {
        $page = new ServiceConfigurationPage($this);
        $page->setProperties(array(
            'hosts' => $this->hostName,
            'description' => $this->serviceName,
            'templates' => 'generic-service',
            'check_command' => 'check_centreon_dummy',
            'check_period' => '24x7',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1,
            'notifications_enabled' => 1,
            'notify_on_recovery' => 1,
            'notify_on_critical' => 1,
            'recovery_notification_delay' => 1,
            'cs' => 'admin_admin'
        ));
        $page->save();
    }
}
