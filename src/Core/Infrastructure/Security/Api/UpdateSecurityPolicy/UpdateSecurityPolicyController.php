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

namespace Core\Infrastructure\Security\Api\UpdateSecurityPolicy;

use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicy;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyPresenterInterface;

class UpdateSecurityPolicyController extends AbstractController
{
    public function __invoke(
        UpdateSecurityPolicy $useCase,
        Request $request,
        UpdateSecurityPolicyPresenterInterface $presenter
    ): object {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $useCase($presenter, $request);

        return $presenter->show();
    }
}
