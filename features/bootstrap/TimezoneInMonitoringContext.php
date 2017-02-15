<?php

use Centreon\Test\Behat\HostMonitoringDetailsPage;
use Centreon\Test\Behat\CentreonContext;

/**
 * Defines application features from the specific context.
 */
class TimezoneInMonitoringContext extends CentreonContext
{
    private $page;

    /**
     *  @Given a host
     */
    public function aHost()
    {
        // Centreon-Server will do for this test.
    }

    /**
     *  @When I open the host monitoring details page
     */
    public function iOpenTheHostMonitoringDetailsPage()
    {
        $this->page = new HostMonitoringDetailsPage($this, 'Centreon-Server');
        $this->page->switchTab(HostMonitoringDetailsPage::HOST_INFORMATIONS_TAB);
    }

    /**
     *  @Then the timezone of this host is displayed
     */
    public function ThenTheTimezoneOfThisHostIsDisplayed()
    {
        $properties = $this->page->getProperties();
        if ($properties['timezone'] != 'Europe/Paris') {
            throw new \Exception(
                'Timezone is not displayed: got ' .
                $properties['timezone'] . ', expected Europe/Paris.'
            );
        }
    }
}
