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

namespace Tests\Centreon\Domain\Monitoring;

use PHPUnit\Framework\TestCase;
use Utility\StringConverter;

class StringConverterTest extends TestCase
{
    /**
     * test convertCamelCaseToSnakeCase
     */
    public function testConvertCamelCaseToSnakeCase()
    {
        $camelCaseName = 'myCurrentProperty1';
        $snakeCaseName = StringConverter::convertCamelCaseToSnakeCase($camelCaseName);

        $this->assertEquals('my_current_property1', $snakeCaseName);
    }

    /**
     * test convertSnakeCaseToCamelCase
     */
    public function testConvertSnakeCaseToCamelCase()
    {
        $snakeCaseName = 'my_current_property1';
        $camelCaseName = StringConverter::convertSnakeCaseToCamelCase($snakeCaseName);

        $this->assertEquals('myCurrentProperty1', $camelCaseName);
    }
}
