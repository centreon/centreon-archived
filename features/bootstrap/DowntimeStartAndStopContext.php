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
class DowntimeStartAndStopContext extends CentreonContext
{
    protected $host = 'Centreon-Server';
    protected $service = 'downtimeService';
    protected $downtimeStartTime;
    protected $downtimeEndTime;
    protected $downtimeDuration = 20;

    public function __construct()
    {
        parent::__construct();
        $this->downtimeStartTime = (new \DateTime('now', new \DateTimezone('Europe/Paris')))->format('H:i');
        $this->page = '';
        $this->dateStartTimestamp = '';
        $this->dateEndTimestamp = '';
        $this->timezone = '';
        $this->timezoneUser = '';
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

        $this->restartAllPollers();
        $this->submitServiceResult($this->host, $this->service, 0, __FUNCTION__);
    }

    /**
     * @Given a fixed downtime on a monitored element
     */
    public function aFixedDowntimeOnAMonitoredElement()
    {
        $page = new DowntimeConfigurationPage($this);
        $this->downtimeEndTime = (new \DateTime('+2 minutes', new \DateTimezone('Europe/Paris')))->format('H:i');
        $page->setProperties(array(
            'type' => DowntimeConfigurationPage::TYPE_SERVICE,
            'service' => $this->host . ' - ' . $this->service,
            'comment' => 'Acceptance test',
            'start_time' => $this->downtimeStartTime,
            'end_time' => $this->downtimeEndTime
        ));
        $page->save();
    }

    /**
     * @Given a flexible downtime on a monitored element
     */
    public function aFlexibleDowntimeOnAMonitoredElement()
    {
        $this->submitServiceResult($this->host, $this->service, 0, __FUNCTION__);

        $page = new DowntimeConfigurationPage($this);
        $this->downtimeEndTime = (new \DateTime('+2 minutes', new \DateTimezone('Europe/Paris')))->format('H:i');
        $page->setProperties(array(
            'type' => DowntimeConfigurationPage::TYPE_SERVICE,
            'service' => $this->host . ' - ' . $this->service,
            'comment' => 'Acceptance test',
            'fixed' => false,
            'duration' => $this->downtimeDuration,
            'start_time' => $this->downtimeStartTime,
            'end_time' => $this->downtimeEndTime
        ));
        $page->save();
    }

    /**
     * @Given the downtime is started
     */
    public function theDowntimeIsStarted()
    {
        $this->spin(
            function ($context) {
                $found = false;
                $page = new DowntimeConfigurationListingPage($context);
                foreach ($page->getEntries() as $entry) {
                    if ($entry['host'] == $context->host &&
                        $entry['service'] == $context->service &&
                        $entry['started'] == true
                    ) {
                        $found = true;
                    }
                }
                return $found;
            }
        );
    }

    /**
     * @Given the flexible downtime is started
     */
    public function theFlexibleDowntimeIsStarted()
    {
        $this->theMonitoredElementIsNotOk();
        $this->theDowntimePeriodIsStarted();
        $this->theDowntimeIsStarted();
    }

    /**
     * @Given the downtime period is started
     */
    public function theDowntimePeriodIsStarted()
    {
        $this->spin(
            function ($context) {
                $currentTime = (new \DateTime('now', new \DateTimezone('Europe/Paris')))->format('H:i');
                if ($currentTime >= $context->downtimeStartTime) {
                    return true;
                }
            },
            'The downtime period did not start (' . $this->downtimeStartTime . ').',
            80
        );
    }

    /**
     * @When the downtime duration is finished
     */
    public function theDowntimeDurationIsFinished()
    {
        sleep($this->downtimeDuration);
    }

    /**
     * @When the monitored element is not OK
     */
    public function theMonitoredElementIsNotOk()
    {
        $this->submitServiceResult($this->host, $this->service, 2, __FUNCTION__);
    }

    /**
     * @When the end date of the downtime happens
     */
    public function theEndDateOfTheDowntimeHappens()
    {
        $this->spin(
            function ($context) {
                $currentTime = (new \DateTime('now', new \DateTimezone('Europe/Paris')))->format('H:i');
                return $currentTime >= $context->downtimeEndTime;
            },
            'The end of the downtime is too late (' . $this->downtimeEndTime . ').',
            180 // 3 minutes for 2 minutes-long downtimes
        );
    }

    /**
     * @Then the downtime is stopped
     */
    public function theDowntimeIsStopped()
    {
        $this->spin(
            function ($context) {
                $found = false;
                $page = new DowntimeConfigurationListingPage($context);
                foreach ($page->getEntries() as $entry) {
                    if ($entry['host'] == $context->host && $entry['service'] == $context->service) {
                        $found = true;
                    }
                }
                return !$found;
            },
            'Downtime is still running.'
        );
    }

    /**
     * @Then the flexible downtime is stopped
     */
    public function theFlexibleDowntimeIsStopped()
    {
        $this->spin(
            function ($context) {
                $finished = false;

                $storageDb = $context->getStorageDatabase();
                $res = $storageDb->query(
                    'SELECT d.downtime_id, d.actual_end_time ' .
                    'FROM downtimes d, hosts h, services s ' .
                    'WHERE h.host_id = d.host_id ' .
                    'AND s.service_id = d.service_id ' .
                    'AND h.name = "' . $context->host . '" ' .
                    'AND s.description = "' . $context->service . '" ' .
                    'AND d.actual_end_time IS NOT NULL ' .
                    'AND d.actual_end_time < ' . time()
                );
                if ($row = $res->fetch()) {
                    $finished = true;
                }
                return $finished;
            },
            'FLexible downtime is still running.',
            30
        );
    }

    /**
     * @Given a downtime in configuration of a user in other timezone
     */
    public function aDowntimeInConfigurationOfAUserInOtherTimezone()
    {
        $this->timezoneUser = 'Asia/Tokyo';

        //user
        $user = new CurrentUserConfigurationPage($this);
        $user->setProperties(array(
            'location' => $this->timezoneUser
        ));
        $user->save();
        $this->iAmLoggedOut();
        $this->iAmLoggedIn();
        $this->reloadAllPollers();

        //downtime
        $this->page = new DowntimeConfigurationPage($this);
        $this->page->setProperties(array(
            'type' => DowntimeConfigurationPage::TYPE_SERVICE,
            'service' => $this->host . ' - ' . $this->service,
            'comment' => 'service comment'
        ));
        $props = $this->page->getProperties();

        //convert local start hour in timestamp utc
        $dataTimeStart = new DateTime(
            $props['start_day'] . ' ' . $props['start_time'],
            timezone_open($this->timezoneUser)
        );
        $dataTimeStart->format('Y/m/d H:i');
        $this->dateStartTimestamp = $dataTimeStart->getTimestamp();

        //convert local end hour in timestamp utc
        $dataTimeEnd = new DateTime($props['end_day'] . ' ' . $props['end_time'], timezone_open($this->timezoneUser));
        $dataTimeEnd->format('Y/m/d H:i');
        $this->dateEndTimestamp = $dataTimeEnd->getTimestamp();

        $this->downtimeDuration = $this->dateEndTimestamp - $this->dateStartTimestamp;
    }

    /**
     * @Given a recurrent downtime on an other timezone service
     */
    public function aRecurrentDowntimeOnService()
    {
        $this->timezone = 'Asia/Tokyo';
        $this->timezoneUser = 'Europe/Paris';
        $this->downtimeDuration = 240;


        $hostListingPage = new HostConfigurationListingPage($this);
        $hostPage = $hostListingPage->inspect($this->host);
        $hostPage->setProperties(array(
            'location' => $this->timezone
        ));
        $hostPage->save();
        $this->reloadAllPollers();


        //get the time of the timezone + x seconds for the start
        $datetimeStartLocal = new DateTime('now +120seconds', new DateTimeZone($this->timezone));
        $datetimeStartLocal->setTime($datetimeStartLocal->format('H'), $datetimeStartLocal->format('i'), '00');
        $datetimeEndLocal = new DateTime(
            'now +' . ($this->downtimeDuration + 120) . 'seconds',
            new DateTimeZone($this->timezone)
        );
        $datetimeEndLocal->setTime($datetimeEndLocal->format('H'), $datetimeEndLocal->format('i'), '00');


        //check if the downtime is on two days and add time
        if ($datetimeStartLocal->format('d') != $datetimeEndLocal->format('d')) {
            $datetimeStartLocal->add(new DateInterval('PT5M'));
            $datetimeEndLocal->add(new DateInterval('PT5M'));
        }

        $startHour = $datetimeStartLocal->format('H:i');
        $endHour = $datetimeEndLocal->format('H:i');

        //convert the local time to utc time
        $this->dateStartTimestamp = $datetimeStartLocal->getTimestamp();
        $this->dateEndTimestamp = $datetimeEndLocal->getTimestamp();

        //add recurent downtime
        $this->page = new RecurrentDowntimeConfigurationPage($this);

        //set downtime properties
        $this->page->setProperties(array(
            'name' => 'test',
            'alias' => $this->service,
            'days' => array(7, 1, 2, 3, 4, 5, 6),
            'start' => $startHour,
            'end' => $endHour,
            'svc_relation' => $this->host . ' - ' . $this->service
        ));

        $this->page->save();
    }

    /**
     * @When I save a downtime
     */
    public function iSaveADowntime()
    {
        $this->page->save();
    }

    /**
     * @When this one gives a downtime
     */
    public function thisOneGivesADowntime()
    {
        /* cron */
        $this->container->execute("php /usr/share/centreon/cron/downtimeManager.php", 'web');
    }

    /**
     * @Then the downtime start and end uses host timezone
     */
    public function theDowntimeUseTheTimezone()
    {
        $dataDowntime = array();
        $this->spin(
            function ($context) use (&$dataDowntime) {
                $listPage = new DowntimeConfigurationListingPage($context);
                $listPage->displayDowntimeCycle();
                $dataDowntime = $listPage->getEntries();
                if (count($dataDowntime)) {
                    return true;
                }
            }
        );

        //get the start and stop time ('Y-m-d H:i') of the downtime in user timezone
        $dateStart = $dataDowntime[0]['start'];
        $dateEnd = $dataDowntime[0]['end'];

        //convert the user timestamp to utc time
        $dataTimeStart = new DateTime($dateStart, new DateTimeZone($this->timezoneUser));
        $dateStartTimestamp = $dataTimeStart->getTimestamp();

        $dataTimeEnd = new DateTime($dateEnd, new DateTimeZone($this->timezoneUser));
        $dateEndTimestamp = $dataTimeEnd->getTimestamp();

        if ($this->dateStartTimestamp != $dateStartTimestamp) {
            throw new \Exception('Error bad timezone in start downtime configuration: ' .
                $this->dateStartTimestamp . ' != ' . $dateStartTimestamp);
        }

        if ($this->dateEndTimestamp != $dateEndTimestamp) {
            throw new \Exception('Error bad timezone in end downtime configuration: ' .
                $this->dateEndTimestamp . ' != ' . $dateEndTimestamp);
        }
    }
}
