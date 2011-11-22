<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */


/**
 * The utils class for LDAP
 */
class CentreonLDAP {

	public $CentreonLog;
	private $_ds;
	private $_db = null;
	private $_linkId;
	private $_ldapHosts = array();
	private $_ldap = null;
	private $_constuctCache = array();
	private $_userSearchInfo = null;
	private $_groupSearchInfo = null;
	private $_debugImport = false;
	private $_debugPath = "";

	/**
	 * Constructor
	 *
	 * @param DB $pearDB The database connection
	 * @param CentreonLog $CentreonLog The logging object
	 */
	public function __construct($pearDB, $CentreonLog = null)
	{
		$this->CentreonLog = $CentreonLog;
		$this->_db = $pearDB;

		/* Check if use service form DNS */
		$use_dns_srv = 0;
		$dbresult = $this->_db->query("SELECT `value` FROM options WHERE `key` = 'ldap_srv_dns'");
		$row = $dbresult->fetchRow();
		$dbresult->free();
		if ($row) {
			$use_dns_srv = $row['value'];
		}

		/* Get the ldap template */
	    $dbresult = $this->_db->query("SELECT ar_id FROM auth_ressource WHERE ar_type = 'ldap_tmpl'");
		$row = $dbresult->fetchRow();
		$dbresult->free();
		if ($row) {
			$tmpl_id = $row['ar_id'];
		} else {
			throw new Exception('Not ldap template has defined');
		}

		/* Debug options */
		$dbresult = $this->_db->query("SELECT `key`, `value` FROM `options` WHERE `key` IN ('debug_ldap_import', 'debug_path')");
		while ($row = $dbresult->fetchRow()) {
		    if ($row['key'] == 'debug_ldap_import') {
		        if ($row['value'] == 1) {
		            $this->_debugImport = true;
		        }
		    } elseif ($row['key'] == 'debug_path') {
		        $this->_debugPath = trim($row['value']);
		    }
		}
		$dbresult->free();
		if ($this->_debugPath == '') {
		    $this->_debugImport = false;
		}


		/* Get the list of server ldap */
		if ($use_dns_srv != "0") {
			$dns_query = '_ldap._tcp';
			$dbresult = $this->_db->query("SELECT `value` FROM options WHERE `key` = 'ldap_dns_use_domain'");
			$row = $dbresult->fetchRow();
			$dbresult->free();
			if ($row && trim($row['value']) != '') {
				$dns_query .= $row['value'];
			}
			$list = dns_get_record($dns_query, DNS_SRV);
			foreach ($list as $entry) {
				$ldap = array();
				$ldap['host'] = $entry['host'];
				$ldap['id'] = 0;
				$ldap['info'] = $this->_getInfoUseDnsConnect();
				$ldap['tmpl'] = $tmpl_id;
				$ldap['info']['port'] = $entry['port'];
				$ldap['info'] = array_merge($ldap['info'], $this->_getBindInfo($tmpl_id));
			}
			$this->_ldapHosts[] = $ldap;
		} else {
			$dbresult = $this->_db->query("SELECT ar.ar_id, ari.ari_value
				FROM auth_ressource as ar, auth_ressource_info as ari
				WHERE ar.ar_type = 'ldap' AND ar.ar_enable = '1' AND ar.ar_id = ari.ar_id AND ari.ari_name = 'host'
				ORDER BY ar_order");
			while ($row = $dbresult->fetchRow()) {
				$ldap = array();
				$ldap['host'] = $row['ari_value'];
				$ldap['id'] = $row['ar_id'];
				$ldap['info'] = $this->_getInfoConnect($row['ar_id']);
				$ldap['tmpl'] = $tmpl_id;
				$ldap['info'] = array_merge($ldap['info'], $this->_getBindInfo($tmpl_id));
			}
			$this->_ldapHosts[] = $ldap;
			$dbresult->free();
		}
	}

	/**
	 * Connect to the first LDAP server
	 *
	 * @return bool
	 */
	public function connect()
	{
		foreach ($this->_ldapHosts as $ldap) {
			$port = "";
			if (isset($ldap['info']['port'])) {
				$port = ":" . $ldap['info']['port'];
			}
			if (isset($ldap['info']['use_ssl']) && $ldap['info']['use_ssl'] == 1) {
			    $url = 'ldaps://' . $ldap['host'] . $port . '/';
			} else {
			    $url = 'ldap://' . $ldap['host'] . $port . '/';
			}
			$this->_debug("LDAP Connect : trying url : " . $url);
			$this->_ds = ldap_connect($url);
			ldap_set_option($this->_ds, LDAP_OPT_REFERRALS, 0);
			$protocol_version = 3;
			if (isset($ldap['info']['protocol_version'])) {
				$protocol_version = $ldap['info']['protocol_version'];
			}
			ldap_set_option($this->_ds, LDAP_OPT_PROTOCOL_VERSION, $protocol_version);
			if (isset($ldap['info']['use_tls']) && $ldap['info']['use_tls'] == 1) {
			    $this->_debug("LDAP Connect : use tls");
			    ldap_start_tls($this->_ds);
			}
			$this->_ldap = $ldap;
			if ($this->rebind()) {
			    return true;
			}
			$this->_debug("LDAP Connect : connection error");
		}
		return false;
	}

	/**
	 * Close LDAP Connexion
	 */
	public function close() {
		ldap_close($this->_ds);
	}

	/**
	 * Rebind with the default bind_dn
	 *
	 * @return If the connection is good
	 */
	public function rebind() {
	    if (isset($this->_ldap['info']['bind_dn']) && isset($this->_ldap['info']['bind_pass'])) {
	        $this->_debug("LDAP Connect : Credentials : " . $this->_ldap['info']['bind_dn'] . " :: " . $this->_ldap['info']['bind_pass']);
			if (@ldap_bind($this->_ds, $this->_ldap['info']['bind_dn'], $this->_ldap['info']['bind_pass'])) {
				$this->_linkId = $this->_ldap['id'];
				$this->_loadSearchInfo($this->_ldap['tmpl']);
				return true;
			}
		} else {
		    $this->_debug("LDAP Connect : Credentials : anonymous");
			if (ldap_bind($this->_ds)) {
				$this->_linkId = $this->_ldap['id'];
				$this->_loadSearchInfo($this->_ldap['tmpl']);
				return true;
			}
		}
		$this->_debug("LDAP Connect : Bind : " . ldap_error($this->_ds));
		return false;
	}

	/**
	 * Retourne the ldap ressource
	 *
	 * @return ldap_ressource
	 */
	public function getDs()
	{
	    return $this->_ds;
	}

	/**
	 * Get the dn for a user
	 *
	 * @param string $username The username
	 * @return string|bool The dn string or false if not found
	 */
	public function findUserDn($username)
	{
	    if (trim($this->_userSearchInfo['filter']) == '') {
	        return false;
	    }
		$filter = preg_replace('/%s/', $username, $this->_userSearchInfo['filter']);
		$result = ldap_search($this->_ds, $this->_userSearchInfo['base_search'], $filter);
		$entries = ldap_get_entries($this->_ds, $result);
		if ($entries["count"] == 0) {
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
	    if (trim($this->_groupSearchInfo['filter']) == '') {
	        return false;
	    }
		$filter = preg_replace('/%s/', $group, $this->_groupSearchInfo['filter']);
		$result = ldap_search($this->_ds, $this->_groupSearchInfo['base_search'], $filter);
		$entries = ldap_get_entries($this->_ds, $result);
		if ($entries["count"] == 0) {
			return false;
		}
		return $entries[0]['dn'];
	}

	/**
	 * Return the list of groups
	 *
	 * @param string $pattern The pattern for search
	 * @return array The list of groups
	 */
	public function listOfGroups($pattern = '*')
	{
	    if (trim($this->_groupSearchInfo['filter']) == '') {
	        return array();
	    }
	    $filter = preg_replace('/%s/', $pattern, $this->_groupSearchInfo['filter']);
	    $result = ldap_search($this->_ds, $this->_groupSearchInfo['base_search'], $filter);
	    if (false === $result) {
	        //print ldap_error($this->_ds);
	        return array();
	    }
	    $entries = ldap_get_entries($this->_ds, $result);
	    $nbEntries = $entries["count"];
	    $list = array();
		for ($i = 0; $i < $nbEntries; $i++) {
		    $list[] = $entries[$i][$this->_groupSearchInfo['group_name']][0];
		}
		return $list;
	}

	/**
	 * Return the list of users
	 *
	 * @param string $pattern The pattern for search
	 * @return array The list of users
	 */
	public function listOfUsers($pattern = '*')
	{
	    if (trim($this->_userSearchInfo['filter']) == '') {
	        return array();
	    }
	    $filter = preg_replace('/%s/', $pattern, $this->_userSearchInfo['filter']);
	    $result = ldap_search($this->_ds, $this->_userSearchInfo['base_search'], $filter);
	    $entries = ldap_get_entries($this->_ds, $result);
	    $nbEntries = $entries["count"];
	    $list = array();
		for ($i = 0; $i < $nbEntries; $i++) {
		    $list[] = $entries[$i][$this->_userSearchInfo['alias']][0];
		}
		return $list;
	}

	/**
	 * Get a LDAP entry
	 *
	 * @param string $dn The DN
	 * @param array $attr The list of attribute
	 * @return array|bool The list of information, or false in error
	 */
	public function getEntry($dn, $attr = array())
	{
	    if (!is_array($attr)) {
	        $attr = array($attr);
	    }
		$result = ldap_read($this->_ds, $dn, '(objectClass=*)', $attr);
		if ($result === false) {
			return false;
		}
		$entry = ldap_get_entries($this->_ds, $result);
		if ($entry['count'] == 0) {
			return false;
		}
		$infos = array();
		foreach ($entry[0] as $info => $value) {
		    if ($value['count'] == 1) {
				$infos[$info] = $value[0];
			} else if ($value['count'] > 1) {
			    $infos[$info] = array();
			    for ($i = 0; $i < $value['count']; $i++) {
			        $infos[$info][$i] = $value[$i];
			    }
			}
		}
		return $infos;
	}

	/**
	 * Get the list of groups for a user
	 *
	 * @param string $userdn The user dn
	 * @return array
	 */
	public function listGroupsForUser($userdn)
	{
	    if (trim($this->_groupSearchInfo['filter']) == '') {
	        return array();
	    }
	    $userdn = str_replace('\\', '\\\\', $userdn);
	    $filter =  '(&' . preg_replace('/%s/', '*', $this->_groupSearchInfo['filter']) . '(' . $this->_groupSearchInfo['member'] . '=' . $userdn . '))';
	    $result = ldap_search($this->_ds, $this->_groupSearchInfo['base_search'], $filter);
	    if (false === $result) {
	        //print ldap_error($this->_ds);
	        return array();
	    }
	    $entries = ldap_get_entries($this->_ds, $result);
	    $nbEntries = $entries["count"];
	    $list = array();
		for ($i = 0; $i < $nbEntries; $i++) {
		    $list[] = $entries[$i][$this->_groupSearchInfo['group_name']][0];
		}
		return $list;
	}

	/**
	 * Return the list of member of a group
	 *
	 * @param string $groupdn The group dn
	 * @return array The listt of member
	 */
	public function listUserForGroup($groupdn)
	{
	    if (trim($this->_groupSearchInfo['member']) == '') {
	        return array();
	    }
	    $groupdn = str_replace('\\', '\\\\', $groupdn);
	    $group = $this->getEntry($groupdn, $this->_groupSearchInfo['member']);
	    $list = array();
	    if (!isset($group[$this->_groupSearchInfo['member']])) {
	        return $list;
	    } elseif (is_array($group[$this->_groupSearchInfo['member']])) {
	        return $group[$this->_groupSearchInfo['member']];
	    } else {
	        return array($group[$this->_groupSearchInfo['member']]);
	    }
	}

	/**
	 * Return the attribute name for ldap
	 *
	 * @param string $type user or group
	 * @param string $info The information to get the attribute name
	 * @return string The attribute name or null if not found
	 */
	public function getAttrName($type, $info)
	{
	    switch ($type) {
	        case 'user':
	            if (isset($this->_userSearchInfo[$info])) {
	                return $this->_userSearchInfo[$info];
	            }
	            break;
	        case 'group':
	            if (isset($this->_groupSearchInfo[$info])) {
	                return $this->_groupSearchInfo[$info];
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
	 * @param string $filter The filter string, null for use default
	 * @param string $basedn The basedn, null for use default
	 * @param int $searchLimit The search limit, null for all
	 * @param int $searchTimeout The search timeout, null for default
	 * @return array The search result
	 */
	public function search($filter, $basedn, $searchLimit, $searchTimeout)
	{
	    $attr = array(
	        $this->_userSearchInfo['alias'],
	        $this->_userSearchInfo['name'],
	        $this->_userSearchInfo['email'],
	        $this->_userSearchInfo['pager'],
	        $this->_userSearchInfo['firstname'],
	        $this->_userSearchInfo['lastname'],
	    );
	    /* Set default */
	    if (is_null($filter)) {
	        $filter = $this->_userSearchInfo['filter'];
	    }
	    if (is_null($basedn)) {
	        $filter = $this->_userSearchInfo['base_search'];
	    }
	    if (is_null($searchLimit)) {
	        $searchLimit = 0;
	    }
	    if (is_null($searchTimeout)) {
	        $searchLimit = 0;
	    }
	    /* Display debug */
	    $this->_debug('LDAP Search : Base DN : ' . $basedn);
	    $this->_debug('LDAP Search : Filter : ' . $filter);
	    $this->_debug('LDAP Search : Size Limit : ' . $searchLimit);
	    $this->_debug('LDAP Search : Timeout : ' . $searchTimeout);
	    /* Search */
	    $sr = ldap_search($this->_ds, $basedn, $filter, $attr, 0, $searchLimit, $searchTimeout);
	    $this->_debug("LDAP Search : Error : ". ldap_err2str($this->_ds));
	    /* Sort */
	    ldap_sort($this->_ds, $sr, "dn");
	    $number_returned = ldap_count_entries($this->_ds,$sr);
		$this->_debug("LDAP Search : ". (isset($number_returned) ? $number_returned : "0") . " entries found");

		$info = ldap_get_entries($this->_ds, $sr);
		$this->_debug("LDAP Search : ". $info["count"]);
		ldap_free_result($sr);

		/* Format the result */
		$results = array();
		for ($i = 0; $i < $info['count']; $i++) {
		    $result = array();
		    $result['dn'] = (isset($info[$i]['dn']) ? $info[$i]['dn'] : "");
		    $result['alias'] = (isset($info[$i][$this->_userSearchInfo['alias']][0]) ? $info[$i][$this->_userSearchInfo['alias']][0] : "");
		    $result['name'] = (isset($info[$i][$this->_userSearchInfo['name']][0]) ? $info[$i][$this->_userSearchInfo['name']][0] : "");
		    $result['email'] = (isset($info[$i][$this->_userSearchInfo['email']][0]) ? $info[$i][$this->_userSearchInfo['email']][0] : "");
		    $result['pager'] = (isset($info[$i][$this->_userSearchInfo['pager']][0]) ? $info[$i][$this->_userSearchInfo['pager']][0] : "");
		    $result['firstname'] = (isset($info[$i][$this->_userSearchInfo['firstname']][0]) ? $info[$i][$this->_userSearchInfo['firstname']][0] : "");
		    $result['lastname'] = (isset($info[$i][$this->_userSearchInfo['lastname']][0]) ? $info[$i][$this->_userSearchInfo['lastname']][0] : "");
		    $results[] = $result;
		}
		return $results;
	}

	/**
	 * Load the search informations
	 */
	private function _loadSearchInfo($id = null)
	{
		if (is_null($id)) {
			$id = $this->_linkId;
		}
		$dbresult = $this->_db->query("SELECT ari_name, ari_value
			FROM auth_ressource_info
			WHERE ari_name IN ('user_filter', 'user_base_search', 'alias', 'user_group', 'user_name', 'user_email', 'user_pager', 'user_firstname', 'user_lastname', 'group_filter', 'group_base_search', 'group_name', 'group_member')
			AND ar_id = " . $id);
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
			$this->_userSearchInfo = $user;
		}
		if (isset($group['filter'])) {
			$this->_groupSearchInfo = $group;
		}
	}

	/**
	 * Get the information from the database for a ldap connection
	 *
	 * @param int $id The identifiant of ldap connection
	 * @return array
	 */
	private function _getInfoConnect($id)
	{
		$dbresult = $this->_db->query("SELECT ari_name, ari_value
			FROM auth_ressource_info
			WHERE ari_name IN ('port', 'use_ssl', 'use_tls', 'protocol_version') AND ar_id = " . $id);
		$infos = array();
		while ($row = $dbresult->fetchRow()) {
			$infos[$row['ari_name']] = $row['ari_value'];
		}
		$dbresult->free();
		return $infos;
	}

	/**
	 * Get the information from the database for a ldap connection
	 *
	 * @return array
	 */
	private function _getInfoUseDnsConnect()
	{
	    $query = "SELECT `key`, `value` FROM `options` WHERE `key` IN ('ldap_dns_use_ssl', 'ldap_dns_use_tls')";
	    $dbresult = $this->_db->query($query);
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
	 * @param int $id The auth resource id
	 * @return array
	 */
	private function _getBindInfo($id)
	{
	    if (isset($this->_constuctCache[$id])) {
			return $this->_constuctCache[$id];
		}
	    $query = "SELECT ari_name, ari_value FROM auth_ressource_info WHERE  ari_name IN ('bind_dn', 'bind_pass') AND ar_id = " . $id;
	    $dbresult = $this->_db->query($query);
	    $infos = array();
		while ($row = $dbresult->fetchRow()) {
			$infos[$row['ari_name']] = $row['ari_value'];
		}
		$dbresult->free();
		$this->_constuctCache[$id] = $infos;
		return $infos;
	}

	/**
	 * Debug for ldap
	 *
	 * @param string $msg
	 */
	private function _debug($msg)
	{
	    if ($this->_debugImport) {
		    error_log("[" . date("d/m/Y H:s") ."]" . $msg . "\n", 3, $this->_debugPath."ldapsearch.log");
	    }
	}
}


/**
 * Ldap Administration class
 */
class CentreonLdapAdmin
{
	private $_db;

	/**
	 * Constructor
	 *
	 * @param CentreonDB $pearDB The database connection
	 */
	public function __construct($pearDB)
	{
		$this->_db = $pearDB;
	}

	/**
	 * Set the general ldap options
	 *
	 * 'ldap_auth_enable', 'ldap_auto_import', 'ldap_srv_dns', 'ldap_search_limit', 'ldap_search_timeout'
	 * and 'ldap_dns_use_ssl', 'ldap_dns_use_tls', 'ldap_dns_use_domain' if ldap_srv_dns = 1
	 *
	 * @param array $options The list of options
	 */
	public function setGeneralOptions($options)
	{
	    $gopt = $this->getGeneralOptions();

	    $keyOptions = array('ldap_auth_enable', 'ldap_auto_import', 'ldap_srv_dns', 'ldap_contact_tmpl', 'ldap_search_limit', 'ldap_search_timeout');
	    if ($options['ldap_srv_dns'] == "1") {
	        $keyOptions[] = 'ldap_dns_use_ssl';
	        $keyOptions[] = 'ldap_dns_use_tls';
	        $keyOptions[] = 'ldap_dns_use_domain';
	    }
	    foreach ($keyOptions as $key) {
	        if (isset($options[$key])) {
	            if (isset($gopt[$key])) {
    	            $query = "UPDATE `options` SET `value` = '" . $options[$key] . "' WHERE `key` = '" . $key . "'";
	            } else {
	                $query = "INSERT INTO `options` (`key`, `value`) VALUES ('" . $key . "', '" . $options[$key] . "')";
	            }
	            $this->_db->query($query);
	        }
	    }
	}

	/**
	 * Get the general options
	 *
	 * @return array
	 */
	public function getGeneralOptions()
	{
	    $gopt = array();
	    $query = "SELECT `key`, `value` FROM `options`
			WHERE `key` IN ('ldap_auth_enable', 'ldap_auto_import', 'ldap_srv_dns',
				'ldap_dns_use_ssl', 'ldap_dns_use_tls', 'ldap_dns_use_domain', 'ldap_contact_tmpl',
				'ldap_search_limit', 'ldap_search_timeout')";
    	$res = $this->_db->query($query);
    	while ($row = $res->fetchRow()) {
    	    $gopt[$row['key']] = $row['value'];
    	}
    	return $gopt;
	}

	/**
	 * Add a Ldap server
	 *
	 * @param string $host The host
	 * @param int $port The port (389)
	 * @param int $use_ssl If use ssl connection 1 - true, 0 - false
	 * @param int $use_tls If use tls connection 1 - true, 0 - false
	 * @param int $order Order for connection
	 * @return int|bool The id of connection, false on error
	 */
	public function addServer($host, $port, $use_ssl, $use_tls, $order)
	{
		if (PEAR::isError($this->_db->query("INSERT INTO auth_ressource (ar_type, ar_enable, ar_order) VALUES ('ldap', '1', " . $order . ")"))) {
			return false;
		}
		$dbresult = $this->_db->query("SELECT MAX(ar_id) as id FROM auth_ressource WHERE ar_type = 'ldap'");
		$row = $dbresult->fetchRow();
		if (PEAR::isError($row)) {
			return false;
		}
		$id = $row['id'];
		$sth = $this->_db->query("INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (" . $id . ", 'host', '" . $host . "')");
		if (PEAR::isError($sth)) {
			return false;
		}
		$sth = $this->_db->query("INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (" . $id . ", 'port', '" . $port . "')");
		if (PEAR::isError($sth)) {
			return false;
		}
		$sth = $this->_db->query("INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (" . $id . ", 'use_ssl', '" . $use_ssl . "')");
		if (PEAR::isError($sth)) {
			return false;
		}
	    $sth = $this->_db->query("INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (" . $id . ", 'use_tls', '" . $use_tls . "')");
		if (PEAR::isError($sth)) {
			return false;
		}
		return $id;
	}

	/**
	 * Modify a Ldap server
	 *
	 * @param string $host The host
	 * @param int $port The port (389)
	 * @param int $use_ssl If use ssl connection 1 - true, 0 - false
	 * @param int $use_tls If use tls connection 1 - true, 0 - false
	 * @param int $order Order for connection
	 * @return int|bool The id of connection, false on error
	 */
    public function modifyServer($id, $host, $port, $use_ssl, $use_tls, $order)
	{
		if (PEAR::isError($this->_db->query("UPDATE auth_ressource
			SET ar_type = 'ldap', ar_order = " . $order . " WHERE ar_id = " . $id))) {
			return false;
		}
		$sth = $this->_db->query("UPDATE auth_ressource_info SET ari_value = '" . $host . "' WHERE ari_name = 'host' AND ar_id = " .$id);
		if (PEAR::isError($sth)) {
			return false;
		}
		$sth = $this->_db->query("UPDATE auth_ressource_info SET ari_value = '" . $port . "' WHERE ari_name = 'port' AND ar_id = " .$id);
		if (PEAR::isError($sth)) {
			return false;
		}
		$sth = $this->_db->query("UPDATE auth_ressource_info SET ari_value = '" . $use_ssl . "' WHERE ari_name = 'use_ssl' AND ar_id = " .$id);
		if (PEAR::isError($sth)) {
			return false;
		}
		$sth = $this->_db->query("UPDATE auth_ressource_info SET ari_value = '" . $use_tls . "' WHERE ari_name = 'use_tls' AND ar_id = " .$id);
		if (PEAR::isError($sth)) {
			return false;
		}
		return $id;
	}

	/**
	 * Add a template
	 *
	 * @param array $options A hash table with options for connections and search in ldap
	 * @return int|bool The id of connection, false on error
	 */
	public function addTemplate($options = array())
	{
		if (PEAR::isError($this->_db->query("INSERT INTO auth_ressource (ar_type, ar_enable) VALUES ('ldap_tmpl', '0')"))) {
			return false;
		}
		$dbresult = $this->_db->query("SELECT MAX(ar_id) as id FROM auth_ressource WHERE ar_type = 'ldap_tmpl'");
		$row = $dbresult->fetchRow();
		if (PEAR::isError($row)) {
			return false;
		}
		$id = $row['id'];
		foreach ($options as $key => $value) {
			$sth = $this->_db->query("INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (" . $id . ", '" . $key . "', '" . $value . "')");
			if (PEAR::isError($sth)) {
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
		    if (isset($config[$key])) {
		        $sth = $this->_db->query("UPDATE auth_ressource_info SET ari_value = '" . $value . "' WHERE ar_id = " . $id . " AND ari_name = '" . $key . "'");
		    } else {
                $sth = $this->_db->query("INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (" . $id . ", '" . $key . "', '" . $value . "')");
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
	        $res = $this->_db->query($queryTemplate);
	        if ($res->numRows() == 0) {
	            return array();
	        }
	        $row = $res->fetchRow();
	        $id = $row['ar_id'];
        }
        $query = "SELECT ari_name, ari_value
			FROM auth_ressource_info
			WHERE ar_id = " . $id;
        $res = $this->_db->query($query);
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
}
?>