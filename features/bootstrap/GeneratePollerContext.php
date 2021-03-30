<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\PollerConfigurationListingPage;

/**
 * Defines application features from the specific context.
 */
class GeneratePollerContext extends CentreonContext
{
    private $pollers_page;

    /**
     * @Given a Centreon platform with multiple pollers
     */
    public function aCentreonPlatformWithMultiplePollers()
    {
        $this->pollers_page = new PollerConfigurationListingPage($this);
        $this->setConfirmBox(true);
        $this->pollers_page->selectEntry('Central');
        $this->pollers_page->moreActions(PollerConfigurationListingPage::ACTION_DUPLICATE);
        $this->pollers_page->enableEntry('Central_1');
    }

    /**
     * @Given one poller is selected
     */
    public function onePollerIsSelected()
    {
        $this->pollers_page->selectEntry('Central');
    }

    /**
     * @Given multiple pollers are selected
     */
    public function multiplePollersAreSelected()
    {
        $this->pollers_page->selectEntry('Central');
        $this->pollers_page->selectEntry('Central_1');
    }

    /**
     * @Given I select another poller
     */
    public function iSelectAnotherPoller()
    {
        $this->pollers_page->setProperties(array('pollers' => 'Central_1'));
    }

    /**
     * @Given no poller is selected
     */
    public function noOnePollerIsSelected()
    {
        $clearAllSpan = $this->assertFind('css', '.clearAllSelect2');
        $this->assertFindIn($clearAllSpan, 'css', 'img')->click();
    }

    /**
     * @When I click on the configuration export button
     */
    public function iClickOnTheConfigurationExportButton()
    {
        $this->pollers_page = $this->pollers_page->exportConfiguration();
    }

    /**
     * @When I click on the export button
     */
    public function iClickOnTheExportButton()
    {
        // Cannot use pollers_page, as the export will fail.
        $this->assertFindButton('Export')->click();
    }

    /**
     * @When I am redirected to generate page
     */
    public function iAmRedirectedToGeneratePage()
    {
        // Check was made by PollerConfigurationListingPage::exportConfiguration.
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
        // Wait configuration is generated.
        $this->spin(
            function ($context) {
                return count($context->getSession()->getPage()
                        ->findAll('css', 'div#consoleDetails font[color="green"]')) === 6;
            }
        );
    }

    /**
     * @Then an error message is displayed to inform that no poller is selected
     */
    public function noPollerErrorMessage()
    {
        /* Wait error message is displayed */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    '#noSelectedPoller[style*="display: inline"]'
                );
            }
        );
    }
}
