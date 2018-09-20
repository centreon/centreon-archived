<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';
require_once _CENTREON_PATH_ . 'www/class/config-generate/generate.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonBroker.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonConfigCentreonBroker.php';
require_once _CENTREON_PATH_ . 'www/include/configuration/configGenerate/DB-Func.php';

use CentreonRemote\Domain\Value\PollerServer;
use Pimple\Container;

class LinkedPollerConfigurationService
{

    /** @var Container */
    private $di;

    /** @var \CentreonDB */
    private $db;


    public function __construct(Container $di)
    {
        $this->di = $di;
        $this->db = $di['centreon.db-manager']->getAdapter('configuration_db')->getCentreonDBInstance();
    }

    /**
     * @param PollerServer[] $pollers
     * @param PollerServer   $server
     */
    public function setPollersConfigurationWithServer(array $pollers, PollerServer $server)
    {
        $pollerIDs = [];

        foreach ($pollers as $poller) {
            $pollerID = $poller->getId();
            $configQuery = "SELECT `config_id` FROM `cfg_centreonbroker` WHERE `ns_nagios_server` = :id 
                        AND `config_filename` LIKE '%-module.xml'";
            $statement = $this->db->prepare($configQuery);
            $statement->execute([':id' => $pollerID]);
            $configID = $statement->fetchColumn();

            $updateQuery = "UPDATE `cfg_centreonbroker_info` SET `config_value` = '{$server->getIp()}' 
                        WHERE `config_id` = {$configID} AND `config_key` = 'host'";
            $this->db->query($updateQuery);

            $pollerIDs[] = $pollerID;
        }

        $this->generateConfiguration($pollerIDs);
        $this->moveConfigurationFiles($pollerIDs);
        $this->restartPoller($pollerIDs);
    }

    private function generateConfiguration(array $pollerIDs)
    {
        $centreon = $_SESSION['centreon'];
        $username = 'unknown';

        if (isset($centreon->user->name)) {
            $username = $centreon->user->name;
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
        } catch(\Exception $e) {
            throw new \Exception('There was an error generating the configuration for a poller.');
        }
    }

    private function moveConfigurationFiles(array $pollerIDs)
    {
        $centreon = $_SESSION['centreon'];
        $centreonBrokerPath = _CENTREON_PATH_ . '/filesGeneration/broker/';

        if (defined('_CENTREON_VARLIB_')) {
            $centCorePipe = _CENTREON_VARLIB_ . '/centcore.cmd';
        } else {
            $centCorePipe = '/var/lib/centreon/centcore.cmd';
        }

        $tabServer = [];
        $tabs = $centreon->user->access->getPollerAclConf([
            'fields'     => ['name', 'id', 'localhost'],
            'order'      => ['name'],
            'conditions' => ['ns_activate' => '1'],
            'keys'       => ['id']
        ]);

        $brokerObj = new \CentreonConfigCentreonBroker($this->db);
        $correlationPath = $brokerObj->getCorrelationFile();
        $localId = getLocalhostId();

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
            if ($correlationPath !== false && $localId !== false) {
                $tmpFilename = $centreonBrokerPath . '/' . $host['id'] . '/correlation_' . $host['id'] . '.xml';
                $filenameToGenerate = dirname($correlationPath) . '/correlation_' . $host['id'] . '.xml';

                // Delete file
                if (file_exists($filenameToGenerate)) {
                    @unlink($filenameToGenerate);
                }
                // Copy file
                if (file_exists($tmpFilename)) {
                    @copy($tmpFilename, $filenameToGenerate);
                }
            }

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

    private function restartPoller(array $pollerIDs)
    {
        $centreon = $_SESSION['centreon'];
        $tabServers = [];

        if (defined('_CENTREON_VARLIB_')) {
            $centCorePipe = _CENTREON_VARLIB_ . '/centcore.cmd';
        } else {
            $centCorePipe = '/var/lib/centreon/centcore.cmd';
        }

        $tabs = $centreon->user->access->getPollerAclConf([
            'fields'     => ['name', 'id', 'localhost', 'init_script'],
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
                    'init_script' => $tab['init_script']
                ];
            }
        }

        foreach ($tabServers as $poller) {
            if (isset($poller['localhost']) && $poller['localhost'] == 1) {
                shell_exec("sudo service {$poller['init_script']} restart");
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
        foreach ($centreon->modules as $key => $value) {
            $moduleFiles = glob(_CENTREON_PATH_ . 'www/modules/' . $key . '/restart_pollers/*.php');

            if ($value['restart'] && $moduleFiles) {
                foreach ($moduleFiles as $fileName) {
                    include $fileName;
                }
            }
        }
    }
}
