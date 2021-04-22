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

namespace Centreon\Domain\PlatformInformation\Model;

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use Centreon\Domain\PlatformInformation\Interfaces\DtoValidatorInterface;

class PlatformInformationDtoValidator implements DtoValidatorInterface
{
    /**
     * @var string $jsonSchemaPath
     */
    private $jsonSchemaPath;

    public function __construct(string $jsonSchemaPath)
    {
        $this->jsonSchemaPath = $jsonSchemaPath;
    }

    /**
     * @inheritDoc
     */
    public function validateOrFail(array $dto): void
    {
        $request = Validator::arrayToObjectRecursive($dto);
        $validator = new Validator();
        $file = 'file://' . $this->jsonSchemaPath;
        $validator->validate(
            $request,
            (object) ['$ref' => $file],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if (!$validator->isValid()) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s" . PHP_EOL, $error['property'], $error['message']);
            }
            throw new \InvalidArgumentException($message);
        }
    }
}
