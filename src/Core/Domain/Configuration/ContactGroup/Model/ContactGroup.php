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

namespace Core\Domain\Configuration\ContactGroup\Model;

use Centreon\Domain\Common\Assertion\Assertion;

class ContactGroup
{
    public const MAX_NAME_LENGTH = 200,
                 MAX_ALIAS_LENGTH = 200;

    /**
     * @param int $id
     * @param string $name
     * @param string $alias
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        private int $id,
        private string $name,
        private string $alias
    ) {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'ContactGroup::name');
        Assertion::notEmpty($name, 'ContactGroup::name');
        Assertion::maxLength($alias, self::MAX_ALIAS_LENGTH, 'ContactGroup::alias');
        Assertion::notEmpty($alias, 'ContactGroup::alias');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }
}
