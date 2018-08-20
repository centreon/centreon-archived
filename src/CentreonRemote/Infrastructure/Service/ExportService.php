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
        $filterExporters = $commitment->getExporters();

        foreach ($this->exporter->all() as $exporterMeta) {
            if ($filterExporters !== null && !in_array($exporterMeta['classname'], $filterExporters)) {
                continue;
            }

            $exporter = $exporterMeta['factory']();
            $exporter->setCommitment($commitment);
            $exporter->export();
        }
    }

    /**
     * Import
     * 
     * @param \CentreonRemote\Infrastructure\Export\ExportCommitment $commitment
     */
    public function import(ExportCommitment $commitment): void
    {
        $filterExporters = $commitment->getExporters();

        foreach ($this->exporter->all() as $exporterMeta) {
            if ($filterExporters !== null && !in_array($exporterMeta['classname'], $filterExporters)) {
                continue;
            }

            $exporter = $exporterMeta['factory']();
            $exporter->setCommitment($commitment);
            $exporter->import();
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
