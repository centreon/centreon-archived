<?php
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;

class Select2ShiftSelectorContext implements Context
{

    /**
     *
     * @var Behat\MinkExtension\Context\MinkContext 
     */
    private $minkContext;

    /**
     *
     * @var FeatureContext 
     */
    private $featureContext;

    /**
     *
     * @var Select2Object
     */
    private $select2Object;

    /**
     * @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function getMinkSession(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->minkContext = $environment->getContext('Behat\MinkExtension\Context\MinkContext');
        $this->featureContext = $environment->getContext('FeatureContext');
    }

    /**
     * @Given a selected object
     */
    public function aSelectedObject()
    {
        $this->minkContext->visit('/centreon/main.php?p=60806&o=c&id=1');
        /* Wait page loaded */
        $this->featureContext->spin(
            function ($context) {
            return $context->getMinkContext()->getSession()->getPage()->has(
                    'named', array(
                    'id_or_name',
                    'command_id'
                    )
            );
        }, 30
        );
    }

    /**
     * @Given a selected select2
     */
    public function aSelectedSelect2()
    {
        $currentPage = $this->minkContext->getSession()->getPage();
        $this->select2Object = $currentPage->find(
            'css', '.select2-selection'
        );

        if (is_null($this->select2Object)) {
            throw new \Exception('Element not found');
        }

        $this->select2Object->press();

        $this->featureContext->spin(
            function ($context) {
                return count($context->getMinkContext()->getSession()->getPage()->findAll('css', '.select2-container--open li.select2-results__option')) > 2;
            }, 
            30
        );
    }

    /**
     * @When I hold shift key
     */
    public function iHoldShiftKey()
    {
        if (is_null($this->select2Object)) {
            throw new \Exception('Element not found');
        }
        
        $script = "jQuery(':focus').trigger(jQuery.Event('keypress', {which: 16, keyCode: 16}));";
        
        /*$script = "jQuery(':focus').keydown(function(e){"
                . "keydown[e.keyCode] = true;"
                . "if(e.keycode == 16) return 'true';"
                . "jQuery(this).keyup(function() {"
                . "if(keysdown[e.keyCode] === true){"
                . "keydown[e.keyCode] = false; return 'false';}});});";*/
        
        $this->minkContext->getSession()->evaluateScript($script);
        
    }
    
    /**
     * @When click on a first item
     */
    public function clickOnAFirstItem()
    {
        if (is_null($this->select2Object)) {
            throw new \Exception('Element not found');
        }
        
        $this->featureContext->spin(
            function ($context) {
            return $context->getMinkContext()->getSession()->getPage()->has(
                    'css', 'li.select2-results__option:first-child'
            );
        }, 30
        );
    }
    
    /**
     * @When click on an another item
     */
    public function clickOnAnAnotherItem()
    {
        if (is_null($this->select2Object)) {
            throw new \Exception('Element not found');
        }
        
        $this->featureContext->spin(
            function ($context) {
            return $context->getMinkContext()->getSession()->getPage()->has(
                    'css', 'li.select2-results__option:nth-child(4)'
            );
        }, 30
        );
    }
    
    /**
     * @Then the items between the two items are selected
     */
    public function theItemsBetweenTheTwoItemsAreSelected()
    {
        if (is_null($this->select2Object)) {
            throw new \Exception('Element not found');
        }
        
        $this->featureContext->spin(
            function ($context) {
                return count($context->session->getPage()->findAll('css', '.select2-container--open li.select2-results__option[aria-selected="true"]')) == 4;
            },
            30
        );
    }
}
