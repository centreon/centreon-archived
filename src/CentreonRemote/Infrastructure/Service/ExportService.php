<?php
namespace CentreonRemote\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use Centreon\Domain\Repository\InformationsRepository;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;

class ExportService
{

    const PATH_EXPORTED_DATA = '/var/lib/centreon/remote-data';

    /**
     * @var \CentreonRemote\Infrastructure\Service\ExporterService
     */
    private $exporter;

    /**
     * @var \CentreonRemote\Infrastructure\Service\ExporterCacheService
     */
    private $cache;

    /**
     * @var String
     */
    private $version;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->exporter = $services->get('centreon_remote.exporter');
        $this->cache = $services->get('centreon_remote.exporter.cache');
        $version = $services->get('centreon.db-manager')
            ->getRepository(InformationsRepository::class)
            ->getOneByKey('version')
        ;

        if ($version) {
            $this->version = $version->getValue();
        }
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

        // remove export directory if exists
        $exportPath = $commitment->getPath();
        if (is_dir($exportPath)) {
            system('rm -rf ' . escapeshellarg($exportPath));
        }

        $manifest = new ExportManifest($commitment, $this->version);

        foreach ($this->exporter->all() as $exporterMeta) {
            if ($filterExporters && !in_array($exporterMeta['classname'], $filterExporters)) {
                continue;
            }

            $exporter = $exporterMeta['factory']();
            $exporter->setCommitment($commitment);
            $exporter->setManifest($manifest);
            $exporter->setCache($this->cache);
            $exporter->export();

            // add exporter to manifest
            $manifest->addExporter($exporterMeta['classname']);
        }

        $this->cache->destroy();
        $manifest->dump();
    }

    /**
     * Import
     * 
     * @throws \Exception
     * @param \CentreonRemote\Infrastructure\Export\ExportCommitment $commitment
     */
    public function import(ExportCommitment $commitment = null): void
    {
        $commitment = $commitment ?? new ExportCommitment(null, null, null, null, static::PATH_EXPORTED_DATA);

        // check is export directory
        $exportPath = $commitment->getPath();

        if (!is_dir($exportPath)) {
            return;
        }

        $manifest = new ExportManifest($commitment, $this->version);
        $manifest->validate();

        $filterExporters = $manifest->get('exporters');

        foreach ($this->exporter->all() as $exporterMeta) {
            if (!in_array($exporterMeta['classname'], $filterExporters)) {
                continue;
            }

            $exporter = $exporterMeta['factory']();
            $exporter->setCommitment($commitment);
            $exporter->import();
        }

        // backup expot directory
        system('rm -rf ' . escapeshellarg($exportPath));
    }
}
