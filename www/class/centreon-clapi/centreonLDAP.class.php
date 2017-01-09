<?php
/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : command@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once "centreonContact.class.php";

/**
 * Class for managing ldap servers
 *
 * @author shotamchay
 */
class CentreonLDAP extends CentreonObject
{
    protected $db;
    const NB_ADD_PARAM = 2;
    const AR_NOT_EXIST = "LDAP configuration ID not found";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->baseParams = array('alias'               => '',
                                  'bind_dn'             => '',
                                  'bind_pass'           => '',
                                  'group_base_search'   => '',
                                  'group_filter'        => '',
                                  'group_member'        => '',
                                  'group_name'          => '',
                                  'ldap_auto_import'    => '',
                                  'ldap_contact_tmpl'   => '',
                                  'ldap_dns_use_domain' => '',
                                  'ldap_search_limit'   => '',
                                  'ldap_search_timeout' => '',
                                  'ldap_srv_dns'        => '',
                                  'ldap_store_password' => '',
                                  'ldap_template'       => '',
                                  'protocol_version'    => '',
                                  'user_base_search'    => '',
                                  'user_email'          => '',
                                  'user_filter'         => '',
                                  'user_firstname'      => '',
                  'user_lastname'       => '',
                                  'user_name'           => '',
                                  'user_pager'          => '',
                      'user_group'          => '');
        $this->serverParams = array('host_address', 'host_port', 'host_order', 'use_ssl', 'use_tls');
    }

    /**
     * Checks if configuration name is unique
     *
     * @param string $name
     * @param int $arId
     * @return boolean
     */
    protected function isUnique($name = "", $arId = 0)
    {
        $stmt = $this->db->query(
            "SELECT ar_name FROM auth_ressource WHERE ar_name = ? AND ar_id != ?",
            array($name, $arId)
        );
        $res = $stmt->fetchAll();
        if (count($res)) {
            return false;
        }
        return true;
    }

    /**
     * Get Ldap Configuration Id
     *
     * @param string $name
     * @return mixed | returns null if no ldap id is found
     * @throws CentreonClapiException
     */
    public function getLdapId($name)
    {
        $res = $this->db->query("SELECT ar_id FROM auth_ressource WHERE ar_name = ?", array($name));
        $row = $res->fetch();
        if (!isset($row['ar_id'])) {
            return null;
        }
        $ldapId = $row['ar_id'];
        unset($res);
        return $ldapId;
    }

    /**
     * Show list of ldap configurations
     *
     * @return void
     */
    public function show()
    {
        $sql = "SELECT ar_id, ar_name, ar_description, ar_enable
        	FROM auth_ressource
        	ORDER BY ar_name";
        $res = $this->db->query($sql);
        $row = $res->fetchAll();
        echo "id;name;description;status\n";
        foreach ($row as $ldap) {
            echo $ldap['ar_id'] . $this->delim
                . $ldap['ar_name'] . $this->delim
                . $ldap['ar_description'] . $this->delim
                . $ldap['ar_enable'] . "\n";
        }
    }

    /**
     * Show server
     *
     * @param string $arName
     * @return void
     */
    public function showserver($arName = null)
    {
        if (is_null($arName) || !$arName) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $arId = $this->getLdapId($arName);
        if (is_null($arId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.' '.$arName);
        }
        $sql = "SELECT ldap_host_id, host_address, host_port, use_ssl, use_tls, host_order
                FROM auth_ressource_host
                WHERE auth_ressource_id = ".$arId."
                ORDER BY host_order";
        $res = $this->db->query($sql);
        $row = $res->fetchAll();
        echo "id;address;port;ssl;tls;order\n";
        foreach ($row as $srv) {
            echo $srv['ldap_host_id'].$this->delim.
                 $srv['host_address'].$this->delim.
                 $srv['host_port'].$this->delim.
                 $srv['use_ssl'].$this->delim.
                 $srv['use_tls'].$this->delim.
                 $srv['host_order']."\n";
        }
    }

    /**
     * Add a new ldap configuration
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function add($parameters)
    {
        if (!isset($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_ADD_PARAM) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        list($name, $description) = $params;
        if (!$this->isUnique($name)) {
            throw new CentreonClapiException(self::NAMEALREADYINUSE.' ('.$name.')');
        }
        $this->db->query(
            "INSERT INTO auth_ressource (ar_name, ar_description, ar_enable) VALUES (:name, :description, :status)",
            array(
                ':name' => $name,
                ':description' => $description,
                ':status' => '1'
            )
        );
    }

    /**
     * Add server to ldap configuration
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function addserver($parameters)
    {
        if (!isset($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $params = explode($this->delim, $parameters);
        if (count($params) < 5) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        list($arName, $address, $port, $ssl, $tls) = $params;
        $arId = $this->getLdapId($arName);
        if (is_null($arId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ' ' . $arName);
        }
        $this->db->query(
            "INSERT INTO auth_ressource_host (auth_ressource_id, host_address, host_port, use_ssl, use_tls)
             VALUES (:arId, :address, :port, :ssl, :tls)",
            array(
                ':arId'    => $arId,
                ':address' => $address,
                ':port'    => $port,
                ':ssl'     => $ssl,
                ':tls'     => $tls
            )
        );
    }

    /**
     * Delete configuration
     *
     * @param int $parameters
     * @throws CentreonClapiException
     */
    public function del($arName = null)
    {
        if (!isset($arName)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $arId = $this->getLdapId($arName);
        if (is_null($arId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.' '.$arName);
        }
        $this->db->query("DELETE FROM auth_ressource WHERE ar_id = ?", array($arId));
    }

    /**
     * Delete server
     *
     * @param int $serverId
     * @return void
     */
    public function delserver($serverId)
    {
        if (!isset($serverId)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $this->db->query("DELETE FROM auth_ressource_host WHERE ldap_host_id = ?", array($serverId));
    }

    /**
     * Set parameters
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function setparam($parameters)
    {
        if (!isset($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $arId = $this->getLdapId($params[0]);
        if (is_null($arId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[0]);
        }
        if (in_array(strtolower($params[1]), array('name', 'description', 'enable'))) {
            if (strtolower($params[1]) == 'name') {
                if (!$this->isUnique($params[2], $arId)) {
                    throw new CentreonClapiException(self::NAMEALREADYINUSE.' ('.$name.')');
                }
            }
            $this->db->query(
                "UPDATE auth_ressource SET ar_" . $params[1] . " = ? WHERE ar_id = ?",
                array($params[2], $arId)
            );
        } elseif (isset($this->baseParams[strtolower($params[1])])) {
            if (strtolower($params[1]) == 'ldap_contact_tmpl') {
                $contactObj = new CentreonContact($this->db);
                $params[2] = $contactObj->getContactID($params[2]);
            }
            $this->db->query(
                "DELETE FROM auth_ressource_info WHERE ari_name = ? AND ar_id = ?",
                array($params[1], $arId)
            );
            $this->db->query(
                "INSERT INTO auth_ressource_info (ari_value, ari_name, ar_id)
                 VALUES (?, ?, ?)",
                array($params[2], $params[1], $arId)
            );
        } else {
            throw new CentreonClapiException(self::UNKNOWNPARAMETER);
        }
    }

    /**
     * Set server param
     *
     * @param string $parameters
     * @return void
     */
    public function setparamserver($parameters = null)
    {
        if (is_null($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $params = explode($this->delim, $parameters);
        if (count($params) < 3) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        list($serverId, $key, $value) = $params;
        if (!in_array(strtolower($key), $this->serverParams)) {
            throw new CentreonClapiException(self::UNKNOWNPARAMETER);
        }
        $this->db->query(
            "UPDATE auth_ressource_host SET " . strtolower($key)
            . " = ? WHERE ldap_host_id = ?",
            array($value, $serverId)
        );
    }
}
