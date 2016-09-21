<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
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
        $this->searchInput = '';
    }

    /**
     * @Given a search on the host template listing
     */
    public function aSearchOnTheHostTemplateListing()
    {

        $this->search = 'Servers';
        $this->searchInput = 'input[name="searchHT"]';

        $this->visit('/main.php?p=60103');
        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    $this->searchInput
                );
            },
            30
        );

        $this->assertFind('css', $this->searchInput)->setValue($this->search);
        $this->assertFind('css', 'tbody tr td input.btc.bt_success')->click();
        sleep(1);
    }


    /**
     * @Given a search on the traps listing
     */
    public function aSearchOnTheTrapsListing()
    {
        $this->search = 'ccm';
        $this->searchInput = 'input[name="searchT"]';

        $this->visit('/main.php?p=617');
        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    $this->searchInput
                );
            },
            30
        );

        $this->assertFind('css', $this->searchInput)->setValue($this->search);
        $this->assertFind('css', 'tbody tr td input.btc.bt_success')->click();
        sleep(1);
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
        if ($this->search != $this->assertFind('css', $this->searchInput)->getValue()) {
            throw new \Exception('saved search not found');
        }
    }
}
