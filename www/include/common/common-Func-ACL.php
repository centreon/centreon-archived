<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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

	/* LCA Generation */
	
	function getLCASG($pearDB){
		if (!$pearDB)
			return ;
		if (session_id() == "") $uid = $_POST["sid"] ; else $uid = session_id();
		$DBRESULT =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$uid."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$DBRESULT->fetchInto($user);
		$user_id = $user["user_id"];	
		$lcaServiceGroup = array();
		$DBRESULT =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$user_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			while($DBRESULT->fetchInto($contactGroup))	{
			 	$DBRESULT1 =& $pearDB->query("SELECT lca.lca_id, lca.lca_hg_childs FROM lca_define_contactgroup_relation ldcgr, lca_define lca WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."' AND ldcgr.lca_define_lca_id = lca.lca_id AND lca.lca_activate = '1'");	
				if (PEAR::isError($DBRESULT1))
					print "DB Error : ".$DBRESULT1->getDebugInfo()."<br>";
				 if ($DBRESULT1->numRows())
					while ($DBRESULT1->fetchInto($lca))	{
						$DBRESULT2 =& $pearDB->query("SELECT sg_id, sg_name FROM servicegroup, lca_define_servicegroup_relation WHERE lca_define_lca_id = '".$lca["lca_id"]."' AND sg_id = servicegroup_sg_id");	
						if (PEAR::isError($DBRESULT2))
							print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
						while ($DBRESULT2->fetchInto($serviceGroup))
							$lcaServiceGroup[$serviceGroup["sg_id"]] = $serviceGroup["sg_name"];
						unset($DBRESULT2);
					}
			}
		}
		return $lcaServiceGroup;
	}
	
	function getLCAHostByID($pearDB){
		if (!$pearDB)
			return ;
		if (session_id() == "") $uid = $_POST["sid"]; else $uid = session_id();
		$DBRESULT =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$uid."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$DBRESULT->fetchInto($user);
		$user_id = $user["user_id"];
		$DBRESULT =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$user_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			while($DBRESULT->fetchInto($contactGroup))	{
			 	$DBRESULT2 =& $pearDB->query("SELECT lca.lca_id, lca.lca_hg_childs FROM lca_define_contactgroup_relation ldcgr, lca_define lca WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."' AND ldcgr.lca_define_lca_id = lca.lca_id AND lca.lca_activate = '1'");	
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				 if ($DBRESULT2->numRows())
					while ($DBRESULT2->fetchInto($lca))	{
						$DBRESULT3 =& $pearDB->query("SELECT DISTINCT host_id, host_name FROM host, lca_define_host_relation ldr WHERE lca_define_lca_id = '".$lca["lca_id"]."' AND host_id = ldr.host_host_id");
						if (PEAR::isError($DBRESULT3))
							print "DB Error : ".$DBRESULT3->getDebugInfo()."<br>";
						while ($DBRESULT3->fetchInto($host))
							$lcaHost[$host["host_id"]] = $host["host_name"];
					 	$DBRESULT3 =& $pearDB->query("SELECT DISTINCT hg_id, hg_name FROM hostgroup, lca_define_hostgroup_relation WHERE lca_define_lca_id = '".$lca["lca_id"]."' AND hg_id = hostgroup_hg_id");	
						if (PEAR::isError($DBRESULT3))
							print "DB Error : ".$DBRESULT3->getDebugInfo()."<br>";
						while ($DBRESULT3->fetchInto($hostGroup))	{
							$lcaHostGroup[$hostGroup["hg_id"]] = $hostGroup["hg_name"];
							# Apply the LCA to hosts contains in
							if ($lca["lca_hg_childs"])	{
								$DBRESULT4 =& $pearDB->query("SELECT h.host_name, hgr.host_host_id FROM hostgroup_relation hgr, host h WHERE hgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."' AND h.host_id = hgr.host_host_id");	
								if (PEAR::isError($DBRESULT4))
									print "DB Error : ".$DBRESULT4->getDebugInfo()."<br>";
								while ($DBRESULT4->fetchInto($host))	
									$lcaHost[$host["host_host_id"]] = $host["host_name"];
							}
						}
					}
			}
		}
		$LcaHHG = array();
		isset($lcaHost) ? $LcaHHG["LcaHost"] = $lcaHost : $LcaHHG["LcaHost"] = array();
		isset($lcaHostGroup) ? $LcaHHG["LcaHostGroup"] = $lcaHostGroup : $LcaHHG["LcaHostGroup"] = array();
		return $LcaHHG;
	}
	
	function getLCAHostByName($pearDB){
		if (!$pearDB)
			return ;
		if (session_id() == "")
			$uid = $_POST["sid"];
		else 
			$uid = session_id();
		$lcaHost = array();
		$lcaHostGroup = array();
		$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$uid."'");
		$res1->fetchInto($user);
		$user_id = $user["user_id"];
		$res1 =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$user_id."'");
		if ($res1->numRows())	{
			while($res1->fetchInto($contactGroup))	{
			 	$res2 =& $pearDB->query("SELECT lca.lca_id, lca.lca_hg_childs FROM lca_define_contactgroup_relation ldcgr, lca_define lca WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."' AND ldcgr.lca_define_lca_id = lca.lca_id AND lca.lca_activate = '1'");	
				 if ($res2->numRows())
					while ($res2->fetchInto($lca))	{
						$res3 =& $pearDB->query("SELECT DISTINCT host_id, host_name FROM host, lca_define_host_relation ldr WHERE lca_define_lca_id = '".$lca["lca_id"]."' AND host_id = ldr.host_host_id");
						while ($res3->fetchInto($host))
							$lcaHost[$host["host_name"]] = $host["host_id"];
					 	$res3 =& $pearDB->query("SELECT DISTINCT hg_id, hg_name FROM hostgroup, lca_define_hostgroup_relation WHERE lca_define_lca_id = '".$lca["lca_id"]."' AND hg_id = hostgroup_hg_id");	
						while ($res3->fetchInto($hostGroup))	{
							$lcaHostGroup[$hostGroup["hg_name"]] = $hostGroup["hg_id"];
							# Apply the LCA to hosts contains in
							if ($lca["lca_hg_childs"])	{
								$res4 =& $pearDB->query("SELECT h.host_name, hgr.host_host_id FROM hostgroup_relation hgr, host h WHERE hgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."' AND h.host_id = hgr.host_host_id");	
								while ($res4->fetchInto($host))	
									$lcaHost[$host["host_name"]] = $host["host_host_id"];
							}
						}
					}
			}
		}
		$LcaHHG = array();
		isset($lcaHost) ? $LcaHHG["LcaHost"] = $lcaHost : $LcaHHG["LcaHost"] = array();
		isset($lcaHostGroup) ? $LcaHHG["LcaHostGroup"] = $lcaHostGroup : $LcaHHG["LcaHostGroup"] = array();
		return $LcaHHG;
	}
	
	function getLCAHostStr($lcaHost){
		$lcaHStr = NULL;
	  	foreach ($lcaHost as $key=>$value)
	  		$lcaHStr ? $lcaHStr .= ", '".$key."'" : $lcaHStr = "'".$key."'";
	  	if (!$lcaHStr) 
	  		$lcaHStr = '\'\'';
  	  	return $lcaHStr;
	}
		
	function getLCAHGStr($lcaHostGroup){
		$lcaHGStr = NULL;
		foreach ($lcaHostGroup as $key=>$value)
	  		$lcaHGStr ? $lcaHGStr .= ", '".$key."'" : $lcaHGStr = "'".$key."'";
	  	if (!$lcaHGStr) 
	  		$lcaHGStr = '\'\'';
	  	return $lcaHGStr;
	}
		
	function getLCASGStr($lcaServiceGroup){
		$lcaSGStr = NULL;
	  	foreach ($lcaServiceGroup as $key=>$value)
	  		$lcaSGStr ? $lcaSGStr .= ", '".$key."'" : $lcaSGStr = "'".$key."'";
	  	if (!$lcaSGStr) 
	  		$lcaSGStr = '\'\'';
		return $lcaSGStr;
	}
	
	function HadUserLca($pearDB){
		if (!$pearDB)
			return ;
		if (session_id() == "")
			$uid = $_POST["sid"];
		else 
			$uid = session_id();
		$num = 0;
		$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$uid."'");
		$res1->fetchInto($user);
		$res1 =& $pearDB->query("SELECT contact_admin FROM contact WHERE contact_id = '".$user["user_id"]."'");
		$res1->fetchInto($user_status);
		
		if ($user_status["contact_admin"]){
			return 0;
		} else {
			$user_id = $user["user_id"];
			$res1 =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$user_id."'");
			if ($res1->numRows())
				while($res1->fetchInto($contactGroup))	{
					$res2 =& $pearDB->query("SELECT lca.lca_id, lca.lca_hg_childs FROM lca_define_contactgroup_relation ldcgr, lca_define lca WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."' AND ldcgr.lca_define_lca_id = lca.lca_id AND lca.lca_activate = '1'");	
				 	$num = $res2->numRows();
				}
			return $num;
		}
	}
	
	function IsHostReadable($lcaHostByName, $host_name){
		global $oreon, $pearDB, $isRestreint;
		if (!isset($isRestreint))
			$isRestreint = HadUserLca($pearDB);
		if ($oreon->user->admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$host_name])))
			return 1;
		return 0;		
	}

?>