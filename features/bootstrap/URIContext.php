<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Monitoring\MonitoringServicesPage;
use Centreon\Test\Behat\Monitoring\ServiceMonitoringDetailsPage;
use Centreon\Test\Behat\Monitoring\CommentMonitoringListingPage;
use Centreon\Test\Behat\Monitoring\CommentMonitoringPage;

class URIContext extends CentreonContext
{
    protected $page;
    protected $hostname = 'passiveHost';
    protected $serviceDescription = 'PassiveService';
    protected $checkOutput = 'https://github.com/centreon';

    /**
     * @Given a monitored passive service
     */
    public function aMonitoredPassiveService()
    {
        // Create host.
        $hostConfig = new HostConfigurationPage($this);
        $hostProperties = array(
            'name' => $this->hostname,
            'alias' => $this->hostname,
            'address' => 'localhost',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => "0",
            'passive_checks_enabled' => "1"
        );
        $hostConfig->setProperties($hostProperties);
        $hostConfig->save();

        // Create service.
        $serviceConfig = new ServiceConfigurationPage($this);
        $serviceProperties = array(
            'description' => $this->serviceDescription,
            'hosts' => $this->hostname,
            'templates' => 'generic-service',
            'check_command' => 'check_centreon_dummy',
            'check_period' => '24x7',
            'active_checks_enabled' => "0",
            'passive_checks_enabled' => "1"
        );
        $serviceConfig->setProperties($serviceProperties);
        $serviceConfig->save();

        // Ensure service is monitored.
        $this->restartAllPollers();
    }

    /**
     * @Given a plugin output which contains an URI
     */
    public function aPluginOutputWhichContainsAnURI()
    {
        $this->aMonitoredPassiveService();

        $this->submitServiceResult($this->hostname, $this->serviceDescription, 2, $this->checkOutput);
    }

    /**
     * @Given a comment which contains an URI
     */
    public function aCommentWhichContainsAnURI()
    {
        $this->aMonitoredPassiveService();

        $this->page = new CommentMonitoringPage($this);
        $this->page->setProperties(array(
            'type' => CommentMonitoringPage::TYPE_HOST,
            'host' => $this->hostname,
            'comment' => $this->checkOutput
        ));
        $this->page->save();

        $this->spin(
            function ($context) {
                $context->page = new CommentMonitoringListingPage($context);
                $context->page->getEntry("HOST;" . $this->hostname . ";1");
                return true;
            },
            'Comment is not applied.',
            20
        );
    }

    /**
     * @When I click on the link in the service output
     */
    public function iClickOnTheLinkInTheServiceOutput()
    {
        $this->spin(
            function ($context) {
                $page = new ServiceMonitoringDetailsPage($context, $context->hostname, $context->serviceDescription);
                $context->assertFind('css', 'table.ListTable td.ListColNoWrap.containsURI a')->click();
                return true;
            },
            'Cannot find link in service output',
            20
        );
    }

    /**
     * @When I click on the link in the comment
     */
    public function iClickOnTheLinkInTheComment()
    {
        $this->spin(
            function ($context) {
                $page = new CommentMonitoringListingPage($context);
                $context->assertFind('css', 'table.ListTable td.ListColNoWrap.containsURI a')->click();
                return true;
            },
            'Cannot find link in service output',
            20
        );
    }

    /**
     * @Then a new tab is open to the link
     */
    public function aNewTabIsOpenToTheLink()
    {
        $this->spin(
            function ($context) {
                $windowNames = $context->getSession()->getWindowNames();
                return count($windowNames) > 1;
            },
            'Link tab is not opened.',
            20
        );

        $windowNames = $this->getSession()->getWindowNames();
        $this->getSession()->switchToWindow($windowNames[1]);
        $openUrl = urldecode($this->getSession()->getCurrentUrl());
        if ($openUrl != $this->checkOutput) {
            throw new \Exception('Link is not correctly open (open tab is ' . $openUrl . ')');
        }
    }
}
