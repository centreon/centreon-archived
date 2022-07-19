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

namespace Core\Severity\RealTime\Infrastructure\API\FindSeverity;

use Core\Severity\RealTime\Domain\Model\Severity;
use Centreon\Application\Controller\AbstractController;
use Core\Severity\RealTime\Application\UseCase\FindSeverity\FindSeverity;
use Core\Severity\RealTime\Application\UseCase\FindSeverity\FindSeverityPresenterInterface;

class FindHostSeverityController extends AbstractController
{
    /**
     * @param FindSeverity $useCase
     * @param FindSeverityPresenterInterface $presenter
     * @return object
     */
    public function __invoke(FindSeverity $useCase, FindSeverityPresenterInterface $presenter): object
    {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $useCase(Severity::HOST_SEVERITY_TYPE_ID, $presenter);
        return $presenter->show();
    }
}
