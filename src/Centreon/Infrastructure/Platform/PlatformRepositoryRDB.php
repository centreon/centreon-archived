<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\Platform;

use Centreon\Domain\Platform\Interfaces\PlatformRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;

/**
 * This class is designed to read version numbers from a database.
 *
 * @package Centreon\Infrastructure\Platform
 */
class PlatformRepositoryRDB extends AbstractRepositoryDRB implements PlatformRepositoryInterface
{
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function getWebVersion(): ?string
    {
        $request = $this->translateDbName('SELECT `value` FROM `:db`.informations WHERE `key` = "version"');
        if (($statement = $this->db->query($request)) !== false) {
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return (string) $result['value'];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getModulesVersion(): array
    {
        $versions = [];
        $request = $this->translateDbName('SELECT `name`, `mod_release` AS version FROM `:db`.modules_informations');
        if (($statement = $this->db->query($request)) !== false) {
            while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $versions[(string) $result['name']] = (string) $result['version'];
            }
        }
        return $versions;
    }

    /**
     * @inheritDoc
     */
    public function getWidgetsVersion(): array
    {
        $versions = [];
        $request = $this->translateDbName('SELECT `title`, `version` FROM `:db`.widget_models');
        if (($statement = $this->db->query($request)) !== false) {
            while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $versions[(string) $result['title']] = (string) $result['version'];
            }
        }
        return $versions;
    }
}
