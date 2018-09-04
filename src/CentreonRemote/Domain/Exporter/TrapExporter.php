<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository;

class TrapExporter implements ExporterServiceInterface
{

    use ExportPathTrait;

    const EXPORT_FILE_TRAP = 'traps.yaml';
    const EXPORT_FILE_VENDOR = 'traps_vendor.yaml';
    const EXPORT_FILE_SERVICE_RELATION = 'traps_service_relation.yaml';
    const EXPORT_FILE_GROUP = 'traps_group.yaml';
    const EXPORT_FILE_GROUP_RELATION = 'traps_group_relation.yaml';
    const EXPORT_FILE_MATCHING_PROP = 'traps_matching_properties.yaml';
    const EXPORT_FILE_PREEXEC = 'traps_preexec.yaml';

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

        $db->getRepository(Repository\TrapRepository::class)->truncate();
    }

    /**
     * Export data
     */
    public function export(): void
    {
        // create path
        $this->createPath();
        $pollerIds = $this->commitment->getPollers();

        $templateChain = $this->db
            ->getRepository(Repository\ServiceRepository::class)
            ->getChainByPoller($pollerIds)
        ;

        // Extract data
        (function() use ($pollerIds, $templateChain) {
            $traps = $this->db
                ->getRepository(Repository\TrapRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($traps, $this->getFile(static::EXPORT_FILE_TRAP));
        })();

        (function() use ($pollerIds, $templateChain) {
            $vendors = $this->db
                ->getRepository(Repository\TrapVendorRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($vendors, $this->getFile(static::EXPORT_FILE_VENDOR));
        })();

        (function() use ($pollerIds, $templateChain) {
            $serviceRelation = $this->db
                ->getRepository(Repository\TrapServiceRelationRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($serviceRelation, $this->getFile(static::EXPORT_FILE_SERVICE_RELATION));
        })();

        (function() use ($pollerIds, $templateChain) {
            $groups = $this->db
                ->getRepository(Repository\TrapGroupRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($groups, $this->getFile(static::EXPORT_FILE_GROUP));
        })();

        (function() use ($pollerIds, $templateChain) {
            $groupRelation = $this->db
                ->getRepository(Repository\TrapGroupRelationRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($groupRelation, $this->getFile(static::EXPORT_FILE_GROUP_RELATION));
        })();

        (function() use ($pollerIds, $templateChain) {
            $matchingProps = $this->db
                ->getRepository(Repository\TrapMatchingPropsRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($matchingProps, $this->getFile(static::EXPORT_FILE_MATCHING_PROP));
        })();

        (function() use ($pollerIds, $templateChain) {
            $preexec = $this->db
                ->getRepository(Repository\TrapPreexecRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($preexec, $this->getFile(static::EXPORT_FILE_PREEXEC));
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

        // insert traps
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_TRAP);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps', $data);
            }
        })();

        // insert vendors
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_VENDOR);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_vendor', $data);
            }
        })();

        // insert service relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_SERVICE_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_service_relation', $data);
            }
        })();

        // insert groups
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_group', $data);
            }
        })();

        // insert group relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_group_relation', $data);
            }
        })();

        // insert properties
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_MATCHING_PROP);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_matching_properties', $data);
            }
        })();

        // insert pre-executed commands
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_PREEXEC);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_preexec', $data);
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
        return 'trap';
    }
}
