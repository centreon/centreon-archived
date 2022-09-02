<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\SnmpTrapsConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostTemplateConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ServiceTemplateConfigurationListingPage;

/**
 * Defines application features from the specific context.
 */
class SaveSearchFilterContext extends CentreonContext
{
    public function __construct()
    {
        parent::__construct();
        $this->search = '';
        $this->page = '';
    }

    /**
     * @Given a search on the host template listing
     */
    public function aSearchOnTheHostTemplateListing()
    {
        $this->search = 'Servers';
        $this->page = new HostTemplateConfigurationListingPage($this);
        $this->page->setSearch($this->search);
    }


    /**
     * @Given a search on the traps listing
     */
    public function aSearchOnTheTrapsListing()
    {
        $this->search = 'ccm';
        $this->page = new SnmpTrapsConfigurationListingPage($this);
        $this->page->setSearch($this->search);
    }


    /**
     * @When I change page
     */
    public function iChangePage()
    {
        new ServiceTemplateConfigurationListingPage($this);
    }

    /**
     * @When I go back on the host template listing
     */
    public function iGoBackOnTheHostTemplateListing()
    {
        new HostTemplateConfigurationListingPage($this);
    }

    /**
     * @When I go back on the traps listing
     */
    public function iGoBackOnTheTrapsListing()
    {
        new SnmpTrapsConfigurationListingPage($this);
    }

    /**
     * @Then the search is fill by the previous search
     */
    public function theSearchIsFillByThePreviousSearch()
    {
        if ($this->search != $this->page->getSearch()) {
            throw new \Exception('saved search not found');
        }
    }
}
