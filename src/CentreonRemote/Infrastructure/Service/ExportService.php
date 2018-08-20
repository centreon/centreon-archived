<?php
namespace CentreonRemote\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportParserYaml;

class ExportService
{

    /**
     * @var ExporterService
     */
    private $exporter;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->exporter = $services->get('centreon_remote.exporter');
    }

    /**
     * Export all that is registered in exporter
     * 
     * @todo separate work of exporters
     * 
     * @param \CentreonRemote\Infrastructure\Export\ExportCommitment $commitment
     */
    public function export(ExportCommitment $commitment): void
    {
        // @todo save info for executed job

        foreach ($this->exporter->all() as $exporterMeta) {
            $exporter = $exporterMeta['factory']();
            $exporter->setCommitment($commitment);
            $exporter->export();

            // @todo save info for executed job
        }
    }

    /**
     * Import
     * 
     * @param \CentreonRemote\Infrastructure\Export\ExportCommitment $commitment
     */
    public function import(ExportCommitment $commitment): void
    {
        foreach ($this->exporter->all() as $exporterMeta) {
            $exporter = $exporterMeta['factory']();
            $exporter->setCommitment($commitment);
            $exporter->import();
        }
    }
}
