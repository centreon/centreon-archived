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

namespace CentreonRemote\Domain\Exporter;

use Pimple\Container;
use CentreonRemote\Infrastructure\Export\ExportManifest;
use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;

use ConfigGenerateRemote\Manifest;

class ConfigurationExporter extends ExporterServiceAbstract
{
    private const MEDIA_PATH = _CENTREON_PATH_ . 'www/img/media';

    /** @var \ConfigGenerateRemote\Generate */
    private $generateService;

    /**
     * Set generate service
     *
     * @param \ConfigGenerateRemote\Generate $generateService
     * @return void
     */
    public function setGenerateService(\ConfigGenerateRemote\Generate $generateService): void
    {
        $this->generateService = $generateService;
    }

    /**
     * Export data
     * @param int $remoteId
     */
    public function export(int $remoteId): array
    {
        // create path
        $this->createPath();

        $this->generateService->configRemoteServerFromId($remoteId, 'user');

        return Manifest::getInstance($this->dependencyInjector)->getManifest();
    }

    /**
     * Import data
     *
     * @param \CentreonRemote\Infrastructure\Export\ExportManifest $manifest
     */
    public function import(ExportManifest $manifest): void
    {
        // skip if no data
        if (!is_dir($this->getPath())) {
            return;
        }

        $db = $this->db->getAdapter('configuration_db');

        // start transaction
        $db->beginTransaction();

        try {
            $truncated = [];
            // allow insert records without foreign key checks
            $db->query('SET FOREIGN_KEY_CHECKS=0;');

            $import = $manifest->get("import");
            foreach ($import['data'] as $data) {
                if (!isset($truncated[$data['table']])) {
                    // empty table
                    $db->query("DELETE FROM `" . $data['table'] . "`");
                    // optimize table
                    $db->query("OPTIMIZE TABLE `" . $data['table'] . "`");
                    $truncated[$data['table']] = 1;
                }

                // insert data
                $exportPathFile = $this->getFile($data['filename']);
                echo date("Y-m-d H:i:s") . " - INFO - Loading '" . $exportPathFile . "'.\n";
                $db->loadDataInfile(
                    $exportPathFile,
                    $data['table'],
                    $import['infile_clauses']['fields_clause'],
                    $import['infile_clauses']['lines_clause'],
                    $data['columns']
                );
            }

            // restore foreign key checks
            $db->query('SET FOREIGN_KEY_CHECKS=1;');

            // commit transaction
            $db->commit();
        } catch (\ErrorException $e) {
            // rollback changes
            $db->rollBack();
            echo date("Y-m-d H:i:s") . " - ERROR - Loading failed.\n";
        }

        // media copy
        $exportPathMedia = $this->commitment->getPath() . "/media";
        $mediaPath = self::MEDIA_PATH;
        $this->recursiveCopy($exportPathMedia, $mediaPath);
    }

    /**
     * Copy directory recursively
     * @param string $src
     * @param string $dst
     */
    private function recursiveCopy($src, $dst): void
    {
        $dir = opendir($src);
        @mkdir($dst, $this->commitment->getFilePermission(), true);
        while (($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    echo date("Y-m-d H:i:s") . " - INFO - Copying '" . $src . "/" . $file . "'.\n";
                    copy($src . '/' . $file, $dst . '/' . $file);
                    chmod($dst . '/' . $file, $this->commitment->getFilePermission());
                }
            }
        }
        closedir($dir);
    }

    public static function order(): int
    {
        return 40;
    }
}
