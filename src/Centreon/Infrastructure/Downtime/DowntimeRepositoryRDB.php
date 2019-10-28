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
declare(strict_types=1);

namespace Centreon\Infrastructure\Downtime;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Downtime\Interfaces\DowntimeRepositoryInterface;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

class DowntimeRepositoryRDB extends AbstractRepositoryDRB implements DowntimeRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @var AccessGroup[] List of access group used to filter the requests
     */
    private $accessGroups;

    /**
     * @var ContactInterface
     */
    private $contact;

    public function __construct(
        DatabaseConnection $db,
        SqlRequestParametersTranslator $sqlRequestTranslator
    ) {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
    }

    /**
     * @inheritDoc
     */
    public function forAccessGroups(?array $accessGroups): DowntimeRepositoryInterface
    {
        $this->accessGroups = $accessGroups;
        return $this;
    }

    /**
     * @return bool Return TRUE if the contact is an admin or has at least one access group.
     */
    private function hasNotEnoughRightsToContinue(): bool
    {
        return count($this->accessGroups) == 0;
    }

    /**
     * @inheritDoc
     */
    public function findHostDowntimeForNonAdminUser(): array
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return [];
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function findHostDowntimeForAdminUser(): array
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return [];
        }

        // TODO: Implement findHostDowntime() method.
        return [];
    }
}