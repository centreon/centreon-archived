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

namespace Centreon\Domain\Entity;

use Centreon\Infrastructure\CentreonLegacyDB\Mapping;
use Symfony\Component\Serializer\Annotation as Serializer;
use PDO;

/**
 * ACL group entity
 *
 * @codeCoverageIgnore
 */
class AclGroup implements Mapping\MetadataInterface
{
    public const SERIALIZER_GROUP_LIST = 'acl-group-list';
    public const ENTITY_IDENTIFICATOR_COLUMN = 'acl_group_id';
    public const TABLE = 'acl_groups';

    /**
     * @Serializer\Groups({
     *     AclGroup::SERIALIZER_GROUP_LIST
     * })
     * @var int an identification of entity
     */
    private $id;

    /**
     * @Serializer\Groups({
     *     AclGroup::SERIALIZER_GROUP_LIST
     * })
     * @var string
     */
    private $name;

    /**
     * @Serializer\Groups({
     *     AclGroup::SERIALIZER_GROUP_LIST
     * })
     * @var string
     */
    private $alias;

    /**
     * @Serializer\Groups({
     *     AclGroup::SERIALIZER_GROUP_LIST
     * })
     * @var bool
     */
    private $changed;

    /**
     * @Serializer\Groups({
     *     AclGroup::SERIALIZER_GROUP_LIST
     * })
     * @var bool
     */
    private $activate;

    /**
     * {@inheritdoc}
     */
    public static function loadMetadata(Mapping\ClassMetadata $metadata): void
    {
        $metadata->setTableName(static::TABLE)
            ->add('id', 'acl_group_id', PDO::PARAM_INT, null, true)
            ->add('name', 'acl_group_name', PDO::PARAM_STR)
            ->add('alias', 'acl_group_alias', PDO::PARAM_STR)
            ->add('changed', 'acl_group_changed', PDO::PARAM_INT)
            ->add('activate', 'acl_group_activate', PDO::PARAM_STR); // enum
    }

    /**
     * @param string|int $id
     * @return void
     */
    public function setId($id): void
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
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias = null): void
    {
        $this->alias = $alias;
    }

    /**
     * @return bool
     */
    public function getChanged(): ?bool
    {
        return $this->changed;
    }

    /**
     * @param bool $changed
     */
    public function setChanged($changed = null): void
    {
        $this->changed = $changed;
    }

    /**
     * @return bool
     */
    public function getActivate(): ?bool
    {
        return $this->activate;
    }

    /**
     * @param bool $activate
     */
    public function setActivate($activate = null): void
    {
        $this->activate = $activate;
    }
}
