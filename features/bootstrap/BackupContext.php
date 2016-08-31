<?php

use Centreon\Test\Behat\BackupConfigurationPage;
use Centreon\Test\Behat\CentreonContext;

class BackupContext extends CentreonContext
{
    /**
     *  @Given the next backup is configured to be :backupType
     */
    public function theNextBackupIsConfiguredToBe($backupType)
    {
        // Set backup type.
        $allDays = array(
            BackupConfigurationPage::DAY_MONDAY,
            BackupConfigurationPage::DAY_TUESDAY,
            BackupConfigurationPage::DAY_WEDNESDAY,
            BackupConfigurationPage::DAY_THURSDAY,
            BackupConfigurationPage::DAY_FRIDAY,
            BackupConfigurationPage::DAY_SATURDAY,
            BackupConfigurationPage::DAY_SUNDAY
        );
        if ($backupType == 'full') {
            $fullBackupDays = $allDays;
            $partialBackupDays = array();
        } else if ($backupType == 'partial') {
            $fullBackupDays = array();
            $partialBackupDays = $allDays;
        } else {
            throw new \Exception('Invalid backup type ' . $backupType);
        }

        // Configure backup in Centreon Web.
        $page = new BackupConfigurationPage($this);
        $page->setProperties(array(
            'enabled' => true,
            'backup_centreon_db' => true,
            'backup_centreon_storage_db' => true,
            'backup_type' => BackupConfigurationPage::BACKUP_TYPE_LVM,
            'full_backup_days' => $fullBackupDays,
            'partial_backup_days' => $partialBackupDays
        ));
        $page->save();
    }

    /**
     *  @When the backup process starts
     */
    public function theBackupProcessStarts()
    {
        // The backup task is scheduled to run sometime during the
        // night. We will check that it is scheduled but for testing
        // purposes we will launch it directly instead.
        $cron = $this->container->execute('cat /etc/cron.d/centreon', 'web', true);
        if (!preg_match('/centreon-backup.pl/m', $cron['output'])) {
            throw new \Exception('centreon-backup is not scheduled');
        }
        $this->container->execute('/usr/share/centreon/cron/centreon-backup.pl', 'web');
    }

    /**
     *  @Then the :dataType data is backed up
     */
    public function theDataIsBackedUp($dataType)
    {
        $file = '/var/cache/centreon/backup/' . date('Y-m-d-central.tar.gz');
        $this->container->execute('ls ' . $file, 'web');
    }
}
