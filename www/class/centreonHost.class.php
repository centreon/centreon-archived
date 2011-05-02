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

 /*
  *  Class that contains various methods for managing hosts
  */
 class CentreonHost
 {
 	protected $db;


 	/**
 	 * Constructor
 	 *
 	 * @param CentreonDB $db
 	 * @return void
 	 */
 	function __construct($db)
 	{
 		$this->db = $db;
 	}

	/**
	 * Method that returns a hostname from host_id
	 *
	 * @param int $host_id
	 * @return string
	 */
 	public function getHostName($host_id)
 	{
 		static $hosts = array();

 		if (!isset($host_id) || !$host_id) {
 		    return null;
 		}
 		if (!isset($hosts[$host_id])) {
     	    $rq = "SELECT host_name
     	    	   FROM host
     	    	   WHERE host_id = ".$this->db->escape($host_id)."
     	    	   LIMIT 1";
     		$res = $this->db->query($rq);
     		if ($res->numRows()) {
     		    $row = $res->fetchRow();
     		    $hosts[$host_id] = $row['host_name'];
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

 	    if (!isst($host_id) || !$host_id) {
 	        return null;
 	    }
 	    if (!isset($aliasTab[$host_id])) {
     	    $rq = "SELECT host_alias
     	    	   FROM host
     	    	   WHERE host_id = ".$this->db->escape($host_id)."
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
     	    	   WHERE host_id = ".$this->db->escape($host_id)."
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
     	    	   WHERE host_name = '".$this->db->escape($host_name)."'
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
 		$res = $this->db->query("SELECT illegal_object_name_chars FROM cfg_nagios");
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
 		       WHERE host_host_id = ".$this->db->escape($host_id)."
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
		$rq = "SELECT host_register FROM host WHERE host_id = '".$host_id."' LIMIT 1";
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
        }
        unset($row);

 		$matches = array();
 		$pattern = '|(\$_HOST[0-9a-zA-Z\_\-]+\$)|';
 		preg_match_all($pattern, $string, $matches);
 		$i = 0;
 		while (isset($matches[1][$i])) {
 			$rq = "SELECT host_macro_value FROM on_demand_macro_host WHERE host_host_id = '".$host_id."' AND host_macro_name LIKE '".$matches[1][$i]."'";
 			$DBRES = $this->db->query($rq);
	 		while ($row = $DBRES->fetchRow()) {
	 			$string = str_replace($matches[1][$i], $row['host_macro_value'], $string);
	 		}
 			$i++;
 		}
 		if ($i) {
	 		$rq2 = "SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = '".$host_id."' ORDER BY `order`";
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
}

?>