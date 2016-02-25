<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     *
     * @var Behat\MinkExtension\Context\MinkContext 
     */
    private $minkContext;

    /**
     * @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->minkContext = $environment->getContext('Behat\MinkExtension\Context\MinkContext');
    }
    
    /**
     * 
     * @return Behat\MinkExtension\Context\MinkContext
     */
    public function getMinkContext()
    {
        return $this->minkContext;
    }

    /**
     * 
     * @param Closure $lambda
     * @param int $wait
     * @return boolean
     * @throws \Exception
     */
    public function spin($lambda, $wait = 60)
    {
        for ($i = 0; $i < $wait; $i++) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (\Exception $e) {}
            sleep(1);
        }
        throw new \Exception('Load timeout');
    }

    /**
     * @Given /^I am logged in$/
     */
    public function iAmLoggedIn()
    {
        $this->minkContext->visit('/');

        /* Login */
        $page = $this->minkContext->getSession()->getPage();
        $useraliasInput = $page->find('css', 'input[name="useralias"]');
        if (is_null($useraliasInput)) {
            throw new \Exception('Element not found');
        }
        $useraliasInput->setValue('admin');
        $passwordInput = $page->find('css', 'input[name="password"]');
        if (is_null($passwordInput)) {
            throw new \Exception('Element not found');
        }
        $passwordInput->setValue('centreon');
        $form = $page->find('css', 'form[name="login"]');
        if (is_null($form)) {
            throw new \Exception('Element not found');
        }
        $form->submit();
        $this->spin(
            function ($context) {
                return $context->getMinkContext()->getSession()->getPage()->has(
                    'css',
                    'a[href="main.php?p=103"]'
                );
            },
            30
        );
    }

    /**
     * @AfterStep
     * @param AfterStepScope $scope
     */
    public function takeScreenshotOnError(AfterStepScope $scope)
    {
        if (!$scope->getTestResult()->isPassed()) {
            $this->saveImage('/tmp/test.png');
        }
    }

    /**
     * 
     * @param string $filename
     */
    public function saveImage($filename)
    {
        $driver = $this->minkContext->getSession()->getDriver();
        if ($driver instanceof Selenium2Driver) {
            $screenshot = $driver->getScreenshot();
            file_put_contents($filename, $screenshot);
        }
    }
}
