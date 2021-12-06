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

namespace Utility;

class StringConverter
{
    /**
     * Convert a string in camel case format to snake case
     *
     * @param string $camelCaseName Name in camelCase format
     * @return string Returns the name converted in snake case format
     */
    public static function convertCamelCaseToSnakeCase(string $camelCaseName): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCaseName));
    }

    /**
     * Convert a string in snake case format to camel case
     *
     * @param string $snakeCaseName Name in snake format
     * @return string Returns the name converted in camel case format
     */
    public static function convertSnakeCaseToCamelCase(string $snakeCaseName): string
    {
        return lcfirst(str_replace('_', '', ucwords($snakeCaseName, '_')));
    }
}
