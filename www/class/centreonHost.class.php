<?php

/*
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

require_once $centreon_path . 'www/class/centreonInstance.class.php';
require_once $centreon_path . 'www/class/centreonService.class.php';

/*
 *  Class that contains various methods for managing hosts
 */

class CentreonHost
{
    /**
     *
     * @var type 
     */
    protected $db;
    
    /**
     *
     * @var type 
     */
    protected $instanceObj;
    
    /**
     *
     * @var type 
     */
    protected $serviceObj;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @return void
     */
    function __construct($db)
    {
        $this->db = $db;
        $this->instanceObj = new CentreonInstance($db);
        $this->serviceObj = new CentreonService($db);
    }

    /**
     * Get the list of all host
     *
     * @param bool $enable If get only host enable
     * @return array
     */
    public function getList($enable = false, $template = false)
    {
        $hostType = 1;
        if ($template) {
            $hostType = 0;
        }
        $queryList = "SELECT host_id, host_name
 	    	FROM host
 	    	WHERE host_register = '" . $hostType . "'";
        if ($enable) {
            $queryList .= " AND host_activate = '1'";
        }
        $queryList .= " ORDER BY host_name";
        $res = $this->db->query($queryList);
        if (PEAR::isError($res)) {
            return array();
        }
        $listHost = array();
        while ($row = $res->fetchRow()) {
            $listHost[$row['host_id']] = $row['host_name'];
        }
        return $listHost;
    }

    /**
     * Get the list of host children for a host
     *
     * @param int $hostId The parent host id
     * @param bool $withHg If use hostgroup relation (not use yet)
     * @return array
     */
    public function getHostChild($hostId, $withHg = False)
    {
        if (!is_numeric($hostId)) {
            return array();
        }
        $queryGetChildren = 'SELECT h.host_id, h.host_name
 	    	FROM host h, host_hostparent_relation hp
 	    	WHERE hp.host_host_id = h.host_id
 	    		AND h.host_register = "1"
 	    		AND h.host_activate = "1"
 	    		AND hp.host_parent_hp_id = ' . $hostId;
        $res = $this->db->query($queryGetChildren);
        if (PEAR::isError($res)) {
            return array();
        }
        $listHostChildren = array();
        while ($row = $res->fetchRow()) {
            $listHostChildren[$row['host_id']] = $row['host_alias'];
        }
        return $listHostChildren;
    }

    /**
     * Get the relation tree
     *
     * @param bool $withHg If use hostgroup relation (not use yet)
     * @return array
     */
    public function getHostRelationTree($withHg = False)
    {
        $queryGetRelationTree = 'SELECT hp.host_parent_hp_id, h.host_id, h.host_name
 	    	FROM host h, host_hostparent_relation hp
 	    	WHERE hp.host_host_id = h.host_id
 	    		AND h.host_register = "1"
 	    		AND h.host_activate = "1"';
        $res = $this->db->query($queryGetRelationTree);
        if (PEAR::isError($res)) {
            return array();
        }
        $listHostRelactionTree = array();
        while ($row = $res->fetchRow()) {
            if (!isset($listHostRelactionTree[$row['host_parent_hp_id']])) {
                $listHostRelactionTree[$row['host_parent_hp_id']] = array();
            }
            $listHostRelactionTree[$row['host_parent_hp_id']][$row['host_id']] = $row['host_alias'];
        }
        return $listHostRelactionTree;
    }

    /**
     * Get list of services for a host
     *
     * @param int $hostId The host id
     * @param bool $withHg If use hostgroup relation
     * @return array
     */
    public function getServices($hostId, $withHg = False)
    {
        /*
         * Get service for a host
         */
        $queryGetServices = 'SELECT s.service_id, s.service_description
 	    	FROM service s, host_service_relation hsr, host h
 	    	WHERE s.service_id = hsr.service_service_id
 	    		AND s.service_register = "1"
 	    		AND s.service_activate = "1"
 	    		AND h.host_id = hsr.host_host_id
 	    		AND h.host_register = "1"
 	    		AND h.host_activate = "1"
 	    		AND hsr.host_host_id = ' . CentreonDB::escape($hostId);
        $res = $this->db->query($queryGetServices);
        if (PEAR::isError($res)) {
            return array();
        }
        $listServices = array();
        while ($row = $res->fetchRow()) {
            $listServices[$row['service_id']] = $row['service_description'];
        }
        /*
         * With hostgroup
         */
        if ($withHg) {
            $queryGetServicesWithHg = 'SELECT s.service_id, s.service_description
     	    	FROM service s, host_service_relation hsr, hostgroup_relation hgr, host h, hostgroup hg
     	    	WHERE s.service_id = hsr.service_service_id
     	    		AND s.service_register = "1"
     	    		AND s.service_activate = "1"
     	    		AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
     	    		AND h.host_id = hgr.host_host_id
     	    		AND h.host_register = "1"
     	    		AND h.host_activate = "1"
     	    		AND hg.hg_id = hgr.hostgroup_hg_id
     	    		AND hg.hg_activate = "1"
     	    		AND hgr.host_host_id = ' . CentreonDB::escape($hostId);
            $res = $this->db->query($queryGetServices);
            if (PEAR::isError($res)) {
                return array();
            }
            while ($row = $res->fetchRow()) {
                $listServices[$row['service_id']] = $row['service_description'];
            }
        }
        return $listServices;
    }

    /**
     * Get the relation tree for host / service
     *
     * @param bool $withHg With Hostgroup
     * @return array
     */
    public function getHostServiceRelationTree($withHg = False)
    {
        /*
         * Get service for a host
         */
        $queryGetServices = 'SELECT hsr.host_host_id, s.service_id, s.service_description
                 	    	 FROM service s, host_service_relation hsr, host h
                 	    	 WHERE s.service_id = hsr.service_service_id
                			 AND s.service_register = "1"
                			 AND s.service_activate = "1"
                			 AND h.host_id = hsr.host_host_id
                			 AND h.host_register = "1"
                			 AND h.host_activate = "1" ';
        if ($withHg == true) {
            $queryGetServices .= ' UNION
								   SELECT hgr.host_host_id, s.service_id, s.service_description
								   FROM service s, host_service_relation hsr, host h, hostgroup_relation hgr
							       WHERE s.service_id = hsr.service_service_id
							  	   AND s.service_register =  "1"
							  	   AND s.service_activate =  "1"
							 	   AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
							 	   AND hgr.host_host_id = h.host_id
							 	   AND h.host_register =  "1"
							 	   AND h.host_activate =  "1"';
        }
        $res = $this->db->query($queryGetServices);
        if (PEAR::isError($res)) {
            return array();
        }
        $listServices = array();
        while ($row = $res->fetchRow()) {
            if (!isset($listServices[$row['host_host_id']])) {
                $listServices[$row['host_host_id']] = array();
            }
            $listServices[$row['host_host_id']][$row['service_id']] = $row['service_description'];
        }
        return $listServices;
    }

    /**
     * Method that returns a hostname from host_id
     *
     * @param int $host_id
     * @return string
     */
    public function getHostName($host_id)
    {
        static $hosts = null;

        if (!isset($host_id) || !$host_id) {
            return null;
        }

        if (is_null($hosts)) {
            $hosts = array();

            $rq = "SELECT host_id, host_name
     	    	   FROM host";
            $res = $this->db->query($rq);
            while ($row = $res->fetchRow()) {
                $hosts[$row['host_id']] = $row['host_name'];
            }
        }
        if (isset($hosts[$host_id])) {
            return $hosts[$host_id];
        }
        return null;
    }

    /**
     * Method that returns a host alias from host_id
     *
     * @param int $host_id
     * @return string
     */
    public function getHostAlias($host_id)
    {
        static $aliasTab = array();

        if (!isset($host_id) || !$host_id) {
            return null;
        }
        if (!isset($aliasTab[$host_id])) {
            $rq = "SELECT host_alias
     	    	   FROM host
     	    	   WHERE host_id = " . $this->db->escape($host_id) . "
     	    	   LIMIT 1";
            $res = $this->db->query($rq);
            if ($res->numRows()) {
                $row = $res->fetchRow();
                $aliasTab[$host_id] = $row['host_alias'];
            }
        }
        if (isset($aliasTab[$host_id])) {
            return $aliasTab[$host_id];
        }
        return null;
    }

    /**
     * Method that returns a host address from host_id
     *
     * @param int $host_id
     * @return string
     */
    public function getHostAddress($host_id)
    {
        static $addrTab = array();

        if (!isset($host_id) || !$host_id) {
            return null;
        }
        if (!isset($addrTab[$host_id])) {
            $rq = "SELECT host_address
     	    	   FROM host
     	    	   WHERE host_id = " . $this->db->escape($host_id) . "
     	    	   LIMIT 1";
            $res = $this->db->query($rq);
            if ($res->numRows()) {
                $row = $res->fetchRow();
                $addrTab[$host_id] = $row['host_address'];
            }
        }
        if (isset($addrTab[$host_id])) {
            return $addrTab[$host_id];
        }
        return null;
    }

    /**
     * Method that returns the id of a host
     *
     * @param string $host_name
     * @return int
     */
    public function getHostId($host_name)
    {
        static $ids = array();

        if (!isset($host_name) || !$host_name) {
            return null;
        }
        if (!isset($ids[$host_name])) {
            $rq = "SELECT host_id
     	    	   FROM host
     	    	   WHERE host_name = '" . $this->db->escape($host_name) . "'
     	    	   LIMIT 1";
            $res = $this->db->query($rq);
            if ($res->numRows()) {
                $row = $res->fetchRow();
                $ids[$host_name] = $row['host_id'];
            }
        }
        if (isset($ids[$host_name])) {
            return $ids[$host_name];
        }
        return null;
    }

    /**
     * Check illegal char defined into nagios.cfg file
     *
     * @param string $host_name
     * @param int $poller_id
     * @return string
     */
    public function checkIllegalChar($host_name, $poller_id = null)
    {
        if($poller_id){
            $res = $this->db->query("SELECT illegal_object_name_chars FROM cfg_nagios where nagios_server_id = " . $this->db->escape($poller_id));
        }else{
            $res = $this->db->query("SELECT illegal_object_name_chars FROM cfg_nagios ");
        }
        
        while ($data = $res->fetchRow()) {
            $tab = str_split(html_entity_decode($data['illegal_object_name_chars'], ENT_QUOTES, "UTF-8"));
            foreach ($tab as $char) {
                $host_name = str_replace($char, "", $host_name);
            }
        }
        $res->free();
        return $host_name;
    }

    /**
     * Method that returns the poller id that monitors the host
     *
     * @param int $host_id
     * @return int
     */
    public function getHostPollerId($host_id)
    {
        $rq = "SELECT nagios_server_id
 		       FROM ns_host_relation
 		       WHERE host_host_id = " . $this->db->escape($host_id) . "
 		       LIMIT 1";
        $res = $this->db->query($rq);
        if (!$res->numRows()) {
            return null;
        }
        $row = $res->fetchRow();
        return $row['nagios_server_id'];
    }

    /**
     * Returns a string that replaces on demand macros by their values
     *
     * @param mixed $hostParam
     * @param string $string
     * @param int $antiLoop
     * @return string
     */
    public function replaceMacroInString($hostParam, $string, $antiLoop = null)
    {
        if (is_numeric($hostParam)) {
            $host_id = $hostParam;
        } elseif (is_string($hostParam)) {
            $host_id = $this->getHostId($hostParam);
        } else {
            return $string;
        }
        $rq = "SELECT host_register FROM host WHERE host_id = '" . CentreonDB::escape($host_id) . "' LIMIT 1";
        $res = $this->db->query($rq);
        if (!$res->numRows()) {
            return $string;
        }
        $row = $res->fetchRow();

        /*
         * replace if not template
         */
        if ($row['host_register'] == 1) {
            if (strpos($string, "\$HOSTADDRESS$")) {
                $string = str_replace("\$HOSTADDRESS\$", $this->getHostAddress($host_id), $string);
            }
            if (strpos($string, "\$HOSTNAME$")) {
                $string = str_replace("\$HOSTNAME\$", $this->getHostName($host_id), $string);
            }
            if (strpos($string, "\$HOSTALIAS$")) {
                $string = str_replace("\$HOSTALIAS\$", $this->getHostAlias($host_id), $string);
            }
            if (preg_match("\$INSTANCENAME\$", $string)) {
                $string = str_replace("\$INSTANCENAME\$", $this->instanceObj->getParam($this->getHostPollerId($host_id), 'name'), $string);
            }
            if (preg_match("\$INSTANCEADDRESS\$", $string)) {
                $string = str_replace("\$INSTANCEADDRESS\$", $this->instanceObj->getParam($this->getHostPollerId($host_id), 'ns_ip_address'), $string);
            }
        }
        unset($row);

        $matches = array();
        $pattern = '|(\$_HOST[0-9a-zA-Z\_\-]+\$)|';
        preg_match_all($pattern, $string, $matches);
        $i = 0;
        while (isset($matches[1][$i])) {
            $rq = "SELECT host_macro_value FROM on_demand_macro_host WHERE host_host_id = '" . $host_id . "' AND host_macro_name LIKE '" . $matches[1][$i] . "'";
            $DBRES = $this->db->query($rq);
            while ($row = $DBRES->fetchRow()) {
                $string = str_replace($matches[1][$i], $row['host_macro_value'], $string);
            }
            $i++;
        }
        if ($i) {
            $rq2 = "SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = '" . $host_id . "' ORDER BY `order`";
            $DBRES2 = $this->db->query($rq2);
            while ($row2 = $DBRES2->fetchRow()) {
                if (!isset($antiLoop) || !$antiLoop) {
                    $string = $this->replaceMacroInString($row2['host_tpl_id'], $string, $row2['host_tpl_id']);
                } elseif ($row2['host_tpl_id'] != $antiLoop) {
                    $string = $this->replaceMacroInString($row2['host_tpl_id'], $string);
                }
            }
        }
        return $string;
    }
    
    /**
     * Insert macro
     * 
     * @param int $hostId
     * @param array $macroInput
     * @param array $macroValue
     * @param array $macroPassword
     * @param array $macroDescription
     * @param bool $isMassiveChange
     * 
     * @return void
     */
    public function insertMacro(
        $hostId,
        $macroInput = array(),
        $macroValue = array(),
        $macroPassword = array(),
        $macroDescription = array(),
        $isMassiveChange = false,
        $cmdId = false
    ) {
        
        if (false === $isMassiveChange) {
            $this->db->query("DELETE FROM on_demand_macro_host 
                WHERE host_host_id = " . $this->db->escape($hostId));
        } else {
            $macroList = "";
            foreach ($macroInput as $v) {
                $macroList .= "'\$_HOST" . strtoupper($this->db->escape($v)) . "\$',";
            }
            if ($macroList) {
                $macroList = rtrim($macroList, ",");
                $this->db->query("DELETE FROM on_demand_macro_host
                    WHERE host_host_id = " . $this->db->escape($hostId) . "
                    AND host_macro_name IN ({$macroList})"
                );
            }
        }


        $stored = array();
        $cnt = 0;
        $macros = $macroInput;
        $macrovalues = $macroValue;
        $this->hasMacroFromHostChanged($hostId,$macros,$macrovalues,$cmdId);
        foreach ($macros as $key => $value) {
            if ($value != "" &&
                    !isset($stored[strtolower($value)])) {
                $this->db->query("INSERT INTO on_demand_macro_host (`host_macro_name`, `host_macro_value`, `is_password`, `description`, `host_host_id`, `macro_order`) 
                                VALUES ('\$_HOST" . strtoupper($this->db->escape($value)) . "\$', '" . $this->db->escape($macrovalues[$key]) . "', " . (isset($macroPassword[$key]) ? 1 : 'NULL') . ", '".$this->db->escape($macroDescription[$key])."', ". $this->db->escape($hostId) . ", " . $cnt. ")");
                $cnt ++;
                $stored[strtolower($value)] = true;
            }
        }
    }

    public function getCustomMacroInDb($hostId = null, $template = null)
    {
        $arr = array();
        $i = 0;
       
        if ($hostId) {
            $sSql = "SELECT host_macro_name, host_macro_value, is_password, description
                                FROM on_demand_macro_host
                                WHERE host_host_id = " . intval($hostId) . " ORDER BY macro_order ASC";

            $res = $this->db->query($sSql);
            
            while ($row = $res->fetchRow()) {
                if (preg_match('/\$_HOST(.*)\$$/', $row['host_macro_name'], $matches)) {
                    $arr[$i]['macroInput_#index#'] = $matches[1];
                    $arr[$i]['macroValue_#index#'] = $row['host_macro_value'];
                    $arr[$i]['macroPassword_#index#'] = $row['is_password'] ? 1 : NULL;
                    $arr[$i]['macroDescription_#index#'] = $row['description'];
                    $arr[$i]['macroDescription'] = $row['description'];
                    if(!is_null($template)){
                        $arr[$i]['macroTpl_#index#'] = $template['host_name'];
                    }
                    
                    
                    $i++;
                }
            }
        }
        return $arr;
    }
    
    
    /**
     * Get host custom macro
     * 
     * @param int $hostId
     * @return array
     */
    public function getCustomMacro($hostId = null)
    {
        $arr = array();
        $i = 0;
       
        if (!isset($_REQUEST['macroInput']) && $hostId) {
            $sSql = "SELECT host_macro_name, host_macro_value, is_password, description
                                FROM on_demand_macro_host
                                WHERE host_host_id = " . intval($hostId) . " ORDER BY macro_order ASC";

            $res = $this->db->query($sSql);
            
            while ($row = $res->fetchRow()) {
                if (preg_match('/\$_HOST(.*)\$$/', $row['host_macro_name'], $matches)) {
                    $arr[$i]['macroInput_#index#'] = $matches[1];
                    $arr[$i]['macroValue_#index#'] = $row['host_macro_value'];
                    $arr[$i]['macroPassword_#index#'] = $row['is_password'] ? 1 : NULL;
                    $arr[$i]['macroDescription_#index#'] = $row['description'];
                    $arr[$i]['macroDescription'] = $row['description'];
                    $i++;
                }
            }
        } elseif (isset($_REQUEST['macroInput'])) {
            foreach ($_REQUEST['macroInput'] as $key => $val) {
                $arr[$i]['macroInput_#index#'] = $val;
                $arr[$i]['macroValue_#index#'] = $_REQUEST['macroValue'][$key];
                $arr[$i]['macroPassword_#index#'] = isset($_REQUEST['is_password'][$key]) ? 1 : NULL;
                $arr[$i]['macroDescription_#index#'] = isset($_REQUEST['description'][$key]) ? $_REQUEST['description'][$key] : NULL;
                $arr[$i]['macroDescription'] = isset($_REQUEST['description'][$key]) ? $_REQUEST['description'][$key] : NULL;
                $i++;
            }
        }
        return $arr;
    }

    /**
     * Get list of template linked to a given host
     * 
     * @param int $hostId
     * @return array
     */
    public function getTemplates($hostId = null)
    {
        $arr = array();
        $i = 0;
        if (!isset($_REQUEST['tpSelect']) && $hostId) {
            $res = $this->db->query("SELECT host_tpl_id
                                FROM host_template_relation
                                WHERE host_host_id = " .
                    $this->db->escape($hostId) . "
                                ORDER BY `order`");
            while ($row = $res->fetchRow()) {
                $arr[$i]['tpSelect_#index#'] = $row['host_tpl_id'];
                $i++;
            }
        } else {
            if (isset($_REQUEST['tpSelect'])) {
                foreach ($_REQUEST['tpSelect'] as $val) {
                    $arr[$i]['tpSelect_#index#'] = $val;
                    $i++;
                }
            }
        }
        return $arr;
    }

    /**
     * Set templates
     * 
     * @param int $hostId
     * @param array $templates
     * @return void
     */
    public function setTemplates($hostId, $templates = array(), $remaining = array())
    {
        $sql = "DELETE FROM host_template_relation 
                WHERE host_host_id = " . $this->db->escape($hostId);
        $stored = array();
        if (count($remaining)) {
            $sql .= " AND host_tpl_id NOT IN (" . implode(',', $remaining) . ") ";
            $stored = $remaining;
        }
        $this->db->query($sql);

        $str = "";
        $i = 1;
        foreach ($templates as $templateId) {
            if (!isset($templateId) || !$templateId || isset($stored[$templateId]) || !hasNoInfiniteLoop($hostId, $templateId)) {
                continue;
            }
            if ($str != "") {
                $str .= ", ";
            }
            $str .= "({$this->db->escape($hostId)}, {$this->db->escape($templateId)}, {$i})";
            $stored[$templateId] = true;
            $i++;
        }
        if ($str) {
            $this->db->query("INSERT INTO host_template_relation (host_host_id, host_tpl_id, `order`) VALUES $str");
        }
    }

    /**
     * Get Host contactgroup list
     * 
     * @param int $host_id
     * @param array $cg
     * @return void
     */
    public function getContactGroupList($host_id, $cg)
    {
        $request = "SELECT * FROM host";
        $res = $this->db->query($sql);
        while ($data = $res->fetchRow()) {
            
        }
        return $cg;

        $rq = "SELECT host_tpl_id " .
                "FROM host_template_relation " .
                "WHERE host_host_id = '" . CentreonDB::escape($host_id) . "' " .
                "ORDER BY `order`";
        $DBRESULT = $pearDB->query($rq);
        while ($row = $DBRESULT->fetchRow()) {
            $rq2 = "SELECT $field " .
                    "FROM host " .
                    "WHERE host_id = '" . $row['host_tpl_id'] . "' LIMIT 1";
            $DBRESULT2 = $pearDB->query($rq2);
            $row2 = $DBRESULT2->fetchRow();
            if (isset($row2[$field]) && $row2[$field])
                return $row2[$field];
            else {
                if ($result_field = getMyHostFieldFromMultiTemplates($row['host_tpl_id'], $field)) {
                    return $result_field;
                }
            }
        }
    }   
    
    public function hasMacroFromHostChanged($host_id,&$macroInput,&$macroValue,$cmdId = false)
    {
        $aTemplates = $this->getTemplateChain($host_id, array(), -1, false);

        if (!isset($cmdId)) {
            $cmdId = "";
        }
        $aMacros = $this->getMacros($host_id, false, $aTemplates, $cmdId);
        foreach($aMacros as $macro){
            foreach($macroInput as $ind=>$input){
                
                if($input == $macro['macroInput_#index#'] && $macroValue[$ind] == $macro["macroValue_#index#"]){
                    unset($macroInput[$ind]);
                    unset($macroValue[$ind]);
                }
            }
        }
    }
    
    /**
     * This method get the macro attached to the host
     * 
     * @param int $iHostId
     * @param int $bIsTemplate
     * @param array $aListTemplate
     * @param int $iIdCommande
     * @return array
     */
    public function getMacros($iHostId, $bIsTemplate, $aListTemplate, $iIdCommande)
    {
        $aMacro = array();
        $macroArray = array();
        $aMacroInCommande = array();
        $aMacroInService = array();
        
        //Get macro attached to the host
        $macroArray = $this->getCustomMacroInDb($iHostId);
        $iNb = count($macroArray);

        //Get macro attached to the template
        $aMacroTemplate = array();
        foreach ($aListTemplate as $template) {
            if (!empty($template['host_id'])) {
                $aMacroTemplate[] = $this->getCustomMacroInDb($template['host_id'],$template);
            }
        }

        
        //Get macro attached to the command        
        if (!empty($iIdCommande)) {
            $oCommand = new CentreonCommand($this->db);
            $aMacroInCommande[] = $oCommand->getMacroByIdAndType($iIdCommande, 'host');
        }
        
        // finaly we don't want macro from service attached to the host.
        /*if (!$bIsTemplate) {
            $aServices = $this->getServices($iHostId);
            if (count($aServices) > 0) {
                $oService = new CentreonService($this->db);
                foreach ($aServices as $serviceId=>$service) {
                    $aMacroInService[] = $oService->getCustomMacroInDb($serviceId);
                }
            }
        }*/

        //filter a macro
        $aTempMacro = array();
        if (count($macroArray) > 0) {
            foreach($macroArray as $directMacro){
                $directMacro['macroOldValue_#index#'] = $directMacro["macroValue_#index#"];
                $directMacro['macroFrom_#index#'] = 'direct';
                $directMacro['source'] = 'direct';
                $aTempMacro[] = $directMacro;
            }
        }
        
        $iNb = count($aTempMacro);
        
        if (count($aMacroTemplate) > 0) {  
            foreach ($aMacroTemplate as $key => $macr) {
                foreach ($macr as $mm) {
                    $mm['macroOldValue_#index#'] = $mm["macroValue_#index#"];
                    $mm['macroFrom_#index#'] = 'fromTpl';
                    $mm['source'] = 'fromTpl';
                    $aTempMacro[$iNb++] = $mm;
                }
            }
        }
        
        
        if (count($aMacroInCommande) > 0) {
            $macroCommande = current($aMacroInCommande);
            for ($i = 0; $i < count($macroCommande); $i++) {
                $macroCommande[$i]['macroOldValue_#index#'] = $macroCommande[$i]["macroValue_#index#"];
                $macroCommande[$i]['macroFrom_#index#'] = 'fromCommand';
                $macroCommande[$i]['source'] = 'fromCommand';
                $aTempMacro[$iNb++] = $macroCommande[$i];
            }
        }

        /*if (count($aMacroInService) > 0) {
            foreach ($aMacroInService as $key => $macr) {
                foreach ($macr as $mm) {
                    $mm['source'] = 'fromService';
                    $aTempMacro[$iNb++] = $mm;
                }
            }
        }*/
       
        $aFinalMacro = macro_unique($aTempMacro);

        return $aFinalMacro;
    }
    
    /**
     * Get template chain (id, text)
     *
     * @param int $hostId The host or host template Id
     * @param array $alreadyProcessed The host templates already processed
     * @param int $depth The depth to search
     * @return array
     */
    public function getTemplateChain($hostId, $alreadyProcessed = array(), $depth = -1, $allFields = false)
    {
        $templates = array();
        
        if (($depth == -1) || ($depth > 0)) {
            if ($depth > 0) {
                $depth--;
            }
            if (in_array($hostId, $alreadyProcessed)) {
                return $templates;
            } else {
                $alreadyProcessed[] = $hostId;

                if(!$allFields){
                    $fields = "h.host_id, h.host_name";
                }else{
                    $fields = " * ";
                }
                
                $sql = "SELECT " . $fields . " " 
                    . " FROM host h, host_template_relation htr"
                    . " WHERE h.host_id = htr.host_tpl_id"
                    . " AND htr.host_host_id = '". CentreonDB::escape($hostId) ."'"
                    . " AND host_activate = '1'"
                    . " AND host_register = '0'"
                    . " ORDER BY `order` ASC";
                
                $DBRESULT = $this->db->query($sql);

                while ($row = $DBRESULT->fetchRow()) {
                    if(!$allFields){
                        $templates[] = array(
                            "id" => $row['host_id'],
                            "host_id" => $row['host_id'],
                            "host_name" => $row['host_name']
                        );
                    } else{
                        $templates[] = $row;
                    }
                    
                    $templates = array_merge($templates, $this->getTemplateChain($row['host_id'], $alreadyProcessed, $depth, $allFields));
                }
                return $templates;
            }
        }
        return $templates;
    }

    /**
     * Returns array of locked host templates
     *
     * @return array
     */
    public function getLockedHostTemplates()
    {
        static $arr = null;

        if (is_null($arr)) {
            $arr = array();
            $res = $this->db->query("SELECT host_id FROM host WHERE host_locked = 1");
            while ($row = $res->fetchRow()) {
                $arr[$row['host_id']] = true;
            }
        }
        return $arr;
    }
    
    public function ajaxMacroControl($form){

        $macroArray = $this->getCustomMacro();
        $indexToSub = $this->purgeOldMacroToForm(&$macroArray,&$form,'fromTpl');
        $aListTemplate = array();
        foreach($form['tpSelect'] as $templates){
            $tmpTpl = array_merge(array(array('host_id' => $templates)),$this->getTemplateChain($templates, array(), -1, false));
            $aListTemplate = array_merge($aListTemplate,$tmpTpl);
        }
        
        
        $aMacroTemplate = array();
        foreach ($aListTemplate as $template) {
            if (!empty($template['host_id'])) {
                $aMacroTemplate = array_merge($aMacroTemplate,$this->getCustomMacroInDb($template['host_id'],$template));
            }
        }
        
        $iIdCommande = $form['command_command_id'];
        
        $aMacroInCommande = array();
        //Get macro attached to the command        
        if (!empty($iIdCommande) && is_numeric($iIdCommande)) {
            $oCommand = new CentreonCommand($this->db);
            $aMacroInCommande[] = $oCommand->getMacroByIdAndType($iIdCommande, 'host');
        }
    
        
        $this->purgeOldMacroToForm(&$macroArray,&$form,'fromCommand',$aMacroInCommande);
        
        //filter a macro
        $aTempMacro = array();
        if (count($macroArray) > 0) {
            foreach($macroArray as $key=>$directMacro){
                $directMacro['macroOldValue_#index#'] = $directMacro["macroValue_#index#"];
                $directMacro['macroFrom_#index#'] = $form['macroFrom'][$key - $indexToSub];
                $directMacro['source'] = 'direct';
                $aTempMacro[] = $directMacro;
            }
        }
        
        $iNb = count($aTempMacro);
        
        if (count($aMacroTemplate) > 0) {  
            foreach ($aMacroTemplate as $key => $macr) {
                //foreach ($macr as $mm) {
                    $macr['macroOldValue_#index#'] = $macr["macroValue_#index#"];
                    $macr['macroFrom_#index#'] = 'fromTpl';
                    $macr['source'] = 'fromTpl';
                    $aTempMacro[$iNb++] = $macr;
                //}
            }
        }
        
        
        
        
        if (count($aMacroInCommande) > 0) {
            $macroCommande = current($aMacroInCommande);
            for ($i = 0; $i < count($macroCommande); $i++) {
                $macroCommande[$i]['macroOldValue_#index#'] = $macroCommande[$i]["macroValue_#index#"];
                $macroCommande[$i]['macroFrom_#index#'] = 'fromCommand';
                $macroCommande[$i]['source'] = 'fromCommand';
                $aTempMacro[$iNb++] = $macroCommande[$i];
            }
        }

        /*if (count($aMacroInService) > 0) {
            foreach ($aMacroInService as $key => $macr) {
                foreach ($macr as $mm) {
                    $mm['source'] = 'fromService';
                    $aTempMacro[$iNb++] = $mm;
                }
            }
        }*/
       
        $aFinalMacro = macro_unique($aTempMacro);
        return $aFinalMacro;
        
    }
    
    public function purgeOldMacroToForm(&$macroArray,&$form,$fromKey,$macrosArrayToCompare = null){
        
        
        if(isset($form["macroInput"]["#index#"])){
            unset($form["macroInput"]["#index#"]); 
        }
        if(isset($form["macroValue"]["#index#"])){
            unset($form["macroValue"]["#index#"]); 
        }
        $indexToSub = 0;
        if(isset($form["macroFrom"]["#index#"])){
            $indexToSub = 1;
        }
        
        
        
        foreach($macroArray as $key=>$macro){
            if($macro["macroInput_#index#"] == ""){
                unset($macroArray[$key]);
            }
        }
        
        if(is_null($macrosArrayToCompare)){
            foreach($macroArray as $key=>$macro){
                if($form['macroFrom'][$key - $indexToSub] == $fromKey){
                    unset($macroArray[$key]);
                }
            }
        }else{
            $inputIndexArray = array();
            foreach($macrosArrayToCompare as $tocompare){
                if (isset($tocompare['macroInput_#index#'])) {
                    $inputIndexArray[] = $tocompare['macroInput_#index#'];
                }
            }
            foreach($macroArray as $key=>$macro){
                if($form['macroFrom'][$key - $indexToSub] == $fromKey){
                    if(!in_array($macro['macroInput_#index#'],$inputIndexArray)){
                        unset($macroArray[$key]);
                    }
                }
            }
        }
        return $indexToSub;

    }
    
    
    /**
     * 
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'host';
        $parameters['currentObject']['id'] = 'host_id';
        $parameters['currentObject']['name'] = 'host_name';
        $parameters['currentObject']['comparator'] = 'host_id';

        switch ($field) {
            case 'timeperiod_tp_id':
            case 'timeperiod_tp_id2':
                $parameters['type'] = 'simple';
                $parameters['externalObject']['table'] = 'timeperiod';
                $parameters['externalObject']['id'] = 'tp_id';
                $parameters['externalObject']['name'] = 'tp_name';
                $parameters['externalObject']['comparator'] = 'tp_id';
                break;
            case 'command_command_id':
            case 'command_command_id2':
                $parameters['type'] = 'simple';
                $parameters['externalObject']['table'] = 'command';
                $parameters['externalObject']['id'] = 'command_id';
                $parameters['externalObject']['name'] = 'command_name';
                $parameters['externalObject']['comparator'] = 'command_id';
                break;
            case 'host_cs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'contact';
                $parameters['externalObject']['id'] = 'contact_id';
                $parameters['externalObject']['name'] = 'contact_name';
                $parameters['externalObject']['comparator'] = 'contact_id';
                $parameters['relationObject']['table'] = 'contact_host_relation';
                $parameters['relationObject']['field'] = 'contact_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_parents':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'host';
                $parameters['externalObject']['id'] = 'host_id';
                $parameters['externalObject']['name'] = 'host_name';
                $parameters['externalObject']['comparator'] = 'host_id';
                $parameters['relationObject']['table'] = 'host_hostparent_relation';
                $parameters['relationObject']['field'] = 'host_parent_hp_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_childs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'host';
                $parameters['externalObject']['id'] = 'host_id';
                $parameters['externalObject']['name'] = 'host_name';
                $parameters['externalObject']['comparator'] = 'host_id';
                $parameters['relationObject']['table'] = 'host_hostparent_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['comparator'] = 'host_parent_hp_id';
                break;
            case 'host_hgs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'hostgroup';
                $parameters['externalObject']['id'] = 'hg_id';
                $parameters['externalObject']['name'] = 'hg_name';
                $parameters['externalObject']['comparator'] = 'hg_id';
                $parameters['relationObject']['table'] = 'hostgroup_relation';
                $parameters['relationObject']['field'] = 'hostgroup_hg_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_hcs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'hostcategories';
                $parameters['externalObject']['id'] = 'hc_id';
                $parameters['externalObject']['name'] = 'hc_name';
                $parameters['externalObject']['comparator'] = 'hc_id';
                $parameters['relationObject']['table'] = 'hostcategories_relation';
                $parameters['relationObject']['field'] = 'hostcategories_hc_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_hcs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'hostcategories';
                $parameters['externalObject']['id'] = 'hc_id';
                $parameters['externalObject']['name'] = 'hc_name';
                $parameters['externalObject']['comparator'] = 'hc_id';
                $parameters['relationObject']['table'] = 'hostcategories_relation';
                $parameters['relationObject']['field'] = 'hostcategories_hc_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
           case 'host_cgs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'contactgroup';
                $parameters['externalObject']['id'] = 'cg_id';
                $parameters['externalObject']['name'] = 'cg_name';
                $parameters['externalObject']['comparator'] = 'cg_id';
                $parameters['relationObject']['table'] = 'contactgroup_host_relation';
                $parameters['relationObject']['field'] = 'contactgroup_cg_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_svTpls':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'service';
                $parameters['externalObject']['id'] = 'service_id';
                $parameters['externalObject']['name'] = 'service_description';
                $parameters['externalObject']['comparator'] = 'service_id';
                $parameters['relationObject']['table'] = 'host_service_relation';
                $parameters['relationObject']['field'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
        }
        
        return $parameters;
    }
    
    /**
     * Get list of services template for a host template
     *
     * @param int $hostTplId The host template id

     * @return array
     */
    public function getServicesTplInHostTpl($hostTplId)
    {
        /*
         * Get service for a host
         */
        $queryGetServices = 'SELECT s.service_id, s.service_description, s.service_alias
 	    	FROM service s, host_service_relation hsr, host h
 	    	WHERE s.service_id = hsr.service_service_id
 	    		AND s.service_register = "0"
 	    		AND s.service_activate = "1"
 	    		AND h.host_id = hsr.host_host_id
 	    		AND h.host_register = "0"
 	    		AND h.host_activate = "1"
 	    		AND hsr.host_host_id = ' . CentreonDB::escape($hostTplId);
        
        
        $res = $this->db->query($queryGetServices);
        if (PEAR::isError($res)) {
            return array();
        }
        $listServices = array();
        while ($row = $res->fetchRow()) {
            $listServices[$row['service_id']] = array("service_description" => $row['service_description'], "service_alias" => $row['service_alias']);
        }
       
        return $listServices;
    }
    
    
    /**
     * Deploy services
     * Recursive method
     *
     * @param int $hostId
     * @param mixed $hostTemplateId
     * @return void
     */
    public function deployServices($hostId, $hostTemplateId = null)
    {
        if (!isset($hostTemplateId)) {
            $id = $hostId;
        } else {
            $id = $hostTemplateId;
        }
        $templates = $this->getTemplateChain($id);

        foreach ($templates as $templateId) {
            $serviceTemplates = $this->getServicesTplInHostTpl($templateId['id']);
     
            foreach ($serviceTemplates as $serviceTemplateId => $service) {
                $sql = "SELECT service_id
                		FROM service s, host_service_relation hsr
                		WHERE s.service_id = hsr.service_service_id
                		AND s.service_description = '" .CentreonDB::escape($service['service_alias']). "'
                		AND hsr.host_host_id = '" . intval($hostId). "'
                		UNION
                		SELECT service_id
                		FROM service s, host_service_relation hsr
                		WHERE s.service_id = hsr.service_service_id
                		AND s.service_description = '" . CentreonDB::escape($service['service_alias']). "'
                		AND hsr.hostgroup_hg_id IN (SELECT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '" . intval($hostId). "')";
                
                $res = $this->db->query($sql);

                if (!$res->numRows()) {
                    $svcId = $this->serviceObj->insert(
                            array(
                                'service_description' => $service['service_alias'],
                                'service_activate' => '1',
                                'service_register' => '1',
                                'service_template_model_stm_id' => $serviceTemplateId
                            )
                    );
                    
                    $this->insertRelHostService($hostId, $svcId);

                    $this->serviceObj->insertExtendInfo(array('service_service_id' => $svcId));
                }
                unset($res);
            }
            $this->deployServices($hostId, $templateId['id']);
        }
    }
        
    /**
     * 
     * @param type $iHostId
     * @param type $iServiceId
     * @return type
     */
    public function insertRelHostService($iHostId, $iServiceId)
    {
       
        if (empty($iHostId) || empty($iServiceId)) {
            return;
        }
        $rq = "INSERT INTO host_service_relation ";
        $rq .= "(host_host_id, service_service_id) ";
        $rq .= "VALUES ";
        $rq .= "('".$iHostId."', '".$iServiceId."')";
       
        $DBRESULT = $this->db->query($rq);
    }
}

?>
