<?php

namespace Centreon\Domain\Entity;

class ContactGroup
{

    const TABLE = 'contactgroup';

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