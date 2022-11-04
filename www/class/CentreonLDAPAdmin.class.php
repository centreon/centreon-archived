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
            'ldap_connection_timeout',
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
    protected function updateLdapServers($arId): void
    {
        $statement = $this->db->prepare(
            "DELETE FROM auth_ressource_host WHERE auth_ressource_id = :id"
        );
        $statement->bindValue(':id', $arId, PDO::PARAM_INT);
        $statement->execute();

        if (isset($_REQUEST['address'])) {
            $subRequest = '';
            $bindValues = [];
            $bindIndex = 0;
            foreach ($_REQUEST['address'] as $index => $address) {
                $bindValues[':address_' . $bindIndex] = [PDO::PARAM_STR => $address];
                $bindValues[':port_' . $bindIndex] = [PDO::PARAM_INT => $_REQUEST['port'][$index]];
                $bindValues[':tls_' . $bindIndex] = [PDO::PARAM_STR => isset($_REQUEST['tls'][$index]) ? '1' : '0'];
                $bindValues[':ssl_' . $bindIndex] = [PDO::PARAM_STR => isset($_REQUEST['ssl'][$index]) ? '1' : '0'];
                $bindValues[':order_' . $bindIndex] = [PDO::PARAM_INT => $bindIndex + 1];
                if (! empty($subRequest)) {
                    $subRequest .= ', ';
                }
                $subRequest .=
                    '(:id, :address_' . $bindIndex . ', :port_' . $bindIndex . ', :ssl_' . $bindIndex
                    . ', :tls_' . $bindIndex     . ', :order_' . $bindIndex . ')';
                $bindIndex++;
            }

            if (! empty($subRequest)) {
                $bindValues[':id'] = [PDO::PARAM_INT => (int) $arId];
                $statement = $this->db->prepare(
                    "INSERT INTO auth_ressource_host
                    (auth_ressource_id, host_address, host_port, use_ssl, use_tls, host_order)
                    VALUES " . $subRequest
                );
                foreach ($bindValues as $bindKey => $bindValue) {
                    $bindType = key($bindValue);
                    $statement->bindValue($bindKey, $bindValue[$bindType], $bindType);
                }
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
     * @param array<string, mixed> $options The list of options
     * @param int $arId
     * @return int resource auth id
     */
    public function setGeneralOptions(array $options, $arId = 0): int
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
                VALUES (:name, :description, 'ldap', :is_enabled, :sync_date)"
            );
            $statement->bindValue(':name', $options['ar_name'], PDO::PARAM_STR);
            $statement->bindValue(':description', $options['ar_description'], PDO::PARAM_STR);
            $statement->bindValue(':is_enabled', $options['ldap_auth_enable']['ldap_auth_enable'], PDO::PARAM_STR);
            $statement->bindValue(':sync_date', $options['ar_sync_base_date'], PDO::PARAM_INT);
            $statement->execute();

            $statement = $this->db->prepare(
                "SELECT MAX(ar_id) as last_id
                FROM auth_ressource
                WHERE ar_name = :name"
            );
            $statement->bindValue(':name', $options['ar_name'], PDO::PARAM_STR);
            $statement->execute();
            $row = $statement->fetch();
            $arId = $row['last_id'];
            unset($statement);
        } else {
            $statement = $this->db->prepare(
                "UPDATE auth_ressource
                    SET ar_name = :name,
                        ar_description = :description,
                        ar_enable = :is_enabled,
                        ar_sync_base_date = :sync_date
                WHERE ar_id = :id"
            );
            $statement->bindValue(':name', $options['ar_name'], PDO::PARAM_STR);
            $statement->bindValue(':description', $options['ar_description'], PDO::PARAM_STR);
            $statement->bindValue(':is_enabled', $options['ldap_auth_enable']['ldap_auth_enable'], PDO::PARAM_STR);
            $statement->bindValue(':sync_date', $options['ar_sync_base_date'], PDO::PARAM_INT);
            $statement->bindValue(':id', $arId, PDO::PARAM_INT);
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
                $statement = $this->db->prepare(
                    "UPDATE `auth_ressource_info`
                        SET `ari_value` = :value
                    WHERE `ari_name` = :name
                        AND `ar_id` = :id"
                );
            } else {
                $statement = $this->db->prepare(
                    "INSERT INTO `auth_ressource_info`
                    (`ar_id`, `ari_name`, `ari_value`)
                    VALUES (:id, :name, :value)"
                );
            }
            $statement->bindValue(':value', $value, PDO::PARAM_STR);
            $statement->bindValue(':name', $key, PDO::PARAM_STR);
            $statement->bindValue(':id', $arId, PDO::PARAM_INT);
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
        $statement = $this->db->prepare(
            "SELECT `ari_name`, `ari_value` FROM `auth_ressource_info`
            WHERE `ari_name` <> 'bind_pass'
            AND ar_id = :id"
        );
        $statement->bindValue(':id', $arId, PDO::PARAM_INT);
        $statement->execute();
        while ($row = $statement->fetch()) {
            $gopt[$row['ari_name']] = $row['ari_value'];
        }
        $gopt['bind_pass'] = CentreonAuth::PWS_OCCULTATION;
        return $gopt;
    }

    /**
     * Add a Ldap server
     * (Possibility of a dead code)
     *
     * @param int $arId
     * @param array<string, mixed> $params
     * @return void
     */
    public function addServer($arId, $params = []): void
    {
        $statement = $this->db->prepare(
            "INSERT INTO auth_ressource_host
            (auth_ressource_id, host_address, host_port, use_ssl, use_tls, host_order)
            VALUES (:id, :address, :port, :ssl, :tls, :order)"
        );
        $statement->bindValue(':id', $arId, PDO::PARAM_INT);
        $statement->bindValue(':address', $params['hostname'], PDO::PARAM_STR);
        $statement->bindValue(':port', $params['port'], PDO::PARAM_INT);
        $statement->bindValue(':ssl', isset($params['use_ssl']) ? 1 : 0, PDO::PARAM_INT);
        $statement->bindValue(':tls', isset($params['use_tls']) ? 1 : 0, PDO::PARAM_INT);
        $statement->bindValue(':order', $params['order'], PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Modify a Ldap server
     *
     * @param int $arId
     * @param array<string, mixed> $params
     * @return void
     */
    public function modifyServer($arId, $params = array()): void
    {
        if (!isset($params['order']) || !isset($params['id'])) {
            return;
        }
        $use_ssl = isset($params['use_ssl']) ? 1 : 0;
        $use_tls = isset($params['use_tls']) ? 1 : 0;

        $statement = $this->db->prepare(
            "UPDATE auth_ressource_host
            SET host_address = :address,
                host_port = :port,
                host_order = :order,
                use_ssl = :ssl,
                use_tls = :tls
            WHERE ldap_host_id = :id AND auth_ressource_id = :resource_id"
        );
        $statement->bindValue(':address', $params['hostname'], PDO::PARAM_STR);
        $statement->bindValue(':port', $params['port'], PDO::PARAM_INT);
        $statement->bindValue(':order', $params['order'], PDO::PARAM_INT);
        $statement->bindValue(':ssl', isset($params['use_ssl']) ? 1 : 0, PDO::PARAM_INT);
        $statement->bindValue(':tls', isset($params['use_tls']) ? 1 : 0, PDO::PARAM_INT);
        $statement->bindValue(':id', $params['id'], PDO::PARAM_INT);
        $statement->bindValue(':resource_id', $arId, PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Add a template
     * (Possibility of a dead code)
     *
     * @param array $options A hash table with options for connections and search in ldap
     * @return int|bool The id of connection, false on error
     */
    public function addTemplate(array $options = [])
    {
        try {
            $this->db->query(
                "INSERT INTO auth_ressource (ar_type, ar_enable) VALUES ('ldap_tmpl', '0')"
            );
        } catch (\PDOException $e) {
            return false;
        }
        try {
            $dbResult = $this->db->query("SELECT MAX(ar_id) as id FROM auth_ressource WHERE ar_type = 'ldap_tmpl'");
            $row = $dbResult->fetch();
        } catch (\PDOException $e) {
            return false;
        }
        $id = $row['id'];
        foreach ($options as $name => $value) {
            try {
                $statement = $this->db->prepare(
                    "INSERT INTO auth_ressource_info
                    (ar_id, ari_name, ari_value)
                    VALUES (:id, :name, :value)"
                );
                $statement->bindValue(':id', $id, PDO::PARAM_INT);
                $statement->bindValue(':name', $name, PDO::PARAM_STR);
                $statement->bindValue(':value', $value, PDO::PARAM_STR);
                $statement->execute();
            } catch (\PDOException $e) {
                return false;
            }
        }
        return $id;
    }

    /**
     * Modify a template
     * (Possibility of a dead code)
     *
     * @param int The id of the template
     * @param array $options A hash table with options for connections and search in ldap
     * @return bool
     */
    public function modifyTemplate($id, array $options = []): bool
    {
        /*
         * Load configuration
         */
        $config = $this->getTemplate($id);

        foreach ($options as $key => $value) {
            try {
                if (isset($config[$key])) {
                    $statement = $this->db->prepare(
                        "UPDATE auth_ressource_info 
                        SET ari_value = :value
                        WHERE ar_id = :id AND ari_name = :name"
                    );
                } else {
                    $statement = $this->db->prepare(
                        "INSERT INTO auth_ressource_info
                        (ar_id, ari_name, ari_value)
                        VALUES (:id, :name, :value)"
                    );
                }
                $statement->bindValue(':value', $value, PDO::PARAM_STR);
                $statement->bindValue(':name', $key, PDO::PARAM_STR);
                $statement->bindValue(':id', $id, PDO::PARAM_INT);
                $statement->execute();
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
     * @return array<string, string>
     */
    public function getTemplate($id = 0): array
    {
        if ($id == 0) {
            $res = $this->db->query(
                "SELECT ar_id 
                 FROM auth_ressource 
                 WHERE ar_type = 'ldap_tmpl'"
            );
            if ($res->rowCount() == 0) {
                return array();
            }
            $row = $res->fetch();
            $id = $row['ar_id'];
        }
        $statement = $this->db->prepare(
            "SELECT ari_name, ari_value
             FROM auth_ressource_info
             WHERE ar_id = :id"
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $list = [];
        while ($row = $statement->fetch()) {
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
     * @return mixed[]
     */
    public function getLdapConfigurationList($search = "", $offset = null, $limit = null): array
    {
        $request = "SELECT ar_id, ar_enable, ar_name, ar_description, ar_sync_base_date FROM auth_ressource ";

        $bindValues = [];
        if ($search !== "") {
            $request .= "WHERE ar_name LIKE :search ";
            $bindValues[':search'] = [PDO::PARAM_STR => '%' . $search . '%'];
        }
        $request .= "ORDER BY ar_name ";
        if (!is_null($offset) && !is_null($limit)) {
            $request .= "LIMIT :offset,:limit";
            $bindValues[':offset'] = [PDO::PARAM_INT => $offset];
            $bindValues[':limit'] = [PDO::PARAM_INT => $limit];
        }
        $statement = $this->db->prepare($request);
        foreach ($bindValues as $bindKey => $bindValue) {
            $bindType = key($bindValue);
            $statement->bindValue($bindKey, $bindValue[$bindType], $bindType);
        }
        $statement->execute();
        $configuration = [];
        while ($row = $statement->fetch()) {
            $configuration[] = $row;
        }
        return $configuration;
    }

    /**
     * Delete ldap configuration
     *
     * @param mixed[] $configList
     * @return void
     */
    public function deleteConfiguration(array $configList = []): void
    {
        if (count($configList)) {
            $configIds = [];
            foreach ($configList as $configId) {
                if (is_numeric($configId)) {
                    $configIds[] = (int) $configId;
                }
            }
            if (count($configIds)) {
                $this->db->query(
                    'DELETE FROM auth_ressource WHERE ar_id IN (' . implode(',', $configIds) . ')'
                );
            }
        }
    }

    /**
     * Enable/Disable ldap configuration
     *
     * @param int $status
     * @param mixed[] $configList
     * @return void
     */
    public function setStatus($status, $configList = array()): void
    {
        if (count($configList)) {
            $configIds = [];
            foreach ($configList as $configId) {
                if (is_numeric($configId)) {
                    $configIds[] = (int) $configId;
                }
            }
            if (count($configIds)) {
                $statement = $this->db->prepare(
                    'UPDATE auth_ressource 
                    SET ar_enable = :is_enabled
                    WHERE ar_id IN (' . implode(',', $configIds) . ')'
                );
                $statement->bindValue(':is_enabled', $status, PDO::PARAM_STR);
                $statement->execute();
            }
        }
    }

    /**
     * Get list of servers from resource id
     *
     * @param int $arId Auth resource id
     * @return array<int, array<string, mixed>>
     */
    public function getServersFromResId($arId): array
    {
        $statement = $this->db->prepare(
            "SELECT host_address, host_port, use_ssl, use_tls
            FROM auth_ressource_host
            WHERE auth_ressource_id = :id
            ORDER BY host_order"
        );
        $statement->bindValue(':id', $arId, PDO::PARAM_INT);
        $statement->execute();
        $arr = [];
        $i = 0;
        while ($row = $statement->fetch()) {
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
     * @param int $arId Auth resource id
     * @return void
     */
    private function manageContactPasswords($arId): void
    {
        $statement = $this->db->prepare(
            'SELECT ari_value 
            FROM auth_ressource_info 
            WHERE ar_id = :id
            AND ari_name = "ldap_store_password"'
        );
        $statement->bindValue(':id', $arId, PDO::PARAM_INT);
        $statement->execute();
        if ($row = $statement->fetch()) {
            if ($row['ari_value'] === '0') {
                $statement2 = $this->db->prepare("SELECT contact_id FROM contact WHERE ar_id = :arId");
                $statement2->bindValue(':arId', $arId, \PDO::PARAM_INT);
                $statement2->execute();
                $ldapContactIdList = [];
                while ($row2 = $statement2->fetch()) {
                    $ldapContactIdList[] = (int) $row2['contact_id'];
                }
                if (!empty($ldapContactIdList)) {
                    $contactIds = implode(', ', $ldapContactIdList);
                    $this->db->query(
                        "DELETE FROM contact_password WHERE contact_id IN ($contactIds)"
                    );
                }
            }
        }
    }
}
