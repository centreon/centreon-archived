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
 * See the License for the spceific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Core\Security\Authentication\Infrastructure\Provider;

use Centreon\Domain\Log\LoggerTrait;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Core\Contact\Application\Repository\WriteContactGroupRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\AccessGroup\Application\Repository\WriteAccessGroupRepositoryInterface;

class AclUpdater implements AclUpdaterInterface
{
    use LoggerTrait;

    /**
     * @var ProviderAuthenticationInterface
     */
    private ProviderAuthenticationInterface $provider;

    /**
     * @param DataStorageEngineInterface $dataStorageEngine
     * @param WriteContactGroupRepositoryInterface $contactGroupRepository
     * @param WriteAccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        private DataStorageEngineInterface $dataStorageEngine,
        private WriteContactGroupRepositoryInterface $contactGroupRepository,
        private WriteAccessGroupRepositoryInterface $accessGroupRepository,
    ) {
    }

    /**
     * @param ProviderAuthenticationInterface $provider
     * @param ContactInterface $user
     */
    public function updateForProviderAndUser(ProviderAuthenticationInterface $provider, ContactInterface $user): void
    {
        $this->provider = $provider;
        if ($provider->isUpdateACLSupported()) {

            /** @var CustomConfiguration $customConfiguration */
            $customConfiguration = $provider->getConfiguration()->getCustomConfiguration();
            $aclConditions = $customConfiguration->getACLConditions();
            if ($aclConditions->isEnabled()) {
                /** @phpstan-ignore-next-line */
                $claims = $aclConditions->getClaimValues();
                /** @phpstan-ignore-next-line */
                $userAccessGroups = $this->provider->getUserAccessGroupsFromClaims($claims);
                $this->updateAccessGroupsForUser($user, $userAccessGroups);
            }

            if ($customConfiguration->getGroupsMapping()->isEnabled()) {
                $this->updateContactGroupsForUser($user);
            }
        }
    }

    /**
     * Delete and Insert Access Group:q!s for authenticated user
     *
     * @param ContactInterface $user
     * @param AccessGroup[] $userAccessGroups
     */
    private function updateAccessGroupsForUser(ContactInterface $user, array $userAccessGroups): void
    {
        try {
            $this->info("Updating User Access Groups", [
                "user_id" => $user->getId(),
                "access_groups" => $userAccessGroups
            ]);
            $this->dataStorageEngine->startTransaction();
            $this->accessGroupRepository->deleteAccessGroupsForUser($user);
            $this->accessGroupRepository->insertAccessGroupsForUser($user, $userAccessGroups);
            $this->dataStorageEngine->commitTransaction();
        } catch (\Exception $ex) {
            $this->dataStorageEngine->rollbackTransaction();
            $this->error('Error during ACL update', [
                "user_id" => $user->getId(),
                "access_groups" => $userAccessGroups,
                "trace" => $ex->getTraceAsString()
            ]);
        }
    }

    /**
     * Delete and Insert Contact Group for authenticated user
     *
     * @param ContactInterface $user
     */
    private function updateContactGroupsForUser(ContactInterface $user): void
    {
        /** @phpstan-ignore-next-line */
        $contactGroups = $this->provider->getUserContactGroups();

        try {
            $this->info('Updating user contact group', [
                "user_id" => $user->getId(),
                "contact_group_id" => [
                    array_map(function ($contactGroup) {
                        return $contactGroup->getId();
                    }, $contactGroups)
                ],
            ]);
            $this->dataStorageEngine->startTransaction();
            $this->contactGroupRepository->deleteContactGroupsForUser($user);
            foreach ($contactGroups as $contactGroup) {
                $this->contactGroupRepository->insertContactGroupForUser($user, $contactGroup);
            }
            $this->dataStorageEngine->commitTransaction();
        } catch (\Exception $ex) {
            $this->dataStorageEngine->rollbackTransaction();
            $this->error('Error during contact group update', [
                "user_id" => $user->getId(),
                "contact_group_id" => $contactGroup->getId(),
                "trace" => $ex->getTraceAsString()
            ]);
        }
    }
}
