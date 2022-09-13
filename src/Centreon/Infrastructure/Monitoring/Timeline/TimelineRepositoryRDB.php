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

use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Entity\EntityCreator;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Monitoring\Timeline\TimelineEvent;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Timeline\TimelineContact;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Domain\Monitoring\Timeline\Interfaces\TimelineRepositoryInterface;

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
        return $this->findTimelineEvents($host->getId(), null);
    }

    /**
     * @inheritDoc
     */
    public function findTimelineEventsByService(Service $service): array
    {
        return $this->findTimelineEvents($service->getHost()->getId(), $service->getId());
    }

    /**
     * find timeline events
     *
     * @param integer $hostId
     * @param integer|null $serviceId
     * @return TimelineEvent[]
     */
    private function findTimelineEvents(int $hostId, ?int $serviceId): array
    {
        $timelineEvents = [];

        if (!$this->hasEnoughRightsToContinue()) {
            return $timelineEvents;
        }

        $this->sqlRequestTranslator->setConcordanceArray([
            'type' => 'log.type',
            'content' => 'log.content',
            'date' => 'log.date',
        ]);

        $collector = new StatementCollector();

        $request = "
            SELECT SQL_CALC_FOUND_ROWS
                log.id,
                log.type,
                log.date,
                log.start_date,
                log.end_date,
                log.content,
                log.contact_id,
                log.contact_name,
                log.status_code,
                log.status_name,
                log.status_severity_code,
                log.tries
            FROM (
        ";

        $subRequests = [];

        // status events
        $subRequests[] = $this->prepareQueryForTimelineStatusEvents($collector, $hostId, $serviceId);

        // notification events
        $subRequests[] = $this->prepareQueryForTimelineNotificationEvents($collector, $hostId, $serviceId);

        // downtime events
        $subRequests[] = $this->prepareQueryForTimelineDowntimeEvents($collector, $hostId, $serviceId);

        // acknowledgement events
        $subRequests[] = $this->prepareQueryForTimelineAcknowledgementEvents($collector, $hostId, $serviceId);

        // comment events
        $subRequests[] = $this->prepareQueryForTimelineCommentEvents($collector, $hostId, $serviceId);

        if (empty($subRequests)) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal(0);

            return [];
        }

        $request .= implode('UNION ALL ', $subRequests);

        $request .= ') AS `log` ';

        /**
         * Here the search filter provides a date in ISO8601.
         * But the date on which we do filter (stored as ctime) is a timestamp.
         * Therefore we need to normalize the data provided in the search parameter
         * and translate it into a timestamp filtering search.
         */
        $this->sqlRequestTranslator->addNormalizer(
            'date',
            new class implements NormalizerInterface
            {
                public function normalize($valueToNormalize)
                {
                    return (new \Datetime($valueToNormalize))->getTimestamp();
                }
            }
        );

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $whereCondition = false;
        if ($searchRequest !== null) {
            $whereCondition = true;
            $request .= $searchRequest;
        }

        // set ACL limitations
        if (!$this->isAdmin()) {
            $request .= ($whereCondition === true) ? ' AND ' : ' WHERE ';
            $request .= $this->translateDbName(
                "EXISTS (SELECT host_id FROM `:dbstg`.`centreon_acl` acl WHERE acl.host_id = :host_id"
            );
            $collector->addValue(':host_id', $hostId, \PDO::PARAM_INT);
            if ($serviceId !== null) {
                $request .= " AND acl.service_id = :service_id";
                $collector->addValue(':service_id', $serviceId, \PDO::PARAM_INT);
            }
            $request .= " AND acl.group_id IN (" . $this->accessGroupIdToString($this->accessGroups) . ")) ";
        }

        // Sort
        $request .= $this->sqlRequestTranslator->translateSortParameterToSql() ?: ' ORDER BY log.date DESC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);
        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $collector->addValue($key, $value, $type);
        }
        $collector->bind($statement);
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $timelineEvent = EntityCreator::createEntityByArray(
                TimelineEvent::class,
                $result
            );

            if ($result['contact_name'] !== null) {
                $timelineEvent->setContact(
                    EntityCreator::createEntityByArray(
                        TimelineContact::class,
                        $result,
                        'contact_'
                    )
                );
            }

            if ($result['status_code'] !== null) {
                $timelineEvent->setStatus(
                    EntityCreator::createEntityByArray(
                        ResourceStatus::class,
                        $result,
                        'status_'
                    )
                );
            }

            $timelineEvents[] = $timelineEvent;
        }

        return $timelineEvents;
    }

    /**
     * get subquery to find status events
     *
     * @param StatementCollector $collector
     * @param integer $hostId
     * @param integer|null $serviceId
     * @return string subquery
     */
    private function prepareQueryForTimelineStatusEvents(
        StatementCollector $collector,
        int $hostId,
        ?int $serviceId
    ): string {
        $request = $this->translateDbName("SELECT
            l.log_id AS `id`,
            'event' AS `type`,
            l.ctime AS `date`,
            NULL AS `start_date`,
            NULL AS `end_date`,
            l.output AS `content`,
            NULL AS `contact_id`,
            NULL AS `contact_name`,
            l.status AS `status_code`,
            CASE
                WHEN l.status = 0 THEN :status_code_0
                WHEN l.status = 1 THEN :status_code_1
                WHEN l.status = 2 THEN :status_code_2
                WHEN l.status = 3 THEN :status_code_3
                WHEN l.status = 4 THEN :status_code_4
            END AS `status_name`,
            CASE
                WHEN l.status = 0 THEN :status_severity_code_0
                WHEN l.status = 1 THEN :status_severity_code_1
                WHEN l.status = 2 THEN :status_severity_code_2
                WHEN l.status = 3 THEN :status_severity_code_3
                WHEN l.status = 4 THEN :status_severity_code_4
            END AS `status_severity_code`,
            l.retry AS `tries`
            FROM `:dbstg`.`logs` l
            WHERE l.host_id = :host_id
            AND (l.service_id = " . ($serviceId !== null ? ':service_id)' : '0 OR l.service_id IS NULL)') . "
            AND l.msg_type IN (0,1,8,9)
            AND l.output NOT LIKE 'INITIAL % STATE:%'
            AND l.instance_name != ''
        ");

        $collector->addValue(':host_id', $hostId, \PDO::PARAM_INT);
        if ($serviceId === null) {
            $collector->addValue(':status_code_0', ResourceStatus::STATUS_NAME_UP, \PDO::PARAM_STR);
            $collector->addValue(':status_code_1', ResourceStatus::STATUS_NAME_DOWN, \PDO::PARAM_STR);
            $collector->addValue(':status_code_2', ResourceStatus::STATUS_NAME_UNREACHABLE, \PDO::PARAM_STR);
            $collector->addValue(':status_code_3', ResourceStatus::STATUS_NAME_PENDING, \PDO::PARAM_STR);
            $collector->addValue(':status_code_4', null, \PDO::PARAM_STR);

            $collector->addValue(':status_severity_code_0', ResourceStatus::SEVERITY_OK, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_1', ResourceStatus::SEVERITY_HIGH, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_2', ResourceStatus::SEVERITY_LOW, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_3', ResourceStatus::SEVERITY_PENDING, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_4', null, \PDO::PARAM_INT);
        } else {
            $collector->addValue(':service_id', $serviceId, \PDO::PARAM_INT);

            $collector->addValue(':status_code_0', ResourceStatus::STATUS_NAME_OK, \PDO::PARAM_STR);
            $collector->addValue(':status_code_1', ResourceStatus::STATUS_NAME_WARNING, \PDO::PARAM_STR);
            $collector->addValue(':status_code_2', ResourceStatus::STATUS_NAME_CRITICAL, \PDO::PARAM_STR);
            $collector->addValue(':status_code_3', ResourceStatus::STATUS_NAME_UNKNOWN, \PDO::PARAM_STR);
            $collector->addValue(':status_code_4', ResourceStatus::STATUS_NAME_PENDING, \PDO::PARAM_STR);

            $collector->addValue(':status_severity_code_0', ResourceStatus::SEVERITY_OK, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_1', ResourceStatus::SEVERITY_MEDIUM, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_2', ResourceStatus::SEVERITY_HIGH, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_3', ResourceStatus::SEVERITY_LOW, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_4', ResourceStatus::SEVERITY_PENDING, \PDO::PARAM_INT);
        }

        return $request;
    }

    /**
     * get subquery to find notification events
     *
     * @param StatementCollector $collector
     * @param integer $hostId
     * @param integer|null $serviceId
     * @return string subquery
     */
    private function prepareQueryForTimelineNotificationEvents(
        StatementCollector $collector,
        int $hostId,
        ?int $serviceId
    ): string {
        $request = $this->translateDbName("SELECT
            l.log_id AS `id`,
            'notification' AS `type`,
            l.ctime AS `date`,
            NULL AS `start_date`,
            NULL AS `end_date`,
            l.output AS `content`,
            c.contact_id AS `contact_id`,
            c.contact_alias AS `contact_name`,
            l.status AS `status_code`,
            CASE
                WHEN l.status = 0 THEN :status_code_0
                WHEN l.status = 1 THEN :status_code_1
                WHEN l.status = 2 THEN :status_code_2
                WHEN l.status = 3 THEN :status_code_3
                WHEN l.status = 4 THEN :status_code_4
            END AS `status_name`,
            CASE
                WHEN l.status = 0 THEN :status_severity_code_0
                WHEN l.status = 1 THEN :status_severity_code_1
                WHEN l.status = 2 THEN :status_severity_code_2
                WHEN l.status = 3 THEN :status_severity_code_3
                WHEN l.status = 4 THEN :status_severity_code_4
            END AS `status_severity_code`,
            NULL AS `tries`
            FROM `:dbstg`.`logs` l
            LEFT JOIN `:db`.contact AS `c` ON c.contact_name = l.notification_contact
            WHERE l.host_id = :host_id
            AND (l.service_id = " . ($serviceId !== null ? ':service_id)' : '0 OR l.service_id IS NULL)') . "
            AND l.msg_type IN (2,3)
        ");

        $collector->addValue(':host_id', $hostId, \PDO::PARAM_INT);
        if ($serviceId === null) {
            $collector->addValue(':status_code_0', ResourceStatus::STATUS_NAME_UP, \PDO::PARAM_STR);
            $collector->addValue(':status_code_1', ResourceStatus::STATUS_NAME_DOWN, \PDO::PARAM_STR);
            $collector->addValue(':status_code_2', ResourceStatus::STATUS_NAME_UNREACHABLE, \PDO::PARAM_STR);
            $collector->addValue(':status_code_3', ResourceStatus::STATUS_NAME_PENDING, \PDO::PARAM_STR);
            $collector->addValue(':status_code_4', null, \PDO::PARAM_STR);

            $collector->addValue(':status_severity_code_0', ResourceStatus::SEVERITY_OK, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_1', ResourceStatus::SEVERITY_HIGH, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_2', ResourceStatus::SEVERITY_LOW, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_3', ResourceStatus::SEVERITY_PENDING, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_4', null, \PDO::PARAM_INT);
        } else {
            $collector->addValue(':service_id', $serviceId, \PDO::PARAM_INT);

            $collector->addValue(':status_code_0', ResourceStatus::STATUS_NAME_OK, \PDO::PARAM_STR);
            $collector->addValue(':status_code_1', ResourceStatus::STATUS_NAME_WARNING, \PDO::PARAM_STR);
            $collector->addValue(':status_code_2', ResourceStatus::STATUS_NAME_CRITICAL, \PDO::PARAM_STR);
            $collector->addValue(':status_code_3', ResourceStatus::STATUS_NAME_UNKNOWN, \PDO::PARAM_STR);
            $collector->addValue(':status_code_4', ResourceStatus::STATUS_NAME_PENDING, \PDO::PARAM_STR);

            $collector->addValue(':status_severity_code_0', ResourceStatus::SEVERITY_OK, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_1', ResourceStatus::SEVERITY_MEDIUM, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_2', ResourceStatus::SEVERITY_HIGH, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_3', ResourceStatus::SEVERITY_LOW, \PDO::PARAM_INT);
            $collector->addValue(':status_severity_code_4', ResourceStatus::SEVERITY_PENDING, \PDO::PARAM_INT);
        }

        return $request;
    }

    /**
     * get subquery to find downtime events
     *
     * @param StatementCollector $collector
     * @param integer $hostId
     * @param integer|null $serviceId
     * @return string subquery
     */
    private function prepareQueryForTimelineDowntimeEvents(
        StatementCollector $collector,
        int $hostId,
        ?int $serviceId
    ): string {
        $request = $this->translateDbName("SELECT
            d.downtime_id AS `id`,
            'downtime' AS `type`,
            d.actual_start_time AS `date`,
            d.actual_start_time AS `start_date`,
            d.actual_end_time AS `end_date`,
            d.comment_data AS `content`,
            c.contact_id AS `contact_id`,
            d.author AS `contact_name`,
            NULL AS `status_code`,
            NULL AS `status_name`,
            NULL AS `status_severity_code`,
            NULL AS `tries`
            FROM `:dbstg`.`downtimes` d
            LEFT JOIN `:db`.contact AS `c` ON c.contact_alias = d.author
            WHERE d.host_id = :host_id
            AND (d.service_id = " . ($serviceId !== null ? ':service_id)' : '0 OR d.service_id IS NULL)') . "
            AND d.actual_start_time < " . time() . "
        ");

        $collector->addValue(':host_id', $hostId, \PDO::PARAM_INT);
        if ($serviceId !== null) {
            $collector->addValue(':service_id', $serviceId, \PDO::PARAM_INT);
        }

        return $request;
    }

    /**
     * get subquery to find acknowledgement events
     *
     * @param StatementCollector $collector
     * @param integer $hostId
     * @param integer|null $serviceId
     * @return string subquery
     */
    private function prepareQueryForTimelineAcknowledgementEvents(
        StatementCollector $collector,
        int $hostId,
        ?int $serviceId
    ): string {
        $request = $this->translateDbName("SELECT
            a.acknowledgement_id AS `id`,
            'acknowledgement' AS `type`,
            a.entry_time AS `date`,
            NULL AS `start_date`,
            NULL AS `end_date`,
            a.comment_data AS `content`,
            c.contact_id AS `contact_id`,
            a.author AS `contact_name`,
            NULL AS `status_code`,
            NULL AS `status_name`,
            NULL AS `status_severity_code`,
            NULL AS `tries`
            FROM `:dbstg`.`acknowledgements` a
            LEFT JOIN `:db`.contact AS `c` ON c.contact_alias = a.author
            WHERE a.host_id = :host_id
            AND (a.service_id = " . ($serviceId !== null ? ':service_id)' : '0 OR a.service_id IS NULL)') . "
        ");

        $collector->addValue(':host_id', $hostId, \PDO::PARAM_INT);
        if ($serviceId !== null) {
            $collector->addValue(':service_id', $serviceId, \PDO::PARAM_INT);
        }

        return $request;
    }

    /**
     * get subquery to find acknowledgement events
     *
     * @param StatementCollector $collector
     * @param integer $hostId
     * @param integer|null $serviceId
     * @return string subquery
     */
    private function prepareQueryForTimelineCommentEvents(
        StatementCollector $collector,
        int $hostId,
        ?int $serviceId
    ): string {
        $request = $this->translateDbName("SELECT
            c.comment_id AS `id`,
            'comment' AS `type`,
            c.entry_time AS `date`,
            NULL AS `start_date`,
            NULL AS `end_date`,
            c.data AS `content`,
            ct.contact_id AS `contact_id`,
            c.author AS `contact_name`,
            NULL AS `status_code`,
            NULL AS `status_name`,
            NULL AS `status_severity_code`,
            NULL AS `tries`
            FROM `:dbstg`.`comments` c
            LEFT JOIN `:db`.contact AS `ct` ON ct.contact_alias = c.author
            WHERE c.host_id = :host_id
            AND (c.service_id = " . ($serviceId !== null ? ':service_id)' : '0 OR c.service_id IS NULL)') . "
            AND source = 1 AND c.deletion_time IS NULL
        ");

        $collector->addValue(':host_id', $hostId, \PDO::PARAM_INT);
        if ($serviceId !== null) {
            $collector->addValue(':service_id', $serviceId, \PDO::PARAM_INT);
        }

        return $request;
    }

    /**
     * Check if contact is an admin
     *
     * @return boolean
     */
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
     * check if contact has enough rights to get events
     *
     * @return bool
     */
    private function hasEnoughRightsToContinue(): bool
    {
        return ($this->contact !== null)
            ? ($this->contact->isAdmin() || count($this->accessGroups) > 0)
            : count($this->accessGroups) > 0;
    }
}
