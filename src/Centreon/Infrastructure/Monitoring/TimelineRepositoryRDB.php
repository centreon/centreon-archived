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

namespace Centreon\Infrastructure\Monitoring;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Entity\AckEventObject;
use Centreon\Domain\Monitoring\Entity\CommentEventObject;
use Centreon\Domain\Monitoring\Entity\DowntimeEventObject;
use Centreon\Domain\Monitoring\Entity\LogEventObject;
use Centreon\Domain\Monitoring\Interfaces\TimelineRepositoryInterface;
use Centreon\Domain\Monitoring\Model\Log;
use Centreon\Domain\Monitoring\TimelineEvent;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Symfony\Component\Validator\Constraints\Time;

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
    public function findTimelineEventsByHostAndService(int $hostId, int $serviceId = null): array
    {
        $timelineEvents = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $timelineEvents;
        }

        $this->sqlRequestTranslator->setConcordanceArray([
            'date' => 'event.timestamp'
        ]);

        $collector = new StatementCollector();
        $request = $this->translateDbName('SELECT SQL_CALC_FOUND_ROWS '
            . 'event.* '
            . 'FROM (('
            . $this->generateLogsQuery($collector, $hostId, $serviceId)
            . ') UNION ALL ('
            . $this->generateCommentsQuery($collector, $hostId, $serviceId)
            . ') UNION ALL ('
            . $this->generateAcknowledgementsQuery($collector, $hostId, $serviceId)
            . ') UNION ALL ('
            . $this->generateDowntimesQuery($collector, $hostId, $serviceId)
            . ')) AS  `event`');

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            // @todo update when date parameter converter is available
            $collector->addValue($key, strtotime(current($data)), key($data));
        }
        $request .= $searchRequest ? $searchRequest : '';

        // Group
        $request .= ' GROUP BY event.eventId';

        // Sort
        $request .= $this->sqlRequestTranslator->translateSortParameterToSql()
            ?: ' ORDER BY event.timestamp DESC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);
        $collector->bind($statement);

        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int)$result->fetchColumn()
        );

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $newEvent = EntityCreator::createEntityByArray(
                $this->detectClass($result['eventType']),
                $result
            );
            $timelineEvents[] = new TimelineEvent($newEvent);
        }

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

    /**
     * Generate SQL query for logs
     *
     * @param \Centreon\Infrastructure\CentreonLegacyDB\StatementCollector $collector
     * @param int $hostId
     * @param int $serviceId
     * @return string
     */
    protected function generateLogsQuery(StatementCollector $collector, int $hostId, int $serviceId = 0): string
    {
        $sql = "SELECT
		CONCAT('L', l.log_id) AS `eventId`,
        '" . LogEventObject::EVENTTYPE . "' AS `eventType`,
        l.log_id AS `id`,
        l.service_id AS `service_id`,
		l.output AS `output`,
		l.ctime AS `timestamp`,
        l.status AS `status`,
        l.type AS `type`,
        l.retry AS `retry`,
        l.notification_contact AS `contact`,
        null AS `contact_id`,
        l.notification_cmd AS `command`,
        NULL AS `persistent`,    
        NULL as `deletion_time`,
        NULL AS `start_time`,
        NULL AS `end_time`,
        NULL AS `actual_start_time`,
        NULL AS `actual_end_time`,
        NULL AS `duration`,
        NULL AS `started`,
        NULL AS `cancelled`,
        NULL AS `fixed`,   
        NULL AS `sticky`,
        NULL AS `notify_contacts` 
        FROM `:dbstg`.`logs` l
        ";

        $collector->addValue(":hostId", $hostId);
        $collector->addValue(":serviceId", (int) $serviceId);
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

        return $sql;
    }

    /**
     * Generate SQL query for comments
     *
     * @param \Centreon\Infrastructure\CentreonLegacyDB\StatementCollector $collector
     * @param int $hostId
     * @param int $serviceId
     * @return string
     */
    protected function generateCommentsQuery(StatementCollector $collector, int $hostId, int $serviceId = 0): string
    {
        $sql = "SELECT
		CONCAT('C', c.comment_id) AS `eventId`,
        '" . CommentEventObject::EVENTTYPE . "' AS `eventType`,
        c.comment_id AS `id`,
        c.service_id AS `service_id`,
		c.data AS `output`,
		c.entry_time AS `timestamp`,
        NULL AS `status`,
        c.type AS `type`,
        NULL AS `retry`,
        c.author AS `contact`,
        null AS `contact_id`,
        NULL AS `command`,
        c.persistent AS `persistent`,
        NULL as `deletion_time`,
        NULL AS `start_time`,
        NULL AS `end_time`,
        NULL AS `actual_start_time`,
        NULL AS `actual_end_time`,
        NULL AS `duration`,
        NULL AS `started`,
        NULL AS `cancelled`,
        NULL AS `fixed`,
        NULL AS `sticky`,
        NULL AS `notify_contacts`
        FROM `:dbstg`.`comments` c
        ";

        $collector->addValue(":hostId", $hostId);
        $collector->addValue(":serviceId", (int) $serviceId);

        // set ACL limitations
        if (!$this->isAdmin()) {
            $sql .= " INNER JOIN `:dbstg`.`centreon_acl` AS service_acl ON service_acl.host_id = c.host_id";
            if ($serviceId) {
                $sql .= " AND service_acl.service_id = c.service_id";
            }
            $sql .= " AND service_acl.group_id IN (" . $this->accessGroupIdToString($this->accessGroups) . ") ";
        }
        $sql .= " WHERE c.host_id = :hostId AND c.service_id = :serviceId";

        //Group to avoid duplicate entries
        $sql .= ' GROUP BY c.comment_id';

        return $sql;
    }

    /**
     * Generate SQL query for downtimes
     *
     * @param \Centreon\Infrastructure\CentreonLegacyDB\StatementCollector $collector
     * @param int $hostId
     * @param int $serviceId
     * @return string
     */
    protected function generateDowntimesQuery(StatementCollector $collector, int $hostId, int $serviceId = 0): string
    {
        $sql = "SELECT
		CONCAT('D', d.downtime_id) AS `eventId`,
        '" . DowntimeEventObject::EVENTTYPE . "' AS `eventType`,
        d.downtime_id AS `id`,
        d.service_id AS `service_id`,
		d.comment_data AS `output`,
		d.entry_time AS `timestamp`,
        NULL AS `status`,
        d.type AS `type`,
        NULL AS `retry`,
        d.author AS `contact`,
        contact_d.contact_id AS `contact_id`,
        NULL AS `command`,
        NULL AS `persistent`,
        d.deletion_time as `deletion_time`,
        d.start_time AS `start_time`,
        d.end_time AS `end_time`,
        d.actual_start_time AS `actual_start_time`,
        d.actual_end_time AS `actual_end_time`,
        d.duration AS `duration`,
        d.started AS `started`,
        d.cancelled AS `cancelled`,
        d.fixed AS `fixed`,
        NULL AS `sticky`,
        NULL AS `notify_contacts`
        FROM `:dbstg`.`downtimes` d
        INNER JOIN `:db`.contact AS `contact_d` ON contact_d.contact_alias = d.author
        ";

        $collector->addValue(":hostId", $hostId);
        $collector->addValue(":serviceId", (int) $serviceId);

        // set ACL limitations
        if (!$this->isAdmin()) {
            $sql .= " INNER JOIN `:dbstg`.`centreon_acl` AS service_acl ON service_acl.host_id = d.host_id";
            if ($serviceId) {
                $sql .= " AND service_acl.service_id = d.service_id";
            }
            $sql .= " AND service_acl.group_id IN (" . $this->accessGroupIdToString($this->accessGroups) . ") ";
        }
        $sql .= " WHERE d.host_id = :hostId AND d.service_id = :serviceId";

        //Group to avoid duplicate entries
        $sql .= ' GROUP BY d.downtime_id';

        return $sql;
    }

    /**
     * Generate SQL query for ack
     *
     * @param \Centreon\Infrastructure\CentreonLegacyDB\StatementCollector $collector
     * @param int $hostId
     * @param int $serviceId
     * @return string
     */
    protected function generateAcknowledgementsQuery(
        StatementCollector $collector,
        int $hostId,
        int $serviceId = 0
    ): string {
        $sql = "SELECT
		CONCAT('A', a.acknowledgement_id) AS `eventId`,
        '" . AckEventObject::EVENTTYPE . "' AS `eventType`,
        a.acknowledgement_id AS `id`,
        a.service_id AS `service_id`,
		a.comment_data AS `output`,
		a.entry_time AS `timestamp`,
        a.state AS `status`,
        a.type AS `type`,
        NULL AS `retry`,
        a.author AS `contact`,
        contact_a.contact_id AS `contact_id`,
        NULL AS `command`,
        a.persistent_comment AS `persistent`,
        a.deletion_time as `deletion_time`,
        NULL AS `start_time`,
        NULL AS `end_time`,
        NULL AS `actual_start_time`,
        NULL AS `actual_end_time`,
        NULL AS `duration`,
        NULL AS `started`,
        NULL AS `cancelled`,
        NULL AS `fixed`, 
        a.sticky AS `sticky`,
        a.notify_contacts AS `notify_contacts`
        FROM `:dbstg`.`acknowledgements` a
        INNER JOIN `:db`.contact AS `contact_a` ON contact_a.contact_alias = a.author
        ";

        $collector->addValue(":hostId", $hostId);
        $collector->addValue(":serviceId", (int) $serviceId);

        // set ACL limitations
        if (!$this->isAdmin()) {
            $sql .= " INNER JOIN `:dbstg`.`centreon_acl` AS service_acl ON service_acl.host_id = a.host_id";
            if ($serviceId) {
                $sql .= " AND service_acl.service_id = a.service_id";
            }
            $sql .= " AND service_acl.group_id IN (" . $this->accessGroupIdToString($this->accessGroups) . ") ";
        }
        $sql .= " WHERE a.host_id = :hostId AND a.service_id = :serviceId";

        //Group to avoid duplicate entries
        $sql .= ' GROUP BY a.acknowledgement_id';

        return $sql;
    }


    /**
     * Generate Objects by database result row
     * @param string $eventType
     * @throws \Exception
     * @return string
     */
    private function detectClass(string $eventType): string
    {
        switch ($eventType) {
            case 'L':
                $eventClass = LogEventObject::class;
                break;
            case 'A':
                $eventClass = AckEventObject::class;
                break;
            case 'C':
                $eventClass = CommentEventObject::class;
                break;
            case 'D':
                $eventClass = DowntimeEventObject::class;
                break;
            default:
                throw new \Exception('Incorrect Event Type');
        }

        return $eventClass;
    }
}
