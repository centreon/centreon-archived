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

namespace CentreonRemote\Infrastructure\Service;

use Pimple\Container;
use CentreonRemote\Infrastructure\Service\ExporterCacheService;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;

abstract class ExporterServiceAbstract implements ExporterServiceInterface
{
    /**
     * @var Container $dependencyInjector
     */
    protected $dependencyInjector;

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
     * @var mixed
     */
    protected $manifest;

    /**
     * Construct
     *
     * @param Container $services
     */
    public function __construct(Container $services)
    {
        $this->dependencyInjector = $services;
        $this->db = $services['centreon.db-manager'];
        $this->config = $services['centreon.config'];
    }

    /**
     * @param ExporterCacheService $cache
     */
    public function setCache(ExporterCacheService $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @param ExportCommitment $commitment
     */
    public function setCommitment(ExportCommitment $commitment): void
    {
        $this->commitment = $commitment;
    }

    /**
     * @param ExportManifest $manifest
     */
    public function setManifest(ExportManifest $manifest): void
    {
        $this->manifest = $manifest;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return static::NAME;
    }

    /**
     * Create path for export
     *
     * @param string $exportPath
     *
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
     *
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
     *
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
}
