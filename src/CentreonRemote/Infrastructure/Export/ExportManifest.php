<?php
namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportCommitment;
use DateTime;

class ExportManifest
{

    const EXPORT_FILE = 'manifest.yaml';

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

    public function __construct(ExportCommitment $commitment, string $version = null)
    {
        $this->commitment = $commitment;
        $this->version = $version;
    }
    
    public function addExporter(string $exporters) {
        $this->exporters[] = $exporters;
    }

    public function addFile(string $file): void
    {
        if (!file_exists($file)) {
            return;
        }

        $filepath = explode($this->commitment->getPath(), $file);

        if (!array_key_exists(1, $filepath)) {
            return;
        }

        $this->files[$filepath[1]] = md5_file($file);
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
        $file = $this->commitment->getPath() . '/' . static::EXPORT_FILE;

        $this->commitment->getParser()::dump($data, $file);
    }
}
