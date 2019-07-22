<?php
namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportCommitment;
use DateTime;
use Exception;

class ExportManifest
{

    const EXPORT_FILE = 'manifest.json';
    const ERR_CODE_MANIFEST_NOT_FOUND = 1001;
    const ERR_CODE_MANIFEST_WRONG_FORMAT = 1002;
    const ERR_CODE_INCOMPATIBLE_VERSIONS = 1005;

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
        
        $checkManifestKeys = function (array $data) : array {
            $keys = ['date', 'remote_server', 'pollers', 'import'];
            $missingKeys = [];
            
            foreach ($keys as $key) {
                if (!array_key_exists($key, $data)) {
                    $missingKeys[] = $key;
                }
            }
            
            return $missingKeys;
        };

        if ($missingKeys = $checkManifestKeys($this->data)) {
            throw new Exception(sprintf("Missing data in a manifest file:\n - %s", join("\n - ", $missingKeys)), static::ERR_CODE_MANIFEST_WRONG_FORMAT);
        }

        // # Compare only the major and minor version, not bugfix because no SQL schema changes
        // $centralVersion = preg_replace('/^(\d+\.\d+).*/', '$1', $this->data['version']);
        // $remoteVersion = preg_replace('/^(\d+\.\d+).*/', '$1', $this->version);

        // if (!version_compare($centralVersion, $remoteVersion, '==')) {
        //     throw new Exception(
        //         sprintf(
        //             'The version of the Central %s and of the Remote %s are incompatible',
        //             $this->data['version'],
        //             $this->version
        //         ),
        //         static::ERR_CODE_INCOMPATIBLE_VERSIONS
        //     );
        // }

        return $this->data;
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
