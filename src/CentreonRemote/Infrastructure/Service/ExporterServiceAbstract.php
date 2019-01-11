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
        $this->db = $services->get('centreon.db-manager');

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

    public static function order(): int {
        return 10;
    }

    protected function _parse(string $filename): array
    {
        $macros = null;

        if ($this->config !== null) {
            $macros = function(&$result) {
                $result !== null ? $this->config->replaceMacros($result) : null;
            };
        }

        $result = $this->commitment->getParser()->parse($filename, $macros);

        return $result;
    }

    protected function _getIf(string $key, callable $data)
    {
        $result = $this->cache->getIf($key, $data);

        return $result;
    }

    protected function _dump(array $input, string $filename): void
    {
        $this->commitment->getParser()->dump($input, $filename);

        $this->manifest->addFile($filename);
    }

    protected function _mergeDump(array $input, string $filename, $uniqueId = 'id')
    {
        $data = $this->_parse($filename);

        if ($data) {
            foreach ($data as $row) {
                $id = $row[$uniqueId];

                foreach ($input as $_key => $_row) {
                    $_id = $_row[$uniqueId];
                    if ($id === $_id) {
                        unset($input[$_key]);
                    }
                }
            }

            $input = array_merge($data, $input);
        }

        $this->_dump($input, $filename);
    }
}
