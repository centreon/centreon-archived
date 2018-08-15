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
     * @var int
     */
    private $filePermission = 0777;

    /**
     * Construct
     * 
     * @param int $poller
     * @param string $path
     */
    public function __construct(int $poller, string $path = null)
    {
        $this->poller = $poller;
        $this->path = $path;

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

    public function getFilePermission(): int
    {
        return $this->filePermission;
    }

    public function getParser(): ExportParserYaml
    {
        return $this->parser;
    }
}
