<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportParserJson;
use CentreonRemote\Infrastructure\Export\ExportParserInterface;

final class ExportCommitment
{

    /**
     * @var int[]
     */
    private $pollers;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportParserInterface
     */
    private $parser;

    /**
     * @var array<mixed>
     */
    private $exporters;

    /**
     * @var int
     */
    private $filePermission = 0775;

    /**
     * @var int
     */
    private $remote;

    /**
     * Construct
     *
     * @param int $remote
     * @param int[] $pollers
     * @param array<mixed> $meta
     * @param \CentreonRemote\Infrastructure\Export\ExportParserInterface $parser
     * @param string $path
     * @param array<int,string> $exporters
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
            $this->path = _CENTREON_CACHEDIR_ . '/config/export/' . $this->remote;
        }

        $this->parser = $parser ?? new ExportParserJson();
    }

    public function getRemote(): int
    {
        return $this->remote;
    }

    /**
     *
     * @return int[]
     */
    public function getPollers()
    {
        return $this->pollers;
    }

    /**
     *
     * @return array<mixed>|null
     */
    public function getMeta()
    {
        return $this->meta;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     *
     * @return array<mixed>
     */
    public function getExporters()
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
