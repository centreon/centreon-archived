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

    if (!isset($oreon)) {
        exit();
    }

    /**
     * Enable a Centreon Broker configuration
     *
     * @param int $id The Centreon Broker configuration in database
     */
    function enableCentreonBrokerInDB($id) {
        if (!$id) return;
        global $pearDB;
        $query = "UPDATE cfg_centreonbroker SET config_activate = '1' WHERE config_id = " . $id;
        $pearDB->query($query);
    }

    /**
     * Disable a Centreon Broker configuration
     *
     * @param int $id The Centreon Broker configuration in database
     */
    function disablCentreonBrokerInDB($id) {
        if (!$id) return;
        global $pearDB;
        $query = "UPDATE cfg_centreonbroker SET config_activate = '0' WHERE config_id = " . $id;
        $pearDB->query($query);
    }

    /**
     * Delete Centreon Broker configurations
     *
     * @param array $id The Centreon Broker configuration in database
     */
    function deleteCentreonBrokerInDB($ids = array())	{
		global $pearDB;
		foreach ($ids as $key => $value)	{
			$pearDB->query("DELETE FROM cfg_centreonbroker WHERE config_id = ".$key);
		}
	}

	/**
	 * Get the information of a server
	 *
	 * @param int $id
	 * @return array
	 */
	function getCentreonBrokerInformation($id) {
	    global $pearDB;
	    $query = "SELECT config_name, config_activate, ns_nagios_server FROM cfg_centreonbroker WHERE config_id = " . $id;
	    $res = $pearDB->query($query);
	    if (PEAR::isError($res)) {
	        return array(
        		"name" => '',
        		"activate" => '1'
    		);
	    }
	    $row = $res->fetchRow();
	    return array(
	    		"id" => $id,
        		"name" => $row['config_name'],
        		"activate" =>  $row['config_activate'],
	            "ns_nagios_server" => $row['ns_nagios_server']
	    );
	}

	/**
	 * Insert into database the Centreon Broker configuration
	 *
	 * @param array $values The post values
	 */
	function insertCentreonBrokerInDB($values)
	{
	    global $pearDB;
	    /*
	     * Insert the Centreon Broker configuration
	     */
	    $query = "INSERT INTO cfg_centreonbroker (config_name, config_activate, ns_nagios_server)
	    	VALUES ('" . $values['name'] . "', '" . $values['activate']['activate'] . "', " . $values['ns_nagios_server'] . ")";
	    if (PEAR::isError($pearDB->query($query))) {
	        return false;
	    }
	    /*
	     * Get the ID
	     */
	    $query = "SELECT config_id FROM cfg_centreonbroker WHERE config_name = '" . $values['name'] . "'";
	    $res = $pearDB->query($query);
	    if (PEAR::isError($res)) {
	        return false;
	    }
	    $row = $res->fetchRow();
	    $id = $row['config_id'];
	    updateCentreonBrokerInfos($id, $values);
	}

	/**
	 * Update the Centreon Broker configuration
	 *
	 * @param int $id The config id
	 * @param array $values The post values
	 */
	function updateCentreonBrokerInDB($id, $values)
	{
	    global $pearDB;
	    /*
	     * Insert the Centreon Broker configuration
	     */
	    $query = "UPDATE cfg_centreonbroker
	    	SET config_name = '" . $values['name'] . "', config_activate = '" . $values['activate']['activate'] . "', ns_nagios_server = " . $values['ns_nagios_server'] . "
	    	WHERE config_id = " . $id;
	    if (PEAR::isError($pearDB->query($query))) {
	        return false;
	    }
	    updateCentreonBrokerInfos($id, $values);
	}

	/**
	 * Update the informations for Centreon Broker
	 *
	 * @param int $id The id
	 * @param array $values The post values
	 */
	function updateCentreonBrokerInfos($id, $values)
	{
	    global $pearDB;
	    /*
	     * List of field for insert
	     */
	    $types = array();
	    $types['ipv4'] = array('name', 'failover', 'type', 'protocol', 'host', 'port', 'net_iface', 'tls', 'ca', 'cert', 'key', 'compress');
	    $types['ipv6'] = array('name', 'failover', 'type', 'protocol', 'host', 'port', 'net_iface', 'tls', 'ca', 'cert', 'key', 'compress');
	    $types['unix_client'] = array('name', 'failover', 'type', 'protocol', 'socket');
	    $types['unix_server'] = array('name', 'failover', 'type', 'protocol', 'socket');
	    $types['file'] = array('name', 'failover', 'type', 'protocol', 'filename', 'config', 'info', 'debug', 'error', 'level', 'file');
	    $db = array('name', 'failover', 'type', 'host', 'port', 'db', 'user', 'password');
	    $types['db2'] = $db;
	    $types['mysql'] = $db;
	    $types['odbc'] = $db;
	    $types['oracle'] = $db;
	    $types['sqlite'] = $db;
	    $types['tds'] = $db;
	    $types['standard'] = array('config', 'info', 'debug', 'error', 'level', 'output');
	    $types['syslog'] = array('config', 'info', 'debug', 'error', 'level');
	    /*
	     * Clean the informations for this id
	     */
	    $query = "DELETE FROM cfg_centreonbroker_info WHERE config_id = " . $id;
	    $pearDB->query($query);
	    /*
	     * Insert
	     */
	    $groups = array('output', 'input', 'logger');
	    $groups_infos['output'] = array();
	    $groups_infos['input'] = array();
	    $groups_infos['logger'] = array();
	    foreach ($groups as $group) {
	        /*
	         * Resort array
	         */
	        if (isset($values[$group])) {
        	    foreach ($values[$group] as $infos) {
        	        $groups_infos[$group][] = $infos;
        	    }
	        }
	    }

	    foreach ($groups_infos as $group => $groups) {
	        foreach ($groups as $gid => $infos) {
	            $gid = $gid + 1;
	            foreach ($types[$infos['type']] as $field) {
    	            if (isset($infos[$field])) {
    	                $query = "INSERT INTO cfg_centreonbroker_info (config_id, config_key, config_value, config_group, config_group_id)
    	            		VALUES (" . $id . ", '" . $field . "', '" . $infos[$field] . "', '" . $group . "', " . $gid . ")";
    	                file_put_contents('/tmp/log', $query, FILE_APPEND);
    	                $pearDB->query($query);
    	            }
    	        }
	        }
	    }
	}

	/**
	 * Duplicate a configuration
	 *
	 * @param array $ids List of id CentreonBroker configuration
	 * @param array $nbr List of number a duplication
	 */
	function multipleCentreonBrokerInDB($ids, $nbrDup)
	{
	    foreach($ids as $id => $value)	{
			global $pearDB;
			$DBRESULT = $pearDB->query("SELECT config_name, config_activate, ns_nagios_server FROM cfg_centreonbroker WHERE config_id = " . $id);
			$row = $DBRESULT->fetchRow();
			$DBRESULT->free();
			/*
			 * Prepare values
			 */
			$values = array();
			$values['activate']['activate'] = '0';
			$values['ns_nagios_server'] = $row['ns_nagios_server'];
			$query = "SELECT config_key, config_value, config_group, config_group_id
				FROM cfg_centreonbroker_info
				WHERE config_id = " . $id;
			$res = $pearDB->query($query);
    	    $values['output'] = array();
    	    $values['input'] = array();
    	    $values['logger'] = array();
    	    while ($rowOpt = $res->fetchRow()) {
    	        $values[$rowOpt['config_group']][$rowOpt['config_group_id']][$rowOpt['config_key']] = $rowOpt['config_value'];
    	    }
			/*
			 * Copy the configuration
			 */
			$j = 1;
			for ($i = 1; $i <= $nbrDup[$id]; $i++)	{
			    $nameNOk = true;
			    /*
			     * Find the name
			     */
			    while ($nameNOk) {
				    $newname = $row['config_name'] . '_' . $j;
				    $query = "SELECT COUNT(*) as nb FROM cfg_centreonbroker WHERE config_name = '" . $newname . "'";
				    $res = $pearDB->query($query);
				    $rowNb = $res->fetchRow();
				    if ($rowNb['nb'] == 0) {
				        $nameNOk = false;
				    }
				    $j++;
			    }
			    $values['name'] = $newname;
			    insertCentreonBrokerInDB($values);
			}
		}
	}
?>