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

namespace Centreon\Infrastructure\MonitoringServer\API\v2110\Validator;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Infrastructure\MonitoringServer\API\v2110\Validator as Validator;

class MonitoringServersDeclarationFileValidator implements
Validator\Interfaces\MonitoringServersDeclarationFileValidatorInterface
{
    public function validateOrFail(array $fileContent): void
    {
        Assertion::keyExists($fileContent, 'central-address');
        Assertion::keyExists($fileContent, 'instances');

        foreach ($fileContent['instances'] as $instance) {
            Assertion::keyExists($instance, 'type');
            Assertion::keyExists($instance, 'name');
            Assertion::keyExists($instance, 'address');
            Assertion::keyExists($instance, 'hostname');
        }
    }
}
