<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostTemplateConfigurationPage;
use Centreon\Test\Behat\Configuration\HostTemplateConfigurationListingPage;

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
        // Click on template edition link (will open new window).
        $this->assertFind('css', 'ul#template img[src*="edit_mode.png"]')->click();
        //[att^=str] :- attribute value starting with str

        $this->spin(
            function ($context) {
                $windows = $context->getSession()->getWindowNames();
                return count($windows) > 1;
            },
            'Host template configuration window is not opened.',
            10
        );
        $windows = $this->getSession()->getWindowNames();
        $this->getSession()->switchToWindow($windows[1]);

        // Check properties of the host template.
        self::$lastUri = 'p=60103&o=c&host_id=2&min=1';

        $this->switchToIframe();

        $this->page = new HostTemplateConfigurationPage($this, false);
        $properties = $this->page->getProperties();
        if ($properties['name'] != 'generic-host') {
            throw new \Exception('Wrong host template configuration page.');
        }
    }
}
