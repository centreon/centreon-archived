<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\ContactListPage;
use Centreon\Test\Behat\ContactConfigurationPage;
use Centreon\Test\Behat\CommandConfigurationPage;
use Centreon\Test\Behat\ConfigurationPollersPage;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\ServiceConfigurationPage;

class BackupContext extends CentreonContext
{
    private $hostName;
    private $serviceName;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @When I check centreon scheduled task
     */
    public function iCheckCentreonScheduledTask()
    {
        try {
            $this->container->execute('ls /etc/cron.d/centreon', 'web', true);
        } catch (\Exception $e) {
            throw new \Exception('Centreon scheduled task does not exist');
        }
    }

    /**
     * @Then backup is scheduled
     */
    public function backupIsScheduled()
    {
        $cron = $this->container->execute('cat /etc/cron.d/centreon', 'web', true);
        if (!preg_match('/centreon-backup.pl/m', $cron['output'])) {
            throw new \Exception('centreon-backup is not scheduled');
        }
    }
}

?>
