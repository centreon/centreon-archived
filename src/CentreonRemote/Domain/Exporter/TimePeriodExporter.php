<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository;

class TimePeriodExporter implements ExporterServiceInterface
{

    use ExportPathTrait;

    const EXPORT_FILE_TIMEPERIOD = 'timeperiod.yaml';
    const EXPORT_FILE_EXCEPTION = 'timeperiod_exceptions.yaml';
    const EXPORT_FILE_INCLUDE = 'timeperiod_include_relations.yaml';
    const EXPORT_FILE_EXCLUDE = 'timeperiod_exclude_relations.yaml';

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

        $db->getRepository(Repository\TimePeriodRepository::class)->truncate();
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
        
        $hostTemplateChain = $this->db
            ->getRepository(Repository\HostTemplateRelationRepository::class)
            ->getChainByPoller($pollerId)
        ;
        
        $serviceTemplateChain = $this->db
            ->getRepository(Repository\ServiceRepository::class)
            ->getChainByPoller($pollerId)
        ;

        $timeperiodList = $this->db
            ->getRepository(Repository\TimePeriodRepository::class)
            ->getChainByPoller($pollerId, $hostTemplateChain, $serviceTemplateChain)
        ;

        // Extract data
        $timePeriods = $this->db
            ->getRepository(Repository\TimePeriodRepository::class)
            ->export($timeperiodList)
        ;

        $timePeriodExceptions = $this->db
            ->getRepository(Repository\TimePeriodExceptionRepository::class)
            ->export($timeperiodList)
        ;

        $timePeriodIncludes = $this->db
            ->getRepository(Repository\TimePeriodIncludeRelationRepository::class)
            ->export($timeperiodList)
        ;

        $timePeriodExcludes = $this->db
            ->getRepository(Repository\TimePeriodExcludeRelationRepository::class)
            ->export($timeperiodList)
        ;

        $this->commitment->getParser()::dump($timePeriods, $this->getFile(static::EXPORT_FILE_TIMEPERIOD));
        $this->commitment->getParser()::dump($timePeriodExceptions, $this->getFile(static::EXPORT_FILE_EXCEPTION));
        $this->commitment->getParser()::dump($timePeriodIncludes, $this->getFile(static::EXPORT_FILE_INCLUDE));
        $this->commitment->getParser()::dump($timePeriodExcludes, $this->getFile(static::EXPORT_FILE_EXCLUDE));
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

        // insert time periods
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_TIMEPERIOD);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('timeperiod', $data);
            }
        })();

        // insert exceptions
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_EXCEPTION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('timeperiod_exceptions', $data);
            }
        })();

        // insert include rules
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_INCLUDE);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('timeperiod_include_relations', $data);
            }
        })();

        // insert exclude rules
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_EXCLUDE);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('timeperiod_exclude_relations', $data);
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
        return 'time-period';
    }
}
