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

final class AcknowledgementRepositoryRDB extends AbstractRepositoryDRB implements AcknowledgementRepositoryInterface
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
     * @var bool Indicates whether the contact is an admin or not
     */
    private $isAdmin = false;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * @var array<string, string>
     */
    private $hostConcordanceArray;

    /**
     * @var array<string, string>
     */
    private $serviceConcordanceArray;

    /**
     * AcknowledgementRepositoryRDB constructor.
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
        $this->hostConcordanceArray = [
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
            'state' => 'ack.state',
            'type' => 'ack.type',
        ];
        $this->serviceConcordanceArray = array_merge(
            $this->hostConcordanceArray,
            [
                'service_id' => 'ack.service_id',
                'service.display_name' => 'srv.display_name',
            ]
        );
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
    public function findHostsAcknowledgements(): array
    {
        return $this->findAcknowledgementsOf(Acknowledgement::TYPE_HOST_ACKNOWLEDGEMENT);
    }

    /**
     * {@inheritDoc}
     * @throws \PDOException
     */
    public function findServicesAcknowledgements(): array
    {
        return $this->findAcknowledgementsOf(Acknowledgement::TYPE_SERVICE_ACKNOWLEDGEMENT);
    }

    /**
     * @inheritDoc
     */
    public function findAcknowledgementsByHost(int $hostId): array
    {
        $acknowledgements = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $acknowledgements;
        }

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = ack.host_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request = 'SELECT
            ack.acknowledgement_id,
            ack.entry_time,
            ack.host_id,
            ack.service_id,
            ack.author,
            `cmts`.data AS `comment_data`,
            ack.deletion_time,
            ack.instance_id,
            ack.notify_contacts,
            ack.persistent_comment,
            ack.state,
            ack.sticky,
            ack.type,
            contact.contact_id AS `author_id`
            FROM `:dbstg`.acknowledgements ack
            LEFT JOIN `:db`.contact
                ON contact.contact_alias = ack.author
            LEFT JOIN `:dbstg`.`comments` AS `cmts`
                ON `cmts`.host_id = ack.host_id AND `cmts`.deletion_time IS NULL'
            . $accessGroupFilter
            . 'WHERE ack.host_id = :host_id
              AND ack.service_id = 0';

        $this->sqlRequestTranslator->addSearchValue('host_id', [\PDO::PARAM_INT => $hostId]);

        return $this->processListingRequest($request);
    }

    /**
     * {@inheritDoc}
     * @throws \PDOException
     */
    public function findAcknowledgementsByService(int $hostId, int $serviceId): array
    {
        $acknowledgements = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $acknowledgements;
        }

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = ack.host_id
                  AND acl.service_id = ack.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request = 'SELECT
            ack.acknowledgement_id,
            ack.entry_time,
            ack.host_id,
            ack.service_id,
            ack.author,
            `cmts`.data AS `comment_data`,
            ack.deletion_time,
            ack.instance_id,
            ack.notify_contacts,
            ack.persistent_comment,
            ack.state,
            ack.sticky,
            ack.type,
            contact.contact_id AS `author_id`
            FROM `:dbstg`.acknowledgements ack
            LEFT JOIN `:db`.contact
                ON contact.contact_alias = ack.author
            LEFT JOIN `:dbstg`.`comments` AS `cmts`
                ON `cmts`.host_id = ack.host_id AND `cmts`.service_id = ack.host_id
                AND `cmts`.deletion_time IS NULL'
            . $accessGroupFilter
            . 'WHERE ack.host_id = :host_id
            AND ack.service_id = :service_id';

        $this->sqlRequestTranslator->addSearchValue('host_id', [\PDO::PARAM_INT => $hostId]);
        $this->sqlRequestTranslator->addSearchValue('service_id', [\PDO::PARAM_INT => $serviceId]);

        return $this->processListingRequest($request);
    }

    /**
     * {@inheritDoc}
     * @throws \PDOException
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
            LEFT JOIN `:db`.contact
            ON contact.contact_alias = ack.author
            WHERE ack.acknowledgement_id = (
            SELECT MAX(ack2.acknowledgement_id)
            FROM `:dbstg`.acknowledgements ack2'
            . $accessGroupFilter
            . 'WHERE ack2.host_id = :host_id
            AND ack2.service_id = 0)';

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
        LEFT JOIN `:db`.contact
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
    private function findAcknowledgementsOf(int $type = Acknowledgement::TYPE_HOST_ACKNOWLEDGEMENT): array
    {
        $acknowledgements = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $acknowledgements;
        }

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = ack.host_id'
                .  (($type === Acknowledgement::TYPE_SERVICE_ACKNOWLEDGEMENT)
                    ? ' AND acl.service_id = ack.service_id '
                    : ''
                )
                . ' INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $this->sqlRequestTranslator->setConcordanceArray(
            $type === Acknowledgement::TYPE_SERVICE_ACKNOWLEDGEMENT
            ? $this->serviceConcordanceArray
            : $this->hostConcordanceArray
        );

        $request = 'SELECT ack.*, contact.contact_id AS author_id
            FROM `:dbstg`.acknowledgements ack
            LEFT JOIN `:db`.contact
                ON contact.contact_alias = ack.author '
            . $accessGroupFilter
            . 'WHERE ack.service_id ' . (($type === Acknowledgement::TYPE_HOST_ACKNOWLEDGEMENT) ? ' = 0' : ' != 0');

        return $this->processListingRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function findOneAcknowledgementForAdminUser(int $acknowledgementId): ?Acknowledgement
    {
        // Internal call for an admin user
        return $this->findOneAcknowledgement($acknowledgementId, true);
    }

    /**
     * @inheritDoc
     */
    public function findOneAcknowledgementForNonAdminUser(int $acknowledgementId): ?Acknowledgement
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return null;
        }

        // Internal call for non admin user
        return $this->findOneAcknowledgement($acknowledgementId, false);
    }

    /**
     * Find one acknowledgement taking into account or not the ACLs.
     *
     * @param int $acknowledgementId Acknowledgement id
     * @param bool $isAdmin Indicates whether user is an admin
     * @return Acknowledgement|null Return NULL if the acknowledgement has not been found
     * @throws \Exception
     */
    private function findOneAcknowledgement(int $acknowledgementId, bool $isAdmin = false): ?Acknowledgement
    {
        $aclRequest = '';

        if ($isAdmin === false) {
            $aclRequest =
                ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = ack.host_id
                  AND (acl.service_id = ack.service_id OR acl.service_id IS NULL)
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups) . ') ';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT ack.*, contact.contact_id AS author_id
            FROM `:dbstg`.acknowledgements ack
            LEFT JOIN `:db`.`contact`
                ON contact.contact_alias = ack.author'
            . $aclRequest
            . ' WHERE ack.acknowledgement_id = :acknowledgement_id';

        $request = $this->translateDbName($request);

        $prepare = $this->db->prepare($request);
        $prepare->bindValue(':acknowledgement_id', $acknowledgementId, \PDO::PARAM_INT);
        $prepare->execute();

        if (false !== ($row = $prepare->fetch(\PDO::FETCH_ASSOC))) {
            return EntityCreator::createEntityByArray(
                Acknowledgement::class,
                $row
            );
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function findAcknowledgementsForAdminUser(): array
    {
        // Internal call for an admin user
        return $this->findAcknowledgements(true);
    }

    /**
     * @inheritDoc
     */
    public function findAcknowledgementsForNonAdminUser(): array
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return [];
        }

        // Internal call for non admin user
        return $this->findAcknowledgements(false);
    }

    /**
     * Find all acknowledgements.
     *
     * @param bool $isAdmin Indicates whether user is an admin
     * @return Acknowledgement[]
     * @throws \Exception
     */
    private function findAcknowledgements(bool $isAdmin): array
    {
        $this->sqlRequestTranslator->setConcordanceArray($this->serviceConcordanceArray);

        $aclRequest = '';

        if ($isAdmin === false) {
            $aclRequest =
                ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = ack.host_id
                  AND (acl.service_id = ack.service_id OR acl.service_id IS NULL)
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups) . ') ';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT ack.*, contact.contact_id AS author_id
            FROM `:dbstg`.acknowledgements ack
            LEFT JOIN `:db`.`contact`
              ON contact.contact_alias = ack.author
            INNER JOIN `:dbstg`.hosts
              ON hosts.host_id = ack.host_id
            LEFT JOIN `:dbstg`.services srv
              ON srv.service_id = ack.service_id
              AND srv.host_id = hosts.host_id'
            . $aclRequest;

        return $this->processListingRequest($request);
    }

    /**
     * Check if the current contact is admin
     *
     * @return bool
     */
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

    /**
     * Execute the request and retrieve the acknowledgements list
     *
     * @param string $request Request to execute
     * @return Acknowledgement[]
     * @throws \Exception
     */
    private function processListingRequest(string $request): array
    {
        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY ack.host_id, ack.service_id, ack.entry_time DESC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $acknowledgements = [];

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $acknowledgements[] = EntityCreator::createEntityByArray(
                Acknowledgement::class,
                $result
            );
        }

        return $acknowledgements;
    }
}
