<?php


use Centreon\Test\Behat\HostMonitoringDetailPage;
use Centreon\Test\Behat\CentreonContext;

/**
 * Defines application features from the specific context.
 */
class TimezoneInMonitoringContext extends CentreonContext
{

    /**
     * @Given a host
     */
    public function aHost()
    {
        $this->restartAllPollers();
    }

    /**
     * @When I open the host detail in the monitoring page
     */
    public function iOpenTheHostDetailInTheMonitoringPage()
    {
        $page = new HostMonitoringDetailPage($this, 'Centreon-Server');
        $page->switchTab('informations');
    }

    /**
     * @Then the timezone of this host is displayed
     */
    public function ThenTheTimezoneOfThisHostIsDisplayed()
    {
        $timezone = $this->assertFind('css', '#tab3 tr:nth-child(16) td.ListColLeft span')->getText();
        if ($timezone == '') {
            throw new \Exception('Timezone does not display');
        }
    }

}
