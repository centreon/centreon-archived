<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\Security\Repository;

use Core\Domain\Security\Model\SecurityPolicy;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Infrastructure\Security\Repository\DbSecurityPolicyFactory;
use Core\Application\Security\Repository\ReadSecurityPolicyRepositoryInterface;

class DbReadSecurityPolicyRepository extends AbstractRepositoryDRB implements ReadSecurityPolicyRepositoryInterface
{
    /**
     * @param DatabaseConnection $db
     */
    public function __construct(private DatabaseConnection $db)
    {
    }

    /**
     * @inheritDoc
     */
    public function findSecurityPolicy(): ?SecurityPolicy
    {
        $statement = $this->db->query("SELECT * FROM password_security_policy");

        $securityPolicy = null;
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $securityPolicy = DbSecurityPolicyFactory::createFromRecord($result);
        }
        return $securityPolicy;
    }
}
