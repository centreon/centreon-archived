<?php
namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportParserYaml;

final class ExportCommitment
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
     * @param int $remote
     * @param int[] $pollers
     * @param string $path
     * @param array $exporters
     */
    public function __construct(int $remote, array $pollers = null, string $path = null, array $exporters = null)
    {
        $this->remote = $remote;
        $this->pollers = $pollers;
        $this->path = $path;
        $this->exporters = $exporters ?? [];

        if ($this->path === null) {
            $this->path = _CENTREON_PATH_ . 'filesGeneration/export/' . $this->remote;
        }

        $this->parser = new ExportParserYaml;
    }

    public function getRemote(): array
    {
        return $this->remote;
    }

    public function getPollers(): array
    {
        return $this->pollers;
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
