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

namespace Core\Security\Authentication\Domain\Exception;

class AclConditionsException extends \Exception
{
    /**
     * Exceptions thrown when authentication conditions are invalid.
     *
     * @return self
     */
    public static function invalidAclConditions(): self
    {
        return new self(_("Invalid roles mapping fetched from provider"));
    }

    /**
     * Exceptions thrown when authentication are not found.
     *
     * @return self
     */
    public static function conditionsNotFound(): self
    {
        return new self(_("Role mapping conditions do not match"));
    }
}
