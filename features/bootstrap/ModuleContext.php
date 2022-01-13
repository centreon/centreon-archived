<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ExtensionsPage;

/**
 * Features context.
 */
class ModuleContext extends CentreonContext
{
    protected $page;
    private $type = ExtensionsPage::MODULE_TYPE;
    private $moduleName = 'centreon-test';

    /**
     * @Given a module is ready to install
     */
    public function aModuleIsReadyToInstall()
    {
        $this->container->execute(
            'mkdir /usr/share/centreon/www/modules/centreon-test',
            'web',
            true
        );

        $this->container->copyToContainer(
            __DIR__ . '/../assets/centreon-test.conf.php',
            '/usr/share/centreon/www/modules/centreon-test/conf.php',
            'web'
        );

        $this->page = new ExtensionsPage($this);
        $module = $this->page->getEntry($this->type, $this->moduleName);
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

        $this->page = new ExtensionsPage($this);
        $module = $this->page->getEntry($this->type, $this->moduleName);
        if (!$module['actions']['remove']) {
            throw new \Exception('Module ' . $this->moduleName . ' is not ready to remove.');
        }
    }

    /**
     * @When I install the module
     */
    public function iInstallTheModule()
    {
        $this->page->install($this->type, $this->moduleName);
    }

    /**
     * @When I remove the module
     */
    public function iRemoveTheModule()
    {
        $this->page->remove($this->type, $this->moduleName);
    }

    /**
     * @Then the module is installed
     */
    public function theModuleIsInstalled()
    {
        $this->page = new ExtensionsPage($this);

        $module = $this->page->getEntry($this->type, $this->moduleName);
        if ($module['actions']['install']) {
            throw new \Exception('Module ' . $this->moduleName . ' is not installed.');
        }
    }

    /**
     * @Then the module is removed
     */
    public function theModuleIsRemoved()
    {
        $this->page = new ExtensionsPage($this);

        $module = $this->page->getEntry($this->type, $this->moduleName);
        if ($module['actions']['remove']) {
            throw new \Exception('Module ' . $this->moduleName . ' is not removed.');
        }
    }
}
