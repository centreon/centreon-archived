<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\DowntimeConfigurationPage;
use Centreon\Test\Behat\CurrentUserConfigurationPage;
use Centreon\Test\Behat\DowntimeConfigurationListingPage;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\ServiceConfigurationPage;
use Centreon\Test\Behat\ServiceDowntimeConfigurationPage;

/**
 * Defines application features from the specific context.
 */
class DowntimeStartAndStopContext extends CentreonContext
{
    public function __construct()
    {
        parent::__construct();
        $this->page = '';
        $this->host = '';
        $this->service = '';
        $this->dateStartUtc = '';
        $this->dateEndUtc = '';
        $this->duration = '';
        $this->timezone = '';
        $this->timezoneUser = '';
    }

    /**
     * @Given a downtime in configuration of a user in other timezone
     */
    public function aDowntimeInConfigurationOfAUserInOtherTimezone()
    {
        $this->host = 'Centreon-Server';
        $this->service = 'Memory';
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

        $this->duration = $this->dateEndUtc - $this->dateStartUtc;
    }

    /**
     * @Given a recurrent downtime on an other timezone service
     */
    public function aRecurrentDowntimeOnService()
    {
        $this->timezone = 'Asia/Magadan';
        $this->timezoneUser = 'Europe/Paris';
        $this->host = 'asia';
        $this->service = 'Tokyo';
        $this->duration = 240;

        //host with timezone
        $hostPage = new HostConfigurationPage($this);
        $hostPage->setProperties(array(
            'name' => $this->host,
            'alias' => $this->host,
            'address' => 'localhost',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'location' => $this->timezone,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1
        ));
        $hostPage->save();
        $this->restartAllPollers();

        //service of the host
        $servicePage = new ServiceConfigurationPage($this);
        $servicePage->setProperties(array(
            'hosts' => $this->host,
            'description' => $this->service,
            'templates' => 'generic-service',
            'check_command' => 'check_centreon_dummy',
            'check_period' => '24x7',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1
        ));
        $servicePage->save();
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
        $dateStartLocal += 1600;
        var_dump(date('Y-m-d H:i', $dateStartLocal));
        $dateEndLocal = $dateStartLocal + $this->duration;
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
        $this->dateEndUtc = $this->dateStartUtc + $this->duration;

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
