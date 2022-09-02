<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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
 *Controller
 */
declare(strict_types=1);

namespace Centreon\Domain\Service\JsonValidator\Interfaces;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface JsonValidatorInterface
{
    /**
     * Validate a JSON string according to a model.
     *
     * @param string $json String representing the JSON to validate
     * @param string $modelName Model name to apply to validate the JSON
     * @return ConstraintViolationListInterface
     * @throws \Exception
     */
    public function validate(string $json, string $modelName): ConstraintViolationListInterface;

    /**
     * Defines the version of the definition files that will be used for the validation process.
     *
     * @param string $version Version to use for the definition files
     * @return $this
     */
    public function forVersion(string $version): JsonValidatorInterface;
}
