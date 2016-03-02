<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class PaginationSelect2Context extends CentreonContext
{
    /**
     * @When I change the configuration value of number of elements loaded in select
     */
    public function iChangeTheConfigurationValueOfNumberOfElementsLoadedInSelect()
    {
        /* Go to the page to options page */
        $this->minkContext->visit('/centreon/main.php?p=50110&o=general');

        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->session->getPage()->has(
                    'css',
                    'input[name="submitC"]'
                );
            },
            30
        );

        $fieldValue = $this->assertFind('css', 'input[name="selectPaginationSize"]');
        $fieldValue->setValue(200);
        $submitButton = $this->assertFind('css', 'input[name="submitC"]')->click();

        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->session->getPage()->has(
                    'css',
                    'input[name="change"]'
                );
            },
            30
        );
    }

    /**
     * @Then the value is saved
     */
    public function theValueIsSaved()
    {
        /* Go to the page to options page */
        $this->minkContext->visit('/centreon/main.php?p=50110&o=general');

        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->session->getPage()->has(
                    'css',
                    'input[name="submitC"]'
                );
            },
            30
        );

        $fieldValue = $this->assertFind('css', 'input[name="selectPaginationSize"]');
        if ($fieldValue->getValue() != 200) {
            throw new \Exception('The value is not saved.');
        }
    }
}