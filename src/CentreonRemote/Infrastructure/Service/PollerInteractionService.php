<?php

namespace CentreonRemote\Infrastructure\Service;

use Pimple\Container;

class PollerInteractionService
{

    /** @var Container */
    private $di;

    /** @var \CentreonDB */
    private $db;

    /**
     * @var \Centreon
     */
    private $centreon;


    public function __construct(Container $di)
    {
        global $centreon;

        $this->di = $di;
        $this->db = $di[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getAdapter('configuration_db')
            ->getCentreonDBInstance();

        $this->centreon = $centreon;
    }


    /**
     * @param int[] $pollers
     */
    public function generateAndExport($pollers): void
    {
        $pollers = (array) $pollers;

        $this->generateConfiguration($pollers);
        $this->moveConfigurationFiles($pollers);
        $this->restartPoller($pollers);
    }

    /**
     * @throws \Exception
     * @param int[] $pollerIDs
     */
    private function generateConfiguration(array $pollerIDs): void
    {
        $username = 'unknown';

        if (isset($this->centreon->user->name)) {
            $username = $this->centreon->user->name;
        }

        try {
            // Sync contact groups with ldap
            $contactGroupObject = new \CentreonContactgroup($this->db);
            $contactGroupObject->syncWithLdap();

            // Generate configuration
            $configGenerateObject = new \Generate($this->di);

            foreach ($pollerIDs as $pollerID) {
                $configGenerateObject->reset();
                $configGenerateObject->configPollerFromId($pollerID, $username);
            }
        } catch (\Exception $e) {
            throw new \Exception('There was an error generating the configuration for a poller.');
        }
    }

    /**
     * @throws \Exception
     * @param int[] $pollerIDs
     */
    private function moveConfigurationFiles(array $pollerIDs): void
    {
        $centreonBrokerPath = _CENTREON_CACHEDIR_ . '/config/broker/';

        if (defined('_CENTREON_VARLIB_')) {
            $centCorePipe = _CENTREON_VARLIB_ . '/centcore.cmd';
        } else {
            $centCorePipe = '/var/lib/centreon/centcore.cmd';
        }

        $tabServer = [];
        $tabs = $this->centreon->user->access->getPollerAclConf([
            'fields'     => ['name', 'id', 'localhost'],
            'order'      => ['name'],
            'conditions' => ['ns_activate' => '1'],
            'keys'       => ['id']
        ]);

        foreach ($tabs as $tab) {
            if (in_array($tab['id'], $pollerIDs)) {
                $tabServer[$tab['id']] = [
                    'id'        => $tab['id'],
                    'name'      => $tab['name'],
                    'localhost' => $tab['localhost']
                ];
            }
        }

        foreach ($tabServer as $host) {
            if (in_array($host['id'], $pollerIDs)) {
                $listBrokerFile = glob($centreonBrokerPath . $host['id'] . "/*.{xml,cfg,sql}", GLOB_BRACE);

                passthru("echo 'SENDCFGFILE:{$host['id']}' >> {$centCorePipe}", $return);

                if ($return) {
                    throw new \Exception(_('Could not write into centcore.cmd. Please check file permissions.'));
                }

                if (count($listBrokerFile) > 0) {
                    passthru("echo 'SENDCBCFG:" . $host['id'] . "' >> $centCorePipe", $return);

                    if ($return) {
                        throw new \Exception(_('Could not write into centcore.cmd. Please check file permissions.'));
                    }
                }
            }
        }
    }

    /**
     * @throws \Exception
     * @param int[] $pollerIDs
     */
    private function restartPoller(array $pollerIDs): void
    {
        $tabServers = [];

        if (defined('_CENTREON_VARLIB_')) {
            $centCorePipe = _CENTREON_VARLIB_ . '/centcore.cmd';
        } else {
            $centCorePipe = '/var/lib/centreon/centcore.cmd';
        }

        $tabs = $this->centreon->user->access->getPollerAclConf([
            'fields'     => ['name', 'id', 'localhost', 'engine_restart_command'],
            'order'      => ['name'],
            'conditions' => ['ns_activate' => '1'],
            'keys'       => ['id']
        ]);

        $broker = new \CentreonBroker($this->db);
        $broker->reload();

        foreach ($tabs as $tab) {
            if (in_array($tab['id'], $pollerIDs)) {
                $tabServers[$tab['id']] = [
                    'id'          => $tab['id'],
                    'name'        => $tab['name'],
                    'localhost'   => $tab['localhost'],
                    'engine_restart_command' => $tab['engine_restart_command']
                ];
            }
        }

        foreach ($tabServers as $poller) {
            if (isset($poller['localhost']) && $poller['localhost'] == 1) {
                shell_exec("sudo {$poller['engine_restart_command']}");
            } else {
                if ($fh = @fopen($centCorePipe, 'a+')) {
                    fwrite($fh, 'RESTART:' . $poller['id'] . "\n");
                    fclose($fh);
                } else {
                    throw new \Exception(_('Could not write into centcore.cmd. Please check file permissions.'));
                }
            }

            $restartTimeQuery = "UPDATE `nagios_server` 
                SET `last_restart` = '" . time() . "' 
                WHERE `id` = '{$poller['id']}'";
            $this->db->query($restartTimeQuery);
        }

        // Find restart actions in modules
        foreach ($this->centreon->modules as $key => $value) {
            $moduleFiles = glob(_CENTREON_PATH_ . 'www/modules/' . $key . '/restart_pollers/*.php');

            if ($value['restart'] && $moduleFiles) {
                foreach ($moduleFiles as $fileName) {
                    include $fileName;
                }
            }
        }
    }
}
