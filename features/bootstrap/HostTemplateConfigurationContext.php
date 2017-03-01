<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\HostConfigurationListingPage;
use Centreon\Test\Behat\HostTemplateConfigurationPage;
use Centreon\Test\Behat\HostTemplateConfigurationListingPage;


class HostTemplateConfigurationContext extends CentreonContext
{
    protected $page;
    protected $hostName = 'AcceptanceHost';
    protected $parentHostTemplate = 'parent';

    /**
     * @Given an host inheriting from an host template
     */
    public function anHostInheritingFromAnHostTemplate()
    {


        $this->page = new HostConfigurationPage($this);
        $this->page->setProperties(array(
            'name' => $this->hostName,
            'alias' => $this->hostName,
            'address' => 'localhost',
            'templates' => array('generic-host')
        ));
        $this->page->save();
    }
    /**
     * @Given an host template inheriting from an host template
     */
    public function anHostTemplateInheritingFromAnHostTemplate()
    {
        $this->page = new HostTemplateConfigurationPage($this);
        $this->page->setProperties(array(
            'name' => $this->hostName,
            'alias' => $this->hostName,
            'templates' => array('generic-host')
        ));
        $this->page->save();
    }


    /**
     * @When I configure the host
     */
    public function iConfigureTheHost()
    {
        $this->page = new HostConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->hostName);
    }

    /**
     * @When I configure the host template
     */
    public function iConfigureTheHostTemplate()
    {
        $this->page = new HostTemplateConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->hostName);
    }

    /**
     * @Then I can configure directly its parent template
     */
    public function iCanConfigureDirectlyItsParentTemplate()
    {
        $this->assertFind('css', 'ul#template img[src*="edit_mode.png"]')->click();
        $windows = $this->getSession()->getWindowNames();
        if (!isset($windows[1])) {
            throw new \Exception('Host template configuration page is not opened.');
        }
        $this->getSession()->switchToWindow($windows[1]);

        $this->page = new HostTemplateConfigurationPage($this, false);
        $properties = $this->page->getProperties();
        if ($properties['name'] != 'generic-host') {
            throw new \Exception('Wrong host template configuration page.');
        }
    }
}