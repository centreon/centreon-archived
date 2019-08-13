<?php
namespace CentreonRemote\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterCacheService;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;
use Centreon\Infrastructure\Service\CentcoreConfigService;

abstract class ExporterServiceAbstract implements ExporterServiceInterface
{

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $db;

    /**
     * @var \CentreonRemote\Infrastructure\Service\ExporterCacheService
     */
    protected $cache;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    protected $commitment;

    /**
     * @var \Centreon\Infrastructure\Service\CentcoreConfigService
     */
    protected $config;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->db = $services->get(\Centreon\ServiceProvider::CENTREON_DB_MANAGER);

        if ($services->has('centreon.config')) {
            $this->config = $services->get('centreon.config');
        }
    }

    public function setCache(ExporterCacheService $cache): void
    {
        $this->cache = $cache;
    }

    public function setCommitment(ExportCommitment $commitment): void
    {
        $this->commitment = $commitment;
    }

    public function setManifest(ExportManifest $manifest): void
    {
        $this->manifest = $manifest;
    }

    public static function getName(): string
    {
        return static::NAME;
    }

    /**
     * Create path for export
     *
     * @param string $exportPath
     * @return string
     */
    public function createPath(string $exportPath = null): string
    {
        // Create export path
        $exportPath = $this->getPath($exportPath);

        // make directory if missing
        if (!is_dir($exportPath)) {
            mkdir($exportPath, $this->commitment->getFilePermission(), true);
        }

        return $exportPath;
    }

    /**
     * Get path of export
     *
     * @param string $exportPath
     * @return string
     */
    public function getPath(string $exportPath = null): string
    {
        $exportPath = $exportPath ?? $this->commitment->getPath() . '/' . $this->getName();

        return $exportPath;
    }

    /**
     * Get exported file
     *
     * @param string $filename
     * @return string
     */
    public function getFile(string $filename): string
    {
        $exportFilepath = $this->getPath() . '/' . $filename;

        return $exportFilepath;
    }

    public static function order(): int
    {
        return 10;
    }

    protected function _getIf(string $key, callable $data)
    {
        $result = $this->cache->getIf($key, $data);

        return $result;
    }
}
