<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use CentreonRemote\Infrastructure\Service\ExporterServicePartialInterface;
use Centreon\Domain\Repository;

class MetaServiceExporter extends ExporterServiceAbstract implements ExporterServicePartialInterface
{

    const NAME = 'meta-service';
    const EXPORT_FILE_META = 'meta_service.yaml';
    const EXPORT_FILE_RELATION = 'meta_service_relation.yaml';

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

        // Extract data
        (function() use ($pollerIds, $hostTemplateChain) {
            $metaServices = $this->db
                ->getRepository(Repository\MetaServiceRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($metaServices, $this->getFile(static::EXPORT_FILE_META));
        })();

        (function() use ($pollerIds, $hostTemplateChain) {
            $metaServiceRelation = $this->db
                ->getRepository(Repository\MetaServiceRelationRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($metaServiceRelation, $this->getFile(static::EXPORT_FILE_RELATION));
        })();
    }

    public function exportPartial(): void
    {
        $metaList = $this->cache->get('meta.list');

        if (!$metaList) {
            return;
        }

        // Extract data
        (function() use ($metaList) {
            $data = $this->db
                ->getRepository(Repository\MetaServiceRepository::class)
                ->exportList($metaList)
            ;
            $this->_mergeDump($data, $this->getFile(static::EXPORT_FILE_META), 'meta_id');
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

        // insert meta services
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_META);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('meta_service', $data);
            }
        })();

        // insert meta service relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('meta_service_relation', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }
}
