<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class CentreonContext implements Context
{
    public $minkContext;

    /**
     *
     * @var \Behat\Mink\Session
     */
    public $session;

    private $container;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /** @BeforeScenario */
    public function getMinkSession(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->minkContext = $environment->getContext('Behat\MinkExtension\Context\MinkContext');
        $this->session = $this->minkContext->getSession();
    }

    /**
     *
     * @param type $lambda
     * @return boolean
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

    public function assertFind($selector, $locator)
    {
        $retval = $this->session->getPage()->find($selector, $locator);
        if (is_null($retval))
            throw new \Exception("Element ('$selector', '$locator') could not be found.");
        return ($retval);
    }

    /**
     *  @Given a Centreon server
     */
    public function aCentreonServer()
    {
        $image = getenv('CENTREON_WEB_IMAGE');
        if (!empty($image))
        {
            $this->container = new CentreonContainer($image);
            $this->minkContext->setMinkParameter('base_url', 'http://localhost:' . $this->container->getPort());
        }
    }

    /**
     * @Given I am logged in
     */
    public function iAmLoggedIn()
    {
        $this->minkContext->visit('/');

        /* Login */
        $page = $this->minkContext->getSession()->getPage();
        $useraliasInput = $page->find('css', 'input[name="useralias"]');
        $useraliasInput->setValue('admin');
        $passwordInput = $page->find('css', 'input[name="password"]');
        $passwordInput->setValue('centreon');
        $form = $page->find('css', 'form[name="login"]');
        $form->submit();
        $this->spin(
            function ($context) {
                return $context->session->getPage()->has(
                    'css',
                    'a[href="main.php?p=103"]'
                );
            },
            30
        );
    }

    /**
     * @AfterStep
     */
    public function takeScreenshotOnError(AfterStepScope $scope)
    {
        if (!$scope->getTestResult()->isPassed()) {
            $this->saveImage('/tmp/test.png');
        }
    }

    public function saveImage($filename)
    {
        $driver = $this->session->getDriver();
        if ($driver instanceof Selenium2Driver) {
            $screenshot = $driver->getScreenshot();
            file_put_contents($filename, $screenshot);
        }
    }
}
