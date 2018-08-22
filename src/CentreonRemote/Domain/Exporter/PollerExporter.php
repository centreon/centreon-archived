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
        $db->getRepository(Repository\CfgResourceRepository::class)->truncate();
        $db->getRepository(Repository\CfgCentreonBorkerRepository::class)->truncate();
    }

    /**
     * Export data
     * 
     * @todo add exceptions
     */
    public function export(): void
    {
        // create path
        $this->createPath();

        $pollerId = $this->commitment->getPoller();

        $nagiosServer = $this->db
            ->getRepository(Repository\NagiosServerRepository::class)
            ->export($pollerId)
        ;

        $cfgResource = $this->db
            ->getRepository(Repository\CfgResourceRepository::class)
            ->export($pollerId)
        ;

        $cfgNagios = $this->db
            ->getRepository(Repository\CfgNagiosRepository::class)
            ->export($pollerId)
        ;

        $cfgNagiosBrokerModule = $this->db
            ->getRepository(Repository\CfgNagiosBrokerModuleRepository::class)
            ->export($pollerId)
        ;

        $cfgCentreonBroker = $this->db
            ->getRepository(Repository\CfgCentreonBorkerRepository::class)
            ->export($pollerId)
        ;

        $cfgCentreonBrokerInfo = $this->db
            ->getRepository(Repository\CfgCentreonBorkerInfoRepository::class)
            ->export($pollerId)
        ;

        // Store exports
        $this->commitment->getParser()::dump($nagiosServer, $this->getFile(static::EXPORT_FILE_NAGIOS_SERVER));
        $this->commitment->getParser()::dump($cfgResource, $this->getFile(static::EXPORT_FILE_CFG_RESOURCE));
        $this->commitment->getParser()::dump($cfgNagios, $this->getFile(static::EXPORT_FILE_CFG_NAGIOS));
        $this->commitment->getParser()::dump($cfgNagiosBrokerModule, $this->getFile(static::EXPORT_FILE_CFG_NAGIOS_BROKER_MODULE));
        $this->commitment->getParser()::dump($cfgCentreonBroker, $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER));
        $this->commitment->getParser()::dump($cfgCentreonBrokerInfo, $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER_INFO));
    }

    /**
     * Import data
     * 
     * @todo add exceptions
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

        // insert nagios server
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

        // insert cfg nagios
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_NAGIOS);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_nagios', $data);
            }
        })();

        // insert cfg centreonbroker
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_NAGIOS_BROKER_MODULE);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_nagios_broker_module', $data);
            }
        })();

        // insert cfg centreonbroker
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_centreonbroker', $data);
            }
        })();

        // insert cfg centreonbroker info
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CFG_CENTREONBROKER_INFO);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('cfg_centreonbroker_info', $data);
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
