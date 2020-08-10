<?php

use Centreon\Test\Behat\Administration\ParametersMyAccountPage;
use Centreon\Test\Behat\CentreonContext;

class LanguageSelectionContext extends CentreonContext
{
    private $currentPage;

    /**
     * @Given I go to my account page
     */
    public function theUserWithAutologinEnabled()
    {
        $this->currentPage = new ParametersMyAccountPage($this);
    }

    /**
     * @When I select the language dropdown
     */
    public function selectTheLanguageDropdown()
    {
        $this->currentPage = new ParametersMyAccountPage($this);

        /* Wait for select2 returned values */
        $this->spin(
            function ($context) {
                return (!empty($this->assertFind('css', 'select[name="contact_lang"]')));
            },
            'Cannot retrieve language list from select2'
        );
    }

    /**
     * @Then I can see unique human readable language options
     */
    public function iCanSeeProperDropdownListItems()
    {
        $this->currentPage = new ParametersMyAccountPage($this);
        $enLang = $this->assertFind('css', 'select[name="contact_lang"] > option[value = "en_US.UTF-8"]');

        if (is_array($enLang) || $enLang->getText() !== 'en_US') {
            throw new \Exception('en_US option not properly worded or duplicate');
        }
    }
}
