<?php

/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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
    public const SERIALIZER_GROUP_LIST = 'contact-group-list';
    public const TABLE = 'contactgroup';
    public const ENTITY_IDENTIFICATOR_COLUMN = 'cg_id';

    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Groups({ContactGroup::SERIALIZER_GROUP_LIST})
     * @var int|null
     */
    private $cg_id;

    /**
     * @Serializer\SerializedName("name")
     * @Serializer\Groups({ContactGroup::SERIALIZER_GROUP_LIST})
     * @var string|null
     */
    private $cg_name;

    /**
     * @var string|null
     */
    private $cg_alias;

    /**
     * @var string|null
     */
    private $cg_comment;

    /**
     * @var string|null
     */
    private $cg_type;

    /**
     * @var string|null
     */
    private $cg_ldap_dn;

    /**
     * @var int|null
     */
    private $ar_id;

    /**
     * @Serializer\SerializedName("activate")
     * @Serializer\Groups({ContactGroup::SERIALIZER_GROUP_LIST})
     * @var string|null
     */
    private $cg_activate;

    /**
     * Alias of getCgId
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getCgId();
    }

    /**
     * @return int|null
     */
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

    /**
     * @return string|null
     */
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

    /**
     * @return string|null
     */
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

    /**
     * @return int
     */
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

    /**
     * @return string|null
     */
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

    /**
     * @return string|null
     */
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

    /**
     * @return int|null
     */
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
