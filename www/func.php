<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	
	# function 
	function get_path($abs_path){
		$len = strlen($abs_path);
		for ($i = 0, $flag = 0; $i < $len; $i++){
			if ($flag == 3)
				break;
			if ($abs_path{$i} == "/")
				$flag++;
		}
		return substr($abs_path, 0, $i);
	}
	
	function get_child($id_page, $lcaTStr){
		global $pearDB;
		$rq = "	SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
				FROM topology 
				WHERE  topology_page IN ($lcaTStr) 
				AND topology_parent = '".$id_page."' AND topology_page IS NOT NULL AND topology_show = '1' 
				ORDER BY topology_order, topology_group "; 
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$DBRESULT->fetchInto($redirect);
		return $redirect;
	}

	function reset_search_page($url){
		# Clean Vars
		global $oreon;
		if (!isset($url))
			return;
		if (isset($_GET["search"]) && isset($oreon->historySearch[$url]) && $_GET["search"] != $oreon->historySearch[$url]){		
			$_POST["num"] = 0;
			$_GET["num"] = 0;
		}	
	}

	function get_my_first_allowed_root_menu($lcaTStr){
		global $pearDB;
		$rq = "	SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
				FROM topology 
				WHERE topology_page IN ($lcaTStr) 
				AND topology_parent IS NULL AND topology_page IS NOT NULL AND topology_show = '1' 
				LIMIT 1"; 
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$root_menu = array();
		if ($DBRESULT->numRows())
			$DBRESULT->fetchInto($root_menu);
		return $root_menu;
	}
	
?>