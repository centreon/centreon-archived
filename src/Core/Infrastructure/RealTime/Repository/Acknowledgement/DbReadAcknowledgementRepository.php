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

namespace Core\Infrastructure\RealTime\Repository\Acknowledgement;

use Centreon\Infrastructure\DatabaseConnection;
use Core\Domain\RealTime\Model\Acknowledgement;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Core\Infrastructure\RealTime\Repository\Acknowledgement\DbAcknowledgementFactory;

class DbReadAcknowledgementRepository extends AbstractRepositoryDRB implements ReadAcknowledgementRepositoryInterface
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
    public function findOnGoingAcknowledgementByHostId(int $hostId): ?Acknowledgement
    {
        return $this->findOnGoingAcknowledegement($hostId, 0);
    }

    /**
     * @inheritDoc
     */
    public function findOnGoingAcknowledgementByHostIdAndServiceId(int $hostId, int $serviceId): ?Acknowledgement
    {
        return $this->findOnGoingAcknowledegement($hostId, $serviceId);
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @return Acknowledgement|null
     */
    private function findOnGoingAcknowledegement(int $hostId, int $serviceId): ?Acknowledgement
    {
        $acknowledgement = null;
        $sql = "SELECT ack.*, contact.contact_id AS author_id
            FROM `:dbstg`.acknowledgements ack
            LEFT JOIN `:db`.contact
            ON contact.contact_alias = ack.author
            WHERE ack.host_id = :hostId
            AND ack.service_id = :serviceId
            AND ack.deletion_time IS NULL";

        $statement = $this->db->prepare($this->translateDbName($sql));
        $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
        $statement->execute();

        if ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return DbAcknowledgementFactory::createFromRecord($row);
        }

        return $acknowledgement;
    }
}
