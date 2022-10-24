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

namespace Core\Security\ProviderConfiguration\Infrastructure\OpenId\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Contact\Infrastructure\Repository\DbContactGroupFactory;
use Core\Contact\Infrastructure\Repository\DbContactTemplateFactory;
use Core\Security\AccessGroup\Infrastructure\Repository\DbAccessGroupFactory;
use Core\Security\ProviderConfiguration\Application\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface
    as ReadRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthorizationRule;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\ContactGroupRelation;
use Throwable;

class DbReadOpenIdConfigurationRepository extends AbstractRepositoryDRB implements ReadRepositoryInterface
{
    /**

     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Get Contact Template
     *
     * @param int $contactTemplateId
     * @return ContactTemplate|null
     * @throws Throwable
     */
    public function getContactTemplate(int $contactTemplateId): ?ContactTemplate
    {
        $statement = $this->db->prepare(
            "SELECT
                contact_id,
                contact_name
            FROM contact
            WHERE
                contact_id = :contactTemplateId
                AND contact_register = 0"
        );
        $statement->bindValue(':contactTemplateId', $contactTemplateId, \PDO::PARAM_INT);
        $statement->execute();

        $contactTemplate = null;
        if ($statement !== false && $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $contactTemplate = DbContactTemplateFactory::createFromRecord($result);
        }

        return $contactTemplate;
    }

    /**
     * Get Contact Group
     *
     * @param int $contactGroupId
     * @return ContactGroup|null
     * @throws Throwable
     */
    public function getContactGroup(int $contactGroupId): ?ContactGroup
    {
        $statement = $this->db->prepare(
            "SELECT
                cg_id,
                cg_name
            FROM contactgroup
            WHERE
                cg_id = :contactGroupId"
        );
        $statement->bindValue(':contactGroupId', $contactGroupId, \PDO::PARAM_INT);
        $statement->execute();

        $contactGroup = null;
        if ($statement !== false && $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $contactGroup = DbContactGroupFactory::createFromRecord($result);
        }

        return $contactGroup;
    }

    /**
     * Get Authorization Rules
     *
     * @throws Throwable
     * @param int $providerConfigurationId
     * @return array<AuthorizationRule>
     */
    public function getAuthorizationRulesByConfigurationId(int $providerConfigurationId): array
    {
        $statement = $this->db->prepare(
            "SELECT * from security_provider_access_group_relation spagn
                INNER JOIN acl_groups ON acl_group_id = spagn.access_group_id
                WHERE spagn.provider_configuration_id = :providerConfigurationId
                ORDER BY spagn.priority asc"
        );
        $statement->bindValue(':providerConfigurationId', $providerConfigurationId, \PDO::PARAM_INT);
        $statement->execute();

        $authorizationRules = [];
        while ($statement !== false && is_array($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $accessGroup = DbAccessGroupFactory::createFromRecord($result);
            $authorizationRules[] = new AuthorizationRule($result['claim_value'], $accessGroup, $result['priority']);
        }
        return $authorizationRules;
    }

    /**
     * Get Contact Group relations
     *
     * @param int $providerConfigurationId
     * @return ContactGroupRelation[]
     * @throws \Throwable
     */
    public function getContactGroupRelationsByConfigurationId(int $providerConfigurationId): array
    {
        $statement = $this->db->prepare(
            "SELECT * FROM security_provider_contact_group_relation spcgn
                INNER JOIN contactgroup ON cg_id = spcgn.contact_group_id
                WHERE spcgn.provider_configuration_id = :providerConfigurationId"
        );
        $statement->bindValue(':providerConfigurationId', $providerConfigurationId, \PDO::PARAM_INT);
        $statement->execute();

        $contactGroupRelations = [];
        while ($statement !== false && is_array($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $contactGroup = DbContactGroupFactory::createFromRecord($result);
            $contactGroupRelations[] = new ContactGroupRelation($result['claim_value'], $contactGroup);
        }

        return $contactGroupRelations;
    }
}
