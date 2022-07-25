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

namespace CentreonUser\Domain\Entity;

use Centreon\Infrastructure\CentreonLegacyDB\Mapping;
use Symfony\Component\Serializer\Annotation as Serializer;
use PDO;

/**
 * Timeperiod entity
 *
 * @codeCoverageIgnore
 */
class Timeperiod implements Mapping\MetadataInterface
{
    final public const SERIALIZER_GROUP_LIST = 'timeperiod-list';

    final public const TABLE = 'timeperiod';
    final public const ENTITY_IDENTIFICATOR_COLUMN = 'tp_id';

    /**
     * @Serializer\Groups({Timeperiod::SERIALIZER_GROUP_LIST})
     * @var int an identification of entity
     */
    private ?int $id = null;

    /**
     * @Serializer\Groups({Timeperiod::SERIALIZER_GROUP_LIST})
     */
    private ?string $name = null;

    /**
     * @Serializer\Groups({Timeperiod::SERIALIZER_GROUP_LIST})
     */
    private ?string $alias = null;

    /**
     * {@inheritdoc}
     */
    public static function loadMetadata(Mapping\ClassMetadata $metadata): void
    {
        $metadata->setTableName(static::TABLE)
            ->add('id', 'tp_id', PDO::PARAM_INT, null, true)
            ->add('name', 'tp_name', PDO::PARAM_STR)
            ->add('alias', 'tp_alias', PDO::PARAM_STR);
    }

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

    public function setAlias(string $alias = null): void
    {
        $this->alias = $alias;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
