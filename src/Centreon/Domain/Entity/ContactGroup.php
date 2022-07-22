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

use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * ContactGroup entity
 *
 * @codeCoverageIgnore
 */
class ContactGroup
{
    final public const SERIALIZER_GROUP_LIST = 'contact-group-list';
    final public const TABLE = 'contactgroup';
    final public const ENTITY_IDENTIFICATOR_COLUMN = 'cg_id';

    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Groups({ContactGroup::SERIALIZER_GROUP_LIST})
     */
    private ?int $cg_id = null;

    /**
     * @Serializer\SerializedName("name")
     * @Serializer\Groups({ContactGroup::SERIALIZER_GROUP_LIST})
     */
    private ?string $cg_name = null;

    private ?string $cg_alias = null;

    private ?string $cg_comment = null;

    private ?string $cg_type = null;

    private ?string $cg_ldap_dn = null;

    private ?int $ar_id = null;

    /**
     * @Serializer\SerializedName("activate")
     * @Serializer\Groups({ContactGroup::SERIALIZER_GROUP_LIST})
     */
    private ?string $cg_activate = null;

    /**
     * Alias of getCgId
     */
    public function getId(): ?int
    {
        return $this->getCgId();
    }

    public function getCgId(): ?int
    {
        return $this->cg_id;
    }

    /**
     * @param int|null $cgId
     */
    public function setCgId(int $cgId = null): void
    {
        $this->cg_id = $cgId;
    }

    public function getCgName(): ?string
    {
        return $this->cg_name;
    }

    /**
     * @param string|null $cgName
     */
    public function setCgName(string $cgName = null): void
    {
        $this->cg_name = $cgName;
    }

    public function getCgAlias(): ?string
    {
        return $this->cg_alias;
    }

    /**
     * @param string|null $cgAlias
     */
    public function setCgAlias(string $cgAlias = null): void
    {
        $this->cg_alias = $cgAlias;
    }

    public function getCgActivate(): int
    {
        return (int)$this->cg_activate;
    }

    /**
     * @param string|null $cgActivate
     */
    public function setCgActivate(string $cgActivate = null): void
    {
        $this->cg_activate = $cgActivate;
    }

    /**
     * @return string
     */
    public function getCgComment(): ?string
    {
        return $this->cg_comment;
    }

    /**
     * @param string|null $cgComment
     */
    public function setCgComment(string $cgComment = null): void
    {
        $this->cg_comment = $cgComment;
    }

    public function getCgType(): ?string
    {
        return $this->cg_type;
    }

    /**
     * @param string|null $cgType
     */
    public function setCgType(string $cgType = null): void
    {
        $this->cg_type = $cgType;
    }

    public function getCgLdapDn(): ?string
    {
        return $this->cg_ldap_dn;
    }

    /**
     * @param string|null $cgLdapDn
     */
    public function setCgLdapDn(string $cgLdapDn = null): void
    {
        $this->cg_ldap_dn = $cgLdapDn;
    }

    public function getArId(): ?int
    {
        return $this->ar_id;
    }

    /**
     * @param int|null $arId
     */
    public function setArId(int $arId = null): void
    {
        $this->ar_id = $arId;
    }
}
