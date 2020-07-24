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

namespace Centreon\Infrastructure\Monitoring\Timeline;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Timeline\Interfaces\TimelineRepositoryInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Timeline\TimelineEvent;

/**
 * Database repository for timeline events.
 *
 * @package Centreon\Infrastructure\Monitoring
 */
final class TimelineRepositoryRDB extends AbstractRepositoryDRB implements TimelineRepositoryInterface
{
    /**
     * @var AccessGroup[] List of access group used to filter the requests
     */
    private $accessGroups = [];

    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @var ContactInterface
     */
    private $contact;

    public function __construct(DatabaseConnection $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Initialized by the dependency injector.
     *
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function setSqlRequestTranslator(SqlRequestParametersTranslator $sqlRequestTranslator): void
    {
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(
                RequestParameters::CONCORDANCE_MODE_STRICT
            );
    }

    /**
     * @inheritDoc
     */
    public function filterByAccessGroups(?array $accessGroups): TimelineRepositoryInterface
    {
        $this->accessGroups = $accessGroups;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findTimelineEventsByHost(Host $host): array
    {
        $timelineEvents = [];

        $sql = $this->translateDbName("SELECT
            l.log_id AS `id`,
            'event' as `type`,
            l.output AS `content`,
            l.ctime AS `timestamp`
            FROM `:dbstg`.`logs` l
            WHERE l.host_id = :host_id
        ");

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':host_id', $host->getId());
        $statement->execute();

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $timelineEvents[] = EntityCreator::createEntityByArray(
                TimelineEvent::class,
                $result
            );
        }

        /*
        // set ACL limitations
        if (!$this->isAdmin()) {
            $sql .= " INNER JOIN `:dbstg`.`centreon_acl` AS service_acl ON service_acl.host_id = l.host_id";
            if ($serviceId) {
                $sql .= " AND service_acl.service_id = l.service_id";
            }
            $sql .= " AND service_acl.group_id IN (" . $this->accessGroupIdToString($this->accessGroups) . ") ";
        }
        $sql .= " WHERE l.host_id = :hostId AND l.service_id = :serviceId";

        //Group to avoid duplicate entries
        $sql .= ' GROUP BY l.log_id';

        $sql .= $this->sqlRequestTranslator->translateSortParameterToSql() ?: ' ORDER BY timestamp DESC';
        */

        return $timelineEvents;
    }

    private function isAdmin(): bool
    {
        return ($this->contact !== null)
            ? $this->contact->isAdmin()
            : false;
    }

    /**
     * {@inheritDoc}
     */
    public function setContact(ContactInterface $contact): TimelineRepositoryInterface
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return bool Return FALSE if the contact is an admin or has at least one access group.
     */
    private function hasNotEnoughRightsToContinue(): bool
    {
        return ($this->contact !== null)
            ? !($this->contact->isAdmin() || count($this->accessGroups) > 0)
            : count($this->accessGroups) == 0;
    }
}
