<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\Configuration\CommandConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;

class FirstNotificationDelayContext extends CentreonContext
{
    private $hostName;
    private $serviceName;

    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'FirstNotificationDelayTestHost';
        $this->serviceName = 'FirstNotificationDelayTestService';
    }

    /**
     * @Given a host configured with first notification delay
     */
    public function aHostConfiguredWithFirstNotificationDelay()
    {
        // Create notification command.
        $this->createNotificationCommand();

        // Update notifications on admin contact
        $this->updateContactNotification();

        // Create host.
        $this->createHostWithFirstNotificationDelay();

        // Restart all pollers.
        $this->reloadAllPollers();
    }

    /**
     * @Given a service configured with first notification delay
     */
    public function aServiceConfiguredWithFirstNotificationDelay()
    {
        // Create notification command.
        $this->createNotificationCommand();

        // Update notifications on admin contact
        $this->updateContactNotification();

        // Create service.
        $this->createHostWithFirstNotificationDelay();
        $this->createServiceWithFirstNotificationDelay();

        // Restart all pollers.
        $this->reloadAllPollers();
    }

    /**
     * @Given the host is UP
     */
    public function theHostIsUp()
    {
        $this->execute('rm -f /tmp/acceptance_notification.tmp', 'web', false);
        $this->submitHostResult($this->hostName, 0, __FUNCTION__);
    }

    /**
     * @Given the host is not UP
     */
    public function theHostIsNotUp()
    {
        $this->execute('rm -f /tmp/acceptance_notification.tmp', 'web', false);
        $this->submitHostResult($this->hostName, 1, __FUNCTION__);
    }

    /**
     * @Given the service is OK
     */
    public function theServiceIsOK()
    {
        $this->execute('rm -f /tmp/acceptance_notification.tmp', 'web', false);
        $this->submitServiceResult($this->hostName, $this->serviceName, 0, __FUNCTION__);
    }

    /**
     * @Given the service is not OK
     */
    public function theServiceIsNotOK()
    {
        $this->execute('rm -f /tmp/acceptance_notification.tmp', 'web', false);
        $this->submitServiceResult($this->hostName, $this->serviceName, 2, __FUNCTION__);
    }

    /**
     * @When the host is still not UP before the first notification delay
     */
    public function theHostIsStillNotUPBeforeFirstNotificationDelay()
    {
        sleep(25);
        $this->submitHostResult($this->hostName, 1, __FUNCTION__);
    }

    /**
     * @When the host is still not UP after the first notification delay
     */
    public function theHostIsStillNotUPAfterFirstNotificationDelay()
    {
        sleep(65);
        $this->submitHostResult($this->hostName, 1, __FUNCTION__);
    }

    /**
     * @When the service is still not OK before the first notification delay
     */
    public function theServiceIsStillNotOKBeforeTheFirstNotificationDelay()
    {
        sleep(25);
        $this->submitServiceResult($this->hostName, $this->serviceName, 2, __FUNCTION__);
    }

    /**
     * @When the service is still not OK after the first notification delay
     */
    public function theServiceIsStillNotOKAfterTheFirstNotificationDelay()
    {
        sleep(65);
        $this->submitServiceResult($this->hostName, $this->serviceName, 2, __FUNCTION__);
    }

    /**
     * @Then no notification is sent
     */
    public function noNotificationIsSent()
    {
        sleep(10);
        $retval = $this->execute('ls /tmp/acceptance_notification.tmp 2>/dev/null', 'web', false);
        if ($retval['exit_code'] == 0) {
            throw new \Exception('Notification was sent out.');
        }
    }

    /**
     * @Then a notification is sent
     */
    public function aNotificationIsSent()
    {
        $this->spin(
            function ($context) {
              $retval = $context->execute('ls /tmp/acceptance_notification.tmp 2>/dev/null', 'web', false);
              return ($retval['exit_code'] == 0);
            },
            'error: No notification was sent out.',
            10
        );
    }

    private function createNotificationCommand()
    {
        $page = new CommandConfigurationPage($this, true, 1);
        $page->setProperties(array(
            'command_name' => 'acceptance_notification_command',
            'command_line' => 'touch /tmp/acceptance_notification.tmp'
        ));
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

    public function createHostWithFirstNotificationDelay()
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
            'first_notification_delay' => 1,
            'cs' => 'admin_admin'
        ));
        $page->save();
    }

    public function createServiceWithFirstNotificationDelay()
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
            'first_notification_delay' => 1,
            'recovery_notification_delay' => 0,
            'cs' => 'admin_admin'
        ));
        $page->save();
    }
}
