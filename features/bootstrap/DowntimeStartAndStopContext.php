<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\DowntimeConfigurationPage;
use Centreon\Test\Behat\ServiceConfigurationPage;
use Centreon\Test\Behat\CurrentUserConfigurationPage;
use Centreon\Test\Behat\DowntimeConfigurationListingPage;
use Centreon\Test\Behat\HostConfigurationListingPage;
use Centreon\Test\Behat\ServiceDowntimeConfigurationPage;


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
        $this->downtimeStartTime = date("H:i");
        $this->page = '';
        $this->dateStartUtc = '';
        $this->dateEndUtc = '';
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

        $downtimeEndTime = '+1 minutes';
        $currentSeconds = date("s");
        if ($currentSeconds >= 45) {
            $downtimeEndTime = '+2 minutes';
        }
        $this->downtimeEndTime = date("H:i", strtotime($downtimeEndTime));
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

        $downtimeEndTime = '+1 minutes';
        $currentSeconds = date("s");
        if ($currentSeconds >= 45) {
            $downtimeEndTime = '+2 minutes';
        }
        $this->downtimeEndTime = date("H:i", strtotime($downtimeEndTime));
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
            function() {
                $found = false;
                $page = new DowntimeConfigurationListingPage($this);
                foreach ($page->getEntries() as $entry) {
                    if ($entry['host'] == $this->host && $entry['service'] == $this->service && $entry['started'] == true) {
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
            function() {
                if (date("H:i") >= $this->downtimeStartTime) {
                    return true;
                }
            }, 80
            , 'The downtime period did not start (' . $this->downtimeStartTime . ').'
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
            function() {
                if (date("H:i") >= $this->downtimeEndTime) {
                    return true;
                }
            }, 80
            , 'The end of the downtime is too late (' . $this->downtimeEndTime . ').'
        );
    }

    /**
     * @Then the downtime is stopped
     */
    public function theDowntimeIsStopped()
    {
        $this->spin(
            function() {
                $found = false;
                $page = new DowntimeConfigurationListingPage($this);
                foreach ($page->getEntries() as $entry) {
                    if ($entry['host'] == $this->host && $entry['service'] == $this->service) {
                        $found = true;
                    }
                }
                return !$found;
            }, 20
            , 'Downtime is still running.'
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
            'location' => $this->timezone
        ));
        $user->save();
        $this->restartAllPollers();

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
        $this->dateStartUtc = $dataTimeStart->getTimestamp();

        //convert local end hour in timestamp utc
        $dataTimeEnd = new DateTime($props['end_day'] . ' ' . $props['end_time'], timezone_open($this->timezoneUser));
        $dataTimeEnd->format('Y/m/d H:i');
        $this->dateEndUtc = $dataTimeEnd->getTimestamp();

        $this->downtimeDuration = $this->dateEndUtc - $this->dateStartUtc;
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
        $this->restartAllPollers();


        //get the time of the timezone + x seconds for the start
        $datetime = new DateTime();
        $datetime->setTimezone(new DateTimeZone($this->timezone));
        $dateStartLocal = mktime(
            $datetime->format('H'),
            $datetime->format('i'),
            0,
            $datetime->format('m'),
            $datetime->format('d'),
            $datetime->format('Y')
        );
        //get start and end timestamp of the time in timezone
        $dateStartLocal += 120;
        var_dump(date('Y-m-d H:i', $dateStartLocal));
        $dateEndLocal = $dateStartLocal + $this->downtimeDuration;
        var_dump(date('Y-m-d H:i', $dateEndLocal));

        //check if the downtime is on two days and add time
        if (date('Y-m-d', $dateStartLocal) != date('Y-m-d', $dateEndLocal)) {
            $dateStartLocal +=300;
            $dateEndLocal +=300;
        }


        var_dump('888888888888888888888888888888888');
        var_dump(date('Y-m-d H:i', $dateStartLocal));
        var_dump(date('Y-m-d H:i', $dateEndLocal));

        //convert the local timestamp to utc time
        $dateStart = date('Y-m-d H:i', $dateStartLocal);
        $dataTimeStart = new DateTime($dateStart, timezone_open($this->timezone));
        $dataTimeStart->format('Y-m-d H:i');
        $this->dateStartUtc = $dataTimeStart->getTimestamp();
        $this->dateEndUtc = $this->dateStartUtc + $this->downtimeDuration;

        //add recurent downtime
        $this->page = new ServiceDowntimeConfigurationPage($this);

        //set downtime properties
        $this->page->setProperties(array(
            'name' => 'test',
            'alias' => $this->service,
            'periods' => array(7, 1, 2, 3, 4, 5, 6),
            'start' => date("H:i", $dateStartLocal),
            'end' => date("H:i", $dateEndLocal),
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
     * @Then the time of the start and end of the downtime took into account the timezone of the supervised element
     */
    public function theDowntimeUseTheTimezone()
    {
        $dataDowntime = array();
        $this->spin(
            function ($context) use (&$dataDowntime) {
                $listPage = new DowntimeConfigurationListingPage($this);
                $listPage->displayDowntimeCycle();
                $dataDowntime = $listPage->getEntries();
                if (count($dataDowntime)) {
                    return true;
                }
            },
            30
        );

        //get the start and stop time of the downtime in user timezone
        $dateStart = $dataDowntime[0]['start'];
        $dateEnd = $dataDowntime[0]['end'];

        //convert the user timestamp to utc time
        $dateStart = date('Y-m-d H:i', $dateStart);
        $dataTimeStart = new DateTime($dateStart, timezone_open($this->timezoneUser));
        $dataTimeStart->format('Y-m-d H:i');
        $dateStartUtc = $dataTimeStart->getTimestamp();

        $dateEnd = date('Y-m-d H:i', $dateEnd);
        $dataTimeEnd = new DateTime($dateEnd, timezone_open($this->timezoneUser));
        $dataTimeEnd->format('Y-m-d H:i');
        $dateEndUtc = $dataTimeEnd->getTimestamp();

        if ($this->dateStartUtc != $dateStartUtc) {
            throw new \Exception(
                'Error bad timezone in start downtime configuration:'.$this->dateStartUtc .'!='.$dateStartUtc
            );
        }

        if ($this->dateEndUtc != $dateEndUtc) {
            throw new \Exception(
                'Error bad timezone in end downtime configuration:'.$this->dateEndUtc .'!='.$dateEndUtc
            );
        }
    }
}
