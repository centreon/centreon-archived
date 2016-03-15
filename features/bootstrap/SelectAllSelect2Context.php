<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\CentreonContext;

/**
 * Defines application features from the specific context.
 */
class SelectAllSelect2Context extends CentreonContext
{
    /**
     * @Given a select2
     */
    public function aSelect2()
    {
        /* Go to the page to connector configuration page */
        $this->visit('/main.php?p=60806&o=c&id=1');

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

        /* Add search to select2 */
        $inputField = $this->assertFind('css', 'select#command_id');

        /* Open the select2 */
        $choice = $inputField->getParent()->find('css', '.select2-selection');
        if (!$choice) {
            throw new \Exception('No select2 choice found');
        }
        $choice->press();
        $this->spin(
            function ($context) {
                return count($context->session->getPage()->findAll('css', '.select2-container--open li.select2-results__option')) >= 4;
            },
            30
        );
    }

    /**
     * @Given enter a research
     */
    public function enterAResearch()
    {
        $this->session->executeScript(
            'jQuery("select#command_id").parent().find(".select2-search__field").val("load");'
        );
        $this->session->wait(1000);
    }

    /**
     * @When I click on Select all button
     */
    public function iClickOnSelectAllButton()
    {
        /* Add search to select2 */
        $inputField = $this->assertFind('css', 'select#command_id');

        /* Click on Select all button */
        $selectAll = $this->assertFind('css', '.select2-results-header__select-all > button');
        $selectAll->press();

        $this->session->wait(1000);

        $confirmButton = $this->assertFind('css', '#confirmcommand_id .btc.bt_success');
        $confirmButton->click();

        $this->spin(
            function ($context) {
                return count($context->session->getPage()->findAll('css', '.select2-container--open li.select2-results__option')) == 0;
            },
            30
        );
    }

    /**
     * @Then all elements are selected
     */
    public function allElementsAreSelected()
    {
        /* Add search to select2 */
        $inputField = $this->assertFind('css', 'select#command_id');

        $values = $inputField->getValue();
        if (count($values) != 52) {
            throw new \Exception('All elements are not selected.');
        }
    }

    /**
     * @Then all filtered elements are selected
     */
    public function allFilteredElementsAreSelected()
    {
        /* Add search to select2 */
        $inputField = $this->assertFind('css', 'select#command_id');

        $values = $inputField->getValue();
        if (count($values) != 4) {
            throw new \Exception('All filtered elements are not selected.');
        }
    }
}
