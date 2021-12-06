<?php

use Centreon\Test\Behat\CentreonContext;

/**
 * Defines application features from the specific context.
 */
class SelectAllSelect2Context extends CentreonContext
{
    private $expectedElements;

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
                return $context->getSession()->getPage()->has(
                    'css',
                    'input[name="submitC"]'
                );
            }
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
                return count($context->getSession()->getPage()
                        ->findAll('css', '.select2-container--open li.select2-results__option')) >= 4;
            }
        );
    }

    /**
     * @Given I search :arg1 in Select
     */
    public function iSearchWordInSelect($search)
    {
        $this->getSession()->executeScript(
            'jQuery("select#command_id").parent().find(".select2-search__field").val("' . $search . '");
            var data = jQuery("select#command_id").data();
            data.select2.trigger("query", {term: "' . $search . '"});'
        );
        $this->getSession()->wait(1000);
    }

    /**
     * @When I click on Select all button
     */
    public function iClickOnSelectAllButton()
    {
        /* Get the number of elements */
        $this->expectedElements = intval($this->assertFind(
            'css',
            '.select2-results-header__nb-elements-value'
        )->getText());

        /* Click on Select all button */
        $selectAll = $this->assertFind('css', '.select2-results-header__select-all > button');
        $selectAll->press();

        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', '.centreon-popin .popin-wrapper');
            }
        );
    }

    /**
     * @When I validate Select all confirm box
     */
    public function iValidateSelectAllConfirmBox()
    {
        $confirmButton = $this->assertFind('css', '.popin-wrapper .button_group_center .btc.bt_success');
        $confirmButton->click();

        $this->spin(
            function ($context) {
                return count($context->getSession()->getPage()
                        ->findAll('css', '.select2-container--open li.select2-results__option')) == 0;
            }
        );
    }

    /**
     * @When I cancel Select all confirm box
     */
    public function iCancelSelectAllConfirmBox()
    {
        $cancelButton = $this->assertFind('css', '.popin-wrapper .button_group_center .btc.bt_default');
        $cancelButton->click();

        $this->spin(
            function ($context) {
                return !$context->assertFind('css', '.centreon-popin .popin-wrapper')->isVisible();
            }
        );
    }

    /**
     * @When I exit Select all confirm box
     */
    public function iExitSelectAllConfirmBox()
    {
        $exitButton = $this->assertFind('css', '.centreon-popin a.close[href="#"] img');
        $exitButton->click();

        $this->spin(
            function ($context) {
                return !$context->assertFind('css', '.centreon-popin .popin-wrapper')->isVisible();
            }
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
        if (count($values) != $this->expectedElements) {
            throw new \Exception('All elements are not selected (got ' . count($values) . ', expected ' .
                $this->expectedElements . ').');
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
        if (count($values) != $this->expectedElements) {
            throw new \Exception('All filtered elements are not selected (got ' . count($values) .
                ', expected ' . $this->expectedElements . ').');
        }
    }

    /**
     * @Then no one element is selected
     */
    public function noOneElementIsSelected()
    {
        /* Add search to select2 */
        $inputField = $this->assertFind('css', 'select#command_id');

        $values = $inputField->getValue();
        if (count($values) != 0) {
            throw new \Exception(count($values) . ' elements have been selected');
        }
    }
}
