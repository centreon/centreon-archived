<?php
namespace CentreonRemote\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use Centreon\Domain\Repository;
use Centreon\Domain\Repository\Interfaces\AclResourceRefreshInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;
use CentreonRemote\Infrastructure\Service\ExporterServicePartialInterface;
use ReflectionClass;
use WebDriver\Exception;

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
     * @var \CentreonClapi\CentreonACL
     */
    private $acl;

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    private $db;

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
        $this->acl = $services->get('centreon.acl');
        $this->db = $services->get(\Centreon\ServiceProvider::CENTREON_DB_MANAGER);

        $version = $this->db
            ->getRepository(Repository\InformationsRepository::class)
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
     * @throws \Exception
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
        $partials = [];
        $interface = ExporterServicePartialInterface::class;

        foreach ($this->exporter->all() as $exporterMeta) {
            if ($filterExporters && !in_array($exporterMeta['classname'], $filterExporters)) {
                continue;
            }

            $exporter = $exporterMeta['factory']();
            $exporter->setCommitment($commitment);
            $exporter->setManifest($manifest);
            $exporter->setCache($this->cache);
            $exporter->export();

            $hasInterface = (new ReflectionClass($exporter))->implementsInterface($interface);

            if ($hasInterface) {
                $partials[] = $exporter;
            }

            // add exporter to manifest
            $manifest->addExporter($exporterMeta['classname']);
        }

        // export partial data
        foreach ($partials as $partial) {
            $partial->exportPartial();
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

        $filterExporters = $manifest->get('exporters') ?? [];

        foreach ($this->exporter->all() as $exporterMeta) {
            if (!in_array($exporterMeta['classname'], $filterExporters)) {
                continue;
            }

            $exporter = $exporterMeta['factory']();
            $exporter->setCommitment($commitment);
            $exporter->import();
        }

        // cleanup ACL removed data
        $this->_refreshAcl();

        // remove export directory
        system('rm -rf ' . escapeshellarg($exportPath));
    }

    private function _refreshAcl(): void
    {
        // cleanup resource table from deleted entities
        $resourceList = [
            Repository\AclResourcesHcRelationsRepository::class,
            Repository\AclResourcesHgRelationsRepository::class,
            Repository\AclResourcesHostexRelationsRepository::class,
            Repository\AclResourcesHostRelationsRepository::class,
            Repository\AclResourcesMetaRelationsRepository::class,
            Repository\AclResourcesPollerRelationsRepository::class,
            Repository\AclResourcesScRelationsRepository::class,
            Repository\AclResourcesServiceRelationsRepository::class,
            Repository\AclResourcesSgRelationsRepository::class,
        ];

        foreach ($resourceList as $resource) {
            $repository = $this->db->getRepository($resource);

            if ($repository instanceof AclResourceRefreshInterface) {
                $repository->refresh();
            }
        }

        // refresh ACL
        $this->acl->reload(true);
    }
}
