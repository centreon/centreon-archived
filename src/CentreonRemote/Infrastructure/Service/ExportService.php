<?php

namespace CentreonRemote\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use Centreon\Domain\Repository;
use Centreon\Domain\Repository\Interfaces\AclResourceRefreshInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;

class ExportService
{
    /**
     * Path to store exported remote server files
     *
     * @var string
     */
    private $pathExportedData;

    /**
     * @var \CentreonRemote\Infrastructure\Service\ExporterService
     */
    private $exporter;

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
        $this->acl = $services->get('centreon.acl');
        $this->db = $services->get(\Centreon\ServiceProvider::CENTREON_DB_MANAGER);

        $this->pathExportedData = _CENTREON_CACHEDIR_ . '/config/remote-data';

        $version = $this->db
            ->getRepository(Repository\InformationsRepository::class)
            ->getOneByKey('version');

        if ($version) {
            $this->version = $version->getValue();
        }
    }

    /**
     * Export all that is registered in exporter
     *
     * @param \CentreonRemote\Infrastructure\Export\ExportCommitment $commitment
     *
     * @throws \Exception
     * @todo separate work of exporters
     */
    public function export(ExportCommitment $commitment): void
    {
        $remoteId = $commitment->getRemote();

        // remove export directory if exists
        $exportPath = $commitment->getPath();
        if (is_dir($exportPath)) {
            system('rm -rf ' . escapeshellarg($exportPath));
        }

        $manifest = new ExportManifest($commitment, $this->version);

        // export configuration and media
        $configurationExporter = $this->exporter->get('configuration')['factory']();
        $configurationExporter->setCommitment($commitment);
        $exportManifest = $configurationExporter->export($remoteId);

        $manifest->dump($exportManifest);
    }

    /**
     * Import
     *
     * @param \CentreonRemote\Infrastructure\Export\ExportCommitment $commitment
     *
     * @throws \Exception
     */
    public function import(ExportCommitment $commitment = null): void
    {
        $commitment = $commitment ?? new ExportCommitment(null, null, null, null, $this->pathExportedData);

        // check is export directory
        $exportPath = $commitment->getPath();

        if (!is_dir($exportPath)) {
            return;
        }

        // parse manifest
        $manifest = new ExportManifest($commitment, $this->version);
        $manifest->validate();

        // import configuration and media
        $configurationExporter = $this->exporter->get('configuration')['factory']();
        $configurationExporter->setCommitment($commitment);
        $configurationExporter->import($manifest);

        // cleanup ACL removed data
        $this->refreshAcl();

        // remove export directory
        system('rm -rf ' . escapeshellarg($exportPath));
    }

    private function refreshAcl(): void
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
