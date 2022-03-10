<?php

/*
 * Copyright 2005-2021 Centreon
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
 */

require_once 'centreonUtils.class.php';

/**
 * Ldap Administration class
 */
class CentreonLdapAdmin
{
    /**
     * @object centreonLog
     */
    public $centreonLog;

    private $db;

    /**
     * Constructor
     *
     * @param CentreonDB $pearDB The database connection
     */
    public function __construct($pearDB)
    {
        $this->db = $pearDB;
        $this->centreonLog = new CentreonLog();
    }

    /**
     * Get ldap parameters
     *
     * @return array
     * @todo sanitize the inputs to avoid XSS
     */
    public function getLdapParameters()
    {
        $tab = array(
            'ldap_store_password',
            'ldap_auto_import',
            'ldap_search_limit',
            'ldap_search_timeout',
            'ldap_contact_tmpl',
            'ldap_default_cg',
            'ldap_srv_dns',
            'ldap_dns_use_ssl',
            'ldap_dns_use_tls',
            'ldap_dns_use_domain',
            'bind_dn',
            'bind_pass',
            'protocol_version',
            'ldap_template',
            'user_base_search',
            'group_base_search',
            'user_filter',
            'alias',
            'user_group',
            'user_name',
            'user_firstname',
            'user_lastname',
            'user_email',
            'user_pager',
            'group_filter',
            'group_name',
            'group_member',
            'ldap_auto_sync', // is auto synchronization enabled
            'ldap_sync_interval' // unsigned integer interval between two LDAP synchronization
        );
        return $tab;
    }

    /**
     * Update Ldap servers
     *
     * @param int $arId | auth resource id
     */
    protected function updateLdapServers($arId)
    {
        $centreonUtils = new CentreonUtils();
        $statement = $this->db->prepare("DELETE FROM auth_ressource_host WHERE auth_ressource_id = :auth_ressource_id");
        $statement->bindValue(':auth_ressource_id', $arId, \PDO::PARAM_INT);
        $statement->execute();
        if (isset($_REQUEST['address']) && is_array($_REQUEST['address'])) {
            $addressList = $centreonUtils->sanitizeInputArrayNew($_REQUEST['address']);
            $portList = isset($_REQUEST['port'])
                ? $centreonUtils->sanitizeInputArrayNew($_REQUEST['port'])
                : null;
            $sslList = isset($_REQUEST['ssl'])
                ? $centreonUtils->sanitizeInputArrayNew($_REQUEST['ssl'])
                : null;
            $tlsList = isset($_REQUEST['tls'])
            ? $centreonUtils->sanitizeInputArrayNew($_REQUEST['tls'])
            : null;
            $insertStr = "";
            $i = 1;
            foreach ($addressList as $key => $addr) {
                if (is_null($addr) || $addr == "") {
                    continue;
                }
                if ($insertStr) {
                    $insertStr .= ", ";
                }
                $insertStr .= "($arId, '" . $this->db->escape($addr) . "', '" .
                    $this->db->escape($portList[$key]) . "', " .
                    $this->db->escape(isset($sslList[$key]) ? 1 : 0) . ", " .
                    $this->db->escape(isset($tlsList[$key]) ? 1 : 0) . ", $i)";
                $i++;
            }
            if ($insertStr) {
                $statement = $this->db->prepare(
                    "INSERT INTO auth_ressource_host
                    (auth_ressource_id, host_address, host_port, use_ssl, use_tls, host_order)
                    VALUES :insertStr"
                );
                $statement->bindValue(':insertStr', $insertStr, \PDO::PARAM_STR);
                $statement->execute();
            }
        }
    }

    /**
     * Set ldap options
     *
     * 'ldap_auth_enable', 'ldap_auto_import', 'ldap_srv_dns', 'ldap_search_limit', 'ldap_search_timeout'
     * and 'ldap_dns_use_ssl', 'ldap_dns_use_tls', 'ldap_dns_use_domain' if ldap_srv_dns = 1
     *
     * @param int $arId
     * @param array $options The list of options
     * @return int resource auth id
     */
    public function setGeneralOptions(array $options, $arId = 0)
    {
        $isUpdate = ((int)$arId !== 0);

        $gopt = $this->getGeneralOptions($arId);
        if (isset($gopt["bind_pass"]) && $gopt["bind_pass"] === CentreonAuth::PWS_OCCULTATION && $isUpdate === false) {
            unset($gopt["bind_pass"]);
        }
        if (
            !count($gopt)
            && isset($options['ar_name'])
            && isset($options['ar_description'])
        ) {
            if (!isset($options['ar_sync_base_date'])) {
                $options['ar_sync_base_date'] = time();
                $this->centreonLog->insertLog(
                    3,
                    "LDAP PARAM - Warning the reference date wasn\'t set for LDAP : " . $options['ar_name']
                );
            }
            $statement = $this->db->prepare(
                "INSERT INTO auth_ressource (ar_name, ar_description, ar_type, ar_enable, ar_sync_base_date)
                VALUES (:ar_name, :ar_description,'ldap', :ar_enable, :ar_sync_base_date)"
            );
            $statement->bindValue(':ar_name', $options['ar_name'], \PDO::PARAM_STR);
            $statement->bindValue(':ar_description', $options['ar_description'], \PDO::PARAM_STR);
            $statement->bindValue(':ar_enable', $options['ldap_auth_enable']['ldap_auth_enable'], \PDO::PARAM_INT);
            $statement->bindValue(':ar_sync_base_date', $options['ar_sync_base_date'], \PDO::PARAM_INT);
            $statement->execute();
            $maxArIdSql = "SELECT MAX(ar_id) as last_id FROM auth_ressource WHERE ar_name = :ar_name";
            $res = $this->db->prepare($maxArIdSql);
            $res->bindValue(':ar_name', $options['ar_name'], \PDO::PARAM_STR);
            $res->execute();
            $row = $res->fetch();
            $arId = $row['last_id'];
            unset($res);
        } else {
            $statement = $this->db->prepare(
                "UPDATE auth_ressource
                SET ar_name = :ar_name,
                ar_description = :ar_description,
                ar_enable = :ar_enable,
                ar_sync_base_date = :ar_sync_base_date
                WHERE ar_id = :ar_id"
            );
            $statement->bindValue(':ar_name', $options['ar_name'], \PDO::PARAM_STR);
            $statement->bindValue(':ar_description', $options['ar_description'], \PDO::PARAM_STR);
            $statement->bindValue(':ar_enable', $options['ldap_auth_enable']['ldap_auth_enable'], \PDO::PARAM_INT);
            $statement->bindValue(':ar_sync_base_date', $options['ar_sync_base_date'], \PDO::PARAM_INT);
            $statement->bindValue(':ar_id', $arId, \PDO::PARAM_INT);
            $statement->execute();
        }
        $knownParameters = $this->getLdapParameters();
        if (
            isset($options["bind_pass"])
            && $options["bind_pass"] === CentreonAuth::PWS_OCCULTATION
            && $isUpdate === true
        ) {
            unset($options["bind_pass"]);
        }

        foreach ($options as $key => $value) {
            if (!in_array($key, $knownParameters)) {
                continue;
            }
            if (is_array($value)) { //radio buttons
                $value = $value[$key];
            }
            // Make all attributes lowercase since ldap_get_entries
            // converts them to lowercase.
            if (
                in_array(
                    $key,
                    array(
                        "alias",
                        "user_name",
                        "user_email",
                        "user_pager",
                        "user_firstname",
                        "user_lastname",
                        "group_name",
                        "group_member"
                    )
                )
            ) {
                $value = strtolower($value);
            }
            if (isset($gopt[$key])) {
                $query = "UPDATE `auth_ressource_info`
                    SET `ari_value` = :ari_value
                    WHERE `ari_name` = :ari_name
                    AND `ar_id` = :ar_id";
            } else {
                $query = "INSERT INTO `auth_ressource_info`
                    (`ar_id`, `ari_name`, `ari_value`)
                    VALUES (:ar_id, :ari_name, :ari_value)";
            }
            $statement = $this->db->prepare($query);
            $statement->bindvalue(':ar_id', $arId, \PDO::PARAM_INT);
            $statement->bindvalue(':ari_name', $this->db->escape($key), \PDO::PARAM_STR);
            $statement->bindvalue(':ari_value', $this->db->escape($value, false), \PDO::PARAM_STR);
            $statement->execute();
        }
        $this->updateLdapServers($arId);

        /* Remove contact passwords if store password option is disabled */
        $this->manageContactPasswords($arId);

        return $arId;
    }

    /**
     * Get the general options
     *
     * @param int $arId
     * @return array
     */
    public function getGeneralOptions($arId)
    {
        $gopt = [];
        $query = "SELECT `ari_name`, `ari_value` FROM `auth_ressource_info`
            WHERE `ari_name` <> 'bind_pass'
            AND ar_id = :ar_id";
        $res = $this->db->prepare($query);
        $res->bindValue(':ar_id', $arId, \PDO::PARAM_INT);
        $res->execute();
        while ($row = $res->fetch()) {
            $gopt[$row['ari_name']] = $row['ari_value'];
        }
        $gopt['bind_pass'] = CentreonAuth::PWS_OCCULTATION;
        return $gopt;
    }

    /**
     * Add a Ldap server
     *
     * @param int $arId
     * @param array $params
     * @return void
     */
    public function addServer($arId, $params = array())
    {
        $use_ssl = isset($params['use_ssl']) ? 1 : 0;
        $use_tls = isset($params['use_tls']) ? 1 : 0;
        $sql = "INSERT INTO auth_ressource_host " .
            "(auth_ressource_id, host_address, host_port, use_ssl, use_tls, host_order) " .
            "VALUES (:ar_id, :hostname, :port, :use_ssl, :use_tls, :host_order)";
        $statement = $this->db->prepare($sql);
        $statement->bindvalue(':ar_id', $arId, \PDO::PARAM_INT);
        $statement->bindvalue(':hostname', $params['hostname'], \PDO::PARAM_STR);
        $statement->bindvalue(':port', $params['port'], \PDO::PARAM_INT);
        $statement->bindvalue(':use_ssl', $use_ssl, \PDO::PARAM_INT);
        $statement->bindvalue(':use_tls', $use_tls, \PDO::PARAM_INT);
        $statement->bindvalue(':host_order', $params['order'], \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Modify a Ldap server
     *
     * @param int $arId
     * @param array $params
     * @return void
     */
    public function modifyServer($arId, $params = array())
    {
        if (!isset($params['order']) || !isset($params['id'])) {
            return false;
        }
        $use_ssl = isset($params['use_ssl']) ? 1 : 0;
        $use_tls = isset($params['use_tls']) ? 1 : 0;
        $sql = "UPDATE auth_ressource_host
            SET host_address = :hostname,
            host_port = :host_port,
            host_order = :host_order,
            use_ssl = :use_ssl,
            use_tls = :use_tls
            WHERE ldap_host_id = :ldap_host_id
            AND auth_ressource_id = :ar_id";
        $statement = $this->db->prepare($sql);
        $statement->bindvalue(':hostname', $params['hostname'], \PDO::PARAM_STR);
        $statement->bindvalue(':host_port', $params['port'], \PDO::PARAM_INT);
        $statement->bindvalue(':host_order', $params['order'], \PDO::PARAM_INT);
        $statement->bindvalue(':use_ssl', $use_ssl, \PDO::PARAM_INT);
        $statement->bindvalue(':use_tls', $use_tls, \PDO::PARAM_INT);
        $statement->bindvalue(':ldap_host_id', $params['id'], \PDO::PARAM_INT);
        $statement->bindvalue(':ar_id', $arId, \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Add a template
     *
     * @param array $options A hash table with options for connections and search in ldap
     * @return int|bool The id of connection, false on error
     */
    public function addTemplate($options = [])
    {
        try {
            $statement = $this->db->prepare(
                "INSERT INTO auth_ressource (ar_type, ar_enable) VALUES ('ldap_tmpl', '0')"
            );
            $statement->execute();
        } catch (\PDOException $e) {
            return false;
        }
        try {
            $dbResult = $this->db->prepare("SELECT MAX(ar_id) as id FROM auth_ressource WHERE ar_type = 'ldap_tmpl'");
            $dbResult->execute();
            $row = $dbResult->fetch();
        } catch (\PDOException $e) {
            return false;
        }
        $id = $row['id'];
        foreach ($options as $key => $value) {
            try {
                $statement = $this->db->prepare(
                    "INSERT INTO auth_ressource_info
                    (ar_id, ari_name, ari_value) VALUES (:ar_id, :ari_name, :ari_value)"
                );
                $statement->bindvalue(':ar_id', $id, \PDO::PARAM_INT);
                $statement->bindvalue(':ari_name', $key, \PDO::PARAM_INT);
                $statement->bindvalue(':ari_value', $value, \PDO::PARAM_STR);
                $statement->execute();
            } catch (\PDOException $e) {
                return false;
            }
        }
        return $id;
    }

    /**
     * Modify a template
     *
     * @param int The id of the template
     * @param array $options A hash table with options for connections and search in ldap
     * @return bool
     */
    public function modifyTemplate($id, $options = array())
    {
        /*
         * Load configuration
         */
        $config = $this->getTemplate($id);

        foreach ($options as $key => $value) {
            try {
                if (isset($config[$key])) {
                    $statement = $this->db->prepare(
                        "UPDATE auth_ressource_info SET ari_value = :ari_value
                        WHERE ar_id = :ar_id AND ari_name = :ari_name"
                    );
                    $statement->bindvalue(':ar_id', $id, \PDO::PARAM_INT);
                    $statement->bindvalue(':ari_name', $key, \PDO::PARAM_STR);
                    $statement->bindvalue(':ari_value', $value, \PDO::PARAM_STR);
                    $statement->execute();
                } else {
                    $statement = $this->db->prepare(
                        "INSERT INTO auth_ressource_info
                        (ar_id, ari_name, ari_value)
                        VALUES (:ar_id, :ari_name, :ari_value)"
                    );
                    $statement->bindvalue(':ar_id', $id, \PDO::PARAM_INT);
                    $statement->bindvalue(':ari_name', $key, \PDO::PARAM_STR);
                    $statement->bindvalue(':ari_value', $value, \PDO::PARAM_STR);
                    $statement->execute();
                }
            } catch (\PDOException $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the template information
     *
     * @param int $id The template id, if 0 get the template
     * @return array $list
     */
    public function getTemplate($id = 0)
    {
        if ($id === 0) {
            $res = $this->db->prepare(
                "SELECT ar_id
                 FROM auth_ressource
                 WHERE ar_type = 'ldap_tmpl'"
            );
            $res->execute();
            if ($res->rowCount() === 0) {
                return [];
            }
            $row = $res->fetch();
            $id = $row['ar_id'];
        }
        $query = "SELECT ari_name, ari_value
                 FROM auth_ressource_info
                 WHERE ar_id = :ar_id";
        $res = $this->db->prepare($query);
        $res->bindValue(':ar_id', $id, \PDO::PARAM_INT);
        $res->execute();
        $list = [];
        while ($row = $res->fetch()) {
            $list[$row['ari_name']] = $row['ari_value'];
        }
        return $list;
    }

    /**
     * Get the default template for Active Directory
     *
     * @return array
     */
    public function getTemplateAd()
    {
        $infos = array();
        $infos['user_filter'] = "(&(samAccountName=%s)(objectClass=user)(samAccountType=805306368))";
        $attr = array();
        $attr['alias'] = 'samaccountname';
        $attr['email'] = 'mail';
        $attr['name'] = 'name';
        $attr['pager'] = 'mobile';
        $attr['group'] = 'memberOf';
        $attr['firstname'] = 'givenname';
        $attr['lastname'] = 'sn';
        $infos['user_attr'] = $attr;
        $infos['group_filter'] = "(&(samAccountName=%s)(objectClass=group)(samAccountType=268435456))";
        $attr = array();
        $attr['group_name'] = 'samaccountname';
        $attr['member'] = 'member';
        $infos['group_attr'] = $attr;
        return $infos;
    }

    /**
     * Get the default template for ldap
     *
     * @return array
     */
    public function getTemplateLdap()
    {
        $infos = array();
        $infos['user_filter'] = "(&(uid=%s)(objectClass=inetOrgPerson))";
        $attr = array();
        $attr['alias'] = 'uid';
        $attr['email'] = 'mail';
        $attr['name'] = 'cn';
        $attr['pager'] = 'mobile';
        $attr['group'] = '';
        $attr['firstname'] = 'givenname';
        $attr['lastname'] = 'sn';
        $infos['user_attr'] = $attr;
        $infos['group_filter'] = "(&(cn=%s)(objectClass=groupOfNames))";
        $attr = array();
        $attr['group_name'] = 'cn';
        $attr['member'] = 'member';
        $infos['group_attr'] = $attr;
        return $infos;
    }

    /**
     * Get LDAP configuration list
     *
     * @param string $search
     * @param string $offset
     * @param int $limit
     * @return array
     */
    public function getLdapConfigurationList($search = "", $offset = null, $limit = null)
    {
        $sql = "SELECT ar_id, ar_enable, ar_name, ar_description, ar_sync_base_date FROM auth_ressource ";
        if ($search != "") {
            $sql .= "WHERE ar_name LIKE '%:ar_name%' ";
        }
        $sql .= "ORDER BY ar_name ";
        if (!is_null($offset) && !is_null($limit)) {
            $sql .= "LIMIT :offset,:limit";
        }
        $res = $this->db->prepare($sql);

        if ($search != "") {
            $res->bindValue(':ar_name', $search, \PDO::PARAM_STR);
        }
        if (!is_null($offset) && !is_null($limit)) {
            $res->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $res->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        $res->execute();
        $tab = [];
        while ($row = $res->fetch()) {
            $tab[] = $row;
        }
        return $tab;
    }

    /**
     * Delete ldap configuration
     *
     * @param array $configList
     * @return void
     */
    public function deleteConfiguration($configList = [])
    {
        if (count($configList)) {

            foreach ($configList as $val) {
                $statement = $this->db->prepare(
                    "DELETE FROM auth_ressource WHERE ar_id IN (:configList)"
                );
                $statement->bindValue(':configList', $val, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }

    /**
     * Enable/Disable ldap configuration
     *
     * @param int $status
     * @param array $configList
     * @return void
     */
    public function setStatus($status, $configList = array())
    {
        if (count($configList)) {
            foreach ($configList as $val) {
                $statement = $this->db->prepare(
                    "UPDATE auth_ressource
                    SET ar_enable = :ar_enable
                    WHERE ar_id IN (:configList)"
                );
                $statement->bindValue(':ar_enable', $status, \PDO::PARAM_INT);
                $statement->bindValue(':configList', $val, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }

    /**
     * Get list of servers from resource id
     *
     * @param int $arId | Auth resource id
     * @return array
     */
    public function getServersFromResId($arId)
    {
        $res = $this->db->prepare(
            "SELECT host_address, host_port, use_ssl, use_tls
            FROM auth_ressource_host
            WHERE auth_ressource_id = :auth_ressource_id ORDER BY host_order"
        );
        $res->bindValue(':auth_ressource_id', $arId, \PDO::PARAM_INT);
        $res->execute();
        $arr = [];
        $i = 0;
        while ($row = $res->fetch()) {
            $arr[$i]['address_#index#'] = $row['host_address'];
            $arr[$i]['port_#index#'] = $row['host_port'];
            if ($row['use_ssl']) {
                $arr[$i]['ssl_#index#'] = $row['use_ssl'];
            }
            if ($row['use_tls']) {
                $arr[$i]['tls_#index#'] = $row['use_tls'];
            }
            $i++;
        }
        return $arr;
    }

    /**
     * Remove contact passwords if password storage is disabled
     *
     * @param int $arId | Auth resource id
     * @return void
     */
    private function manageContactPasswords($arId)
    {
        $result = $this->db->prepare(
            'SELECT ari_value ' .
            'FROM auth_ressource_info ' .
            'WHERE ar_id = :ar_id AND ari_name = "ldap_store_password" '
        );
        $result->bindValue(':ar_id', $arId, \PDO::PARAM_INT);
        $result->execute();
        if ($row = $result->fetch()) {
            if ($row['ari_value'] == '0') {
                $statement = $this->db->prepare("SELECT contact_id FROM contact WHERE ar_id = :ar_id");
                $statement->bindValue(':ar_id', $arId, \PDO::PARAM_INT);
                $statement->execute();
                $ldapContactIdList = [];
                while ($row = $statement->fetch()) {
                    $ldapContactIdList[] = $row['contact_id'];
                }
                if (!empty($ldapContactIdList)) {
                    foreach ($ldapContactIdList as $val) {
                        $statement = $this->db->prepare(
                            "DELETE FROM contact_password WHERE contact_id IN (:contactIds)"
                        );
                        $statement->bindValue(':contactIds', $val, \PDO::PARAM_INT);
                        $statement->execute();
                    }
                }
            }
        }
    }
}
