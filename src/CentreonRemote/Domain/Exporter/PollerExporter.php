<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository;

class PollerExporter implements ExporterServiceInterface
{

    use ExportPathTrait;

    const EXPORT_FILE_NAGIOS_SERVER = 'nagios_server.yaml';
    const EXPORT_FILE_CFG_RESOURCE = 'cfg_resource.yaml';
    const EXPORT_FILE_CFG_NAGIOS = 'cfg_nagios.yaml';
    const EXPORT_FILE_CFG_NAGIOS_BROKER_MODULE = 'cfg_nagios_broker_module.yaml';
    const EXPORT_FILE_CFG_CENTREONBROKER = 'cfg_centreonbroker.yaml';
    const EXPORT_FILE_CFG_CENTREONBROKER_INFO = 'cfg_centreonbroker_info.yaml';
    const EXPORT_FILE_TIMEZONE = 'timezone.yaml';
    const EXPORT_FILE_POLLER_COMMAND = 'poller_command_relations.yaml';

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    private $db;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    private $commitment;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->db = $services->get('centreon.db-manager');
    }

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\NagiosServerRepository::class)->truncate();
        $db->getRepository(Repository\PollerCommandRelationsRepository::class)->truncate();
        $db->getRepository(Repository\CfgResourceRepository::class)->truncate();
        $db->getRepository(Repository\CfgCentreonBorkerRepository::class)->truncate();
    }

    /**
     * Export data
     */
    public function export(): void
    {
        // create path
        $this->createPath();
        $pollerIds = $this->commitment->getPollers();

        (function() use ($pollerIds) {
            $nagiosServer = $this->db
                ->getRepository(Repository\NagiosServerRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($nagiosServer, $this->getFile(static::EXPORT_FILE_NAGIOS_SERVER));
        })();

        (function() use ($pollerIds) {
            $cfgResource = $this->db
                ->getRepository(Repository\CfgResourceRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($cfgResource, $this->getFile(static::EXPORT_FILE_CFG_RESOURCE));
        })();

        (function() use ($pollerIds) {
            $pollerCommand = $this->db
                ->getRepository(Repository\PollerCommandRelationsRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($pollerCommand, $this->getFile(static::EXPORT_FILE_POLLER_COMMAND));
        })();

        (function() use ($pollerIds) {
            $cfgNagios = $this->db
                ->getRepository(Repository\CfgNagiosRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($cfgNagios, $this->getFile(static::EXPORT_FILE_CFG_NAGIOS));
        })();

        (function() use ($pollerIds) {
            $cfgNagiosBrokerModule = $this->db
                ->getRepository(Repository\CfgNagiosBrokerModuleRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($cfgNagiosBrokerModule, $this->getFile(static::EXPORT_FILE_CFG_NAGIOS_BROKER_MODULE));
        })();

        (function() use ($pollerIds) {
            $cfgCentreonBroker = $this->db
                ->getRepository(Repository\CfgCentreonBorkerRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($cfgCentreonBroker, $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER));
        })();

        (function() use ($pollerIds) {
            $cfgCentreonBrokerInfo = $this->db
                ->getRepository(Repository\CfgCentreonBorkerInfoRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($cfgCentreonBrokerInfo, $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER_INFO));
        })();

        (function() use ($pollerIds) {
            $timezone = $this->db
                ->getRepository(Repository\TimezoneRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($timezone, $this->getFile(static::EXPORT_FILE_TIMEZONE));
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
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('nagios_server', $data);
            }
        })();

        // insert cfg resource
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_RESOURCE);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $dataRelation = [
                    'resource_id' => $data['resource_id'],
                    'instance_id' => $data['_instance_id'],
                ];

                unset($data['_instance_id']);

                $db->insert('cfg_resource', $data);
                $db->insert('cfg_resource_instance_relations', $dataRelation);
            }
        })();

        // insert poller commands
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_POLLER_COMMAND);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('poller_command_relations', $data);
            }
        })();

        // insert cfg nagios
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_NAGIOS);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_nagios', $data);
            }
        })();

        // insert cfg broker module
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_NAGIOS_BROKER_MODULE);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_nagios_broker_module', $data);
            }
        })();

        // insert cfg broker
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_centreonbroker', $data);
            }
        })();

        // insert cfg broker info
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER_INFO);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_centreonbroker_info', $data);
            }
        })();

        // insert timezone if missing
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_TIMEZONE);
            $result = $this->commitment->getParser()::parse($exportPathFile);

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

    public function setCommitment(ExportCommitment $commitment): void
    {
        $this->commitment = $commitment;
    }

    public static function getName(): string
    {
        return 'poller';
    }
}
