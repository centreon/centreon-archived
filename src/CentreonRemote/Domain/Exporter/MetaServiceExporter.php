<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository;

class MetaServiceExporter implements ExporterServiceInterface
{

    use ExportPathTrait;

    const EXPORT_FILE_META = 'meta_service.yaml';
    const EXPORT_FILE_RELATION = 'meta_service_relation.yaml';

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

        $db->getRepository(Repository\MetaServiceRepository::class)->truncate();
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

        // Extract data
        $metaServices = $this->db
            ->getRepository(Repository\MetaServiceRepository::class)
            ->export($pollerId, $hostTemplateChain)
        ;

        $metaServiceRelation = $this->db
            ->getRepository(Repository\MetaServiceRelationRepository::class)
            ->export($pollerId, $hostTemplateChain)
        ;

        $this->commitment->getParser()::dump($metaServices, $this->getFile(static::EXPORT_FILE_META));
        $this->commitment->getParser()::dump($metaServiceRelation, $this->getFile(static::EXPORT_FILE_RELATION));
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

        // insert meta services
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_META);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('meta_service', $data);
            }
        })();

        // insert meta service relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('meta_service_relation', $data);
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
        return 'host';
    }
}
