<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ExtensionsPage;

/**
 * Features context.
 */
class WidgetContext extends CentreonContext
{
    protected $page;
    private $type = ExtensionsPage::WIDGET_TYPE;
    private $widgetName = 'host-monitoring';

    /**
     * @Given a widget is ready to install
     */
    public function aWidgetIsReadyToInstall()
    {
        $this->container->execute('yum install -y --nogpgcheck centreon-widget-host-monitoring', 'web');

        $this->page = new ExtensionsPage($this);
        $widget = $this->page->getEntry($this->type, $this->widgetName);
        if (!$widget['actions']['install']) {
            throw new \Exception('Widget ' . $this->widgetName . ' is not ready to install.');
        }
    }

    /**
     * @Given a widget is ready to remove
     */
    public function aWidgetIsReadyToRemove()
    {
        $this->aWidgetIsReadyToInstall();
        $this->iInstallTheWidget();
        $this->theWidgetIsInstalled();
    }

    /**
     * @When I install the widget
     */
    public function iInstallTheWidget()
    {
        $this->page->install($this->type, $this->widgetName);
    }

    /**
     * @When I remove the widget
     */
    public function iRemoveTheWidget()
    {
        $this->page->remove($this->type, $this->widgetName);
    }

    /**
     * @Then the widget is installed
     */
    public function theWidgetIsInstalled()
    {
        $this->page = new ExtensionsPage($this);

        $module = $this->page->getEntry($this->type, $this->widgetName);
        if ($module['actions']['install']) {
            throw new \Exception('Widget ' . $this->widgetName . ' is not installed.');
        }
    }

    /**
     * @Then the widget is removed
     */
    public function theWidgetIsRemoved()
    {
        $this->page = new ExtensionsPage($this);

        $widget = $this->page->getEntry($this->type, $this->widgetName);
        if ($widget['actions']['remove']) {
            throw new \Exception('Widget ' . $this->widgetName . ' is not removed.');
        }
    }
}
