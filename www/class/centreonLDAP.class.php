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
 * The utils class for LDAP
 */
class CentreonLDAP
{
    public $centreonLog;
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
     * @param \CentreonDB $pearDB The database connection
     * @param \CentreonLog $centreonLog The logging object
     * @param string|null $arId
     */
    public function __construct($pearDB, $centreonLog = null, $arId = null)
    {
        $this->centreonLog = $centreonLog;
        $this->db = $pearDB;

        /* Check if use service form DNS */
        $use_dns_srv = 0;
        $dbResult = $this->db->query(
            "SELECT `ari_value`  
            FROM `auth_ressource_info` 
            WHERE `ari_name` = 'ldap_srv_dns' AND ar_id = " . (int) $arId
        );
        $row = $dbResult->fetch();
        $dbResult->closeCursor();
        if (isset($row['ari_value'])) {
            $use_dns_srv = $row['ari_value'];
        }

        $dbResult = $this->db->query(
            "SELECT `key`, `value` 
            FROM `options` 
            WHERE `key` 
            IN ('debug_ldap_import', 'debug_path')"
        );
        while ($row = $dbResult->fetch()) {
            if ($row['key'] == 'debug_ldap_import') {
                if ($row['value'] == 1) {
                    $this->debugImport = true;
                }
            } elseif ($row['key'] == 'debug_path') {
                $this->debugPath = trim($row['value']);
            }
        }
        $dbResult->closeCursor();
        if ($this->debugPath == '') {
            $this->debugImport = false;
        }

        $searchTimeout = 5;
        $tempSearchTimeout = $this->getLdapHostParameters($arId, 'ldap_search_timeout');
        if (count($tempSearchTimeout) > 0) {
            if (
                isset($tempSearchTimeout['ari_value'])
                && !empty($tempSearchTimeout['ari_value'])
            ) {
                $searchTimeout = $tempSearchTimeout['ari_value'];
            }
        }

        /* Get the list of server ldap */
        if ($use_dns_srv != "0") {
            $dns_query = '_ldap._tcp';
            $dbResult = $this->db->query(
                "SELECT `ari_value` 
                FROM auth_ressource_info 
                WHERE `ari_name` = 'ldap_dns_use_domain' 
                AND ar_id = " . (int) $arId
            );
            $row = $dbResult->fetch();
            $dbResult->closeCursor();
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
                $ldap['info'] = array_merge($ldap['info'], $this->getBindInfo((int) $arId));
                $this->ldapHosts[] = $ldap;
            }
        } else {
            $dbResult = $this->db->query(
                'SELECT ldap_host_id, host_address
                FROM auth_ressource_host
                WHERE auth_ressource_id = ' . (int) $arId . ' ORDER BY host_order'
            );
            while ($row = $dbResult->fetch()) {
                $ldap = array();
                $ldap['host'] = $row['host_address'];
                $ldap['id'] = $arId;
                $ldap['search_timeout'] = $searchTimeout;
                $ldap['info'] = $this->getInfoConnect($row['ldap_host_id']);
                $ldap['info'] = array_merge($ldap['info'], $this->getBindInfo((int) $arId));
                $this->ldapHosts[] = $ldap;
            }
            $dbResult->closeCursor();
        }
    }

    /**
     *
     * @param int $arId
     * @param string $filter
     * @return array<int, array<string, string>>
     */
    public function getLdapHostParameters($arId, $filter = ''): array
    {
        // ldap_search_timeout
        $queryLdapHostParameters = 'SELECT * FROM auth_ressource_info WHERE ar_id = ' . (int) $arId;

        if (!empty($filter)) {
            $queryLdapHostParameters .= " AND `ari_name` = :filter";
        }

        $statement = $this->db->prepare($queryLdapHostParameters);
        if (! empty($filter)) {
            $statement->bindValue(':filter', $filter, PDO::PARAM_STR);
        }
        $statement->execute();

        $finalLdapHostParameters = [];

        while ($rowLdapHostParameters = $statement->fetch()) {
            $finalLdapHostParameters = $rowLdapHostParameters;
        }

        return $finalLdapHostParameters;
    }

    /**
     * Connect to the first LDAP server
     *
     * @return bool
     */
    public function connect(): bool
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
            $this->debug('LDAP Connect : trying url : ' . $url);
            $this->setErrorHandler();
            $this->ds = ldap_connect($url);
            ldap_set_option($this->ds, LDAP_OPT_REFERRALS, 0);
            $protocol_version = 3;
            if (isset($ldap['info']['protocol_version'])) {
                $protocol_version = $ldap['info']['protocol_version'];
            }
            ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, $protocol_version);
            if (isset($ldap['info']['use_tls']) && $ldap['info']['use_tls'] == 1) {
                $this->debug('LDAP Connect : use tls');
                @ldap_start_tls($this->ds);
            }
            restore_error_handler();
            $this->ldap = $ldap;
            $bindResult = $this->rebind();
            if ($bindResult) {
                return true;
            }
            $this->debug('LDAP Connect : connection error');
        }
        return false;
    }

    /**
     * Close LDAP Connexion
     */
    public function close(): void
    {
        $this->setErrorHandler();
        ldap_close($this->ds);
        restore_error_handler();
    }

    /**
     * Rebind with the default bind_dn
     *
     * @return bool If the connection is good
     */
    public function rebind(): bool
    {
        $this->setErrorHandler();
        if (
            isset($this->ldap['info']['bind_dn'])
            && $this->ldap['info']['bind_dn'] != ""
            && isset($this->ldap['info']['bind_pass'])
            && $this->ldap['info']['bind_pass'] != ""
        ) {
            $this->debug('LDAP Connect : Credentials : ' . $this->ldap['info']['bind_dn']);
            $bindResult = @ldap_bind($this->ds, $this->ldap['info']['bind_dn'], $this->ldap['info']['bind_pass']);
        } else {
            $this->debug('LDAP Connect : Credentials : anonymous');
            $bindResult = @ldap_bind($this->ds);
        }
        if ($bindResult) {
            $this->linkId = $this->ldap['id'];
            $this->loadSearchInfo($this->ldap['id']);
            restore_error_handler();
            return true;
        }
        $this->debug('LDAP Connect : Bind : ' . ldap_error($this->ds));
        restore_error_handler();
        return false;
    }

    /**
     * Send back the ldap resource
     *
     * @return \LDAP\Connection|resource
     */
    public function getDs()
    {
        return $this->ds;
    }

    /**
     * Transform user, group name for filter
     *
     * @param string $name the atrribute
     * @return string The string changed
     */
    public function replaceFilter($name): string
    {
        $name = str_replace('(', "\\(", $name);
        $name = str_replace(')', "\\)", $name);
        return $name;
    }

    /**
     * Get the dn for a user
     *
     * @param string $username The username
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
        // no results were returned using this base_search
        if ($result === false) {
            return false;
        }
        $entries = ldap_get_entries($this->ds, $result);
        restore_error_handler();
        if ($entries['count'] === 0) {
            return false;
        }
        return $entries[0]['dn'];
    }

    /**
     * Get the dn for a group
     *
     * @param string $group The group
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
        if ($entries['count'] === 0) {
            return false;
        }
        return $entries[0]['dn'];
    }

    /**
     * Return the list of groups
     *
     * @param string $pattern The pattern for search
     * @return array<array<string,string>> The list of groups
     */
    public function listOfGroups($pattern = '*'): array
    {
        if (! isset($this->groupSearchInfo['filter']) || trim($this->groupSearchInfo['filter']) === '') {
            return [];
        }
        $this->setErrorHandler();
        $filter = preg_replace('/%s/', $pattern, $this->groupSearchInfo['filter']);
        $result = @ldap_search($this->ds, $this->groupSearchInfo['base_search'], $filter);
        if (false === $result) {
            restore_error_handler();
            return [];
        }

        $entries = ldap_get_entries($this->ds, $result);

        $groups = [];
        for ($i = 0; $i < $entries['count']; $i++) {
            $groups[] = [
                'name' => $entries[$i][$this->groupSearchInfo['group_name']][0],
                'dn' => $entries[$i]['dn'],
            ];
        }

        restore_error_handler();

        return $groups;
    }

    /**
     * Return the list of users
     *
     * @param string $pattern The pattern for search
     * @return array The list of users
     */
    public function listOfUsers($pattern = '*'): array
    {
        if (trim($this->userSearchInfo['filter']) == '') {
            return array();
        }
        $this->setErrorHandler();
        $filter = preg_replace('/%s/', $pattern, $this->userSearchInfo['filter']);
        $result = ldap_search($this->ds, $this->userSearchInfo['base_search'], $filter);
        $entries = ldap_get_entries($this->ds, $result);
        $nbEntries = $entries['count'];
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
     * @param string $dn The DN
     * @param array $attr The list of attribute
     * @return array|bool The list of information, or false in error
     */
    public function getEntry($dn, $attr = [])
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
        if ($entry['count'] === 0) {
            restore_error_handler();
            return false;
        }
        $infos = array();
        foreach ($entry[0] as $info => $value) {
            if (isset($value['count'])) {
                if ($value['count'] === 1) {
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
     * @param string $userdn The user dn
     * @return array
     */
    public function listGroupsForUser($userdn): array
    {
        $this->setErrorHandler();
        if (trim($this->groupSearchInfo['filter']) === '') {
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
     * @param string $groupdn The group dn
     * @return array The list of member
     */
    public function listUserForGroup($groupdn): array
    {
        $this->setErrorHandler();
        if (trim($this->userSearchInfo['filter']) == '') {
            restore_error_handler();
            return array();
        }
        $groupdn = str_replace('\\', '\\\\', $groupdn);
        $list = array();
        if (!empty($this->userSearchInfo['group'])) {
            /**
             * we have specific parameter for user to denote groups he belongs to
             */
            $filter = '(&' . preg_replace('/%s/', '*', $this->userSearchInfo['filter']) .
                '(' . $this->userSearchInfo['group'] . '=' . $this->replaceFilter($groupdn) . '))';
            $result = @ldap_search($this->ds, $this->userSearchInfo['base_search'], $filter);

            if (false === $result) {
                restore_error_handler();
                return array();
            }
            $entries = ldap_get_entries($this->ds, $result);
            $nbEntries = $entries["count"];
            for ($i = 0; $i < $nbEntries; $i++) {
                $list[] = $entries[$i]['dn'];
            }
            restore_error_handler();
        } else {
            /**
             * we get list of members by group
             */
            $filter = preg_replace('/%s/', $this->getCnFromDn($groupdn), $this->groupSearchInfo['filter']);
            $result = @ldap_search($this->ds, $this->groupSearchInfo['base_search'], $filter);

            if (false === $result) {
                restore_error_handler();
                return array();
            }
            $entries = ldap_get_entries($this->ds, $result);
            $memberAttribute = $this->groupSearchInfo['member'];
            $nbEntries = !empty($entries[0][$memberAttribute]['count']) ? $entries[0][$memberAttribute]['count'] : 0;
            for ($i = 0; $i < $nbEntries; $i++) {
                $list[] = $entries[0][$memberAttribute][$i];
            }
            restore_error_handler();
        }

        return $list;
    }

    /**
     * Return the attribute name for ldap
     *
     * @param string $type user or group
     * @param string $info The information to get the attribute name
     * @return string|null The attribute name or null if not found
     */
    public function getAttrName($type, $info): ?string
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
     * @param string|null $filter The filter string, null for use default
     * @param string $basedn The basedn, null for use default
     * @param int $searchLimit The search limit, null for all
     * @param int $searchTimeout The search timeout, null for default
     * @return array<int, array<string, mixed>> The search result
     */
    public function search($filter, $basedn, $searchLimit, $searchTimeout): array
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

        /* Sort */
        if ($sr !== false) {
            $numberReturned = ldap_count_entries($this->ds, $sr);
            $this->debug("LDAP Search : " . (isset($numberReturned) ? $numberReturned : "0") . " entries found");
        } else {
            $this->debug("LDAP Search : cannot retrieve entries");
            return [];
        }


        $info = ldap_get_entries($this->ds, $sr);
        $this->debug("LDAP Search : " . $info["count"]);
        ldap_free_result($sr);

        /* Format the result */
        $results = [];
        for ($i = 0; $i < $info['count']; $i++) {
            $result = [];
            $result['dn'] = $info[$i]['dn'] ?? "";
            $result['alias'] = $info[$i][$this->userSearchInfo['alias']][0] ?? "";
            $result['name'] = $info[$i][$this->userSearchInfo['name']][0] ?? "";
            $result['email'] = $info[$i][$this->userSearchInfo['email']][0] ?? "";
            $result['pager'] = $info[$i][$this->userSearchInfo['pager']][0] ?? "";
            $result['firstname'] = $info[$i][$this->userSearchInfo['firstname']][0] ?? "";
            $result['lastname'] = $info[$i][$this->userSearchInfo['lastname']][0] ?? "";
            $results[] = $result;
        }
        restore_error_handler();
        return $results;
    }

    /**
     * Validate the filter string
     *
     * @param string $filter The filter string to validate
     * @return boolean
     */
    public static function validateFilterPattern($filter): bool
    {
        return !(strpos($filter, '%s') === false);
    }

    /**
     * Load the search information
     *
     * @param int $ldapHostId
     * @return void
     */
    private function loadSearchInfo($ldapHostId = null): void
    {
        if (is_null($ldapHostId)) {
            $ldapHostId = $this->linkId;
        }
        $dbResult = $this->db->query(
            "SELECT ari_name, ari_value
            FROM auth_ressource_info ari
            WHERE ari_name IN
                ('user_filter', 'user_base_search', 'alias', 'user_group', 'user_name', 'user_email', 'user_pager',
                'user_firstname', 'user_lastname', 'group_filter', 'group_base_search', 'group_name', 'group_member')
            AND ari.ar_id = " . (int) $ldapHostId
        );
        $user = array();
        $group = array();
        while ($row = $dbResult->fetch()) {
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
     * @param int $id | id of ldap host
     * @return array<string, mixed>
     */
    private function getInfoConnect($id): array
    {
        $dbResult = $this->db->query(
            "SELECT use_ssl, use_tls, host_port as port
            FROM auth_ressource_host
            WHERE ldap_host_id = " . (int) $id
        );
        return $dbResult->fetch();
    }

    /**
     * Get the information from the database for a ldap connection
     *
     * @return array<string, string>
     */
    private function getInfoUseDnsConnect(): array
    {
        $query = "SELECT `key`, `value` FROM `options` WHERE `key` IN ('ldap_dns_use_ssl', 'ldap_dns_use_tls')";
        $dbResult = $this->db->query($query);
        $infos = array();
        while ($row = $dbResult->fetch()) {
            if ($row['key'] == 'ldap_dns_use_ssl') {
                $infos['use_ssl'] = $row['value'];
            } elseif ($row['key'] == 'ldap_dns_use_tls') {
                $infos['use_tls'] = $row['value'];
            }
        }
        $dbResult->closeCursor();
        return $infos;
    }

    /**
     * Get bind information for connection
     *
     * @param int $id The auth resource id
     * @return array<string, string>
     */
    private function getBindInfo($id): array
    {
        if (isset($this->constuctCache[$id])) {
            return $this->constuctCache[$id];
        }
        $query = "SELECT ari_name, ari_value 
                 FROM auth_ressource_info 
                 WHERE ari_name IN ('bind_dn', 'bind_pass', 'protocol_version') 
                 AND ar_id = " . (int) $id;
        $dbResult = $this->db->query($query);
        $infos = array();
        while ($row = $dbResult->fetch()) {
            $infos[$row['ari_name']] = $row['ari_value'];
        }
        $dbResult->closeCursor();
        $this->constuctCache[$id] = $infos;
        return $infos;
    }

    /**
     * Debug for ldap
     *
     * @param string $msg
     */
    private function debug($msg): void
    {
        if ($this->debugImport) {
            error_log("[" . date("d/m/Y H:i") . "] " . $msg . "\n", 3, $this->debugPath . "ldapsearch.log");
        }
    }

    /**
     * Override the custom errorHandler to avoid false errors in the log,
     *
     * @param int $errno The error num
     * @param string $errstr The error message
     * @param string $errfile The error file
     * @param int $errline The error line
     * @return boolean
     */
    private function errorLdapHandler($errno, $errstr, $errfile, $errline): bool
    {
        if ($errno === 2 && ldap_errno($this->ds) === 4) {
            /*
            Silencing : 'size limit exceeded' warnings in the logs
            As the $searchLimit value needs to be consistent with the ldap server's configuration and
            as the size limit error thrown is not related with the results.
                ldap_errno : 4 = LDAP_SIZELIMIT_EXCEEDED
                $errno     : 2 = PHP_WARNING
            */
            $this->debug("LDAP Error : Size limit exceeded error. This error was not added to php log. "
                . "Kindly, check your LDAP server's configuration and your Centreon's LDAP parameters.");
            return true;
        }

        // throwing all errors
        $this->debug("LDAP Error : " . ldap_error($this->ds));
        return false;
    }

    /**
     * Set the error handler for LDAP
     * @see errorLdapHandler
     */
    private function setErrorHandler(): void
    {
        set_error_handler(array($this, 'errorLdapHandler'));
    }

    /**
     * get cn from dn
     *
     * @param string $dn
     * @return string|bool
     */
    private function getCnFromDn($dn)
    {
        if (preg_match('/(?i:(?<=cn=)).*?(?=,[A-Za-z]{0,2}=|$)/', $dn, $dnArray)) {
            return !empty($dnArray) ? $dnArray[0] : false;
        }
        return false;
    }

    /**
     * Set a relation between the LDAP's default contactgroup and the user
     *
     * @internal Method needed for the user's manual and auto import from the LDAP
     * @since 18.10.4
     *
     * @param int|null $arId The Id of the chosen LDAP, from which we'll find the default contactgroup
     * @param int|null $contactId The Id of the contact to be added
     *
     * @return bool Return true to the parent if everything goes well. Needed for the method calling it
     * @throws exception
     */
    public function addUserToLdapDefaultCg(int $arId = null, int $contactId = null): bool
    {
        $ldapCg = null;
        try {
            // Searching the default contactgroup chosen in the ldap configuration
            $resLdap = $this->db->prepare(
                "SELECT ari_value FROM auth_ressource_info " .
                "WHERE ari_name LIKE 'ldap_default_cg' AND ar_id = :arId"
            );
            $resLdap->bindValue(':arId', $arId, \PDO::PARAM_INT);
            $resLdap->execute();
            while ($result = $resLdap->fetch()) {
                $ldapCg = $result['ari_value'];
            }
            unset($resLdap);
            if (!$ldapCg) {
                // No default contactgroup was set in the LDAP parameters
                return true;
            }

            // Checking if the user isn't already linked to this contactgroup
            $resCgExist = $this->db->prepare(
                "SELECT COUNT(*) AS `exist` FROM contactgroup_contact_relation " .
                "WHERE contact_contact_id = :contactId " .
                "AND contactgroup_cg_id = :ldapCg"
            );
            $resCgExist->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
            $resCgExist->bindValue(':ldapCg', $ldapCg, \PDO::PARAM_INT);
            $resCgExist->execute();
            $row = $resCgExist->fetch();
            unset($resCgExist);
            if ($row['exist'] != 0) {
                // User is already linked to this contactgroup
                return true;
            }

            // Inserting the user to the chosen default contactgroup
            $resCg = $this->db->prepare(
                "INSERT INTO contactgroup_contact_relation " .
                "(contactgroup_cg_id, contact_contact_id) " .
                "VALUES (:ldapDefaultCg, :contactId)"
            );
            $resCg->bindValue(':ldapDefaultCg', $ldapCg, \PDO::PARAM_INT);
            $resCg->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
            $resCg->execute();
            unset($resCg);
        } catch (\PDOException $e) {
            return false;
        }
        return true;
    }

    /**
     * Update user's LDAP last sync in the contact table
     *
     * @param array<string, mixed> $currentUser User's alias and Id are needed
     * @return void
     * @throws Exception
     */
    public function setUserCurrentSyncTime(array $currentUser): void
    {
        $stmt = $this->db->prepare(
            "UPDATE contact SET
               `contact_ldap_last_sync` = :currentTime,
               `contact_ldap_required_sync` = '0'
            WHERE contact_id = :contactId"
        );
        try {
            $stmt->bindValue(':currentTime', time(), \PDO::PARAM_INT);
            $stmt->bindValue(':contactId', $currentUser['contact_id'], \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            $this->centreonLog->insertLog(
                3,
                "LDAP MANUAL SYNC : Failed to update ldap_last_sync's values for " . $currentUser['contact_alias']
            );
        }
    }

    /**
     * If the option is disabled in the LDAP parameter's form, we don't sync the LDAP user's modifications on login
     * unless it's required
     * If it's enabled, we need to wait until the next synchronization
     *
     * @param int $arId Id of the current LDAP
     * @param int $contactId Id the contact
     * @return bool
     * @internal Needed on user's login and when manually requesting an update of user's LDAP data
     *
     */
    public function isSyncNeededAtLogin(int $arId, int $contactId): bool
    {
        try {
            // checking if an override was manually set on this contact
            $stmtManualRequest = $this->db->prepare(
                'SELECT `contact_name`, `contact_ldap_required_sync`, `contact_ldap_last_sync` 
                FROM contact WHERE contact_id = :contactId'
            );
            $stmtManualRequest->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
            $stmtManualRequest->execute();
            $contactData = $stmtManualRequest->fetch();
            // check if a manual override was set for this user
            if ($contactData !== false && $contactData['contact_ldap_required_sync'] === '1') {
                $this->centreonLog->insertLog(
                    3,
                    'LDAP AUTH : LDAP synchronization was requested manually for ' . $contactData['contact_name']
                );
                return true;
            }

            // getting the synchronization options
            $stmtSyncState = $this->db->prepare(
                "SELECT ari_name, ari_value FROM auth_ressource_info
                WHERE ari_name IN ('ldap_auto_sync', 'ldap_sync_interval')
                AND ar_id = :arId"
            );
            $stmtSyncState->bindValue(':arId', $arId, \PDO::PARAM_INT);
            $stmtSyncState->execute();
            $syncState = array();
            while ($row = $stmtSyncState->fetch()) {
                $syncState[$row['ari_name']] = $row['ari_value'];
            }

            if ($syncState['ldap_auto_sync'] || $contactData['contact_ldap_last_sync'] === 0) {
                // getting the base date reference set in the LDAP parameters
                $stmtLdapBaseSync = $this->db->prepare(
                    'SELECT ar_sync_base_date AS `referenceDate` FROM auth_ressource
                    WHERE ar_id = :arId'
                );
                $stmtLdapBaseSync->bindValue(':arId', $arId, \PDO::PARAM_INT);
                $stmtLdapBaseSync->execute();
                $ldapBaseSync = $stmtLdapBaseSync->fetch();

                // checking if the interval between two synchronizations is reached
                $currentTime = time();

                if (
                    ($syncState['ldap_sync_interval'] * 3600 + $contactData['contact_ldap_last_sync']) <= $currentTime
                    && $contactData['contact_ldap_last_sync'] < $ldapBaseSync['referenceDate']
                ) {
                    // synchronization is expected
                    $this->centreonLog->insertLog(
                        3,
                        'LDAP AUTH : Updating user DN of ' . $contactData['contact_name']
                    );
                    return true;
                }
            }
        } catch (\PDOException $e) {
            $this->centreonLog->insertLog(
                3,
                'Error while getting automatic synchronization value for LDAP Id : ' . $arId
            );
            // assuming it needs to be synchronized
            $this->centreonLog->insertLog(
                3,
                'LDAP AUTH : Updating user DN of ' .
                (!empty($contactData['contact_name']) ? $contactData['contact_name'] : "contact id $contactId")
            );
            return true;
        }
        $this->centreonLog->insertLog(
            3,
            'LDAP AUTH : Synchronization was skipped. For more details, check your LDAP parameters in Administration'
        );
        return false;
    }
}
