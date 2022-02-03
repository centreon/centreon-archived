<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\BrokerConfigurationListingPage;
use Centreon\Test\Behat\Configuration\BrokerConfigurationPage;

class BrokerConfigurationContext extends CentreonContext
{
    private $currentPage;

    private $initialProperties = array(
        'name' => 'brokerConfigName',
        'filename' => 'brokerFilename',
        'cache_directory' => '/var/lib/centreon-broker/'
    );

    private $luaProperties = array(
        'name' => 'lua_script',
        'path' => '/tmp/lua.lua',
        'metricType' => 'Number',
        'metricName' => 'integer',
        'metricValue' => 42
    );


    /**
     * @When I add a custom output
     */
    public function iAddACustomOutput()
    {
        $this->currentPage = new BrokerConfigurationListingPage($this);
        $this->assertFind('css', 'table.ToolbarTable.table a.btc.bt_success')->click();
        $page = $this->currentPage = new BrokerConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->assertFind('css', '#c3')->click();
        $this->selectInList('#block_output', 'Generic - Stream connector');
        $this->assertFind('css', 'a#add_output.btc.bt_success')->click();
        $this->currentPage;

        $this->spin(
            function ($context) use ($page) {
                return $context->getSession()->getPage()->has('css', 'tr.list_one td input.v_required');
            }
        );

        $this->assertFind('css', 'tr.list_one td input.v_required')->setValue($this->luaProperties['name']);
        $this->assertFind('css', 'tr.list_two td input.v_required')->setValue($this->luaProperties['path']);
        $this->selectInList(
            'tbody#output_1 tr:nth-child(1) td.FormRowValue:nth-child(2) select',
            $this->luaProperties['metricType']
        );
        $this->assertFind('css', 'tbody#output_1 tr.list_one:nth-child(2) td:nth-child(2).FormRowValue input')
            ->setValue($this->luaProperties['metricName']);
        $this->assertFind('css', 'tbody#output_1 input.v_number')
            ->setValue($this->luaProperties['metricValue']);

        $this->currentPage->save();
    }

    /**
     * @Then the output is saved
     */
    public function theOutputIsSaved()
    {
        $this->currentPage = new BrokerConfigurationListingPage($this);
        $this->currentPage->getEntry($this->initialProperties['name']);
    }
}
