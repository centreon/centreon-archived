<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\WidgetListingPage;

/**
 * Features context.
 */
class WidgetContext extends CentreonContext
{
    protected $page;
    private $widgetName = 'Host Monitoring';

    /**
     * @Given a widget is ready to install
     */
    public function aWidgetIsReadyToInstall()
    {
        $this->container->execute('yum install -y --nogpgcheck centreon-widget-host-monitoring', 'web');

        $this->page = new WidgetListingPage($this);
        $widget = $this->page->getEntry($this->widgetName);
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
        $this->page->install($this->widgetName);
    }

    /**
     * @When I remove the widget
     */
    public function iRemoveTheWidget()
    {
        $this->page->remove($this->widgetName);
    }

    /**
     * @Then the widget is installed
     */
    public function theWidgetIsInstalled()
    {
        $self = $this;
        $this->spin(
            function () use ($self) {
                $widget = $self->page->getEntry($self->widgetName);
                if ($widget['actions']['install']) {
                    return false;
                }
                return true;
            },
            'Widget ' . $self->widgetName . ' is not installed.',
            5
        );
    }

    /**
     * @Then the widget is removed
     */
    public function theWidgetIsRemoved()
    {
        $this->page = new WidgetListingPage($this);
        $widget = $this->page->getEntry($this->widgetName);
        if ($widget['actions']['remove']) {
            throw new \Exception('Widget ' . $this->widgetName . ' is not removed.');
        }
    }
}
