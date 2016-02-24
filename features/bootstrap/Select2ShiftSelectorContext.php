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
                    'named',
                    array(
                        'id_or_name', 
                        'command_id'
                    )
                );
            },
            30
        );
    }
    
    /**
     * @Given a selected select2
     */
    public function aSelectedSelect2()
    {
        $currentPage = $this->minkContext->getSession()->getPage();
        $this->select2Object = $currentPage->find(
            'css',
            '.select2'
        );
        
        if (is_null($this->select2Object)) {
            throw new \Exception('Element not found');
        }

        $nbElement = $this->minkContext->getSession()->evaluateScript(
             "function(){"
            . " return jQuery('.select2').click(function() {
                jQuery('.select2').click();
                return jQuery('.select2-results__options li').length;
            }) }()");
        var_dump($nbElement);
    }
    
    /*

            "return jQuery('.select2').click(function() {
                jQuery('.select2').click();
                return jQuery('.select2-results__options li').length;
            });
        "*/

    
    /**
     * @When I hold shift key
     */
    public function iHoldShiftKey()
    {
        $this->minkContext->getSession()->executeScript("
            jQuery(':focus').trigger(jQuery.Event('keypress', {which: 16, keyCode: 16}));
        ");
    }
}
