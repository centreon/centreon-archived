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

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Contact\Application\Repository\ReadContactGroupRepositoryInterface;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Contact\Domain\Model\ContactGroup;
use Core\Security\Domain\ProviderConfiguration\OpenId\{
    Model\OpenIdConfigurationFactory,
    Exceptions\OpenIdConfigurationException
};
use Core\Security\Application\ProviderConfiguration\OpenId\Repository\WriteOpenIdConfigurationRepositoryInterface;
use Core\Security\Application\ProviderConfiguration\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;
use Core\Contact\Application\Repository\ReadContactTemplateRepositoryInterface;
use Core\Security\Application\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration\UpdateOpenIdConfigurationErrorResponse;
use Core\Security\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\AuthorizationRule;

class UpdateOpenIdConfiguration
{
    use LoggerTrait;

    /**
     * @param ReadOpenIdConfigurationRepositoryInterface $readRepository
     * @param WriteOpenIdConfigurationRepositoryInterface $repository
     * @param ReadContactTemplateRepositoryInterface $contactTemplateRepository
     * @param ReadContactGroupRepositoryInterface $contactGroupRepository
     * @param ReadAccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        private ReadOpenIdConfigurationRepositoryInterface $readRepository,
        private WriteOpenIdConfigurationRepositoryInterface $repository,
        private ReadContactTemplateRepositoryInterface $contactTemplateRepository,
        private ReadContactGroupRepositoryInterface $contactGroupRepository,
        private ReadAccessGroupRepositoryInterface $accessGroupRepository,
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
            $contactGroup = $this->getContactGroupOrFail($request->contactGroupId);
            $authorizationRules = $this->createAuthorizationRulesIfNotExist($request->authorizationRules);
            $configuration = OpenIdConfigurationFactory::create(
                $request,
                $contactTemplate,
                $contactGroup,
                $authorizationRules
            );
            $this->repository->updateConfiguration($configuration);
        } catch (AssertionException | OpenIdConfigurationException $ex) {
            $this->error(
                'Unable to create OpenID Configuration because one or many parameters are invalid',
                ['trace' => $ex->getTraceAsString()]
            );
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        } catch (\Throwable $ex) {
            $this->error('Error during Opend ID Configuration Update',['trace' => $ex->getTraceAsString()]);
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
     * @param integer|null $contactGroupId
     * @return ContactGroup|null
     */
    private function getContactGroupOrFail(?int $contactGroupId): ?ContactGroup
    {
        if ($contactGroupId === null) {
            return null;
        }
        if (($contactGroup = $this->contactGroupRepository->find($contactGroupId)) === null) {
            throw OpenIdConfigurationException::contactGroupNotFound(
                $contactGroupId
            );
        }

        return $contactGroup;
    }

    /**
     * Create non already existent AuthorizationRule Objects
     *
     * @param array{claim_value: string, access_group_id: int} $authorizationRules
     * @return AuthorizationRule[]
     */
    private function createAuthorizationRulesIfNotExist(array $authorizationRulesFromRequest): array
    {
        $existingAuthorizationRules = $this->readRepository->findAuthorizationRules();
        $authorizationRules = [];
        foreach ($authorizationRulesFromRequest as $authorizationRule) {
            if (($accessGroup = $this->accessGroupRepository->find($authorizationRule["access_group_id"])) === null) {
                $this->error("Access Group not found", [
                    "access_group_id" => $authorizationRule["access_group_id"]
                ]);
                continue;
            }

            //Check if Authorization Rule already exists
            $authorizationRules[] = new AuthorizationRule($authorizationRule["claim_value"], $accessGroup);
        }

        return $this->findNonExistingAuthorizationRules($existingAuthorizationRules, $authorizationRules);
    }

    /**
     * Return all the non already existent Authorization Rules
     *
     * @param AuthorizationRule[] $existingAuthorizationRules
     * @param AuthorizationRule[] $newAuthorizationRules
     * @return AuthorizationRule[]
     */
    private function findNonExistingAuthorizationRules(
        array $existingAuthorizationRules,
        array $newAuthorizationRules
    ): array {
        $nonExistentAuthorizationRules = array_udiff(
            $newAuthorizationRules,
            $existingAuthorizationRules,
            function ($newAuthorizationRule, $existingAuthorizationRule) {
                return (int) ! $newAuthorizationRule->isEqual($existingAuthorizationRule);
            }
        );

        return $nonExistentAuthorizationRules;
    }
}
