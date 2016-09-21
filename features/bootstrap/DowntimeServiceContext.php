<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\DowntimeConfigurationPage;
use Centreon\Test\Behat\DowntimeConfigurationListingPage;
use Centreon\Test\Behat\MetaServiceConfigurationPage;
use Centreon\Test\Behat\ServiceMonitoringDetailsPage;

/**
 * Defines application features from the specific context.
 */
class DowntimeServiceContext extends CentreonContext
{

    public function __construct()
    {
        parent::__construct();
        $this->metaName = 'testmeta';
    }

    /**
     * @Given I have a meta service
     */
    public function iHaveAMetaServices()
    {
        $metaservicePage = new MetaServiceConfigurationPage($this);
        $metaservicePage->setProperties(array(
            'name' => $this->metaName,
            'check_period' => 5,
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1
        ));
        $metaservicePage->save();
        $this->restartAllPollers();
    }

    /**
     * @When I place a downtime
     */
    public function iPlaceADowntime()
    {
        $page = new DowntimeConfigurationPage($this);
        $page->setProperties(array(
            'type' => DowntimeConfigurationPage::TYPE_SERVICE,
            'service' => 'Meta - ' . $this->metaName,
            'comment' => __METHOD__
        ));
        $page->save();
    }

    /**
     * @When I place a comment
     */
    public function iPlaceAComment()
    {
        $this->visit('main.php?p=20201&o=svcd&host_name=_Module_Meta&service_description=meta_1');
        $this->assertFind('css', '.ListTable.table.linkList tr.list_one:nth-child(5) a')->click();
        sleep(1);
        $this->assertFind('css', 'textarea[name="comment"]')->setValue('downtime');
        $this->assertFind('css', 'input[name="submitA"]')->click();
        sleep(3);
    }

    /**
     * @When I cancel a downtime
     */
    public function iCancelADowntime()
    {
        $this->spin(
            function ($context) {
                $page = new DowntimeConfigurationListingPage($this);
                return count($page->getEntries()) > 0;
            },
            10
        );
        $page = new DowntimeConfigurationListingPage($this);
        $page->selectEntry(0);
        $page->cancel();
    }

    /**
     * @Then this one appears in the interface
     */
    public function thisOneAppearsInTheInterface()
    {
        $this->visit('main.php?p=21002');
        $this->getSession()->getPage()->has('css', 'table.ListTable tbody tr.list_two td.ListColLeft a');
    }

    /**
     * @Then this one does not appear in the interface
     */
    public function thisOneDoesNotAppearInTheInterface()
    {
        $this->spin(function ($context) {
            $page = new ServiceMonitoringDetailsPage(
                $context,
                '_Module_Meta',
                'meta_1'
            );
            $props = $page->getProperties();
            return !$props['in_downtime'];
        },
            3);

    }

    /**
     * @Then this one appears in the interface in downtime
     */
    public function thisOneAppearsInTheInterfaceInDowntime()
    {
        $this->spin(
            function ($context) {
                $page = new ServiceMonitoringDetailsPage(
                    $context,
                    '_Module_Meta',
                    'meta_1'
                );
                $props = $page->getProperties();
                return $props['in_downtime'];
            },
            3
        );
    }
}
