<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

declare(strict_types=1);

namespace Core\Platform\Infrastructure\Repository;

use Pimple\Container;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Platform\Application\Repository\WriteUpdateRepositoryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Centreon\Domain\Repository\RepositoryException;

class DbWriteUpdateRepository extends AbstractRepositoryDRB implements WriteUpdateRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param string $libDir
     * @param string $installDir
     * @param Container $dependencyInjector
     * @param DatabaseConnection $db
     * @param Filesystem $filesystem
     */
    public function __construct(
        private string $libDir,
        private string $installDir,
        private Container $dependencyInjector,
        DatabaseConnection $db,
        private Filesystem $filesystem,
    ) {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function runUpdate(string $version): void
    {
        $this->runMonitoringSql($version);
        $this->runScript($version);
        $this->runConfigurationSql($version);
        $this->runPostScript($version);
        $this->updateVersionInformation($version);
    }

    /**
     * @inheritDoc
     */
    public function runPostUpdate(string $currentVersion): void
    {
        if (! $this->filesystem->exists($this->installDir)) {
            return;
        }

        $this->backupInstallDirectory($currentVersion);
        $this->removeInstallDirectory();
    }

    /**
     * Backup installation directory
     *
     * @param string $currentVersion
     */
    private function backupInstallDirectory(string $currentVersion): void
    {
        $backupDirectory = $this->libDir . '/installs/install-' . $currentVersion . '-' . date('Ymd_His');

        $this->info(
            "Backing up installation directory",
            [
                'source' => $this->installDir,
                'destination' => $backupDirectory,
            ],
        );

        $this->filesystem->mirror(
            $this->installDir,
            $backupDirectory,
        );
    }

    /**
     * Remove installation directory
     */
    private function removeInstallDirectory(): void
    {
        $this->info(
            "Removing installation directory",
            [
                'installation_directory' => $this->installDir,
            ],
        );

        $this->filesystem->remove($this->installDir);
    }

    /**
     * Run sql queries on monitoring database
     *
     * @param string $version
     */
    private function runMonitoringSql(string $version): void
    {
        $upgradeFilePath = $this->installDir . '/sql/centstorage/Update-CSTG-' . $version . '.sql';
        if (is_readable($upgradeFilePath)) {
            $this->db->switchToDb($this->db->getStorageDbName());
            $this->runSqlFile($upgradeFilePath);
        }
    }

    /**
     * Run php upgrade script
     *
     * @param string $version
     */
    private function runScript(string $version): void
    {
        $pearDB = $this->dependencyInjector['configuration_db'];
        $pearDBO = $this->dependencyInjector['realtime_db'];

        $upgradeFilePath = $this->installDir . '/php/Update-' . $version . '.php';
        if (is_readable($upgradeFilePath)) {
            include_once $upgradeFilePath;
        }
    }

    /**
     * Run sql queries on configuration database
     *
     * @param string $version
     */
    private function runConfigurationSql(string $version): void
    {
        $upgradeFilePath = $this->installDir . '/sql/centreon/Update-DB-' . $version . '.sql';
        if (is_readable($upgradeFilePath)) {
            $this->db->switchToDb($this->db->getCentreonDbName());
            $this->runSqlFile($upgradeFilePath);
        }
    }

    /**
     * Run php post upgrade script
     *
     * @param string $version
     */
    private function runPostScript(string $version): void
    {
        $pearDB = $this->dependencyInjector['configuration_db'];
        $pearDBO = $this->dependencyInjector['realtime_db'];

        $upgradeFilePath = $this->installDir . '/php/Update-' . $version . '.post.php';
        if (is_readable($upgradeFilePath)) {
            include_once $upgradeFilePath;
        }
    }

    /**
     * Update version information
     *
     * @param string $version
     */
    private function updateVersionInformation(string $version): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                "UPDATE `:db`.`informations` SET `value` = :version WHERE `key` = 'version'"
            )
        );
        $statement->bindValue(':version', $version, \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * Run sql file and use temporary file to store last executed line
     *
     * @param string $filePath
     * @return void
     */
    private function runSqlFile(string $filePath): void
    {
        set_time_limit(0);

        $fileName = basename($filePath);
        $tmpFile = $this->installDir . '/tmp/' . $fileName;

        $alreadyExecutedQueriesCount = $this->getAlreadyExecutedQueriesCount($tmpFile);

        if (is_readable($filePath)) {
            $fileStream = fopen($filePath, 'r');
            if (is_resource($fileStream)) {
                $query = '';
                $currentLineNumber = 0;
                $executedQueriesCount = 0;
                try {
                    while (! feof($fileStream)) {
                        $currentLineNumber++;
                        $currentLine = fgets($fileStream);
                        if ($currentLine && ! $this->isSqlComment($currentLine)) {
                            $query .= ' ' . trim($currentLine);
                        }

                        if ($this->isSqlCompleteQuery($query)) {
                            $executedQueriesCount++;
                            if ($executedQueriesCount > $alreadyExecutedQueriesCount) {
                                try {
                                    $this->executeQuery($query);
                                } catch (RepositoryException $ex) {
                                    $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
                                    throw $ex;
                                }

                                $this->writeExecutedQueriesCountInTemporaryFile($tmpFile, $executedQueriesCount);
                            }
                            $query = '';
                        }
                    }
                } catch (\Throwable $ex) {
                    $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
                    throw $ex;
                } finally {
                    fclose($fileStream);
                }
            }
        }
    }

    /**
     * Get stored executed queries count in temporary file to retrieve next query to run in case of an error occurred
     *
     * @param string $tmpFile
     * @return int
     */
    private function getAlreadyExecutedQueriesCount(string $tmpFile): int
    {
        $startLineNumber = 0;
        if (is_readable($tmpFile)) {
            $lineNumber = file_get_contents($tmpFile);
            if (is_numeric($lineNumber)) {
                $startLineNumber = (int) $lineNumber;
            }
        }

        return $startLineNumber;
    }

    /**
     * Write executed queries count in temporary file to retrieve upgrade when an error occurred
     *
     * @param string $tmpFile
     * @param int $count
     */
    private function writeExecutedQueriesCountInTemporaryFile(string $tmpFile, int $count): void
    {
        if (! file_exists($tmpFile) || is_writable($tmpFile)) {
            $this->info('Writing in temporary file : ' . $tmpFile);
            file_put_contents($tmpFile, $count);
        } else {
            $this->warning('Cannot write in temporary file : ' . $tmpFile);
        }
    }

    /**
     * Check if a line a sql comment
     *
     * @param string $line
     * @return bool
     */
    private function isSqlComment(string $line): bool
    {
        return str_starts_with(trim($line), '--');
    }

    /**
     * Check if a query is complete (trailing semicolon)
     *
     * @param string $query
     * @return bool
     */
    private function isSqlCompleteQuery(string $query): bool
    {
        return ! empty(trim($query)) && preg_match('/;\s*$/', $query);
    }

    /**
     * Execute sql query
     *
     * @param string $query
     *
     * @throws \Exception
     */
    private function executeQuery(string $query): void
    {
        try {
            $this->db->query($query);
        } catch (\Exception $ex) {
            $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            throw new RepositoryException('Cannot execute query: ' . $query, 0, $ex);
        }
    }
}
