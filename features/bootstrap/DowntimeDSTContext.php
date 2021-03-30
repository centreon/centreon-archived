<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\DowntimeConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
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

    private function setRecurrentDowntime()
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

    private function setRealtimeDowntime()
    {
        $this->page = new DowntimeConfigurationPage($this);

        $this->page->setProperties(array(
            'type' => DowntimeConfigurationPage::TYPE_SERVICE,
            'service' => $this->host . ' - ' . $this->service,
            'comment' => 'Acceptance test',
            'start_day' => $this->downtimeProperties['start_day'],
            'start_time' => $this->downtimeProperties['start_time'],
            'end_day' => $this->downtimeProperties['end_day'],
            'end_time' => $this->downtimeProperties['end_time'],
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
        $this->waitServiceInMonitoring();
    }

    private function waitServiceInMonitoring()
    {
        $this->spin(
            function ($context) {
                $monitored = false;
                $storageDb = $context->getStorageDatabase();
                $res = $storageDb->query(
                    'SELECT s.service_id ' .
                    'FROM hosts h, services s ' .
                    'WHERE s.host_id = h.host_id ' .
                    'AND h.name = "' . $context->host . '" ' .
                    'AND s.description = "' . $context->service . '" '
                );
                if ($res->fetch()) {
                    $monitored = true;
                }
                return $monitored;
            },
            'Service ' . $this->host . ' / ' . $this->service . ' is not monitored.',
            30
        );
    }

    /**
     * @Given a downtime starting on summer changing time
     */
    public function aDowntimeStartingOnSummerChangingTime()
    {
        // on Europe/Paris at 2AM, we jump to 3AM
        $this->downtimeProperties = array(
            'start_day' => '03/30/2025',
            'start_time' => '02:30',
            'end_day' => '03/30/2025',
            'end_time' => '03:30',
            'expected_start' => '2025-03-30 03:00',
            'expected_end' => '2025-03-30 03:30',
            'expected_duration' => '1800', // 30m
            'faketime' => '2025-03-30 01:56:00'
        );
    }

    /**
     * @Given a downtime ending on summer changing time
     */
    public function aDowntimeEndingOnSummerChangingTime()
    {
        // on Europe/Paris at 2AM, we jump to 3AM
        $this->downtimeProperties = array(
            'start_day' => '03/30/2025',
            'start_time' => '01:30',
            'end_day' => '03/30/2025',
            'end_time' => '02:30',
            'expected_start' => '2025-03-30 01:30',
            'expected_end' => '2025-03-30 03:00',
            'expected_duration' => '1800', // 30m
            'faketime' => '2025-03-30 01:26:00'
        );
    }

    /**
     * @Given a downtime starting and ending on summer changing time
     */
    public function aDowntimeStartingAndEndingOnSummerChangingTime()
    {
        // on Europe/Paris at 2AM, we jump to 3AM
        $this->downtimeProperties = array(
            'start_day' => '03/30/2025',
            'start_time' => '02:03',
            'end_day' => '03/30/2025',
            'end_time' => '02:33',
            'expected_start' => '',
            'expected_end' => '',
            'expected_duration' => '0',
            'faketime' => '2025-03-30 01:58:00'
        );
    }

    /**
     * @Given a downtime during all day on summer changing date
     */
    public function aDowntimeDuringAllDayOnSummerChangingDate()
    {
        // on Europe/Paris at 2AM, we jump to 3AM
        $this->downtimeProperties = array(
            'start_day' => '03/30/2025',
            'start_time' => '00:00',
            'end_day' => '03/30/2025',
            'end_time' => '24:00',
            'expected_start' => '2025-03-30 00:00',
            'expected_end' => '2025-03-31 00:00',
            'expected_duration' => '82800', // 23h
            'faketime' => '2025-03-29 23:56:00'
        );
    }

    /**
     * @Given a downtime during all day on summer changing date is scheduled
     */
    public function aDowntimeDuringAllDayOnSummerChangingDateIsScheduled()
    {
        $this->aDowntimeDuringAllDayOnSummerChangingDate();
        $this->downtimeIsApplied('recurrent');
        $this->theDowntimeIsProperlyScheduled();
    }

    /**
     * @Given a downtime of next day of summer changing date
     */
    public function aDowntimeOfNextDayOfSummerChangingDate()
    {
        $this->downtimeProperties = array(
            'start_day' => '03/31/2025',
            'start_time' => '00:00',
            'end_day' => '03/31/2025',
            'end_time' => '24:00',
            'expected_start' => '2025-03-31 00:00',
            'expected_end' => '2025-04-01 00:00',
            'expected_duration' => '86400', // 24h
            'faketime' => '2025-03-30 23:58:00'
        );
    }

    /**
     * @Given a downtime starting on winter changing time
     */
    public function aDowntimeStartingOnWinterChangingDate()
    {
        // on Europe/Paris at 3AM, backward to 2AM
        $this->downtimeProperties = array(
            'start_day' => '10/26/2025',
            'start_time' => '02:03',
            'end_day' => '10/26/2025',
            'end_time' => '03:33',
            'expected_start' => '2025-10-26 02:03',
            'expected_end' => '2025-10-26 03:33',
            'expected_duration' => '9000', // 2h30
            'faketime' => '2025-10-26 01:58:00'
        );
    }

    /**
     * @Given a downtime ending on winter changing time
     */
    public function aDowntimeEndingOnWinterChangingDate()
    {
        // on Europe/Paris at 3AM, backward to 2AM
        $this->downtimeProperties = array(
            'start_day' => '10/26/2025',
            'start_time' => '01:00',
            'end_day' => '10/26/2025',
            'end_time' => '02:30',
            'expected_start' => '2025-10-26 01:00',
            'expected_end' => '2025-10-26 02:30',
            'expected_duration' => '9000', // 2h30
            'faketime' => '2025-10-26 00:58:00'
        );
    }

    /**
     * @Given a downtime starting and ending on winter changing time
     */
    public function aDowntimeStartingAndEndingOnWinterChangingDate()
    {
        // on Europe/Paris at 3AM, backward to 2AM
        $this->downtimeProperties = array(
            'start_day' => '10/26/2025',
            'start_time' => '02:03',
            'end_day' => '10/26/2025',
            'end_time' => '02:33',
            'expected_start' => '2025-10-26 02:03',
            'expected_end' => '2025-10-26 02:33',
            'expected_duration' => '5400', // 1h30
            'faketime' => '2025-10-26 01:58:00'
        );
    }

    /**
     * @Given a downtime during all day on winter changing date
     */
    public function aDowntimeDuringAllDayOnWinterChangingDate()
    {
        // on Europe/Paris at 3AM, backward to 2AM
        $this->downtimeProperties = array(
            'start_day' => '10/26/2025',
            'start_time' => '00:00',
            'end_day' => '10/26/2025',
            'end_time' => '24:00',
            'expected_start' => '2025-10-26 00:00',
            'expected_end' => '2025-10-27 00:00',
            'expected_duration' => '90000', // 25h
            'faketime' => '2025-10-25 23:58:00'
        );
    }

    /**
     * @Given a downtime during all day on winter changing date is scheduled
     */
    public function aDowntimeDuringAllDayOnWinterChangingDateIsScheduled()
    {
        $this->aDowntimeDuringAllDayOnWinterChangingDate();
        $this->downtimeIsApplied('recurrent');
        $this->theDowntimeIsProperlyScheduled();
    }

    /**
     * @Given a downtime of next day of winter changing date
     */
    public function aDowntimeOfNextDayOfWinterChangingDate()
    {
        $this->downtimeProperties = array(
            'start_day' => '10/27/2025',
            'start_time' => '00:00',
            'end_day' => '10/27/2025',
            'end_time' => '24:00',
            'expected_start' => '2025-10-27 00:00',
            'expected_end' => '2025-10-28 00:00',
            'expected_duration' => '86400', // 24h
            'faketime' => '2025-10-26 23:58:00'
        );
    }

    /**
     * @When :downtimeType downtime is applied
     */
    public function downtimeIsApplied($downtimeType)
    {
        if ($downtimeType == 'realtime') {
            $this->setRealtimeDowntime();
        } else {
            $this->setRecurrentDowntime();
            $this->container->execute(
                "faketime '" . $this->downtimeProperties['faketime'] . "'" .
                " php /usr/share/centreon/cron/downtimeManager.php",
                'web'
            );
        }
    }

    /**
     * @Then the downtime is properly scheduled
     */
    public function theDowntimeIsProperlyScheduled()
    {
        $this->spin(
            function ($context) {
                $return = $context->container->execute(
                    "cat /var/log/centreon-engine/centengine.log",
                    'web'
                );
                $output = $return['output'];
                if (preg_match_all(
                    '/SCHEDULE_SVC_DOWNTIME;' . $context->host . ';' . $context->service . ';(\d+);(\d+);.+/',
                    $output,
                    $matches
                )) {
                    $startTimestamp = (int)end($matches[1]);
                    $endTimestamp = (int)end($matches[2]);

                    $dateStart = new DateTime('now', new \DateTimeZone('Europe/Paris'));
                    $dateStart->setTimestamp($startTimestamp);
                    $dateEnd = new DateTime('now', new \DateTimeZone('Europe/Paris'));
                    $dateEnd->setTimestamp($endTimestamp);

                    if ($dateStart->format('Y-m-d H:i') != $context->downtimeProperties['expected_start'] ||
                        $dateEnd->format('Y-m-d H:i') != $context->downtimeProperties['expected_end'] ||
                        ($endTimestamp - $startTimestamp) != (int)$context->downtimeProperties['expected_duration']) {
                        throw new \Exception('Downtime external command parameters are wrong (start, end or duration)');
                    }
                    $storageDb = $context->getStorageDatabase();
                    $res = $storageDb->query(
                        "SELECT downtime_id FROM downtimes " .
                        "WHERE start_time = " . $startTimestamp . " " .
                        "AND end_time = " . $endTimestamp
                    );
                    if (!$res->fetch()) {
                        throw new \Exception('Downtime does not exist in storage database');
                    }
                } else {
                    throw new \Exception('Downtime external command does not exist in centengine logs');
                }

                return true;
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
                if (preg_match(
                    '/SCHEDULE_SVC_DOWNTIME;' . $this->host . ';' . $this->service . ';(\d+);(\d+);.+/',
                    $output
                )) {
                    $scheduled = false;
                }

                return $scheduled;
            },
            'Downtime is scheduled',
            10
        );
    }
}
