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

namespace Centreon\Infrastructure\Option;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Option\Interfaces\OptionRepositoryInterface;
use Centreon\Domain\Option\Option;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;

/**
 * This class is designed to manage the configuration options of Centreon in
 * the database (table options).
 *
 * @package Centreon\Infrastructure\Options
 */
class OptionRepositoryRDB extends AbstractRepositoryDRB implements OptionRepositoryInterface
{
    /**
     * @var Option[]
     */
    private $options;

    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function findAllOptions(bool $useCache = true): array
    {
        if ($useCache && !empty($this->options)) {
            return $this->options;
        }
        $request = $this->translateDbName('SELECT `key` AS `name`, `value` FROM `:db`.options');
        if (false !== ($statement = $this->db->query($request))) {
            $statement->execute();

            while (false !== ($option = $statement->fetch(\PDO::FETCH_ASSOC))) {
                $this->options[] = EntityCreator::createEntityByArray(Option::class, $option);
            }
        }
        return $this->options;
    }
}
