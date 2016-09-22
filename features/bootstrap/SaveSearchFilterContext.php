<?php


use Centreon\Test\Behat\SnmpTrapsConfigurationListingPage;
use Centreon\Test\Behat\HostTemplateConfigurationListingPage;
use Centreon\Test\Behat\CentreonContext;

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
        /* service configuration page */
        $this->visit('/main.php?p=602');
        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'input[name="searchS"]'
                );
            },
            30
        );
    }

    /**
     * @When I go back on the host template listing
     */
    public function iGoBackOnTheHostTemplateListing()
    {
        $this->visit('/main.php?p=60103');
        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'input[name="searchHT"]'
                );
            },
            30
        );
    }

    /**
     * @When I go back on the traps listing
     */
    public function iGoBackOnTheTrapsListing()
    {
        $this->visit('/main.php?p=617');
        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'input[name="searchT"]'
                );
            },
            30
        );
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
