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

namespace Core\Platform\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Application\Repository\ReadRequirementsRepositoryInterface;
use Core\Platform\Application\Repository\RequirementProviderRepositoryInterface;

class ReadRequirementsRepository implements ReadRequirementsRepositoryInterface
{
    use LoggerTrait;

    /**
     * @var RequirementProviderRepositoryInterface[]
     */
    private $requirementProviders;

    /**
     * @param \Traversable<RequirementProviderRepositoryInterface> $requirementProviders
     * @throws \Exception
     */
    public function __construct(
        \Traversable $requirementProviders,
    ) {
        if (iterator_count($requirementProviders) === 0) {
            throw new \Exception('Requirement providers not found');
        }
        $this->requirementProviders = iterator_to_array($requirementProviders);
    }

    /**
     * @inheritDoc
     */
    public function validateRequirementsOrFail(): void
    {
        foreach ($this->requirementProviders as $requirementProvider) {
            $this->info('Validating platform requirement with ' . $requirementProvider::class);
            $requirementProvider->validateRequirementOrFail();
        }
    }
}
