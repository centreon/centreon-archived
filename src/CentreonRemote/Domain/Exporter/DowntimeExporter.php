<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Repository;

class DowntimeExporter extends ExporterServiceAbstract
{

    const NAME = 'downtime';
    const EXPORT_FILE_DOWNTIME = 'downtime.yaml';
    const EXPORT_FILE_PERIOD = 'downtime_period.yaml';
    const EXPORT_FILE_CACHE = 'downtime_cache.yaml';
    const EXPORT_FILE_HOST_RELATION = 'downtime_host_relation.yaml';
    const EXPORT_FILE_HOST_GROUP_RELATION = 'downtime_hostgroup_relation.yaml';
    const EXPORT_FILE_SERVICE_RELATION = 'downtime_service_relation.yaml';
    const EXPORT_FILE_SERVICE_GROUP_RELATION = 'downtime_servicegroup_relation.yaml';

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\DowntimeRepository::class)->truncate();
    }

    /**
     * Export data
     */
    public function export(): void
    {
        // create path
        $this->createPath();
        $pollerIds = $this->commitment->getPollers();

        $hostTemplateChain = $this->_getIf('host.tpl.relation.chain', function() use ($pollerIds) {
            return $this->db
                    ->getRepository(Repository\HostTemplateRelationRepository::class)
                    ->getChainByPoller($pollerIds)
            ;
        });

        $serviceTemplateChain = $this->_getIf('service.chain', function() use ($pollerIds) {
            return $this->db
                    ->getRepository(Repository\ServiceRepository::class)
                    ->getChainByPoller($pollerIds)
            ;
        });

        // Extract data
        (function() use ($pollerIds, $hostTemplateChain, $serviceTemplateChain) {
            $downtimes = $this->db
                ->getRepository(Repository\DowntimeRepository::class)
                ->export($pollerIds, $hostTemplateChain, $serviceTemplateChain)
            ;
            $this->_dump($downtimes, $this->getFile(static::EXPORT_FILE_DOWNTIME));
        })();

        (function() use ($pollerIds, $hostTemplateChain, $serviceTemplateChain) {
            $downtimePeriods = $this->db
                ->getRepository(Repository\DowntimePeriodRepository::class)
                ->export($pollerIds, $hostTemplateChain, $serviceTemplateChain)
            ;
            $this->_dump($downtimePeriods, $this->getFile(static::EXPORT_FILE_PERIOD));
        })();

        (function() use ($pollerIds, $hostTemplateChain) {
            $downtimeCaches = $this->db
                ->getRepository(Repository\DowntimeCacheRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($downtimeCaches, $this->getFile(static::EXPORT_FILE_CACHE));
        })();

        (function() use ($pollerIds, $hostTemplateChain) {
            $downtimeHostRelation = $this->db
                ->getRepository(Repository\DowntimeHostRelationRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($downtimeHostRelation, $this->getFile(static::EXPORT_FILE_HOST_RELATION));
        })();

        (function() use ($pollerIds, $hostTemplateChain) {
            $downtimeHostGroupRelation = $this->db
                ->getRepository(Repository\DowntimeHostGroupRelationRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($downtimeHostGroupRelation, $this->getFile(static::EXPORT_FILE_HOST_GROUP_RELATION));
        })();

        (function() use ($pollerIds, $hostTemplateChain) {
            $downtimeServiceRelation = $this->db
                ->getRepository(Repository\DowntimeServiceRelationRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($downtimeServiceRelation, $this->getFile(static::EXPORT_FILE_SERVICE_RELATION));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $downtimeServiceGroupRelation = $this->db
                ->getRepository(Repository\DowntimeServiceGroupRelationRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($downtimeServiceGroupRelation, $this->getFile(static::EXPORT_FILE_SERVICE_GROUP_RELATION));
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

        // insert downtimes
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_DOWNTIME);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime', $data);
            }
        })();

        // insert downtime periods
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_PERIOD);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_period', $data);
            }
        })();

        // insert downtime cache
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CACHE);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_cache', $data);
            }
        })();

        // insert downtime host relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_HOST_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_host_relation', $data);
            }
        })();

        // insert downtime host group relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_HOST_GROUP_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_hostgroup_relation', $data);
            }
        })();

        // insert downtime service relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_SERVICE_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_service_relation', $data);
            }
        })();

        // insert downtime service group relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_SERVICE_GROUP_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_servicegroup_relation', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }

    public static function order(): int
    {
        return 60;
    }
}
