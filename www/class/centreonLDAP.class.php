<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 *
 */
class CentreonLDAP {
	
	public $CentreonLog;
	private $_ds;
	private $_db = null;
	private $_linkId;
	private $_ldapHosts = array();
	private $_constuctCache = array();
	private $_userSearchInfo = null;
	private $_groupSearchInfo = null;
	
	/**
	 * Constructor
	 * 
	 * @param DB $pearDB The database connection
	 * @param CentreonLog $CentreonLog The logging object
	 */
	public function __construct($pearDB, $CentreonLog)
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
		
		/* Get the list of server ldap */
		if ($use_dns_srv !== 0) {
			$dns_query = '_ldap._tcp';
			$dbresult = $this->_db->query("SELECT `value` FROM options WHERE `key` = 'ldap_dns_domain'");
			$row = $dbresult->fetchRow();
			$dbresult->free();
			if ($row && trim($row['value']) != '') {
				$dns_query .= $row['value'];
			}
			$list = dns_get_record($dns_query, DNS_SRV);
			$dbresult = $this->_db->query("SELECT `value` FROM options WHERE `key` = 'ldap_dns_tmpl'");
			$row = $dbresult->fetchRow();
			$dbresult->free();
			if ($row) {
				$ar_id = $row['value'];
			} else {
				throw new Exception('Not ldap template has defined');
			}
			foreach ($list as $entry) {
				$ldap = array();	
				$ldap['host'] = $entry['host'];
				$ldap['id'] = $ar_id;
				$ldap['info'] = $this->_getInfoConnect($ar_id);
			}
		} else {
			$dbresult =& $this->_db->query("SELECT ar.ar_id, ari.ari_value 
				FROM auth_ressource as ar, auth_ressource_info as ari  
				WHERE ar.ar_type = 'ldap' AND ar.ar_enable = '1' AND ar.ar_id = ari.ar_id AND ari.ari_name = 'host'
				ORDER BY ar_order");
			while ($row =& $dbresult->fetchRow()) {
				$ldap = array();
				$ldap['host'] = $row['ari_value'];
				$ldap['id'] = $row['ar_id'];
				$ldap['info'] = $this->_getInfoConnect($row['ar_id']);
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
			$port = 389;
			if (isset($ldap['info']['port'])) {
				$port = $ldap['info']['port'];
			}
			$this->_ds = ldap_connect($ldap['host'], $port);
			ldap_set_option($this->_ds, LDAP_OPT_REFERRALS, 0);
			$protocol_version = 3;
			if (isset($ldap['info']['protocol_version'])) {
				$protocol_version = $ldap['info']['protocol_version'];
			}
			ldap_set_option($this->_ds, LDAP_OPT_PROTOCOL_VERSION, $protocol_version);
			if (isset($ldap['info']['bind_dn']) && isset($ldap['info']['bind_pass'])) {
				if (ldap_bind($this->_ds, $ldap['info']['bind_dn'], $ldap['info']['bind_pass'])) {
					$this->_linkId = $ldap['id'];
					$this->_loadSearchInfo();
					return true;
				}
			} else {
				if (ldap_bind($this->_ds)) {
					$this->_linkId = $ldap['id'];
					$this->_loadSearchInfo();
					return true;
				}
			}
		}
		return false;
	}
	
	/*
	 * Close LDAP Connexion
	 */
	public function close() {
		ldap_close($this->_ds);
	}
	
	/**
	 * Get the dn for a user
	 * 
	 * @param string $username The username
	 * @return string|bool The dn string or false if not found
	 */
	public function findUserDn($username)
	{
		$filter = preg_replace('/%s/', $username, $this->_userSearchInfo['filter']);
		$result = ldap_search($this->_ds, $this->_userSearchInfo['base_search']);
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
		$filter = preg_replace('/%s/', $group, $this->_groupSearchInfo['filter']);
		$result = ldap_search($this->_ds, $this->_groupSearchInfo['base_search']);
		$entries = ldap_get_entries($this->_ds, $result);
		if ($entries["count"] == 0) {
			return false;
		}
		return $entries[0]['dn'];
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
			if (isset($value[0])) {
				$infos[$info] = $value;
			}
		}
		return $infos;
	} 
	
	/**
	 * Load the search informations
	 */
	private function _loadSearchInfo($id = null)
	{
		if (is_null($id)) {
			$id = $this->_linkId;
		}
		$dbresult =& $this->_db->query("SELECT ari_name, ari_value
			FROM auth_ressource_info
			WHERE ari_name IN ('user_filter', 'user_base_search', 'group_filter', 'group_base_filter') AND ar_id = " . $id);
		$user = array();
		$group = array();
		while ($row =& $dbresult->fetchRow()) {
			switch ($row['ari_name']) {
				case 'user_filter':
					$user['filter'] = $row['ari_value'];
					break;
				case 'user_base_search':
					$user['base_search'] = $row['ari_value'];
					break;
				case 'group_filter':
					$group['filter'] = $row['ari_value'];
					break;
				case 'group_base_search':
					$group['base_search'] = $row['ari_value'];
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
		if (isset($this->_constuctCache[$id])) {
			return $this->_constuctCache[$id];
		}
		$dbresult =& $this->_db->query("SELECT ari_name, ari_value
			FROM auth_ressource_info
			WHERE ari_name IN ('port', 'bind_dn', 'bind_pass', 'protocol_version', 'use_ssl', 'use_tls') AND ar_id = " . $id);
		$infos = array();
		while ($row =& $dbresult->fetchRow()) {
			$infos[$row['ari_name']] = $row['ari_value'];
		}
		$dbresult->free();
		$this->_constuctCache[$id] = $info;
		return $info;
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
	 * 'ldap_auth_enable', 'ldap_auto_import', 'ldap_srv_dns'
	 * 
	 * @param array $options The list of options
	 */
	public function setGeneralOptions($options)
	{
	    $keyOptions = array('ldap_auth_enable', 'ldap_auto_import', 'ldap_srv_dns');
	    foreach ($keyOptions as $key) {
	        if (isset($options[$key])) {
	            $query = "UPDATE `options` SET `value` = '" . $options[$key] . "' WHERE `key` = '" . $key . "'";
	            $this->_db->query($query);
	        }
	    }
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
		foreach ($options as $key => $value) {
			$sth = $this->_db->query("UPDATE auth_ressource_info SET ari_value = '" . $value . "' WHERE ar_id = " . $id . " AND ari_name = '" . $key . "'");
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
		$infos['user_filter'] = "(&(objectClass=user)(samAccountType=))";
		$attr = array();
		$attr['alias'] = 'samAccountName'; 
		$attr['email'] = 'mail';
		$attr['name'] = 'name';
		$attr['pager'] = 'mobile';
		$attr['group'] = 'memberOf';
		$infos['user_attr'] = $attr;
		$infos['group_filter'] = "()";
		$attr = array();
		$attr['group_name'] = '';
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
		$infos['user_filter'] = "(objectClass=inetOrgPerson)";
		$attr = array();
		$attr['alias'] = 'uid';
		$attr['email'] = 'mail';
		$attr['name'] = 'displayName';
		$attr['pager'] = 'mobile';
		$infos['user_attr'] = $attr;
		$infos['group_filter'] = "(objectClass=groupOfNames)";
		$attr = array();
		$attr['group_name'] = '';
		$attr['member'] = 'member';
		$infos['group_attr'] = $attr;
		return $infos;
	}
}
?>