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

use CentreonRemote\Infrastructure\Export\ExportCommitment;
use DateTime;
use Exception;

/**
 * Writes manifest of exported files and reads them for import process.
 */
class ExportManifest
{
    public const EXPORT_FILE = 'manifest.json';
    public const ERR_CODE_MANIFEST_NOT_FOUND = 1001;
    public const ERR_CODE_MANIFEST_WRONG_FORMAT = 1002;
    public const ERR_CODE_INCOMPATIBLE_VERSIONS = 1005;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    private $commitment;

    /**
     * @var string
     */
    private $version;

    /**
     * @var array<mixed>
     */
    private $data;

    /**
     * @param ExportCommitment $commitment
     * @param string|null $version
     */
    public function __construct(ExportCommitment $commitment, string $version = null)
    {
        $this->commitment = $commitment;
        $this->version = $version;
    }

    /**
     * Retrieves data array field based on key
     *
     * @param string $key Key of data array to retrieve
     *
     * @return array<mixed>
     */
    public function get(string $key)
    {
        $result = $this->data && array_key_exists($key, $this->data) ? $this->data[$key] : null;

        return $result;
    }

    /**
     * Validate manifest format and return content
     * @throws Exception
     * @return array<mixed>
     */
    public function validate()
    {
        $file = $this->getFile();

        if (!file_exists($file)) {
            throw new Exception(sprintf('Manifest file %s not found', $file), static::ERR_CODE_MANIFEST_NOT_FOUND);
        }

        $this->data = $this->commitment->getParser()->parse($file);

        $checkManifestKeys = function (array $data): array {
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
            throw new Exception(
                sprintf("Missing data in a manifest file:\n - %s", join("\n - ", $missingKeys)),
                static::ERR_CODE_MANIFEST_WRONG_FORMAT
            );
        }

        # Compare only the major and minor version, not bugfix because no SQL schema changes
        $centralVersion = preg_replace('/^(\d+\.\d+).*/', '$1', $this->data['version']);
        $remoteVersion = preg_replace('/^(\d+\.\d+).*/', '$1', $this->version);

        if (!version_compare($centralVersion, $remoteVersion, '==')) {
            throw new Exception(
                sprintf(
                    'The version of the Central "%s" and of the Remote "%s" are incompatible',
                    $this->data['version'],
                    $this->version
                ),
                static::ERR_CODE_INCOMPATIBLE_VERSIONS
            );
        }

        return $this->data;
    }

    /**
     * Dump data in file
     *
     * @param array<string,mixed> $exportManifest
     * @return void
     */
    public function dump(array $exportManifest): void
    {
        $data = array_merge($exportManifest, ["version" => $this->version]);

        $this->commitment->getParser()->dump($data, $this->getFile());
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getFile(): string
    {
        $file = $this->commitment->getPath() . '/' . static::EXPORT_FILE;

        return $file;
    }
}
