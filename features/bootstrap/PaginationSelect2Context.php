<?php

use Centreon\Test\Behat\CentreonContext;

/**
 * Defines application features from the specific context.
 */
class PaginationSelect2Context extends CentreonContext
{
    private $expectedValue;

    /**
     * @When I change the number of elements loaded in select in the configuration
     */
    public function iChangeTheNumberOfElementsLoadedInSelectInTheConfiguration()
    {
        /* Go to the page to options page */
        $this->visit('/main.php?p=50110&o=general');
        $this->waitForGeneralOptionsPage();

        $fieldValue = $this->assertFind('css', 'input[name="selectPaginationSize"]');
        $originalValue = $fieldValue->getValue();
        $this->expectedValue = $originalValue + 25;
        if ($this->expectedValue > 200) {
            $this->expectedValue = 50;
        }
        $fieldValue->setValue($this->expectedValue);
        $this->assertFind('css', 'input[name="submitC"]')->click();

        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'input[name="change"]'
                );
            }
        );
    }

    /**
     * @Then the value is saved
     */
    public function theValueIsSaved()
    {
        /* Go to the page to options page */
        $this->visit('/main.php?p=50110&o=general');
        $this->waitForGeneralOptionsPage();

        $fieldValue = $this->assertFind('css', 'input[name="selectPaginationSize"]');
        if ($fieldValue->getValue() != $this->expectedValue) {
            throw new \Exception('The value is not saved.');
        }
    }

    public function waitForGeneralOptionsPage()
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'input[name="submitC"]'
                );
            }
        );
    }
}
