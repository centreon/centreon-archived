<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ServiceConfigurationListingPage;

/**
 * Defines application features from the specific context.
 */
class GenerateServiceContactContext extends CentreonContext
{
    protected $currentPage;

    /**
     * @Given a one service associated on host
     */
    public function AOneServiceAssociatedOnHost()
    {
        $this->currentPage = new ServiceConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect('Ping');
    }

    /**
     * @Given I am on Notifications tab
     */
    public function IAmOnNotificationsTab()
    {
        $linkNotifications = $this->getSession()->getPage()->findAll('named', array('link', 'Notifications'));
        foreach ($linkNotifications as $link) {
            if ($link->getAttribute('href') == "#") {
                $tabExist = true;
                $link->click();
            }
        }

        if (!isset($tabExist)) {
            throw new \Exception("The notifications tab is not visible.");
        }
    }

    /**
     * @When I check case yes
     */
    public function iSelectTheRadioButton()
    {
        $name = 'service_use_only_contacts_from_host[service_use_only_contacts_from_host]';
        $radioButtons = $this->getSession()->getPage()->findAll('named', array('radio', $name));
        foreach ($radioButtons as $radio) {
            if ($radio->getAttribute('value') == 1) {
                $this->currentPage->checkRadio($radio);
                if (!$radio->isChecked()) {
                    throw new \Exception("Radio for $name not checked");
                }
            }
        }
    }

    /**
     * @Then a case Inherit contacts are disabled
     */
    public function aCheckboxInhertAreDisabled()
    {
        $sName = "service_inherit_contacts_from_host[service_inherit_contacts_from_host]";
        $radioButtons = $this->getSession()->getPage()->findAll('named', array('radio', $sName));
        foreach ($radioButtons as $radio) {
            if (!$radio->getAttribute('disabled')) {
                throw new \Exception("The case Inherit contacts are disabled");
            }
        }
    }

    /**
     * @Then the field contact service are disabled
     */
    public function theFieldContactServiceAreDisabled()
    {
        $sChecked = $this->getSession()->evaluateScript(
            "return jQuery('#service_cs').prop('disabled').toString();"
        );
        if ($sChecked != "true") {
            throw new \Exception("The field contact service are not disabled");
        }
    }

    /**
     * @Then the field contact group service are disabled
     */
    public function theFieldContactGroupServiceAreDisabled()
    {
        $sChecked = $this->getSession()->evaluateScript(
            "return jQuery('#service_cgs').prop('disabled').toString();"
        );
        if ($sChecked != "true") {
            throw new \Exception("The checkbox Inherit contacts group from host are not disabled");
        }
    }
}
