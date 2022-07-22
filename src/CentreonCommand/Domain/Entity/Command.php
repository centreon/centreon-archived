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
 *
 */

namespace CentreonCommand\Domain\Entity;

use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Command entity
 *
 * @codeCoverageIgnore
 */
class Command
{
    final public const SERIALIZER_GROUP_LIST = 'command-list';
    final public const TABLE = 'command';
    final public const TYPE_NOTIFICATION = 1;
    final public const TYPE_CHECK = 2;
    final public const TYPE_MISC = 3;
    final public const TYPE_DISCOVERY = 4;

    /**
     * @Serializer\Groups({Command::SERIALIZER_GROUP_LIST})
     * @var int an identification of entity
     */
    private ?int $id = null;

    /**
     * @Serializer\Groups({Command::SERIALIZER_GROUP_LIST})
     */
    private ?string $name = null;

    public function setId(int $id = null): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name = null): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Convert type from string to integer
     */
    public static function getTypeIdFromName(string $name = null): ?int
    {
        return match ($name) {
            'notification' => static::TYPE_NOTIFICATION,
            'check' => static::TYPE_CHECK,
            'misc' => static::TYPE_MISC,
            'discovery' => static::TYPE_DISCOVERY,
            default => null,
        };
    }
}
