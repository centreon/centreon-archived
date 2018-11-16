<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ModuleListingPage;

/**
 * Features context.
 */
class ModuleContext extends CentreonContext
{
    protected $page;
    private $moduleName = 'centreon-license-manager';

    /**
     * @Given a module is ready to install
     */
    public function aModuleIsReadyToInstall()
    {
        $this->page = new ModuleListingPage($this);
        $module = $this->page->getEntry($this->moduleName);
        if (!$module['actions']['install']) {
            throw new \Exception('Module ' . $this->moduleName . ' is not ready to install.');
        }
    }

    /**
     * @Given a module is ready to remove
     */
    public function aModuleIsReadyToRemove()
    {
        $this->aModuleIsReadyToInstall();
        $this->iInstallTheModule();
        $this->theModuleIsInstalled();

        $this->page = new ModuleListingPage($this);
        $module = $this->page->getEntry($this->moduleName);
        if (!$module['actions']['remove']) {
            throw new \Exception('Module ' . $this->moduleName . ' is not ready to remove.');
        }
    }

    /**
     * @When I install the module
     */
    public function iInstallTheModule()
    {
        $this->page->install($this->moduleName);
    }

    /**
     * @When I remove the module
     */
    public function iRemoveTheModule()
    {
        $this->page->remove($this->moduleName);
    }

    /**
     * @Then the module is installed
     */
    public function theModuleIsInstalled()
    {
        //wait the widget is installed
        sleep(2);

        // initialize page to manage iframe selection
        $this->page = new ModuleListingPage($this);

        $module = $this->page->getEntry($this->moduleName);
        if ($module['actions']['install']) {
            throw new \Exception('Module ' . $this->moduleName . ' is not installed.');
        }
    }

    /**
     * @Then the module is removed
     */
    public function theModuleIsRemoved()
    {
        $this->page = new ModuleListingPage($this);
        $module = $this->page->getEntry($this->moduleName);
        if ($module['actions']['remove']) {
            throw new \Exception('Module ' . $this->moduleName . ' is not removed.');
        }
    }
}
