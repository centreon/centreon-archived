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
    public const SERIALIZER_GROUP_LIST = 'command-list';
    public const TABLE = 'command';
    public const TYPE_NOTIFICATION = 1;
    public const TYPE_CHECK = 2;
    public const TYPE_MISC = 3;
    public const TYPE_DISCOVERY = 4;

    /**
     * @Serializer\Groups({Command::SERIALIZER_GROUP_LIST})
     * @var int an identification of entity
     */
    private $id;

    /**
     * @Serializer\Groups({Command::SERIALIZER_GROUP_LIST})
     * @var string
     */
    private $name;

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id = null): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name = null): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Convert type from string to integer
     *
     * @param string $name
     * @return int|null
     */
    public static function getTypeIdFromName(string $name = null): ?int
    {
        switch ($name) {
            case 'notification':
                return static::TYPE_NOTIFICATION;
            case 'check':
                return static::TYPE_CHECK;
            case 'misc':
                return static::TYPE_MISC;
            case 'discovery':
                return static::TYPE_DISCOVERY;
        }

        return null;
    }
}
