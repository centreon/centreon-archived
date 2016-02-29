<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;

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
        $this->minkContext->visit('/centreon/main.php?p=60806&o=c&id=1');
        
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
        
        $page = $this->session->getPage();
        
        /* Add search to select2 */
        $inputField = $page->find('css', 'select#command_id');
        if (!$inputField) {
            throw new \Exception('No field found');
        }
        
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
        $page = $this->session->getPage();
        /* Add search to select2 */
        $inputField = $page->find('css', 'select#command_id');
        if (!$inputField) {
            throw new \Exception('No field found');
        }
        
        /* Click on Select all button */
        $selectAll = $page->find('css', '.select2-results-header__select-all > button');
        if (!$selectAll) {
            throw new \Exception('No field found');
        }
        $selectAll->press();

        $this->session->wait(1000);
        
        $confirmButton = $page->find('css', '#confirmcommand_id .btc.bt_success');
        if (!$confirmButton) {
            throw new \Exception('No button found');
        }
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
        $page = $this->session->getPage();
        /* Add search to select2 */
        $inputField = $page->find('css', 'select#command_id');
        if (!$inputField) {
            throw new \Exception('No field found');
        }
        
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
        $page = $this->session->getPage();
        /* Add search to select2 */
        $inputField = $page->find('css', 'select#command_id');
        if (!$inputField) {
            throw new \Exception('No field found');
        }
        
        $values = $inputField->getValue();
        if (count($values) != 4) {
            throw new \Exception('All filtered elements are not selected.');
        }
    }
}