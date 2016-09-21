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
        $this->hostTemplateSearch = 'Servers';
    }

    /**
     * @Given a search on the host template listing
     */
    public function aSearchOnTheHostTemplateListing()
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

        $this->assertFind('css', 'input[name="searchHT"]')->setValue($this->hostTemplateSearch);
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
     * @Then the search is fill by the previous search
     */
    public function theSearchIsFillByThePreviousSearch()
    {
        if ($this->hostTemplateSearch != $this->assertFind('css', 'input[name="searchHT"]')->getValue()) {
            throw new \Exception('Search not save');
        }
    }
}
