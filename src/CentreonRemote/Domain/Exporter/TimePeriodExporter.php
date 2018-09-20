<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use CentreonRemote\Infrastructure\Service\ExporterServicePartialInterface;
use Centreon\Domain\Repository;

class TimePeriodExporter extends ExporterServiceAbstract implements ExporterServicePartialInterface
{

    const NAME = 'time-period';
    const EXPORT_FILE_TIMEPERIOD = 'timeperiod.yaml';
    const EXPORT_FILE_EXCEPTION = 'timeperiod_exceptions.yaml';
    const EXPORT_FILE_INCLUDE = 'timeperiod_include_relations.yaml';
    const EXPORT_FILE_EXCLUDE = 'timeperiod_exclude_relations.yaml';

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\TimePeriodRepository::class)->truncate();
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

        $timeperiodList = $this->db
            ->getRepository(Repository\TimePeriodRepository::class)
            ->getChainByPoller($pollerIds, $hostTemplateChain, $serviceTemplateChain)
        ;

        // Extract data
        (function() use ($timeperiodList) {
            $timePeriods = $this->db
                ->getRepository(Repository\TimePeriodRepository::class)
                ->export($timeperiodList)
            ;
            $this->_dump($timePeriods, $this->getFile(static::EXPORT_FILE_TIMEPERIOD));
        })();

        (function() use ($timeperiodList) {
            $timePeriodExceptions = $this->db
                ->getRepository(Repository\TimePeriodExceptionRepository::class)
                ->export($timeperiodList)
            ;
            $this->_dump($timePeriodExceptions, $this->getFile(static::EXPORT_FILE_EXCEPTION));
        })();

        (function() use ($timeperiodList) {
            $timePeriodIncludes = $this->db
                ->getRepository(Repository\TimePeriodIncludeRelationRepository::class)
                ->export($timeperiodList)
            ;
            $this->_dump($timePeriodIncludes, $this->getFile(static::EXPORT_FILE_INCLUDE));
        })();

        (function() use ($timeperiodList) {
            $timePeriodExcludes = $this->db
                ->getRepository(Repository\TimePeriodExcludeRelationRepository::class)
                ->export($timeperiodList)
            ;
            $this->_dump($timePeriodExcludes, $this->getFile(static::EXPORT_FILE_EXCLUDE));
        })();
    }

    public function exportPartial(): void
    {
        $timeperiodList = $this->cache->get('timeperiod.list');

        if (!$timeperiodList) {
            return;
        }

        // Extract data
        (function() use ($timeperiodList) {
            $timePeriods = $this->db
                ->getRepository(Repository\TimePeriodRepository::class)
                ->export($timeperiodList)
            ;
            $this->_mergeDump($timePeriods, $this->getFile(static::EXPORT_FILE_TIMEPERIOD), 'tp_id');
        })();

        (function() use ($timeperiodList) {
            $timePeriodExceptions = $this->db
                ->getRepository(Repository\TimePeriodExceptionRepository::class)
                ->export($timeperiodList)
            ;
            $this->_mergeDump($timePeriodExceptions, $this->getFile(static::EXPORT_FILE_EXCEPTION), 'exception_id');
        })();

        (function() use ($timeperiodList) {
            $timePeriodIncludes = $this->db
                ->getRepository(Repository\TimePeriodIncludeRelationRepository::class)
                ->export($timeperiodList)
            ;
            $this->_mergeDump($timePeriodIncludes, $this->getFile(static::EXPORT_FILE_INCLUDE), 'include_id');
        })();

        (function() use ($timeperiodList) {
            $timePeriodExcludes = $this->db
                ->getRepository(Repository\TimePeriodExcludeRelationRepository::class)
                ->export($timeperiodList)
            ;
            $this->_mergeDump($timePeriodExcludes, $this->getFile(static::EXPORT_FILE_EXCLUDE), 'exclude_id');
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

        // insert time periods
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_TIMEPERIOD);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('timeperiod', $data);
            }
        })();

        // insert exceptions
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_EXCEPTION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('timeperiod_exceptions', $data);
            }
        })();

        // insert include rules
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_INCLUDE);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('timeperiod_include_relations', $data);
            }
        })();

        // insert exclude rules
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_EXCLUDE);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('timeperiod_exclude_relations', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }
}
