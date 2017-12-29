<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;

class CommandArgumentsContext extends CentreonContext
{
    private $hostName;
    private $serviceName;
    private $currentPage;
    private $argumentField;

    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'CommandArgumentsTestHost';
        $this->serviceHostName = 'Centreon-Server';
        $this->serviceName = 'CommandArgumentsTestService';
    }

    /**
     * @Given a service being configured
     */
    public function aServiceBeingConfigured()
    {
        $this->currentPage = new ServiceConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'hosts' => $this->serviceHostName,
            'description' => $this->serviceName
        ));
    }

    /**
     * @When i select a check command
     */
    public function iSelectACheckCommand()
    {
        $this->currentPage->setProperties(array(
            'check_command' => 'check_centreon_dummy'
        ));
        sleep(2);
    }

    /**
     * @Then Arguments of this command are displayed for the service
     */
    public function argumentsOfThisCommandAreDisplayedForTheService()
    {
        $this->argumentField = $this->assertFind('css', 'input[name="ARG1"]');
    }

    /**
     * @Then Arguments of this command are displayed for the host
     */
    public function argumentsOfThisCommandAreDisplayedForTheHost()
    {
        $this->argumentField = $this->assertFind('css', 'input[name="command_command_id_arg1"]');
    }

    /**
     * @Then i can configure those arguments
     */
    public function iCanConfigureThoseArguments()
    {
        $this->argumentField = null;
    }

    /**
     * @Given a host being configured
     */
    public function aHostBeingConfigured()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => $this->hostName
        ));
    }
}
