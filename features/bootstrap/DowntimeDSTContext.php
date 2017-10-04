<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\DowntimeConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Configuration\CurrentUserConfigurationPage;
use Centreon\Test\Behat\Configuration\DowntimeConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\Configuration\RecurrentDowntimeConfigurationPage;

/**
 * Defines application features from the specific context.
 */
class DowntimeDSTContext extends CentreonContext
{
    protected $page;
    protected $host = 'Centreon-Server';
    protected $service = 'downtimeService';
    protected $downtimeProperties;

    private function setDowntime()
    {
        $this->page = new RecurrentDowntimeConfigurationPage($this);

        $this->page->setProperties(array(
            'name' => 'test',
            'alias' => $this->service,
            'days' => array(7, 1, 2, 3, 4, 5, 6),
            'start' => $this->downtimeProperties['start_time'],
            'end' => $this->downtimeProperties['end_time'],
            'svc_relation' => $this->host . ' - ' . $this->service
        ));

        $this->page->save();
    }

    /**
     * @Given a passive service is monitored
     */
    public function aPassiveServiceIsMonitored()
    {
        $page = new ServiceConfigurationPage($this);
        $page->setProperties(array(
            'hosts' => $this->host,
            'description' => $this->service,
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

        $this->reloadAllPollers();
        $this->submitServiceResult($this->host, $this->service, 0, __FUNCTION__);
    }

    /**
     * @Given a recurrent downtime starting on summer changing time
     */
    public function aRecurrentDowntimeStartingOnSummerChangingTime()
    {
        // on Europe/Paris at 2AM, we jump to 3AM
        $this->downtimeProperties = array(
            'start_time' => '02:30',
            'end_time' => '03:30',
            'expected_start' => '2021-03-28 03:00',
            'expected_end' => '2021-03-28 03:30',
            'expected_duration' => '1800', // 30m
            'faketime' => '2021-03-28 01:56:00'
        );

        $this->setDowntime();
    }

    /**
     * @Given a recurrent downtime ending on summer changing time
     */
    public function aRecurrentDowntimeEndingOnSummerChangingTime()
    {
        // on Europe/Paris at 2AM, we jump to 3AM
        $this->downtimeProperties = array(
            'start_time' => '01:30',
            'end_time' => '02:30',
            'expected_start' => '2021-03-28 01:30',
            'expected_end' => '2021-03-28 03:00',
            'expected_duration' => '1800', // 30m
            'faketime' => '2021-03-28 01:26:00'
        );

        $this->setDowntime();
    }

    /**
     * @Given a recurrent downtime starting and ending on summer changing time
     */
    public function aRecurrentDowntimeStartingAndEndingOnSummerChangingTime()
    {
        // on Europe/Paris at 2AM, we jump to 3AM
        $this->downtimeProperties = array(
            'start_time' => '02:03',
            'end_time' => '02:33',
            'expected_start' => '',
            'expected_end' => '',
            'expected_duration' => '0',
            'faketime' => '2021-03-28 01:58:00'
        );

        $this->setDowntime();
    }

    /**
     * @Given a recurrent downtime during all day on summer changing date
     */
    public function aRecurrentDowntimeDuringAllDayOnSummerChangingDate()
    {
        // on Europe/Paris at 2AM, we jump to 3AM
        $this->downtimeProperties = array(
            'start_time' => '00:00',
            'end_time' => '24:00',
            'expected_start' => '2021-03-28 00:00',
            'expected_end' => '2021-03-29 00:00',
            'expected_duration' => '82800', // 23h
            'faketime' => '2021-03-27 23:56:00'
        );

        $this->setDowntime();
    }

    /**
     * @Given a recurrent downtime during all day on summer changing date is scheduled
     */
    public function aRecurrentDowntimeDuringAllDayOnSummerChangingDateIsScheduled()
    {
        $this->aRecurrentDowntimeDuringAllDayOnSummerChangingDate();
        $this->downtimeIsApproaching();
        $this->theDowntimeIsScheduled();
    }

    /**
     * @Given a recurrent downtime of next day of summer changing date
     */
    public function aRecurrentDowntimeOfNextDayOfSummerChangingDate()
    {
        $this->downtimeProperties = array(
            'start_time' => '00:00',
            'end_time' => '24:00',
            'expected_start' => '2021-03-29 00:00',
            'expected_end' => '2021-03-30 00:00',
            'expected_duration' => '86400', // 24h
            'faketime' => '2021-03-28 23:58:00'
        );

        $this->setDowntime();
    }

    /**
     * @Given a recurrent downtime starting on winter changing time
     */
    public function aRecurrentDowntimeStartingOnWinterChangingDate()
    {
        // on Europe/Paris at 3AM, backward to 2AM
        $this->downtimeProperties = array(
            'start_time' => '02:03',
            'end_time' => '03:33',
            'expected_start' => '2021-10-31 02:03',
            'expected_end' => '2021-10-31 03:33',
            'expected_duration' => '5400', // 1h30
            'faketime' => '2021-10-31 01:58:00'
        );

        $this->setDowntime();
    }

    /**
     * @Given a recurrent downtime ending on winter changing time
     */
    public function aRecurrentDowntimeEndingOnWinterChangingDate()
    {
        // on Europe/Paris at 3AM, backward to 2AM
        $this->downtimeProperties = array(
            'start_time' => '01:00',
            'end_time' => '02:30',
            'expected_start' => '2021-10-31 01:00',
            'expected_end' => '2021-10-31 02:30',
            'expected_duration' => '9000', // 2h30
            'faketime' => '2021-10-31 00:58:00'
        );

        $this->setDowntime();
    }

    /**
     * @Given a recurrent downtime starting and ending on winter changing time
     */
    public function aRecurrentDowntimeStartingAndEndingOnWinterChangingDate()
    {
        // on Europe/Paris at 3AM, backward to 2AM
        $this->downtimeProperties = array(
            'start_time' => '02:03',
            'end_time' => '02:33',
            'expected_start' => '2021-10-31 02:03',
            'expected_end' => '2021-10-31 02:33',
            'expected_duration' => '1800', // 30m
            'faketime' => '2021-10-31 01:58:00'
        );

        $this->setDowntime();
    }

    /**
     * @Given a recurrent downtime during all day on winter changing date
     */
    public function aRecurrentDowntimeDuringAllDayOnWinterChangingDate()
    {
        // on Europe/Paris at 3AM, backward to 2AM
        $this->downtimeProperties = array(
            'start_time' => '00:00',
            'end_time' => '24:00',
            'expected_start' => '2021-10-31 00:00',
            'expected_end' => '2021-11-01 00:00',
            'expected_duration' => '90000', // 25h
            'faketime' => '2021-10-30 23:58:00'
        );

        $this->setDowntime();
    }

    /**
     * @Given a recurrent downtime during all day on winter changing date is scheduled
     */
    public function aRecurrentDowntimeDuringAllDayOnWinterChangingDateIsScheduled()
    {
        $this->aRecurrentDowntimeDuringAllDayOnWinterChangingDate();
        $this->downtimeIsApproaching();
        $this->theDowntimeIsScheduled();
    }

    /**
     * @Given a recurrent downtime of next day of winter changing date
     */
    public function aRecurrentDowntimeOfNextDayOfWinterChangingDate()
    {
        $this->downtimeProperties = array(
            'start_time' => '00:00',
            'end_time' => '24:00',
            'expected_start' => '2021-11-01 00:00',
            'expected_end' => '2021-11-02 00:00',
            'expected_duration' => '86400', // 24h
            'faketime' => '2021-10-31 23:58:00'
        );

        $this->setDowntime();
    }

    /**
     * @When downtime is approaching
     */
    public function downtimeIsApproaching()
    {
        $this->container->execute(
            "faketime '" . $this->downtimeProperties['faketime'] . "'" .
            " php /usr/share/centreon/cron/downtimeManager.php",
            'web'
        );
    }

    /**
     * @Then the downtime is scheduled
     */
    public function theDowntimeIsScheduled()
    {
        $this->spin(
            function ($context) {
                $scheduled = false;
                $return = $context->container->execute(
                    "cat /var/log/centreon-engine/centengine.log",
                    'web'
                );
                $output = $return['output'];
                if (
                    preg_match_all(
                        '/SCHEDULE_SVC_DOWNTIME;' . $this->host . ';' . $this->service . ';(\d+);(\d+);.+/',
                        $output,
                        $matches
                    )
                ) {
                    $startTimestamp = end($matches[1]);
                    $endTimestamp = end($matches[2]);
                    $dateStart = new DateTime('now', new \DateTimeZone('Europe/Paris'));
                    $dateStart->setTimestamp($startTimestamp);
                    $dateEnd = new DateTime('now', new \DateTimeZone('Europe/Paris'));
                    $dateEnd->setTimestamp($endTimestamp);
                    if ($dateStart->format('Y-m-d H:i') == $this->downtimeProperties['expected_start'] &&
                        $dateEnd->format('Y-m-d H:i') == $this->downtimeProperties['expected_end'] &&
                        ($endTimestamp - $startTimestamp) == $this->downtimeProperties['expected_duration']) {
                        $scheduled = true;
                    }
                    $storageDb = $this->getStorageDatabase();
                    $res = $storageDb->query(
                        "SELECT downtime_id FROM downtimes " .
                        "WHERE start_time = " . $startTimestamp . " " .
                        "AND end_time = " . $endTimestamp
                    );
                    if (!$res->fetch()) {
                        $scheduled = false;
                    }
                }

                return $scheduled;
            },
            'Downtime is not scheduled',
            10
        );
    }

    /**
     * @Then the downtime is not scheduled
     */
    public function theDowntimeIsNotScheduled()
    {
        $this->spin(
            function ($context) {
                $scheduled = true;
                $return = $context->container->execute(
                    "cat /var/log/centreon-engine/centengine.log",
                    'web'
                );
                $output = $return['output'];
                if (
                preg_match(
                    '/SCHEDULE_SVC_DOWNTIME;' . $this->host . ';' . $this->service . ';(\d+);(\d+);.+/',
                    $output
                )
                ) {
                    $scheduled = false;
                }

                return $scheduled;
            },
            'Downtime is scheduled',
            10
        );
    }
}
