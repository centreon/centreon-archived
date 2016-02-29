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
            $this->featureContext->saveImage('/tmp/aSelectedSelect2.png');
    }

    /**
     * @When I hold shift key
     */
    public function iHoldShiftKey()
    {
        if (is_null($this->select2Object)) {
            throw new \Exception('Element not found');
        }
        
        $this->select2Object->keyDown(16);
        
        /*$script = "jQuery.Event( 'keydown', { keyCode: 16, which: 16 } )";
        
        $this->minkContext->getSession()->executeScript($script);*/
        
        $script = "return jQuery(document).keydown(function(e){"
                . "if(e.keyCode == 16) jQuery('body').css('background-color:red');"
                . "else  jQuery('body').css('background-color:yellow');})"
                ;
        
        $shift = $this->minkContext->getSession()->evaluateScript($script);
        /*$this->featureContext->saveImage('/tmp/iHoldShiftKey.png');
        var_dump($shift);
        if ($shift != 'false') {
            throw new \Exception('Shift not hold');
        }*/
        
        $this->featureContext->saveImage('/tmp/iHoldShiftKey.png');
    }
    
    /**
     * @When click on a first item
     */
    public function clickOnAFirstItem()
    {
        if (is_null($this->select2Object)) {
            throw new \Exception('Element not found');
        }
       
        $element = $this->minkContext->getSession()->getPage()->find(
                    'css', 'li.select2-results__option:nth-child(1)');
        /*
        if (is_null($element)) {
            throw new \Exception('Element not found');
        }
        */
        $element->press();
        
        $this->featureContext->saveImage('/tmp/clickOnAFirstItem.png');
        
        /*$this->featureContext->spin(
            function ($context) {
            return $context->getMinkContext()->getSession()->getPage()->find(
                    'css', 'li.select2-results__option:first-child'
            );
            
        }, 30
        );*/
    }
    
    /**
     * @When click on an another item
     */
    public function clickOnAnAnotherItem()
    {
        if (is_null($this->select2Object)) {
            throw new \Exception('Element select 2 not found');
        }
        
        $this->featureContext->saveImage('/tmp/clickOnAnAnotherItem.png');
        
        $element = $this->minkContext->getSession()->getPage()->find(
                    'css', 'li.select2-results__option:nth-child(3)');
        
        $element->press();
        
        /*
        if (is_null($element)) {
            throw new \Exception('Element not found');
        }
        */
        
        
        /*$this->featureContext->spin(
            function ($context) {
            return $context->getMinkContext()->getSession()->getPage()->find(
                    'css', 'li.select2-results__option:nth-child(4)'
            );
        }, 30
        );*/
    }
    
    /**
     * @Then the items between the two items are selected
     */
    public function theItemsBetweenTheTwoItemsAreSelected()
    {
        if (is_null($this->select2Object)) {
            throw new \Exception('Element not found');
        }
        
        $elem = $this->minkContext->getSession()->getPage()->find(
                    'named', array(
                    'id_or_name',
                    'command_id'
                    ));
       
        $count = count($elem->getValue());
        var_dump($count);
        
        if ($count == 0) {
            throw new \Exception('Nb element');
        }
        
    }
}
