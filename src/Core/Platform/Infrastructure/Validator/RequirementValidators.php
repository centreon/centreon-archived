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

namespace Core\Platform\Infrastructure\Validator;

use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Application\Validator\RequirementValidatorsInterface;
use Core\Platform\Application\Validator\RequirementValidatorInterface;

class RequirementValidators implements RequirementValidatorsInterface
{
    use LoggerTrait;

    /**
     * @var RequirementValidatorInterface[]
     */
    private $requirementValidators;

    /**
     * @param \Traversable<RequirementValidatorInterface> $requirementValidators
     *
     * @throws \Exception
     */
    public function __construct(
        \Traversable $requirementValidators,
    ) {
        if (iterator_count($requirementValidators) === 0) {
            throw new \Exception('Requirement validators not found');
        }
        $this->requirementValidators = iterator_to_array($requirementValidators);
    }

    /**
     * @inheritDoc
     */
    public function validateRequirementsOrFail(): void
    {
        foreach ($this->requirementValidators as $requirementValidator) {
            $this->info('Validating platform requirement with ' . $requirementValidator::class);
            $requirementValidator->validateRequirementOrFail();
        }
    }
}
