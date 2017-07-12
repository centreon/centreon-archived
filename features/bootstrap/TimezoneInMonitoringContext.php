<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Monitoring\HostMonitoringDetailsPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Monitoring\MonitoringServicesPage;

/**
 * Defines application features from the specific context.
 */
class TimezoneInMonitoringContext extends CentreonContext
{
    private $page;
    private $hostname = 'acceptancetest';
    private $serviceName = 'Ping';
    private $timezone = 'Africa/Accra';

    /**
     * @Given a host
     */
    public function aHost()
    {
        $this->page = new HostConfigurationPage($this);
        $this->page->setProperties(array(
            'name' => $this->hostname,
            'alias' => $this->hostname,
            'address' => '127.0.0.1',
            'templates' => array('generic-host'),
            'location' => array($this->timezone)
        ));
        $this->page->save();
        $this->reloadAllPollers();
        $this->page = new MonitoringServicesPage($this);
        $this->spin(
            function ($context) {
                $context->page->scheduleImmediateCheckOnService($context->hostname, $context->serviceName);
                return true;
            },
            'Could not schedule check.'
        );
    }

    /**
     * @When I open the host monitoring details page
     */
    public function iOpenTheHostMonitoringDetailsPage()
    {
        $this->page = new HostMonitoringDetailsPage($this, $this->hostname);
    }

    /**
     * @Then the timezone of this host is displayed
     */
    public function thenTheTimezoneOfThisHostIsDisplayed()
    {
        $this->spin(
            function ($context) {
                $properties = $context->page->getProperties();
                if ($properties['timezone'] == 'Africa/Accra') {
                    return true;
                }
                new HostMonitoringDetailsPage($context, $context->hostname);
                return false;
            },
            'Wrong timezone displayed, expected ' . $this->timezone . '.'
        );
    }
}
