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
class GenerateServiceContactContext extends CentreonContext
{
    private $pollers_page;

    public function __construct()
    {
        parent::__construct();
        $this->pollers_page = new ConfigurationPollersPage($this);
    }

    /**
     * @Given a one service associated on host
     */
    public function AOneServiceAssociatedOnHost()
    {
        $this->visit('/main.php?p=60201');
        $serviceLink = $this->assertFind('named', array('link', 'Broker-Retention'));
        if (!$serviceLink->isVisible()) {
            throw new \Exception("The service 'Broker-Retention' is not visible.");
        } 
        $serviceLink->click();
    }
    
    
    /**
     * @Given I am on Notifications tab
     */
    public function IAmOnNotificationsTab()
    {
        $notificationsTab = $this->assertFind('named', array('link', 'Notifications'));
        if (!$notificationsTab->isVisible()) {
            throw new \Exception("The notifications tab is not visible.");
        } 
        $notificationsTab->click();
    }
    
    /**
     * @When I check case yes
     */
    public function ICheckCaseYes()
    {
        $name = 'service_use_only_contacts_from_host[service_use_only_contacts_from_host]';
        $checkedRadio = $this->iSelectTheRadioButton("Yes");
        
    }
    
    private function iSelectTheRadioButton($radio_label) {
      $radio_button = $this->getSession()->getPage()->findField($radio_label);
      if (null === $radio_button) {
        throw new Exception(
          'form field '. $radio_label
        );
      }
      $value = $radio_button->getAttribute('value');
      $this->fillField($radio_label, $value);
    }
}
