<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactListPage;

class PartitioningContext extends CentreonContext
{
    private $hostName;
    private $serviceName;

    public function __construct()
    {
        parent::__construct();
        $this->hostName = 'RecoveryNotificationDelayTestHost';
        $this->serviceName = 'RecoveryNotificationDelayTestService';
    }

    /**
     * @When I am on database informations page
     */
    public function iAmOnDatabaseInformationsPage()
    {
        $this->visit('main.php?p=50503');

        $this->spin(
            fn($context) => $context->getSession()->getPage()->has('named', ['id_or_name', 'database_informations'])
        );
    }

    /**
     * @Then partitioning informations are displayed
     */
    public function partitioningInformationsAreDisplayed()
    {
        $this->spin(
            fn($context) => $context->getSession()->getPage()->has('named', ['id_or_name', 'tab1']) &&
                $context->getSession()->getPage()->has('named', ['id_or_name', 'tab2'])
        );
    }
}
