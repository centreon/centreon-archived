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

namespace Core\Platform\Infrastructure\Api\UpdateVersions;

use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Contact\Contact;
use Core\Platform\Application\UseCase\UpdateVersions\{
    UpdateVersions,
    UpdateVersionsPresenterInterface
};
use Core\Application\Common\UseCase\UnauthorizedResponse;

class UpdateVersionsController extends AbstractController
{
    use LoggerTrait;

    /**
     * @param UpdateVersions $useCase
     * @param Request $request
     * @param UpdateVersionsPresenterInterface $presenter
     * @return object
     */
    public function __invoke(
        UpdateVersions $useCase,
        Request $request,
        UpdateVersionsPresenterInterface $presenter
    ): object {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (! $contact->isAdmin()) {
            $presenter->setResponseStatus(new UnauthorizedResponse('Only admin user can perform upgrade'));

            return $presenter->show();
        }

        $this->info('Validating request body...');
        $this->validateDataSent($request, __DIR__ . '/UpdateVersionsSchema.json');

        $useCase($presenter);

        return $presenter->show();
    }
}
