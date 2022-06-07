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
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\Domain\ProviderConfiguration\OpenId\{
    Model\OpenIdConfigurationFactory,
    Exceptions\OpenIdConfigurationException
};
use Core\Security\Application\ProviderConfiguration\OpenId\Repository\WriteOpenIdConfigurationRepositoryInterface;
use Core\Contact\Application\Repository\ReadContactTemplateRepositoryInterface;

class UpdateOpenIdConfiguration
{
    use LoggerTrait;

    /**
     * @param WriteOpenIdConfigurationRepositoryInterface $repository
     * @param ReadContactTemplateRepositoryInterface $contactTemplateRepository
     */
    public function __construct(
        private WriteOpenIdConfigurationRepositoryInterface $repository,
        private ReadContactTemplateRepositoryInterface $contactTemplateRepository
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
            $configuration = OpenIdConfigurationFactory::createFromRequest($request);
            if ($configuration->getContactTemplate() !== null) {
                $this->validateContactTemplateExistsOrFail($configuration->getContactTemplate());
            }
        } catch (AssertionException | OpenIdConfigurationException $ex) {
            $this->error('Unable to create OpenID Configuration because one or many parameters are invalid');
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        }
        $this->repository->updateConfiguration($configuration);

        $presenter->setResponseStatus(new NoContentResponse());
    }

    /**
     * Validate that contact template link to configuration exists or throw an Exception.
     *
     * @param ContactTemplate $contactTemplate
     * @throws OpenIdConfigurationException
     */
    private function validateContactTemplateExistsOrFail(ContactTemplate $contactTemplate): void
    {
        if ($this->contactTemplateRepository->find($contactTemplate->getId()) === null) {
            throw OpenIdConfigurationException::contactTemplateNotFound(
                $contactTemplate->getName()
            );
        }
    }
}
