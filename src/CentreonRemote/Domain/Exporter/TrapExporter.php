<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Repository;

class TrapExporter extends ExporterServiceAbstract
{

    const NAME = 'trap';
    const EXPORT_FILE_TRAP = 'traps.yaml';
    const EXPORT_FILE_VENDOR = 'traps_vendor.yaml';
    const EXPORT_FILE_SERVICE_RELATION = 'traps_service_relation.yaml';
    const EXPORT_FILE_GROUP = 'traps_group.yaml';
    const EXPORT_FILE_GROUP_RELATION = 'traps_group_relation.yaml';
    const EXPORT_FILE_MATCHING_PROP = 'traps_matching_properties.yaml';
    const EXPORT_FILE_PREEXEC = 'traps_preexec.yaml';

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

        $serviceTemplateChain = $this->_getIf('service.chain', function() use ($pollerIds) {
            return $this->db
                    ->getRepository(Repository\ServiceRepository::class)
                    ->getChainByPoller($pollerIds)
            ;
        });

        // Extract data
        (function() use ($pollerIds, $serviceTemplateChain) {
            $traps = $this->db
                ->getRepository(Repository\TrapRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($traps, $this->getFile(static::EXPORT_FILE_TRAP));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $vendors = $this->db
                ->getRepository(Repository\TrapVendorRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($vendors, $this->getFile(static::EXPORT_FILE_VENDOR));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $serviceRelation = $this->db
                ->getRepository(Repository\TrapServiceRelationRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($serviceRelation, $this->getFile(static::EXPORT_FILE_SERVICE_RELATION));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $groups = $this->db
                ->getRepository(Repository\TrapGroupRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($groups, $this->getFile(static::EXPORT_FILE_GROUP));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $groupRelation = $this->db
                ->getRepository(Repository\TrapGroupRelationRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($groupRelation, $this->getFile(static::EXPORT_FILE_GROUP_RELATION));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $matchingProps = $this->db
                ->getRepository(Repository\TrapMatchingPropsRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($matchingProps, $this->getFile(static::EXPORT_FILE_MATCHING_PROP));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $preexec = $this->db
                ->getRepository(Repository\TrapPreexecRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($preexec, $this->getFile(static::EXPORT_FILE_PREEXEC));
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
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps', $data);
            }
        })();

        // insert vendors
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_VENDOR);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_vendor', $data);
            }
        })();

        // insert service relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_SERVICE_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_service_relation', $data);
            }
        })();

        // insert groups
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_group', $data);
            }
        })();

        // insert group relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_group_relation', $data);
            }
        })();

        // insert properties
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_MATCHING_PROP);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_matching_properties', $data);
            }
        })();

        // insert pre-executed commands
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_PREEXEC);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('traps_preexec', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }
}
