<?php
namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportCommitment;
use Symfony\Component\Finder\Finder;
use DateTime;
use Exception;

class ExportManifest
{

    const EXPORT_FILE = 'manifest.yaml';
    const ERR_CODE_MANIFEST_NOT_FOUND = 1001;
    const ERR_CODE_MANIFEST_WRONG_FORMAT = 1002;
    const ERR_CODE_MISSING_EXPORTERS = 1003;
    const ERR_CODE_MISSING_DATA = 1004;
    const ERR_CODE_INCOMPATIBLE_VERSIONS = 1005;
    const ERR_CODE_MODIFIED_FILES = 1006;
    const ERR_CODE_MISSING_FILES = 1007;
    const ERR_CODE_EXTERNAL_FILES = 1008;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    private $commitment;

    /**
     * @var string
     */
    private $version;

    /**
     * @var array
     */
    private $files;

    /**
     * @var array
     */
    private $exporters;

    /**
     * @var array
     */
    private $data;

    public function __construct(ExportCommitment $commitment, string $version = null)
    {
        $this->commitment = $commitment;
        $this->version = $version;
    }

    public function addExporter(string $exporters)
    {
        $this->exporters[] = $exporters;
    }

    public function addFile(string $file): void
    {
        if (!file_exists($file)) {
            return;
        }

        $filepath = $this->removePath($file);

        if ($filepath === null) {
            return;
        }

        $this->files[$filepath] = md5_file($file);
    }

    public function get(string $key)
    {
        $result = $this->data && array_key_exists($key, $this->data) ? $this->data[$key] : null;

        return $result;
    }

    public function validate()
    {
        $file = $this->getFile();

        if (!file_exists($file)) {
            throw new Exception(sprintf('Manifest file %s not found', $file), static::ERR_CODE_MANIFEST_NOT_FOUND);
        }

        $this->data = $this->commitment->getParser()->parse($file);
        $checkManifestKeys = function(array $data) : array {
            $keys = ['version', 'datetime', 'remote-poller', 'pollers', 'meta', 'exporters', 'exports'];
            $missingKeys = [];
            
            foreach ($keys as $key) {
                if (!array_key_exists($key, $data)) {
                    $missingKeys[] = $key;
                }
            }
            
            return $missingKeys;
        };

        if ($missingKeys = $checkManifestKeys($this->data)) {
            throw new Exception(sprintf("Missing data in a manifest file:\n - %s", join("\n - %s", $missingKeys)), static::ERR_CODE_MANIFEST_WRONG_FORMAT);
        }

        if ($this->data['version'] !== $this->version) {
            throw new Exception(sprintf('The version of the Central %s and of the Remote %s are incompatible', $this->data['version'], $this->version), static::ERR_CODE_INCOMPATIBLE_VERSIONS);
        }

        if (!$this->data['exporters']) {
            throw new Exception('Missing exporters', static::ERR_CODE_MISSING_EXPORTERS);
        }

        if (!$this->data['exports']) {
            throw new Exception('Missing export data', static::ERR_CODE_MISSING_DATA);
        }

        $missing = $this->data['exports'];
        $modified = [];
        $externals = [];

        $finder = new Finder();
        $finder->files()->name('*.yaml')->in($this->commitment->getPath());

        // check for missing, modified and external files
        foreach ($finder as $file) {
            $filepath = $this->removePath($file->getPathName());

            // skip manifest
            if ($filepath === '/' . static::EXPORT_FILE) {
                continue;
            } elseif (array_key_exists($filepath, $this->data['exports'])) {
                // remove from missing list
                unset($missing[$filepath]);

                // check if the file has been modified
                $hash = md5_file($file);

                if ($this->data['exports'][$filepath] !== $hash) {
                    $modified[] = $filepath;
                }

                continue;
            }

            $externals[] = $filepath;
        }

        $missing = array_keys($missing);

        if ($missing) {
            throw new Exception(sprintf("Export contains external files:\n - %s", join("\n - ", $missing)), static::ERR_CODE_MISSING_FILES);
        }

        if ($modified) {
            throw new Exception(sprintf("Export files modified:\n - %s", join("\n - ", $modified)), static::ERR_CODE_MODIFIED_FILES);
        }

        if ($externals) {
            throw new Exception(sprintf("Export contains external files:\n - %s", join("\n - ", $externals)), static::ERR_CODE_EXTERNAL_FILES);
        }
    }

    public function dump(): void
    {
        $data = [
            'version' => $this->version,
            'datetime' => (new DateTime())->format(DateTime::W3C),
            'remote-poller' => $this->commitment->getRemote(),
            'pollers' => $this->commitment->getPollers(),
            'meta' => $this->commitment->getMeta(),
            'exporters' => $this->exporters,
            'exports' => $this->files,
        ];

        $this->commitment->getParser()->dump($data, $this->getFile());
    }

    public function removePath(string $file): ?string
    {
        $filepath = explode($this->commitment->getPath(), $file);

        if (!array_key_exists(1, $filepath)) {
            return null;
        }

        return $filepath[1];
    }

    public function getFile(): string
    {
        $file = $this->commitment->getPath() . '/' . static::EXPORT_FILE;

        return $file;
    }
}
