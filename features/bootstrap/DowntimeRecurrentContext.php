<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Configuration\DowntimeConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\RecurrentDowntimeConfigurationPage;

/**
 * Defines application features from the specific context.
 */
class DowntimeRecurrentContext extends CentreonContext
{
    protected $currentPage;
    protected $startDate;
    protected $endDate;

    protected $host = array(
        'name' => 'host',
        'alias' => 'host',
        'address' => 'host2@localhost',
        'check_command' => 'check_centreon_dummy',
        'location' => 'Europe/Paris'
    );

    protected $hostGroup = array(
        'name' => 'hostGroupName',
        'alias' => 'hostGroupAlias',
        'hosts' => 'host',
        'enabled' => 1
    );

    protected $service = array(
        'hosts' => 'host',
        'description' => 'service',
        'templates' => 'generic-service',
        'check_command' => 'check_centreon_dummy',
        'check_period' => '24x7',
        'max_check_attempts' => 1,
        'normal_check_interval' => 1,
        'retry_check_interval' => 1,
        'active_checks_enabled' => 1,
        'passive_checks_enabled' => 0,
        'notifications_enabled' => 1,
        'notify_on_recovery' => 1,
        'notify_on_critical' => 1,
        'recovery_notification_delay' => 1,
        'cs' => 'admin_admin'
    );

    /**
     * @Given a hostGroup is configured
     */
    public function aHostGroupIsConfigured()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setproperties($this->host);
        $this->currentPage->save();
        $this->currentPage = new HostGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->hostGroup);
        $this->currentPage->save();
        $this->currentPage = new ServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->service);
        $this->currentPage->save();
        $this->reloadAllPollers();
    }

    /**
     * @Given a recurrent downtime on a hostGroup
     */
    public function aRecurrentDowntime()
    {
        $this->startDate = new \DateTime('now', new \DateTimezone('Europe/Paris'));
        $this->endDate = new \DateTime('+360 minutes', new \DateTimezone('Europe/Paris'));

        //check if the downtime is on two days and add time
        if ($this->startDate->format('d') != $this->endDate->format('d')) {
            $endDateTest = '23:59';
        } else {
            $endDateTest = $this->endDate->format('H:i');
        }

        $this->currentPage = new RecurrentDowntimeConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => 'test_DT',
            'alias' => 'recurrent_DT',
            'days' => array(7, 1, 2, 3, 4, 5, 6),
            'start' => $this->startDate->format('H:i'),
            'end' => $endDateTest,
            'hostgroup_relation' => $this->hostGroup['name']
        ));
        $this->currentPage->save();
    }

    /**
     * @When this one gives a downtime
     */
    public function thisOneGivesADowntime()
    {
        /* faking cron's launchtime. 2 min sooner */
        $this->container->execute(
            "faketime -f '-120s' php /usr/share/centreon/cron/downtimeManager.php",
            'web'
        );
    }

    /**
     * @Then the recurrent downtime started
     */
    public function aRecurrentDowntimeIsStarted()
    {
        /* checking for results */
        $this->spin(
            function ($context) {
                $found = false;
                $this->currentPage = new DowntimeConfigurationListingPage($context);
                $this->currentPage->displayDowntimeCycle();
                foreach ($this->currentPage->getEntries() as $entry) {
                    if ($entry['host'] == $context->host['name'] &&
                        $entry['service'] == $context->service['description'] &&
                        $entry['started'] == true
                    ) {
                        $found = true;
                    }
                }
                return $found;
            }
        );
    }
}
