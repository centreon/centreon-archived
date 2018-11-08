<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Domain\Service\PollerDefaultsOverwriteService;
use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Repository;

class PollerExporter extends ExporterServiceAbstract
{

    const NAME = 'poller';
    const EXPORT_FILE_NAGIOS_SERVER = 'nagios_server.yaml';
    const EXPORT_FILE_CFG_RESOURCE = 'cfg_resource.yaml';
    const EXPORT_FILE_CFG_RESOURCE_INSTANCE_RELATION = 'cfg_resource_instance_relations.yaml';
    const EXPORT_FILE_CFG_NAGIOS = 'cfg_nagios.yaml';
    const EXPORT_FILE_CFG_NAGIOS_BROKER_MODULE = 'cfg_nagios_broker_module.yaml';
    const EXPORT_FILE_CFG_CENTREONBROKER = 'cfg_centreonbroker.yaml';
    const EXPORT_FILE_CFG_CENTREONBROKER_INFO = 'cfg_centreonbroker_info.yaml';
    const EXPORT_FILE_TIMEZONE = 'timezone.yaml';
    const EXPORT_FILE_POLLER_COMMAND = 'poller_command_relations.yaml';

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\NagiosServerRepository::class)->truncate();
        $db->getRepository(Repository\PollerCommandRelationsRepository::class)->truncate();
        $db->getRepository(Repository\CfgResourceRepository::class)->truncate();
        $db->getRepository(Repository\CfgCentreonBrokerRepository::class)->truncate();
    }

    /**
     * Export data
     */
    public function export(): void
    {
        // create path
        $this->createPath();
        $pollerIds = $this->commitment->getPollers();
        $overwritePollerService = new PollerDefaultsOverwriteService;
        $overwritePollerService->setPollerID($this->commitment->getRemote());

        (function() use ($pollerIds, $overwritePollerService) {
            $nagiosServer = $this->db
                ->getRepository(Repository\NagiosServerRepository::class)
                ->export($pollerIds);
            $nagiosServer = $overwritePollerService->setNagiosServer($nagiosServer);
            $this->_dump($nagiosServer, $this->getFile(static::EXPORT_FILE_NAGIOS_SERVER));
        })();

        (function() use ($pollerIds, $overwritePollerService) {
            $cfgResource = $this->db
                ->getRepository(Repository\CfgResourceRepository::class)
                ->export($pollerIds);
            $cfgResource = $overwritePollerService->setCfgResource($cfgResource);
            $this->_dump($cfgResource, $this->getFile(static::EXPORT_FILE_CFG_RESOURCE));
        })();

        (function() use ($pollerIds) {
            $data = $this->db
                ->getRepository(Repository\CfgResourceInstanceRelationsRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($data, $this->getFile(static::EXPORT_FILE_CFG_RESOURCE_INSTANCE_RELATION));
        })();

        (function() use ($pollerIds) {
            $pollerCommand = $this->db
                ->getRepository(Repository\PollerCommandRelationsRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($pollerCommand, $this->getFile(static::EXPORT_FILE_POLLER_COMMAND));
        })();

        (function() use ($pollerIds, $overwritePollerService) {
            $cfgNagios = $this->db
                ->getRepository(Repository\CfgNagiosRepository::class)
                ->export($pollerIds);
            $cfgNagios = $overwritePollerService->setCfgNagios($cfgNagios);
            $this->_dump($cfgNagios, $this->getFile(static::EXPORT_FILE_CFG_NAGIOS));
        })();

        (function() use ($pollerIds, $overwritePollerService) {
            $cfgNagiosBrokerModule = $this->db
                ->getRepository(Repository\CfgNagiosBrokerModuleRepository::class)
                ->export($pollerIds);
            $cfgNagiosBrokerModule = $overwritePollerService->setCfgNagiosBroker($cfgNagiosBrokerModule);
            $this->_dump($cfgNagiosBrokerModule, $this->getFile(static::EXPORT_FILE_CFG_NAGIOS_BROKER_MODULE));
        })();

        (function() use ($pollerIds, $overwritePollerService) {
            $cfgCentreonBroker = $this->db
                ->getRepository(Repository\CfgCentreonBrokerRepository::class)
                ->export($pollerIds);
            $cfgCentreonBroker = $overwritePollerService->setCfgCentreonBroker($cfgCentreonBroker);
            $this->_dump($cfgCentreonBroker, $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER));
        })();

        (function() use ($pollerIds, $overwritePollerService) {
            $cfgCentreonBrokerInfo = $this->db
                ->getRepository(Repository\CfgCentreonBrokerInfoRepository::class)
                ->export($pollerIds);
            $cfgCentreonBrokerInfo = $overwritePollerService->setCfgCentreonBrokerInfo($cfgCentreonBrokerInfo);
            $this->_dump($cfgCentreonBrokerInfo, $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER_INFO));
        })();

        (function() use ($pollerIds) {
            $timezone = $this->db
                ->getRepository(Repository\TimezoneRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($timezone, $this->getFile(static::EXPORT_FILE_TIMEZONE));
        })();
    }

    /**
     * Import data
     */
    public function import(): void
    {
        // skip if no data
        if (!is_dir($this->getPath())) {
            return;
        }

        $db = $this->db->getAdapter('configuration_db');

        // start transaction
        $db->beginTransaction();

        // allow insert records without foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=0;');

        // truncate tables
        $this->cleanup();

        // insert nagios server
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_NAGIOS_SERVER);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('nagios_server', $data);
            }
        })();

        // insert cfg resource
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_RESOURCE);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                unset($data['_instance_id']);
                $db->insert('cfg_resource', $data);
            }
        })();

        // insert cfg resource
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_RESOURCE_INSTANCE_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_resource_instance_relations', $data);
            }
        })();

        // insert poller commands
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_POLLER_COMMAND);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('poller_command_relations', $data);
            }
        })();

        // insert cfg nagios
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_NAGIOS);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_nagios', $data);
            }
        })();

        // insert cfg broker module
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_NAGIOS_BROKER_MODULE);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_nagios_broker_module', $data);
            }
        })();

        // insert cfg broker
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_centreonbroker', $data);
            }
        })();

        // insert cfg broker info
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER_INFO);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_centreonbroker_info', $data);
            }
        })();

        // insert timezone if missing
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_TIMEZONE);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $tz = $db
                    ->getRepository(Repository\TimezoneRepository::class)
                    ->get($data['_nagios_id'])
                ;

                if ($tz) {
                    continue;
                }

                unset($data['_nagios_id']);
                $db->insert('timezone', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }

    public static function order(): int
    {
        return 10;
    }
}
