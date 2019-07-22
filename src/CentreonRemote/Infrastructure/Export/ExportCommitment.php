<?php
namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportParserJson;
use CentreonRemote\Infrastructure\Export\ExportParserInterface;

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
     * @var \CentreonRemote\Infrastructure\Export\ExportParserInterface
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
     * @param array $meta
     * @param \CentreonRemote\Infrastructure\Export\ExportParserInterface $parser
     * @param string $path
     * @param array $exporters
     */
    public function __construct(
        int $remote = null,
        array $pollers = null,
        array $meta = null,
        ExportParserInterface $parser = null,
        string $path = null,
        array $exporters = null
    ) {
        if ($remote && $pollers && !in_array($remote, $pollers)) {
            $pollers[] = $remote;
        }

        $this->remote = $remote;
        $this->pollers = $pollers;
        $this->meta = $meta;
        $this->path = $path;
        $this->exporters = $exporters ?? [];

        if ($this->path === null) {
            $this->path = _CENTREON_PATH_ . 'filesGeneration/export/' . $this->remote;
        }

        $this->parser = $parser ?? new ExportParserJson;
    }

    public function getRemote(): int
    {
        return $this->remote;
    }

    public function getPollers(): array
    {
        return $this->pollers;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
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

    public function getParser(): ExportParserInterface
    {
        return $this->parser;
    }
}
