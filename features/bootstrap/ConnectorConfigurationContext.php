<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ConnectorConfigurationPage;
use Centreon\Test\Behat\Configuration\ConnectorConfigurationListingPage;

class ConnectorConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $initialProperties = array(
        'name' => 'connectorName',
        'description' => 'connectorDescription',
        'command_line' => 'connectorCommandLine',
        'command' => 'service-notify-by-email', //'service-notify-by-epager'
        'enabled' => 1
    );

    protected $updatedProperties = array(
        'name' => 'connectorNameChanged',
        'description' => 'connectorDescriptionChanged',
        'command_line' => 'connectorCommandLineChanged',
        'command' => 'service-notify-by-epager',
        'enabled' => 1
    );

    /**
     * @Given a connector is configured
     */
    public function aConnectorIsConfigured()
    {
        //$this->currentPage = new ConnectorConfigurationPage($this);
        //$this->currentPage->setProperties($this->initialPropert
    }

    /**
     * @When I change the properties of a connector
     */
    public function iChangeThePropertiesOfAConnector()
    {
    }

    /**
     * @Then the properties are updated
     */
    public function thePropertiesAreUpdated()
    {
      $this->tableau = array();
        $this->spin(
            function ($context) {
                var_dump('boucle');
                //$this->tableau[] = 'boucle';
                return false;
             },
             'Normal',
             15
        );
    }

    /**
     * @When I duplicate a connector
     */
    public function iDuplicateAConnector()
    {
    }

    /**
     * @Then the new connector has the same properties
     */
    public function theNewConnectorHasTheSameProperties()
    {
    }

    /**
     * @When I delete a connector
     */
    public function iDeleteAConnector()
    {
    }

    /**
     * @Then the deleted connector is not displayed in the list
     */
    public function theDeletedConnectorIsNotDisplayedInTheList()
    {
    }
}
