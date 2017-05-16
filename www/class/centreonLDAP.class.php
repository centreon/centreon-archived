<?php
/**
 * Copyright 2005-2015 Centreon
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
 *
 * @param type $errno The error num
 * @param type $errstr The error message
 * @param type $errfile The error file
 * @param type $errline The error line
 * @param type $errcontext
 * @return boolean
 */
function errorLdapHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    // @todo LOG
    return false;
}

/**
 * The utils class for LDAP
 */
class CentreonLDAP
{

    public $CentreonLog;
    private $ds;
    private $db = null;
    private $linkId;
    private $ldapHosts = array();
    private $ldap = null;
    private $constuctCache = array();
    private $userSearchInfo = null;
    private $groupSearchInfo = null;
    private $debugImport = false;
    private $debugPath = "";

    /**
     * Constructor
     * @param type $pearDB The database connection
     * @param type $CentreonLog The logging object
     * @param type $arId
     */
    public function __construct($pearDB, $CentreonLog = null, $arId = null)
    {
        $this->CentreonLog = $CentreonLog;
        $this->db = $pearDB;

        /* Check if use service form DNS */
        $use_dns_srv = 0;
        
        $stmt = $pearDB->prepare(
            "SELECT `ari_value`  " .
            "FROM `auth_ressource_info` " .
            "WHERE `ari_name` = 'ldap_srv_dns' " .
            "AND ar_id = ?"
        );
        
        $dbresult = $this->db->execute($stmt, array($arId));
        if (PEAR::isError($dbresult)) {
            die("An error occured");
        }
        
        $row = $dbresult->fetchRow();
        $dbresult->free();
        if (isset($row['ari_value'])) {
            $use_dns_srv = $row['ari_value'];
        }

        $dbresult = $this->db->query(
            "SELECT `key`, `value` FROM `options` WHERE `key` IN ('debug_ldap_import', 'debug_path')"
        );
        while ($row = $dbresult->fetchRow()) {
            if ($row['key'] == 'debug_ldap_import') {
                if ($row['value'] == 1) {
                    $this->debugImport = true;
                }
            } elseif ($row['key'] == 'debug_path') {
                $this->debugPath = trim($row['value']);
            }
        }
        $dbresult->free();
        if ($this->debugPath == '') {
            $this->debugImport = false;
        }
        
        $searchTimeout = 5;
        $tempSearchTimeout = $this->getLdapHostParameters($arId, 'ldap_search_timeout');
        if (count($tempSearchTimeout) > 0) {
            if (isset($tempSearchTimeout['ari_value'])
                && !empty($tempSearchTimeout['ari_value'])
            ) {
                $searchTimeout = $tempSearchTimeout['ari_value'];
            }
        }

        /* Get the list of server ldap */
        if ($use_dns_srv != "0") {
            $dns_query = '_ldap._tcp';
            $dbresult = $this->db->query(
                "SELECT `ari_value` 
                FROM auth_ressource_info 
                WHERE `ari_name` = 'ldap_dns_use_domain' 
                AND ar_id = " . $this->db->escape($arId)
            );
            $row = $dbresult->fetchRow();
            $dbresult->free();
            if ($row && trim($row['ari_value']) != '') {
                $dns_query .= "." . $row['ari_value'];
            }
            $list = dns_get_record($dns_query, DNS_SRV);
            foreach ($list as $entry) {
                $ldap = array();
                $ldap['host'] = $entry['target'];
                $ldap['id'] = $arId;
                $ldap['search_timeout'] = $searchTimeout;
                $ldap['info'] = $this->getInfoUseDnsConnect();
                $ldap['info']['port'] = $entry['port'];
                $ldap['info'] = array_merge($ldap['info'], $this->getBindInfo($arId));
                $this->ldapHosts[] = $ldap;
            }
        } else {
            $dbresult = $this->db->query(
                "SELECT ldap_host_id, host_address
                FROM auth_ressource_host
                WHERE auth_ressource_id = " . $this->db->escape($arId) . "
                ORDER BY host_order"
            );
            while ($row = $dbresult->fetchRow()) {
                $ldap = array();
                $ldap['host'] = $row['host_address'];
                $ldap['id'] = $arId;
                $ldap['search_timeout'] = $searchTimeout;
                $ldap['info'] = $this->getInfoConnect($row['ldap_host_id']);
                $ldap['info'] = array_merge($ldap['info'], $this->getBindInfo($arId));
                $this->ldapHosts[] = $ldap;
            }
            $dbresult->free();
        }
    }
    
    /**
     *
     * @param int    $arId
     * @param string $filter
     * @return array
     */
    public function getLdapHostParameters($arId, $filter = '')
    {
        // ldap_search_timeout
        $queryLdapHostParemeters = "SELECT * FROM auth_ressource_info WHERE ar_id = " . $this->db->escape($arId);
        
        if (!empty($filter)) {
            $queryLdapHostParemeters .= " AND `ari_name` = '$filter'";
        }
        
        $resLdapHostParameters = $this->db->query($queryLdapHostParemeters);
        
        $finalLdapHostParameters = array();
        
        while ($rowLdapHostParameters = $resLdapHostParameters->fetchRow()) {
            $finalLdapHostParameters = $rowLdapHostParameters;
        }
        
        return $finalLdapHostParameters;
    }

    /**
     * Connect to the first LDAP server
     *
     * @return bool
     */
    public function connect()
    {
        foreach ($this->ldapHosts as $ldap) {
            $port = "";
            if (isset($ldap['info']['port'])) {
                $port = ":" . $ldap['info']['port'];
            }
            if (isset($ldap['info']['use_ssl']) && $ldap['info']['use_ssl'] == 1) {
                $url = 'ldaps://' . $ldap['host'] . $port . '/';
            } else {
                $url = 'ldap://' . $ldap['host'] . $port . '/';
            }
            $this->debug("LDAP Connect : trying url : " . $url);
            $this->setErrorHandler();
            $this->ds = ldap_connect($url);
            ldap_set_option($this->ds, LDAP_OPT_REFERRALS, 0);
            $protocol_version = 3;
            if (isset($ldap['info']['protocol_version'])) {
                $protocol_version = $ldap['info']['protocol_version'];
            }
            ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, $protocol_version);
            if (isset($ldap['info']['use_tls']) && $ldap['info']['use_tls'] == 1) {
                $this->debug("LDAP Connect : use tls");
                @ldap_start_tls($this->ds);
            }
            restore_error_handler();
            $this->ldap = $ldap;
            $bindResult = $this->rebind();
            if ($bindResult) {
                return true;
            }
            $this->debug("LDAP Connect : connection error");
        }
        return false;
    }

    /**
     * Close LDAP Connexion
     */
    public function close()
    {
        $this->setErrorHandler();
        ldap_close($this->ds);
        restore_error_handler();
    }

    /**
     * Rebind with the default bind_dn
     *
     * @return If the connection is good
     */
    public function rebind()
    {
        $this->setErrorHandler();
        if (isset($this->ldap['info']['bind_dn'])
            && $this->ldap['info']['bind_dn'] != ""
            && isset($this->ldap['info']['bind_pass'])
            && $this->ldap['info']['bind_pass'] != ""
        ) {
            $this->debug("LDAP Connect : Credentials : " . $this->ldap['info']['bind_dn']);
            $bindResult = @ldap_bind($this->ds, $this->ldap['info']['bind_dn'], $this->ldap['info']['bind_pass']);
        } else {
            $this->debug("LDAP Connect : Credentials : anonymous");
            $bindResult = @ldap_bind($this->ds);
        }
        if ($bindResult) {
            $this->linkId = $this->ldap['id'];
            $this->loadSearchInfo($this->ldap['id']);
            restore_error_handler();
            return true;
        }
        $this->debug("LDAP Connect : Bind : " . ldap_error($this->ds));
        restore_error_handler();
        return false;
    }

    /**
     * Retourne the ldap ressource
     *
     * @return ldap_ressource
     */
    public function getDs()
    {
        return $this->ds;
    }
    
     /**
     * Transform user, group name for filter
     *
     * @param  string $name the atrribute
     * @return string The string changed
     */
    public function replaceFilter($name)
    {
        $name = str_replace('(', "\\(", $name);
        $name = str_replace(')', "\\)", $name);
        return $name;
    }

    /**
     * Get the dn for a user
     *
     * @param  string $username The username
     * @return string|bool The dn string or false if not found
     */
    public function findUserDn($username)
    {
        if (trim($this->userSearchInfo['filter']) == '') {
            return false;
        }
        $this->setErrorHandler();
        $filter = preg_replace('/%s/', $this->replaceFilter($username), $this->userSearchInfo['filter']);
        $result = ldap_search($this->ds, $this->userSearchInfo['base_search'], $filter);
        $entries = ldap_get_entries($this->ds, $result);
        restore_error_handler();
        if ($entries["count"] == 0) {
            return false;
        }
        return $entries[0]['dn'];
    }

    /**
     * Get the dn for a group
     *
     * @param  string $group The group
     * @return string|bool The dn string or false if not found
     */
    public function findGroupDn($group)
    {
        if (trim($this->groupSearchInfo['filter']) == '') {
            return false;
        }
        $this->setErrorHandler();
        $filter = preg_replace('/%s/', $this->replaceFilter($group), $this->groupSearchInfo['filter']);
        $result = ldap_search($this->ds, $this->groupSearchInfo['base_search'], $filter);
        $entries = ldap_get_entries($this->ds, $result);
        restore_error_handler();
        if ($entries["count"] == 0) {
            return false;
        }
        return $entries[0]['dn'];
    }

    /**
     * Return the list of groups
     *
     * @param  string $pattern The pattern for search
     * @return array The list of groups
     */
    public function listOfGroups($pattern = '*')
    {
        if (trim($this->groupSearchInfo['filter']) == '') {
            return array();
        }
        $this->setErrorHandler();
        $filter = preg_replace('/%s/', $pattern, $this->groupSearchInfo['filter']);
        $result = @ldap_search($this->ds, $this->groupSearchInfo['base_search'], $filter);
        if (false === $result) {
            restore_error_handler();
            return array();
        }
        $entries = ldap_get_entries($this->ds, $result);
        $nbEntries = $entries["count"];
        $list = array();
        for ($i = 0; $i < $nbEntries; $i++) {
            $list[] = $entries[$i][$this->groupSearchInfo['group_name']][0];
        }
        restore_error_handler();
        return $list;
    }

    /**
     * Return the list of users
     *
     * @param  string $pattern The pattern for search
     * @return array The list of users
     */
    public function listOfUsers($pattern = '*')
    {
        if (trim($this->userSearchInfo['filter']) == '') {
            return array();
        }
        $this->setErrorHandler();
        $filter = preg_replace('/%s/', $pattern, $this->userSearchInfo['filter']);
        $result = ldap_search($this->ds, $this->userSearchInfo['base_search'], $filter);
        $entries = ldap_get_entries($this->ds, $result);
        $nbEntries = $entries["count"];
        $list = array();
        for ($i = 0; $i < $nbEntries; $i++) {
            $list[] = $entries[$i][$this->userSearchInfo['alias']][0];
        }
        restore_error_handler();
        return $list;
    }

    /**
     * Get a LDAP entry
     *
     * @param  string $dn   The DN
     * @param  array  $attr The list of attribute
     * @return array|bool The list of information, or false in error
     */
    public function getEntry($dn, $attr = array())
    {
        $this->setErrorHandler();
        if (!is_array($attr)) {
            $attr = array($attr);
        }
        $result = ldap_read($this->ds, $dn, '(objectClass=*)', $attr);
        if ($result === false) {
            restore_error_handler();
            return false;
        }
        $entry = ldap_get_entries($this->ds, $result);
        if ($entry['count'] == 0) {
            restore_error_handler();
            return false;
        }
        $infos = array();
        foreach ($entry[0] as $info => $value) {
            if (isset($value['count'])) {
                if ($value['count'] == 1) {
                    $infos[$info] = $value[0];
                } elseif ($value['count'] > 1) {
                    $infos[$info] = array();
                    for ($i = 0; $i < $value['count']; $i++) {
                        $infos[$info][$i] = $value[$i];
                    }
                }
            }
        }
        restore_error_handler();
        return $infos;
    }

    /**
     * Get the list of groups for a user
     *
     * @param  string $userdn The user dn
     * @return array
     */
    public function listGroupsForUser($userdn)
    {
        $this->setErrorHandler();
        if (trim($this->groupSearchInfo['filter']) == '') {
            restore_error_handler();
            return array();
        }
        $userdn = str_replace('\\', '\\\\', $userdn);
        $filter = '(&' . preg_replace('/%s/', '*', $this->groupSearchInfo['filter']) .
            '(' . $this->groupSearchInfo['member'] . '=' . $this->replaceFilter($userdn) . '))';
        $result = @ldap_search($this->ds, $this->groupSearchInfo['base_search'], $filter);
        if (false === $result) {
            restore_error_handler();
            return array();
        }
        $entries = ldap_get_entries($this->ds, $result);
        $nbEntries = $entries["count"];
        $list = array();
        for ($i = 0; $i < $nbEntries; $i++) {
            $list[] = $entries[$i][$this->groupSearchInfo['group_name']][0];
        }
        restore_error_handler();
        return $list;
    }

    /**
     * Return the list of member of a group
     *
     * @param  string $groupdn The group dn
     * @return array The listt of member
     */
    public function listUserForGroup($groupdn)
    {
        if (trim($this->groupSearchInfo['member']) == '') {
            return array();
        }
        $groupdn = str_replace('\\', '\\\\', $groupdn);
        $group = $this->getEntry($groupdn, $this->groupSearchInfo['member']);
        $list = array();
        if (!isset($group[$this->groupSearchInfo['member']])) {
            return $list;
        } elseif (is_array($group[$this->groupSearchInfo['member']])) {
            return $group[$this->groupSearchInfo['member']];
        } else {
            return array($group[$this->groupSearchInfo['member']]);
        }
    }

    /**
     * Return the attribute name for ldap
     *
     * @param  string $type user or group
     * @param  string $info The information to get the attribute name
     * @return string The attribute name or null if not found
     */
    public function getAttrName($type, $info)
    {
        switch ($type) {
            case 'user':
                if (isset($this->userSearchInfo[$info])) {
                    return $this->userSearchInfo[$info];
                }
                break;
            case 'group':
                if (isset($this->groupSearchInfo[$info])) {
                    return $this->groupSearchInfo[$info];
                }
                break;
            default:
                return null;
        }
        return null;
    }

    /**
     * Search function
     *
     * @param  string $filter        The filter string, null for use default
     * @param  string $basedn        The basedn, null for use default
     * @param  int    $searchLimit   The search limit, null for all
     * @param  int    $searchTimeout The search timeout, null for default
     * @return array The search result
     */
    public function search($filter, $basedn, $searchLimit, $searchTimeout)
    {
        $this->setErrorHandler();
        $attr = array(
            $this->userSearchInfo['alias'],
            $this->userSearchInfo['name'],
            $this->userSearchInfo['email'],
            $this->userSearchInfo['pager'],
            $this->userSearchInfo['firstname'],
            $this->userSearchInfo['lastname'],
        );
        /* Set default */
        if (is_null($filter)) {
            $filter = $this->userSearchInfo['filter'];
        }
        if (is_null($basedn)) {
            $filter = $this->userSearchInfo['base_search'];
        }
        if (is_null($searchLimit)) {
            $searchLimit = 0;
        }
        if (is_null($searchTimeout)) {
            $searchTimeout = 0;
        }
        /* Display debug */
        $this->debug('LDAP Search : Base DN : ' . $basedn);
        $this->debug('LDAP Search : Filter : ' . $filter);
        $this->debug('LDAP Search : Size Limit : ' . $searchLimit);
        $this->debug('LDAP Search : Timeout : ' . $searchTimeout);
        /* Search */
        $filter = preg_replace('/%s/', '*', $filter);
        $sr = ldap_search($this->ds, $basedn, $filter, $attr, 0, $searchLimit, $searchTimeout);
        $this->debug("LDAP Search : Error : " . ldap_error($this->ds));
        /* Sort */
        ldap_sort($this->ds, $sr, "dn");
        $number_returned = ldap_count_entries($this->ds, $sr);
        $this->debug("LDAP Search : " . (isset($number_returned) ? $number_returned : "0") . " entries found");

        $info = ldap_get_entries($this->ds, $sr);
        $this->debug("LDAP Search : " . $info["count"]);
        ldap_free_result($sr);

        /* Format the result */
        $results = array();
        for ($i = 0; $i < $info['count']; $i++) {
            $result = array();
            $result['dn'] = (isset($info[$i]['dn']) ? $info[$i]['dn'] : "");
            $result['alias'] = (
                isset($info[$i][$this->userSearchInfo['alias']][0]) ?$info[$i][$this->userSearchInfo['alias']][0] : ""
            );
            $result['name'] = (
                isset($info[$i][$this->userSearchInfo['name']][0]) ? $info[$i][$this->userSearchInfo['name']][0] : ""
            );
            $result['email'] = (
                isset($info[$i][$this->userSearchInfo['email']][0]) ? $info[$i][$this->userSearchInfo['email']][0] : ""
            );
            $result['pager'] = (
                isset($info[$i][$this->userSearchInfo['pager']][0]) ? $info[$i][$this->userSearchInfo['pager']][0] : ""
            );
            $result['firstname'] = (
                isset($info[$i][$this->userSearchInfo['firstname']][0]) ?
                    $info[$i][$this->userSearchInfo['firstname']][0] : ""
            );
            $result['lastname'] = (
                isset($info[$i][$this->userSearchInfo['lastname']][0]) ?
                    $info[$i][$this->userSearchInfo['lastname']][0] : ""
            );
            $results[] = $result;
        }
        restore_error_handler();
        return $results;
    }

    /**
     * Validate the filter string
     *
     * @param  string $filter The filter string to validate
     * @return boolean
     */
    public static function validateFilterPattern($filter)
    {
        if (strpos($filter, '%s') === false) {
            return false;
        }
        return true;
    }

    /**
     * Load the search informations
     *
     * @param  int $ldapHostId
     * @return void
     */
    private function loadSearchInfo($ldapHostId = null)
    {
        if (is_null($ldapHostId)) {
            $ldapHostId = $this->linkId;
        }
        $dbresult = $this->db->query(
            "SELECT ari_name, ari_value
            FROM auth_ressource_info ari
            WHERE ari_name IN
                ('user_filter', 'user_base_search', 'alias', 'user_group', 'user_name', 'user_email', 'user_pager',
                'user_firstname', 'user_lastname', 'group_filter', 'group_base_search', 'group_name', 'group_member')
            AND ari.ar_id = " . CentreonDB::escape($ldapHostId)
        );
        $user = array();
        $group = array();
        while ($row = $dbresult->fetchRow()) {
            switch ($row['ari_name']) {
                case 'user_filter':
                    $user['filter'] = $row['ari_value'];
                    break;
                case 'user_base_search':
                    $user['base_search'] = $row['ari_value'];
                    /*
                     * Fix for domino
                     */
                    if (trim($user['base_search']) == '') {
                        $user['base_search'] = '';
                    }
                    break;
                case 'alias':
                    $user['alias'] = $row['ari_value'];
                    break;
                case 'user_group':
                    $user['group'] = $row['ari_value'];
                    break;
                case 'user_name':
                    $user['name'] = $row['ari_value'];
                    break;
                case 'user_email':
                    $user['email'] = $row['ari_value'];
                    break;
                case 'user_pager':
                    $user['pager'] = $row['ari_value'];
                    break;
                case 'user_firstname':
                    $user['firstname'] = $row['ari_value'];
                    break;
                case 'user_lastname':
                    $user['lastname'] = $row['ari_value'];
                    break;
                case 'group_filter':
                    $group['filter'] = $row['ari_value'];
                    break;
                case 'group_base_search':
                    $group['base_search'] = $row['ari_value'];
                    /*
                     * Fix for domino
                     */
                    if (trim($group['base_search']) == '') {
                        $group['base_search'] = ' ';
                    }
                    break;
                case 'group_name':
                    $group['group_name'] = $row['ari_value'];
                    break;
                case 'group_member':
                    $group['member'] = $row['ari_value'];
                    break;
            }
        }
        if (isset($user['filter'])) {
            $this->userSearchInfo = $user;
        }
        if (isset($group['filter'])) {
            $this->groupSearchInfo = $group;
        }
    }

    /**
     * Get the information from the database for a ldap connection
     *
     * @param  int $id | id of ldap host
     * @return array
     */
    private function getInfoConnect($id)
    {
        $dbresult = $this->db->query(
            "SELECT use_ssl, use_tls, host_port as port
                                       FROM auth_ressource_host
                                       WHERE ldap_host_id = " . CentreonDB::escape($id)
        );
        $row = $dbresult->fetchRow();
        return $row;
    }

    /**
     * Get the information from the database for a ldap connection
     *
     * @return array
     */
    private function getInfoUseDnsConnect()
    {
        $query = "SELECT `key`, `value` FROM `options` WHERE `key` IN ('ldap_dns_use_ssl', 'ldap_dns_use_tls')";
        $dbresult = $this->db->query($query);
        $infos = array();
        while ($row = $dbresult->fetchRow()) {
            if ($row['key'] == 'ldap_dns_use_ssl') {
                $infos['use_ssl'] = $row['value'];
            } elseif ($row['key'] == 'ldap_dns_use_tls') {
                $infos['use_tls'] = $row['value'];
            }
        }
        $dbresult->free();
    }

    /**
     * Get bind information for connection
     *
     * @param  int $id The auth resource id
     * @return array
     */
    private function getBindInfo($id)
    {
        if (isset($this->constuctCache[$id])) {
            return $this->constuctCache[$id];
        }
        $query = "SELECT ari_name, ari_value 
                  FROM auth_ressource_info 
                  WHERE ari_name IN ('bind_dn', 'bind_pass', 'protocol_version') 
                  AND ar_id = " . CentreonDB::escape($id);
        $dbresult = $this->db->query($query);
        $infos = array();
        while ($row = $dbresult->fetchRow()) {
            $infos[$row['ari_name']] = $row['ari_value'];
        }
        $dbresult->free();
        $this->constuctCache[$id] = $infos;
        return $infos;
    }

    /**
     * Debug for ldap
     *
     * @param string $msg
     */
    private function debug($msg)
    {
        if ($this->debugImport) {
            error_log("[" . date("d/m/Y H:i") . "]" . $msg . "\n", 3, $this->debugPath . "ldapsearch.log");
        }
    }

    /**
     * Set the error hanlder for LDAP
     *
     * @see errorLdapHandler
     */
    private function setErrorHandler()
    {
        set_error_handler('errorLdapHandler');
    }
}

/**
 * Ldap Administration class
 */
class CentreonLdapAdmin
{

    private $db;

    /**
     * Constructor
     *
     * @param CentreonDB $pearDB The database connection
     */
    public function __construct($pearDB)
    {
        $this->db = $pearDB;
    }

    /**
     * Get ldap parameters
     *
     * @return array
     */
    public function getLdapParameters()
    {
        $tab = array('ldap_store_password', 'ldap_auto_import', 'ldap_search_limit',
            'ldap_search_timeout', 'ldap_contact_tmpl', 'ldap_srv_dns',
            'ldap_dns_use_ssl', 'ldap_dns_use_tls', 'ldap_dns_use_domain',
            'bind_dn', 'bind_pass', 'protocol_version', 'ldap_template', 'user_base_search',
            'group_base_search', 'user_filter', 'alias', 'user_group', 'user_name',
            'user_firstname', 'user_lastname', 'user_email', 'user_pager', 'group_filter',
            'group_name', 'group_member');
        return $tab;
    }

    /**
     * Update Ldap servers
     *
     * @param int $arId | auth resource id
     */
    protected function updateLdapServers($arId)
    {
        $this->db->query("DELETE FROM auth_ressource_host WHERE auth_ressource_id = " . $this->db->escape($arId));
        if (isset($_REQUEST['address'])) {
            $addressList = $_REQUEST['address'];
            $portList = $_REQUEST['port'];
            $sslList = $_REQUEST['ssl'];
            $tlsList = $_REQUEST['tls'];
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
                        $this->db->escape($sslList[$key] ? 1 : 0) . ", " .
                        $this->db->escape($tlsList[$key] ? 1 : 0) . ", $i)";
                $i++;
            }
            if ($insertStr) {
                $this->db->query(
                    "INSERT INTO auth_ressource_host
                    (auth_ressource_id, host_address, host_port, use_ssl, use_tls, host_order)
                    VALUES $insertStr"
                );
            }
        }
    }
    
    /**
     * Set ldap options
     *
     * 'ldap_auth_enable', 'ldap_auto_import', 'ldap_srv_dns', 'ldap_search_limit', 'ldap_search_timeout'
     * and 'ldap_dns_use_ssl', 'ldap_dns_use_tls', 'ldap_dns_use_domain' if ldap_srv_dns = 1
     *
     * @param  int   $arId
     * @param  array $options The list of options
     * @return int | auth ressource id
     */
    public function setGeneralOptions($arId, $options)
    {
        $gopt = $this->getGeneralOptions($arId);
        if (!count($gopt) && isset($options['ar_name']) && isset($options['ar_description'])) {
            $this->db->query(
                "INSERT INTO auth_ressource (ar_name, ar_description, ar_type, ar_enable) 
                                   VALUES ('" . $this->db->escape($options['ar_name']) . "', 
                                           '" . $this->db->escape($options['ar_description']) . "', 
                                           'ldap', 
                                           '" . $options['ldap_auth_enable']['ldap_auth_enable'] . "')"
            );
            $maxArIdSql = "SELECT MAX(ar_id) as last_id
                FROM auth_ressource
                WHERE ar_name = '" . $this->db->escape($options['ar_name']) . "'";
            $res = $this->db->query($maxArIdSql);
            $row = $res->fetchRow();
            $arId = $row['last_id'];
            unset($res);
        } else {
            $this->db->query(
                "UPDATE auth_ressource SET 
                                   ar_name = '" . $this->db->escape($options['ar_name']) . "', 
                                   ar_description = '" . $this->db->escape($options['ar_description']) . "', 
                                   ar_enable = '" . $options['ldap_auth_enable']['ldap_auth_enable'] . "'
                                   WHERE ar_id = " . $this->db->escape($arId)
            );
        }

        $knownParameters = $this->getLdapParameters();
        foreach ($options as $key => $value) {
            if (!in_array($key, $knownParameters)) {
                continue;
            }
            if (is_array($value)) { //radio buttons
                $value = $value[$key];
            }
            if (isset($gopt[$key])) {
                $query = "UPDATE `auth_ressource_info` 
                              SET `ari_value` = '" . $this->db->escape($value, false) . "' 
                              WHERE `ari_name` = '" . $this->db->escape($key) . "' 
                              AND ar_id = " . $this->db->escape($arId);
            } else {
                $query = "INSERT INTO `auth_ressource_info`
                    (`ar_id`, `ari_name`, `ari_value`) 
                    VALUES (" . $this->db->escape($arId) . ", '" . $this->db->escape($key) . "', '" .
                    $this->db->escape($value, false) . "')";
            }
            $this->db->query($query);
        }
        $this->updateLdapServers($arId);
        return $arId;
    }

    /**
     * Get the general options
     *
     * @param  int $arId
     * @return array
     */
    public function getGeneralOptions($arId)
    {
        $gopt = array();
        
        $query = "SELECT `ari_name`, `ari_value` FROM `auth_ressource_info` WHERE ar_id = ?";
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array($arId));
        while ($row = $res->fetchRow()) {
            $gopt[$row['ari_name']] = $row['ari_value'];
        }
        return $gopt;
    }

    /**
     * Add a Ldap server
     *
     * @param  int   $arId
     * @param  array $params
     * @return void
     */
    public function addServer($arId, $params = array())
    {
        $use_ssl = isset($params['use_ssl']) ? 1 : 0;
        $use_tls = isset($params['use_tls']) ? 1 : 0;
        $sql = "INSERT INTO auth_ressource_host " .
            "(auth_ressource_id, host_address, host_port, use_ssl, use_tls, host_order) " .
            "VALUES ($arId, '" . $this->db->escape($params['hostname']) . "', '" .
            $this->db->escape($params['port']) . "', " .
            $use_ssl . ", " .
            $use_tls . ", '" .
            $this->db->escape($params['order']) . "')";
        $this->db->query($sql);
    }

    /**
     * Modify a Ldap server
     *
     * @param  int   $arId
     * @param  array $params
     * @return void
     */
    public function modifyServer($arId, $params = array())
    {
        if (!isset($params['order']) || !isset($params['id'])) {
            return false;
        }
        $use_ssl = isset($params['use_ssl']) ? 1 : 0;
        $use_tls = isset($params['use_tls']) ? 1 : 0;
        $sql = "UPDATE auth_ressource_host SET 
                    host_address = '" . $this->db->escape($params['hostname']) . "',
                    host_port = '" . $this->db->escape($params['port']) . "',
                    host_order = '" . $this->db->escape($params['order']) . "', 
                    use_ssl = " . $use_ssl . ", 
                    use_tls = " . $use_tls . "
                    WHERE ldap_host_id = " . $this->db->escape($params['id']) . "
                    AND auth_ressource_id = " . $arId;
        $this->db->query($sql);
    }

    /**
     * Add a template
     *
     * @param  array $options A hash table with options for connections and search in ldap
     * @return int|bool The id of connection, false on error
     */
    public function addTemplate($options = array())
    {
        if (PEAR::isError(
            $this->db->query(
                "INSERT INTO auth_ressource (ar_type, ar_enable) VALUES ('ldap_tmpl', '0')"
            )
        )) {
            return false;
        }
        $dbresult = $this->db->query("SELECT MAX(ar_id) as id FROM auth_ressource WHERE ar_type = 'ldap_tmpl'");
        $row = $dbresult->fetchRow();
        if (PEAR::isError($row)) {
            return false;
        }
        $id = $row['id'];
        foreach ($options as $key => $value) {
            $sth = $this->db->query(
                "INSERT INTO auth_ressource_info
                    (ar_id, ari_name, ari_value) VALUES (" . CentreonDB::escape($id) . ", '" .
                    $this->db->escape($key) . "', '" . $this->db->escape($value) . "')"
            );
            if (PEAR::isError($sth)) {
                return false;
            }
        }
        return $id;
    }

    /**
     * Modify a template
     *
     * @param  int The id of the template
     * @param  array                      $options A hash table with options for connections and search in ldap
     * @return bool
     */
    public function modifyTemplate($id, $options = array())
    {
        /*
         * Load configuration
         */
        $config = $this->getTemplate($id);

        foreach ($options as $key => $value) {
            if (isset($config[$key])) {
                $sth = $this->db->query(
                    "UPDATE auth_ressource_info SET ari_value = '" . $this->db->escape($value) . "'
                    WHERE ar_id = " . CentreonDB::escape($id) . " AND ari_name = '" . $this->db->escape($key) . "'"
                );
            } else {
                $sth = $this->db->query(
                    "INSERT INTO auth_ressource_info
                        (ar_id, ari_name, ari_value)
                        VALUES (" . CentreonDB::escape($id) . ", '" . $this->db->escape($key) . "', '" .
                        $this->db->escape($value) . "')"
                );
            }
            if (PEAR::isError($sth)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the template information
     *
     * @param int $id The template id, if 0 get the template
     */
    public function getTemplate($id = 0)
    {
        if ($id == 0) {
            $queryTemplate = "SELECT ar_id FROM auth_ressource WHERE ar_type = 'ldap_tmpl'";
            $res = $this->db->query($queryTemplate);
            if ($res->numRows() == 0) {
                return array();
            }
            $row = $res->fetchRow();
            $id = $row['ar_id'];
        }
        $query = "SELECT ari_name, ari_value
			FROM auth_ressource_info
			WHERE ar_id = " . CentreonDB::escape($id);
        $res = $this->db->query($query);
        $list = array();
        while ($row = $res->fetchRow()) {
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
     * @param  string $search
     * @param  string $offset
     * @param  int    $limit
     * @return array
     */
    public function getLdapConfigurationList($search = "", $offset = null, $limit = null)
    {
        $sql = "SELECT ar_id, ar_enable, ar_name, ar_description ";
        $sql .= "FROM auth_ressource ";
        if ($search != "") {
            $sql .= "WHERE ar_name LIKE '%" . $this->db->escape($search) . "%' ";
        }
        $sql .= "ORDER BY ar_name ";
        if (!is_null($offset) && !is_null($limit)) {
            $sql .= "LIMIT $offset,$limit";
        }
        $res = $this->db->query($sql);
        $tab = array();
        while ($row = $res->fetchRow()) {
            $tab[] = $row;
        }
        return $tab;
    }

    /**
     * Delete ldap configuraition
     *
     * @param  array $configList
     * @return void
     */
    public function deleteConfiguration($configList = array())
    {
        if (count($configList)) {
            $this->db->query(
                "DELETE FROM auth_ressource 
                WHERE ar_id IN (" . implode(',', $configList) . ")"
            );
        }
    }

    /**
     * Enable/Disable ldap configuration
     *
     * @param  int   $status
     * @param  array $configList
     * @return void
     */
    public function setStatus($status, $configList = array())
    {
        if (count($configList)) {
            $this->db->query(
                "UPDATE auth_ressource 
                                   SET ar_enable = '" . $this->db->escape($status) . "'
                                   WHERE ar_id IN (" . implode(',', $configList) . ")"
            );
        }
    }

    /**
     * Get list of servers from resource id
     *
     * @param  int $arId | Auth resource id
     * @return array
     */
    public function getServersFromResId($arId)
    {
        $serverStmt = $this->db->prepare(
            "SELECT host_address, host_port, use_ssl, use_tls " .
            "FROM auth_ressource_host " .
            "WHERE auth_ressource_id = ? ".
            "ORDER BY host_order"
        );
        $res = $this->db->execute($serverStmt, array($arId));
        $arr = array();
        $i = 0;
        while ($row = $res->fetchRow()) {
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
}
