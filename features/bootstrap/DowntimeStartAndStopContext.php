<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\DowntimeConfigurationPage;
use Centreon\Test\Behat\CurrentUserConfigurationPage;
use Centreon\Test\Behat\DowntimeConfigurationListingPage;

//   echo date("Y m d H:i:s");
//   echo gmdate("Y m d H:i:s");
//   int mktime (H, i,s,n,j,Y)


/**
 * Defines application features from the specific context.
 */
class DowntimeStartAndStopContext extends CentreonContext
{
    protected $host = 'Centreon-Server';
    protected $service = 'Memory';
    protected $downtimeEndTime;

    public function __construct()
    {
        parent::__construct();
        $this->page = '';
        $this->ete = date('I');
        $this->dateStartLocal = '';
        $this->dateStartUtc = '';
        $this->dateEndLocal = '';
        $this->dateEndUtc = '';
        $this->duration = '';
    }

    /**
     * @Given a fixed downtime on a monitored element
     */
    public function aFixedDowntimeOnAMonitoredElement()
    {
        $this->restartAllPollers();

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
                    if ($entry['host'] == $this->host && $entry['service'] == $this->service) {
                        $found = true;
                    }
                }
                return $found;
            }
        );
    }

    /**
     * @When the end date of the downtime happens
     */
    public function theEndDateOfTheDowntimeHappens()
    {
        $this->spin(
            function() {
                if (date("H:i") == $this->downtimeEndTime) {
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
     * @Given a downtime in configuration of a user in london timezone
     */
    public function aDowntimeInConfigurationOfAUserInLondon()
    {
        //user
        $user = new CurrentUserConfigurationPage($this);
        $user->setProperties(array(
            'location' => 'Europe/London'
        ));
        $user->save();

        //downtime
        $this->dateStartUtc = mktime(gmdate('H'), gmdate('i'), 0, gmdate('m'), gmdate('d'), gmdate('Y'));
        $this->page = new DowntimeConfigurationPage($this);
        $this->page->setProperties(array(
            'type' => DowntimeConfigurationPage::TYPE_SERVICE,
            'service' => $this->service,
            'comment' => 'service comment'
        ));
        $props = $this->page->getProperties();
        $dataDay = explode('/', $props['start_day']);
        $dataTime = explode(':', $props['start_time']);
        $dateStartLondon = mktime($dataTime[0], $dataTime[1], 0, $dataDay[1], $dataDay[2], $dataDay[0]);

        $dataDayEnd = explode('/', $props['end_day']);
        $dataTimeEnd = explode(':', $props['end_time']);
        $dateEndLondon = mktime($dataTimeEnd[0], $dataTimeEnd[1], 0, $dataDayEnd[1], $dataDayEnd[2], $dataDayEnd[0]);
        $this->duration = $dateEndLondon - $dateStartLondon;
        $this->dateEndUtc = (int)$this->dateStartUtc + $this->duration;

        $dst = 0;
        if ($this->ete == 1) {
            $dst = 3600;
        }

        $dateStartLondon -= $dst;
        $dateEndLondon -= $dst;

        if ($this->dateStartUtc != $dateStartLondon) {
            throw new \Exception('Error bad timezone in start downtime configuration');
        }

        if ($this->dateEndUtc != $dateEndLondon) {
            throw new \Exception('Error bad timezone in end downtime configuration');
        }
    }


    /**
     * @Given a downtime in configuration
     */
    public function aDowntimeInConfiguration()
    {
        $this->page = new DowntimeConfigurationPage($this);
        $this->page->setProperties(array(
            'type' => DowntimeConfigurationPage::TYPE_SERVICE,
            'service' => $this->service,
            'duration' => 60,
            'comment' => 'service comment'
        ));
    }


    /**
     * @When I save a downtime
     */
    public function iSaveADowntime()
    {
        throw new Exception('777');
        $this->page->save();
        sleep(10000000000);
    }


    /**
     * @Then the time of the start and end of the downtime took into account the timezone of the supervised element
     */
    public function TheTimeUseTheTimezone()
    {
        sleep(5);
        $listePage = new DowntimeConfigurationListingPage($this);
        $dataDowntime = $listePage->getEntries();

        var_dump($dataDowntime);

        $dateStart = $dataDowntime['start'];
        $dateEnd = $dataDowntime['end'];

        var_dump($dateStart);
        var_dump($dateEnd);


        //dst
        $dst = 0;
        if ($this->ete == 1) {
            $dst = 3600;
        }

        $this->dateStartLocal -= $dst;
        $this->dateEndLocal -= $dst;


    }


    public function ParisTimestamp($heures, $minutes, $secondes, $mois, $jours, $annees)
    {
        //reference datetime
        date_default_timezone_set('Europe/Paris');
        return mktime($heures, $minutes, $secondes, $mois, $jours, $annees);
    }


    public function LondonTimestamp($heures, $minutes, $secondes, $mois, $jours, $annees)
    {
        //reference datetime
        date_default_timezone_set('Europe/London');
        return mktime($heures, $minutes, $secondes, $mois, $jours, $annees);
    }

}
