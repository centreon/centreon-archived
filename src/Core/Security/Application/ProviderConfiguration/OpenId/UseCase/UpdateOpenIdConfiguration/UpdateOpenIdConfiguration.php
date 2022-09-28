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

namespace Core\Security\Application\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration;

use Assert\AssertionFailedException;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Contact\Application\Repository\ReadContactGroupRepositoryInterface;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Contact\Domain\Model\ContactGroup;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\Application\ProviderConfiguration\OpenId\Builder\ConfigurationBuilder;
use Core\Security\Application\ProviderConfiguration\OpenId\Repository\WriteOpenIdConfigurationRepositoryInterface;
use Core\Security\Application\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration\{
    UpdateOpenIdConfigurationErrorResponse
};
use Core\Contact\Application\Repository\ReadContactTemplateRepositoryInterface;
use Core\Security\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Security\Domain\AccessGroup\Model\AccessGroup;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\Configuration;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\AuthorizationRule;

class UpdateOpenIdConfiguration
{
    use LoggerTrait;

    /**
     * @param WriteOpenIdConfigurationRepositoryInterface $repository
     * @param ReadContactTemplateRepositoryInterface $contactTemplateRepository
     * @param ReadContactGroupRepositoryInterface $contactGroupRepository
     * @param ReadAccessGroupRepositoryInterface $accessGroupRepository
     * @param DataStorageEngineInterface $dataStorageEngine
     */
    public function __construct(
        private WriteOpenIdConfigurationRepositoryInterface $repository,
        private ReadContactTemplateRepositoryInterface $contactTemplateRepository,
        private ReadContactGroupRepositoryInterface $contactGroupRepository,
        private ReadAccessGroupRepositoryInterface $accessGroupRepository,
        private DataStorageEngineInterface $dataStorageEngine
    ) {
    }

    /**
     * @param UpdateOpenIdConfigurationPresenterInterface $presenter
     * @param UpdateOpenIdConfigurationRequest $request
     */
    public function __invoke(
        UpdateOpenIdConfigurationPresenterInterface $presenter,
        UpdateOpenIdConfigurationRequest $request
    ): void {
        $this->info('Updating OpenID Configuration');
        try {
            $contactTemplate = $this->getContactTemplateOrFail($request->contactTemplate);
            $contactGroup = $request->contactGroupId !== null
                ? $this->getContactGroupOrFail($request->contactGroupId)
                : null;
            $authorizationRules = $this->createAuthorizationRules($request->authorizationRules);
            $configuration = ConfigurationBuilder::create(
                $request,
                $contactTemplate,
                $contactGroup,
                $authorizationRules
            );
            $this->updateConfiguration($configuration);
        } catch (AssertionException | AssertionFailedException | OpenIdConfigurationException $ex) {
            $this->error(
                'Unable to create OpenID Configuration because one or several parameters are invalid',
                ['trace' => $ex->getTraceAsString()]
            );
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        } catch (\Throwable $ex) {
            $this->error('Error during Opend ID Configuration Update', ['trace' => $ex->getTraceAsString()]);
            $presenter->setResponseStatus(new UpdateOpenIdConfigurationErrorResponse());
            return;
        }

        $presenter->setResponseStatus(new NoContentResponse());
    }

    /**
     * Get Contact template or throw an Exception
     *
     * @param array{id: int, name: string}|null $contactTemplateFromRequest
     * @return ContactTemplate|null
     * @throws \Throwable|OpenIdConfigurationException
     */
    private function getContactTemplateOrFail(?array $contactTemplateFromRequest): ?ContactTemplate
    {
        if ($contactTemplateFromRequest === null) {
            return null;
        }
        if (($contactTemplate = $this->contactTemplateRepository->find($contactTemplateFromRequest["id"])) === null) {
            throw OpenIdConfigurationException::contactTemplateNotFound(
                $contactTemplateFromRequest["name"]
            );
        }

        return $contactTemplate;
    }

    /**
     * Get Contact Group or throw an Exception
     *
     * @param integer $contactGroupId
     * @return ContactGroup
     * @throws \Throwable|OpenIdConfigurationException
     */
    private function getContactGroupOrFail(int $contactGroupId): ContactGroup
    {
        $this->info('Getting Contact Group');
        if (($contactGroup = $this->contactGroupRepository->find($contactGroupId)) === null) {
            $this->error('An existent contact group is mandatory for OpenID Configuration');
            throw OpenIdConfigurationException::contactGroupNotFound(
                $contactGroupId
            );
        }

        return $contactGroup;
    }

    /**
     * Create Authorization Rules
     *
     * @param array<array{claim_value: string, access_group_id: int}> $authorizationRulesFromRequest
     * @return AuthorizationRule[]
     * @throws \Throwable
     */
    private function createAuthorizationRules(array $authorizationRulesFromRequest): array
    {
        $this->info('Creating Authorization Rules');
        $accessGroupIds = $this->getAccessGroupIds($authorizationRulesFromRequest);

        if (empty($accessGroupIds)) {
            return [];
        }

        $foundAccessGroups = $this->accessGroupRepository->findByIds($accessGroupIds);

        $this->logNonExistentAccessGroupsIds($accessGroupIds, $foundAccessGroups);

        $authorizationRules = [];
        foreach ($authorizationRulesFromRequest as $authorizationRule) {
            $accessGroup = $this->findAccessGroupFromFoundAccessGroups(
                $authorizationRule["access_group_id"],
                $foundAccessGroups
            );
            if ($accessGroup !== null) {
                $authorizationRules[] = new AuthorizationRule($authorizationRule["claim_value"], $accessGroup);
            }
        }

        return $authorizationRules;
    }

    /**
     * Add log for all the non existent access groups
     *
     * @param int[] $accessGroupIdsFromRequest
     * @param AccessGroup[] $foundAccessGroups
     */
    private function logNonExistentAccessGroupsIds(array $accessGroupIdsFromRequest, array $foundAccessGroups): void
    {
        $foundAccessGroupsId = [];
        foreach ($foundAccessGroups as $foundAccessGroup) {
            $foundAccessGroupsId[] = $foundAccessGroup->getId();
        }
        $nonExistentAccessGroupsIds = array_diff($accessGroupIdsFromRequest, $foundAccessGroupsId);
        $this->error("Access Groups not found", [
            "access_group_ids" => implode(', ', $nonExistentAccessGroupsIds)
        ]);
    }

    /**
     * Compare the access group id sent in request with Access groups from database
     * Return the access group that have the same id than the access group id from the request
     *
     * @param int $accessGroupIdFromRequest Access group id sent in the request
     * @param AccessGroup[] $foundAccessGroups Access groups found in data storage
     * @return AccessGroup|null
     */
    private function findAccessGroupFromFoundAccessGroups(
        int $accessGroupIdFromRequest,
        array $foundAccessGroups
    ): ?AccessGroup {
        foreach ($foundAccessGroups as $foundAccessGroup) {
            if ($accessGroupIdFromRequest === $foundAccessGroup->getId()) {
                return $foundAccessGroup;
            }
        }
        return null;
    }

    /**
     * Return all unique access group id from request
     *
     * @param array<array{claim_value: string, access_group_id: int}> $authorizationRulesFromRequest
     * @return int[]
     */
    private function getAccessGroupIds(array $authorizationRulesFromRequest): array
    {
        $accessGroupIds = [];
        foreach ($authorizationRulesFromRequest as $authorizationRules) {
            $accessGroupIds[] = $authorizationRules["access_group_id"];
        }

        return array_unique($accessGroupIds);
    }

    /**
     * Update OpenId Configuration
     *
     * @param Configuration $configuration
     * @throws \Throwable
     */
    private function updateConfiguration(Configuration $configuration): void
    {
        $isAlreadyInTransaction = $this->dataStorageEngine->isAlreadyinTransaction();
        try {
            if (! $isAlreadyInTransaction) {
                $this->dataStorageEngine->startTransaction();
            }
            $this->info('Updating OpenID Configuration');
            $this->repository->updateConfiguration($configuration);
            $this->info('Removing existent Authorization Rules');
            $this->repository->deleteAuthorizationRules();
            $this->info('Inserting new Authorization Rules');
            $this->repository->insertAuthorizationRules($configuration->getAuthorizationRules());
            if (! $isAlreadyInTransaction) {
                $this->dataStorageEngine->commitTransaction();
            }
        } catch (\Throwable $ex) {
            if (! $isAlreadyInTransaction) {
                $this->dataStorageEngine->rollbackTransaction();
                throw $ex;
            }
        }
    }
}
