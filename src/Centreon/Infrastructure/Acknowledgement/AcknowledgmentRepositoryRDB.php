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

namespace Centreon\Infrastructure\Acknowledgement;

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Acknowledgement\Interfaces\AcknowledgementRepositoryInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

final class AcknowledgmentRepositoryRDB extends AbstractRepositoryDRB implements AcknowledgementRepositoryInterface
{
    /**
     * Define a host acknowledgement (0)
     */
    const TYPE_HOST_ACKNOWLEDGEMENT = 0;

    /**
     * Define a service acknowledgement (1)
     */
    const TYPE_SERVICE_ACKNOWLEDGEMENT = 1;

    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @var AccessGroup[] List of access group used to filter the requests
     */
    private $accessGroups;

    /**
     * @var bool Indicates whether the contact is an admin or not
     */
    private $isAdmin = false;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * AcknowledgmentRepositoryRDB constructor.
     *
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function __construct(
        DatabaseConnection $db,
        SqlRequestParametersTranslator $sqlRequestTranslator
    ) {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
    }

    /**
     * @inheritDoc
     */
    public function filterByAccessGroups(?array $accessGroups): AcknowledgementRepositoryInterface
    {
        $this->accessGroups = $accessGroups;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAdmin(bool $isAdmin): AcknowledgementRepositoryInterface
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @throws \PDOException
     */
    public function findLatestAcknowledgementOfAllHosts(): array
    {
        return $this->findLatestAcknowledgementOf(self::TYPE_HOST_ACKNOWLEDGEMENT);
    }

    /**
     * {@inheritDoc}
     * @throws \PDOException
     */
    public function findLatestAcknowledgementOfAllServices(): array
    {
        return $this->findLatestAcknowledgementOf(self::TYPE_SERVICE_ACKNOWLEDGEMENT);
    }

    /**
     * @inheritDoc
     */
    public function findLatestHostAcknowledgement(int $hostId): ?Acknowledgement
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return null;
        }

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = ack2.host_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT ack.*, contact.contact_id AS author_id
            FROM `:dbstg`.acknowledgements ack
            INNER JOIN `:db`.contact
              ON contact.contact_alias = ack.author
            WHERE ack.acknowledgement_id = (
              SELECT MAX(ack2.acknowledgement_id)
              FROM `:dbstg`.acknowledgements ack2'
            . $accessGroupFilter
            . 'WHERE ack2.host_id = :host_id
              AND ack2.service_id IS NULL)';

        $request = $this->translateDbName($request);
        $statement = $this->db->prepare($request);

        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->execute();

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return EntityCreator::createEntityByArray(
                Acknowledgement::class,
                $result
            );
        }
        return null;
    }

    /**
     * {@inheritDoc}
     * @throws \PDOException
     */
    public function findLatestServiceAcknowledgement(int $hostId, int $serviceId): ?Acknowledgement
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return null;
        }

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = ack2.host_id
                  AND acl.service_id = ack2.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT ack.*, contact.contact_id AS author_id
        FROM `:dbstg`.acknowledgements ack
        INNER JOIN `:db`.contact
          ON contact.contact_alias = ack.author
        WHERE ack.acknowledgement_id = (
          SELECT MAX(ack2.acknowledgement_id)
          FROM `:dbstg`.acknowledgements ack2'
            . $accessGroupFilter
            . 'WHERE ack2.host_id = :host_id
            AND ack2.service_id = :service_id)';

        $request = $this->translateDbName($request);
        $statement = $this->db->prepare($request);

        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
        $statement->execute();

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return EntityCreator::createEntityByArray(
                Acknowledgement::class,
                $result
            );
        }
        return null;
    }

    /**
     * Generic function to find acknowledgement.
     *
     * @param int $type Type of acknowledgement
     * @return Acknowledgement[]
     * @throws \Exception
     * @throws \PDOException
     * @throws RequestParametersTranslatorException
     */
    private function findLatestAcknowledgementOf(int $type = self::TYPE_HOST_ACKNOWLEDGEMENT): array
    {
        $acknowledgements = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $acknowledgements;
        }

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = ack2.host_id'
                .  (($type === self::TYPE_SERVICE_ACKNOWLEDGEMENT) ? ' AND acl.service_id = ack2.service_id ' : '')
                . ' INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $concordanceArray = [
            'author_id' => 'contact.contact_id',
            'comment' => 'ack.comment_data',
            'entry_time' => 'ack.entry_time',
            'deletion_time' => 'ack.deletion_time',
            'host_id' => 'ack.host_id',
            'id' => 'ack.acknowledgement_id',
            'is_notify_contacts' => 'ack.notify_contacts',
            'is_persistent_comment' => 'ack.persistent_comment',
            'is_sticky' => 'ack.sticky',
            'poller_id' => 'ack.instance_id',
            'state' => 'ack.state'];

        if ($type === self::TYPE_SERVICE_ACKNOWLEDGEMENT) {
            $concordanceArray['service_id'] = 'ack.service_id';
        }

        $this->sqlRequestTranslator->setConcordanceArray($concordanceArray);

        $request = 'SELECT ack.*, contact.contact_id AS author_id
            FROM `:dbstg`.acknowledgements ack
            INNER JOIN `:db`.contact
                ON contact.contact_alias = ack.author ';

        $mainSearchRequest = 'ack.acknowledgement_id IN (
                SELECT MAX(ack2.acknowledgement_id)
                FROM `:dbstg`.acknowledgements ack2'
                . $accessGroupFilter
                . 'WHERE ack2.service_id IS '
                . (($type === self::TYPE_HOST_ACKNOWLEDGEMENT) ? 'NULL' : 'NOT NULL')
                .' GROUP BY ack2.host_id, ack2.service_id)';

        // Added the sub request of the search parameter
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();

        $request .= !is_null($searchRequest)
            ? $searchRequest . ' AND ' . $mainSearchRequest
            : 'WHERE ' . $mainSearchRequest;

        $request = $this->translateDbName($request);

        $request .= ' GROUP BY ack.host_id, ack.service_id';

        // Added the sub request of the sort parameter
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY ack.entry_time';

        // Added the sub request of the pagination parameter
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $acknowledgements[] = EntityCreator::createEntityByArray(
                Acknowledgement::class,
                $result
            );
        }

        $result = $this->db->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

        return $acknowledgements;
    }

    private function isAdmin(): bool
    {
        return ($this->contact !== null)
            ? $this->contact->isAdmin()
            : false;
    }

    /**
     * @inheritDoc
     */
    public function setContact(ContactInterface $contact): AcknowledgementRepositoryInterface
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @return bool Return TRUE if the contact is an admin or has at least one access group.
     */
    private function hasNotEnoughRightsToContinue(): bool
    {
        return ($this->contact !== null)
            ? !($this->contact->isAdmin() || count($this->accessGroups) > 0)
            : count($this->accessGroups) == 0;
    }
}
