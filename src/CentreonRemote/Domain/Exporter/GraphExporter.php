<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use CentreonRemote\Infrastructure\Service\ExporterServicePartialInterface;
use Centreon\Domain\Repository;

class GraphExporter extends ExporterServiceAbstract implements ExporterServicePartialInterface
{

    const NAME = 'graph';
    const EXPORT_FILE_GRAPH = 'giv_graphs_template.yaml';

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\GivGraphTemplateRepository::class)->truncate();
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
        $graphs = $this->db
            ->getRepository(Repository\GivGraphTemplateRepository::class)
            ->export($pollerIds, $hostTemplateChain, $serviceTemplateChain)
        ;

        $this->_dump($graphs, $this->getFile(static::EXPORT_FILE_GRAPH));
    }

    public function exportPartial(): void
    {
        $graphList = $this->cache->get('graph.list');

        if (!$graphList) {
            return;
        }

        // Extract data
        $graphs = $this->db
            ->getRepository(Repository\GivGraphTemplateRepository::class)
            ->exportList($graphList)
        ;

        $this->_mergeDump($graphs, $this->getFile(static::EXPORT_FILE_GRAPH), 'graph_id');
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

        // insert graphs
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GRAPH);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('giv_graphs_template', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }
}
