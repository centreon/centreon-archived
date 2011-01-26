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
 * SVN : $URL: $
 * SVN : $Id: $
 * 
 */

/**
 * Manage contactgroups
 */
class CentreonContactgroup
{
    private $db;
    
    /**
     * Constructor
     * 
     * @param CentreonDB $pearDB
     */
    public function __construct($pearDB)
    {
        $this->db = $pearDB;
    }
    
    /**
     * Get the list of contactgroups with his id, or his name for a ldap groups if is not sync in database
     * 
     * @param unknown_type $withLdap
     */
    public function getListContactgroup($withLdap = false)
    {
        /* Contactgroup from database */
        $contactgroups = array();
        $query = "SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name";
	    $res = $this->db->query($query);
    	while ($contactgroup = $res->fetchRow()) {
    		$contactgroups[$contactgroup["cg_id"]] = $contactgroup["cg_name"];
    	}
    	$res->free();
    	
    	$query = "SELECT `value` FROM `options` WHERE `key` = 'ldap_auth_enable'";
    	$res = $this->db->query($query);
    	$row = $res->fetchRow();
    	if ($row['value'] == 1) {
    	    $ldapEnable = true;
    	} else {
    	    $ldapEnable = false;
    	}
    	
    	if ($withLdap && $ldapEnable) {
        	/* ContactGroup from LDAP */
        	$ldap = new CentreonLDAP($this->db, null);
        	$ldap->connect();
        	$cg_ldap = $ldap->listOfGroups();
        	
    	    /* Merge contactgroup from ldap and from db */
        	foreach ($cg_ldap as $cg_name) {
        	    if (false === array_search($cg_name, $contactgroups)) {
        	        $contactgroups[$cg_name] = $cg_name;
        	    }
        	}
    	}
    	
    	return $contactgroups;
    }
    
    /**
     * Insert the ldap groups in table contactgroups
     * 
     * @param string $cg_name The ldap group name
     * @return int The contactgroup id or 0 if error
     */
    public function insertLdapGroup($cg_name)
    {
        $ldap = new CentreonLDAP($this->db, null);
        $ldap->connect();
        $ldap_dn = $ldap->findGroupDn($cg_name);
        $query = "INSERT INTO contactgroup
        	(cg_name, cg_alias, cg_activate, cg_type, cg_ldap_dn)
        	VALUES
        	('" . $cg_name . "', '" . $cg_name . "', '1', 'ldap', '" . $ldap_dn . "')";
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return 0;
        }
        $query = "SELECT cg_id FROM contactgroup WHERE cg_ldap_dn = '" . $ldap_dn . "'";
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return 0;
        }
        $row = $res->fetchRow();
        return $row['cg_id'];
    }
}