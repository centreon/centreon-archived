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

namespace Core\Infrastructure\Configuration\MetaService\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\Configuration\MetaService\Repository\ReadMetaServiceRepositoryInterface;
use Core\Domain\Configuration\Model\MetaService;

class DbReadMetaServiceRepository extends AbstractRepositoryDRB implements ReadMetaServiceRepositoryInterface
{
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
    public function findMetaServiceByIdAndAccessGroupIds(int $metaId, array $accessGroupIds): ?MetaService
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findMetaServiceById(int $metaId): ?MetaService
    {
        return null;
    }
}
