<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\ConfigurationPollersPage;

/**
 * Defines application features from the specific context.
 */
class GeneratePollerContext extends CentreonContext
{
    private $pollers_page;

    public function __construct()
    {
        parent::__construct();
        $this->pollers_page = new ConfigurationPollersPage($this);
    }

    /**
     * @Given a Centreon platform with multiple pollers
     */
    public function aCentreonPlatformWithMultiplePollers()
    {
        $this->pollers_page->duplicatePoller('Central');
    }

    /**
     * @Given one poller is selected
     */
    public function onePollerIsSelected()
    {
        $this->pollers_page->selectPoller('Central');
    }

    /**
     * @Given multiple pollers are selected
     */
    public function multiplePollersAreSelected()
    {
        $this->pollers_page->selectPoller('Central');
        $this->pollers_page->selectPoller('Central_1');
    }

    /**
     * @Given I click on the button :arg1
     */
    public function iClickOnTheButton($arg1)
    {
        sleep(10);
        $applyConfigurationButton = $this->assertFind('css', 'input[name="' . $arg1 . '"]');
        $applyConfigurationButton->click();
    }

    /**
     * @Given no one poller is selected
     */
    public function noOnePollerIsSelected()
    {
        $clearAllSpan = $this->assertFind('css', '.clearAllSelect2');
        $this->assertFindIn($clearAllSpan, 'css', 'img')->click();
    }

    /**
     * @When I am redirected to generate page
     */
    public function iAmRedirectedToGeneratePage()
    {
        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'select#nhost'
                );
            },
            30
        );
    }

    /**
     * @When I select an other poller
     */
    public function iSelectAnOtherPoller()
    {
        $inputField = $this->assertFind('css', 'select#nhost');
        $choice = $inputField->getParent()->find('css', '.select2-selection');
        if (!$choice) {
            throw new \Exception('No select2 choice found');
        }
        $choice->press();

        $this->spin(
            function ($context) {
                return count($context->getSession()->getPage()->findAll('css', '.select2-container--open li.select2-results__option')) >= 2;
            },
            30
        );

        $chosenResults = $this->getSession()->getPage()->findAll('css', '.select2-results li:not(.select2-results__option--highlighted)');
        $found = FALSE;
        foreach ($chosenResults as $result) {
            if ($result->getText() == "Central_1") {
                $result->click();
                $found = TRUE;
                break;
            }
        }
        if (!$found) {
            throw new \Exception('Could not find Central_1 in select2 list.');
        }
    }

    /**
     * @Then the pollers are already selected
     */
    public function thePollersAreAlreadySelected()
    {
        $selectedPollers = array();
        $printedPollers = $this->getSession()->getPage()->findAll('css', '.select2-content');
        foreach ($printedPollers as $printedPoller) {
            array_push($selectedPollers, $printedPoller->getText());
        }
        sort($selectedPollers);
        if ($selectedPollers != array('Central', 'Central_1')) {
            throw new \Exception('Wrong selected pollers');
        }
    }

    /**
     * @Then poller configuration is generated
     */
    public function pollerConfigurationIsGenerated()
    {
        /* Wait configuration is generated */
        $this->spin(
            function ($context) {
                return count($context->getSession()->getPage()->findAll('css', 'div#consoleDetails font[color="green"]')) == 6;
            },
            30
        );
    }
    /**
     * @Then an error message is displayed to inform that no one poller is selected
     */
    public function noPollerErrorMessage() {
        /* Wait error message is displayed */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    '#noSelectedPoller[style*="display: inline"]'
                );
            },
            5
        );
    }
}
