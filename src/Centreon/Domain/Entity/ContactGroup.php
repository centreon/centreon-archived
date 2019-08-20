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

class ContactGroup
{

    const TABLE = 'contactgroup';
    const ENTITY_IDENTIFICATOR_COLUMN = 'cg_id';

    /**
     * @var int
     */
    private $cg_id;

    /**
     * @var string
     */
    private $cg_name;

    /**
     * @var string
     */
    private $cg_alias;

    /**
     * @var string
     */
    private $cg_comment;

    /**
     * @var string
     */
    private $cg_type;

    /**
     * @var string
     */
    private $cg_ldap_dn;

    /**
     * @var int
     */
    private $ar_id;

    /**
     * @var string
     */
    private $cg_activate;

    /**
     * @return int
     */
    public function getCgId(): int
    {
        return $this->cg_id;
    }

    /**
     * @param int $cg_id
     */
    public function setCgId(int $cg_id): void
    {
        $this->cg_id = $cg_id;
    }

    /**
     * @return string
     */
    public function getCgName(): string
    {
        return $this->cg_name;
    }

    /**
     * @param string $cg_name
     */
    public function setCgName(string $cg_name): void
    {
        $this->cg_name = $cg_name;
    }

    /**
     * @return string
     */
    public function getCgAlias(): string
    {
        return $this->cg_alias;
    }

    /**
     * @param string $cg_alias
     */
    public function setCgAlias(string $cg_alias): void
    {
        $this->cg_alias = $cg_alias;
    }

    /**
     * @return string
     */
    public function getCgActivate(): string
    {
        return $this->cg_activate;
    }

    /**
     * @param string $cg_activate
     */
    public function setCgActivate(string $cg_activate): void
    {
        $this->cg_activate = $cg_activate;
    }

    /**
     * @return string
     */
    public function getCgComment(): string
    {
        return $this->cg_comment;
    }

    /**
     * @param string $cg_comment
     */
    public function setCgComment(string $cg_comment): void
    {
        $this->cg_comment = $cg_comment;
    }

    /**
     * @return string
     */
    public function getCgType(): string
    {
        return $this->cg_type;
    }

    /**
     * @param string $cg_type
     */
    public function setCgType(string $cg_type): void
    {
        $this->cg_type = $cg_type;
    }

    /**
     * @return string
     */
    public function getCgLdapDn(): string
    {
        return $this->cg_ldap_dn;
    }

    /**
     * @param string $cg_ldap_dn
     */
    public function setCgLdapDn(string $cg_ldap_dn): void
    {
        $this->cg_ldap_dn = $cg_ldap_dn;
    }

    /**
     * @return int
     */
    public function getArId(): int
    {
        return $this->ar_id;
    }

    /**
     * @param int $ar_id
     */
    public function setArId(int $ar_id): void
    {
        $this->ar_id = $ar_id;
    }
}
