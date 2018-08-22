<?php
namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportParserYaml;

class ExportCommitment
{

    /**
     * @var int
     */
    private $poller;

    /**
     * @var string
     */
    private $path;

    /**
     * @var ExportParserYaml
     */
    private $parser;

    /**
     * @var array
     */
    private $exporters;

    /**
     * @var int
     */
    private $filePermission = 0777;

    /**
     * Construct
     * 
     * @param int $poller
     * @param string $path
     * @param array $exporters
     */
    public function __construct(int $poller, string $path = null, array $exporters = null)
    {
        $this->poller = $poller;
        $this->path = $path;
        $this->exporters = $exporters ?? [];

        if ($this->path === null) {
            $this->path = _CENTREON_PATH_ . 'filesGeneration/export/' . $poller;
        }

        $this->parser = new ExportParserYaml;
    }

    public function getPoller(): int
    {
        return $this->poller;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getExporters(): array
    {
        return $this->exporters;
    }

    public function getFilePermission(): int
    {
        return $this->filePermission;
    }

    public function getParser(): ExportParserYaml
    {
        return $this->parser;
    }
}
