<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\PlatformInformation\UseCase\V21;

use Centreon\Domain\PlatformInformation\Interfaces\DtoValidatorInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationReadRepositoryInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationWriteRepositoryInterface;

class UpdatePartiallyPlatformInformation
{
    /**
     * @var PlatformInformationWriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * @var PlatformInformationReadRepositoryInterface
     */
    private $readRepository;

    public function __construct(
        PlatformInformationWriteRepositoryInterface $writeRepository,
        PlatformInformationReadRepositoryInterface $readRepository
    ) {
        $this->writeRepository = $writeRepository;
    }

    /**
     * Array of all available validators for this use case.
     *
     * @var array
     */
    private $validators = [];

    /**
     * @param array $validators
     */
    public function addValidators(array $validators): void
    {
        foreach ($validators as $validator)
        {
            $this->addValidator($validator);
        }
    }

    /**
     * @param DtoValidatorInterface $dtoValidator
     */
    private function addValidator(DtoValidatorInterface $dtoValidator): void
    {
        $this->validators[] = $dtoValidator;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindHostCategoriesResponse
     * @throws \Exception
     */
    public function execute(array $request)
    {
        //methode private validate DTO
        foreach ($this->validators as $validator) {
            try {
                $validator->validateOrFail($request);
            } catch(\Throwable $ex) {
                throw $ex;
            }
        }

        //utiliser les services déjà existant

        //writeRepository->update


    }
}