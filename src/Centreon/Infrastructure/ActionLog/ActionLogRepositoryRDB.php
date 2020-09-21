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

namespace Centreon\Infrastructure\ActionLog;

use Centreon\Domain\ActionLog\ActionLog;
use Centreon\Domain\ActionLog\Interfaces\ActionLogRepositoryInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

class ActionLogRepositoryRDB extends AbstractRepositoryDRB implements ActionLogRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function __construct(DatabaseConnection $db, SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
    }

    /**
     * @inheritDoc
     */
    public function addLog(ActionLog $actionLog): void
    {
        $request = $this->translateDbName(
            'INSERT INTO `:dbstg`.log_action 
            (action_log_date, object_type, object_id, object_name, action_type, log_contact_id)
            VALUES (:creation_date, :object_type, :object_id, :object_name, :action_type, :contact_id)'
        );

        $creationDate = $actionLog->getCreationDate() !== null
            ? $actionLog->getCreationDate()->getTimestamp()
            : (new \DateTime())->getTimestamp();

        $statement = $this->db->prepare($request);
        $statement->bindValue(':creation_date', $creationDate, \PDO::PARAM_INT);
        $statement->bindValue(':object_type', $actionLog->getObjectType(), \PDO::PARAM_STR);
        $statement->bindValue(':object_id', $actionLog->getObjectId(), \PDO::PARAM_INT);
        $statement->bindValue(':object_name', $actionLog->getObjectName(), \PDO::PARAM_STR);
        $statement->bindValue(':action_type', $actionLog->getActionType(), \PDO::PARAM_STR);
        $statement->bindValue(':contact_id', $actionLog->getContactId(), \PDO::PARAM_INT);
        $statement->execute();
    }
}
