<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

    if (!isset($oreon)) {
        exit();
    }
    
    /**
    *
    * Test broker file config existance
    * @param $name
    */
    function testExistence ($name = NULL)	{
    	global $pearDB, $form;
    	
    	$id = NULL;
    	
    	if (isset($form)){
    		$id = $form->getSubmitValue('id');
    	}
    	
    	$DBRESULT = $pearDB->query("SELECT config_name, config_id FROM `cfg_centreonbroker` WHERE `config_name` = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
    	$ndomod = $DBRESULT->fetchRow();
    	if ($DBRESULT->numRows() >= 1 && $ndomod["config_id"] == $id) {
    		return true;
    	} else if ($DBRESULT->numRows() >= 1 && $ndomod["config_id"] != $id) {
    		return false;
    	} else {
    		return true;
    	}
    }

    /**
     * Enable a Centreon Broker configuration
     *
     * @param int $id The Centreon Broker configuration in database
     */
    function enableCentreonBrokerInDB($id) {
        global $pearDB;

        if (!$id) {
        	return;
        }

        $query = "UPDATE cfg_centreonbroker SET config_activate = '1' WHERE config_id = " . $id;
        $pearDB->query($query);
    }

    /**
     * Disable a Centreon Broker configuration
     *
     * @param int $id The Centreon Broker configuration in database
     */
    function disablCentreonBrokerInDB($id) {
        global $pearDB;

        if (!$id) {
        	return;
        }

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

	    $query = "SELECT config_name, config_filename, config_write_timestamp, config_write_thread_id, config_activate, ns_nagios_server, event_queue_max_size
                      FROM cfg_centreonbroker 
                      WHERE config_id = " . $id;
	    $res = $pearDB->query($query);
	    if (PEAR::isError($res)) {
	        return array(
        		"name" => '',
                "filename" => '',
                "write_timestamp" => '1',
                "write_thread_id" => "1",
        		"activate" => '1',
                "event_queue_max_size" => ''
    		);
	    }
	    $row = $res->fetchRow();
	    return array(
	    		"id" => $id,
        		"name" => $row['config_name'],
                "filename" => $row['config_filename'],
                "write_timestamp" => $row['config_write_timestamp'],
                "write_thread_id" => $row['config_write_thread_id'],
        		"activate" =>  $row['config_activate'],
                "ns_nagios_server" => $row['ns_nagios_server'],
                "event_queue_max_size" => $row['event_queue_max_size']
	    );
	}

	/**
	 * Duplicate a configuration
	 *
	 * @param array $ids List of id CentreonBroker configuration
	 * @param array $nbr List of number a duplication
	 */
	function multipleCentreonBrokerInDB($ids, $nbrDup) {
	    foreach ($ids as $id => $value)	{
			global $pearDB;

			$cbObj = new CentreonConfigCentreonBroker($pearDB);

			$DBRESULT = $pearDB->query("SELECT config_name, config_filename, config_activate, ns_nagios_server, event_queue_max_size
                                                    FROM cfg_centreonbroker WHERE config_id = " . $id);
			$row = $DBRESULT->fetchRow();
			$DBRESULT->free();

			/*
			 * Prepare values
			 */
			$values = array();
			$values['activate']['activate'] = '0';
			$values['ns_nagios_server'] = $row['ns_nagios_server'];
                        $values['event_queue_max_size'] = $row['event_queue_max_size'];
			$query = "SELECT config_key, config_value, config_group, config_group_id
				FROM cfg_centreonbroker_info
				WHERE config_id = " . $id;
			$DBRESULT = $pearDB->query($query);
    	    $values['output'] = array();
    	    $values['input'] = array();
    	    $values['logger'] = array();
    	    while ($rowOpt = $DBRESULT->fetchRow()) {
    	        $values[$rowOpt['config_group']][$rowOpt['config_group_id']][$rowOpt['config_key']] = $rowOpt['config_value'];
    	    }
    	    $DBRESULT->free();
    	    /*
    	     * Convert values radio button
    	     */
    	    foreach ($values as $group => $groups) {
    	        foreach ($groups as $gid => $infos) {
    	            if (isset($infos['blockId'])) {
        	            list($tagId, $typeId) = explode('_', $infos['blockId']);
        	            $fieldtype = $cbObj->getFieldtypes($typeId);
    	            } else {
    	                $fieldtype = array();
    	            }
    	            foreach ($infos as $key => $value) {
    	                if (isset($fieldtype[$key]) && $fieldtype[$key] == 'radio') {
    	                    $values[$group][$gid][$key] = array($key => $value);
    	                }
    	            }
    	        }
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
				    $newfilename = $j . '_' . $row['config_filename'];
				    $query = "SELECT COUNT(*) as nb FROM cfg_centreonbroker WHERE config_name = '" . $newname . "'";
				    $res = $pearDB->query($query);
				    $rowNb = $res->fetchRow();
				    if ($rowNb['nb'] == 0) {
				        $nameNOk = false;
				    }
				    $j++;
			    }
			    $values['name'] = $newname;
			    $values['filename'] = $newfilename;
			    $cbObj->insertConfig($values);
			}
		}
	}
?>