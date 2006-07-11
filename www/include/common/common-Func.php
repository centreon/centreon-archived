<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	function myDecode($arg)	{
		return html_entity_decode($arg, ENT_QUOTES);
	}

	#
	## SMARTY
	#
	
	function initSmartyTpl($path = NULL, $tpl = NULL, $subDir = NULL)	{
		if (!$tpl)
			return;
		$tpl->template_dir = $path . $subDir;
		$tpl->compile_dir = "../GPL_LIB/SmartyCache/compile";
		$tpl->config_dir = "../GPL_LIB/SmartyCache/config";
		$tpl->cache_dir = "../GPL_LIB/SmartyCache/cache";

		$tpl->caching = 0;
		$tpl->compile_check = true;
		$tpl->force_compile = true;
		return $tpl;
	}

	function initSmartyTplForLogs($path = NULL, $tpl = NULL)	{
		if (!$tpl)
			return;
		$tpl->template_dir = $path;
		$tpl->compile_dir = "../../../GPL_LIB/SmartyCache/compile";
		$tpl->config_dir = "../../../GPL_LIB/SmartyCache/config";
		$tpl->cache_dir = "../../../GPL_LIB/SmartyCache/cache";

		$tpl->caching = 0;
		$tpl->compile_check = true;
		$tpl->force_compile = true;
		return $tpl;
	}

	#
	## HOST
	#
	
	function getMyHostName($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT host_name, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["host_name"])
				return $row["host_name"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}
	
	function isAHostTpl($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT host_register FROM host WHERE host_id = '".$host_id."' LIMIT 1");
		$row =& $res->fetchRow();
		if ($row["host_register"])
			return true;
		else
			return false;
	}

	function getMyHostAddress($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT host_address, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["host_address"])
				return $row["host_address"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function getMyHostAddressByName($host_name = NULL)	{
		if (!$host_name) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT host_address, host_template_model_htm_id FROM host WHERE host_name = '".$host_name."' LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["host_address"])
				return $row["host_address"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function getMyHostParents($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT host_template_model_htm_id AS tpl FROM host WHERE host_id = '".$host_id."'");
			$host = clone($res->fetchRow());
			$res =& $pearDB->query("SELECT hpr.host_parent_hp_id FROM host_hostparent_relation hpr WHERE hpr.host_host_id = '".$host_id."'");
			if ($res->numRows())
				return $res;
			else if (isset($host["tpl"]) && $host["tpl"])
				$host_id = $host["tpl"];
			else
				return $res;
		}
	}
		
	function getMySnmpCommunity($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT host_snmp_community, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["host_snmp_community"])
				return $row["host_snmp_community"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function getMySnmpVersion($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT host_snmp_version, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["host_snmp_version"])
				return $row["host_snmp_version"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}
	
	#
	## HOST GROUP
	#
	
	function getMyHostGroupName($hg_id = NULL)	{
		if (!$hg_id) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT hg_name FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		$row =& $res->fetchRow();
		if ($row["hg_name"])
			return $row["hg_name"];
		return NULL;
	}
	
	#
	## SERVICE
	#

	function getMyServiceName($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT service_description, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["service_description"])
				return $row["service_description"];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}

	function getMyServiceGraphID($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT esi.graph_id, service_template_model_stm_id FROM service, extended_service_information esi WHERE service_id = '".$service_id."' AND esi.service_service_id = service_id LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["graph_id"])
				return $row["graph_id"];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
		return NULL;
	}

	function getMyServiceID($service_description = NULL, $host_id = NULL, $hg_id = NULL)	{
		if (!$service_description && (!$host_id || !$hg_id)) return;
		global $pearDB;
		if ($host_id)	{
			$res =& $pearDB->query("SELECT service_id FROM service, host_service_relation hsr " .
					"WHERE hsr.host_host_id = '".$host_id."' AND hsr.service_service_id = service_id " .
							"AND service_description = '".$service_description."' LIMIT 1");
			$row =& $res->fetchRow();
			# Service is directely link to a host, no problem
			if ($row["service_id"])
				return $row["service_id"];
			# The Service might be link with a HostGroup
			$res =& $pearDB->query("SELECT service_id FROM hostgroup_relation hgr, service, host_service_relation hsr" .
					" WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
							" AND service_id = hsr.service_service_id AND service_description = '".$service_description."'");
			$row =& $res->fetchRow();
			if ($row["service_id"])
				return $row["service_id"];
		}
		if ($hg_id)	{
			$res =& $pearDB->query("SELECT service_id FROM service, host_service_relation hsr WHERE hsr.hostgroup_hg_id = '".$hg_id."' AND hsr.service_service_id = service_id AND service_description = '".$service_description."' LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["service_id"])
				return $row["service_id"];
		}
		return NULL;
	}

	function getAllMyServiceHosts($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		$hosts = array();
		$res =& $pearDB->query("SELECT host_host_id, hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		while ($res->fetchInto($elem))	{
			if ($elem["host_host_id"])
				$hosts[$elem["host_host_id"]] = $elem["host_host_id"];
			else if ($elem["hostgroup_hg_id"])	{
				$res2 =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$elem["hostgroup_hg_id"]."'");
				while ($res2->fetchInto($elem2))
					$hosts[$elem2["host_host_id"]] = $elem2["host_host_id"];
				$res2->free();
			}
		}
		$res->free();
		return $hosts;
	}

	function getMyServiceHosts($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		$hosts = array();
		$res =& $pearDB->query("SELECT DISTINCT host_host_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		while ($res->fetchInto($elem))
			if ($elem["host_host_id"])
				$hosts[$elem["host_host_id"]] = $elem["host_host_id"];
		$res->free();
		return $hosts;
	}

	function getMyServiceHostGroups($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		$hgs = array();
		$res =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		while ($res->fetchInto($elem))
			if ($elem["hostgroup_hg_id"])
				$hgs[$elem["hostgroup_hg_id"]] = $elem["hostgroup_hg_id"];
		$res->free();
		return $hgs;
	}

	function getMyServiceTPLID($service_description = NULL)	{
		if (!$service_description) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT service_id FROM service WHERE service_description = '".htmlentities($service_description, ENT_QUOTES)."' AND service_register = '0' LIMIT 1");
		$row =& $res->fetchRow();
		if ($row["service_id"])
			return $row["service_id"];
		return NULL;
	}

	function isACheckGraphService($service_id = NULL)	{
		if (!$service_id)	return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT command_command_id, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["command_command_id"])	{
				$res2 =& $pearDB->query("SELECT command_name FROM command WHERE command_id = '".$row["command_command_id"]."' LIMIT 1");
				$row2 =& $res2->fetchRow();
				if (strstr($row2["command_name"], "check_graph_"))
					return true;
				else
					return false;
			}
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				return NULL;
		}
		return NULL;
	}
	
	#
	## COMMAND
	#
		
	function getMyCheckCmdGraph($service_id = NULL, $host_id = NULL)	{
		if (!$service_id)	return;
		global $pearDB;
		$i = 0;
		$host_id ? $host_id = "!".$host_id."_".$service_id : NULL;
		while(1)	{
			$res =& $pearDB->query("SELECT command_command_id, command_command_id_arg, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $res->fetchRow();
			if ($row["command_command_id"])	{
				$res2 =& $pearDB->query("SELECT command_name FROM command WHERE command_id = '".$row["command_command_id"]."' LIMIT 1");
				$row2 =& $res2->fetchRow();
				$row["command_command_id_arg"] = str_replace('#BR#', "\\n", $row["command_command_id_arg"]);
				$row["command_command_id_arg"] = str_replace('#T#', "\\t", $row["command_command_id_arg"]);
				$row["command_command_id_arg"] = str_replace('#R#', "\\r", $row["command_command_id_arg"]);
				$row["command_command_id_arg"] = str_replace('#S#', "/", $row["command_command_id_arg"]);
				$row["command_command_id_arg"] = str_replace('#BS#', "\\", $row["command_command_id_arg"]);
				if (strstr($row2["command_name"], "check_graph_"))
					return ($row2["command_name"].$row["command_command_id_arg"].$host_id);
				else if (!$i)
					return ($row2["command_name"].$row["command_command_id_arg"]);
				else
					return NULL;
			}
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				return NULL;
			$i++;
		}
		return NULL;
	}

	#
	## Upload conf needs
	#

	function getMyHostID($host_name = NULL)	{
		if (!$host_name) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT host_id FROM host WHERE host_name = '".htmlentities($host_name, ENT_QUOTES)."' LIMIT 1");
		if ($res->numRows())	{
			$row =& $res->fetchRow();
			return $row["host_id"];
		}
		return NULL;
	}

	function getMyHostGroupID($hostgroup_name = NULL)	{
		if (!$hostgroup_name) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT hg_id FROM hostgroup WHERE hg_name = '".htmlentities($hostgroup_name, ENT_QUOTES)."' LIMIT 1");
		if ($res->numRows())	{
			$row =& $res->fetchRow();
			return $row["hg_id"];
		}
		return NULL;
	}

	function getMyServiceGroupID($servicegroup_name = NULL)	{
		if (!$servicegroup_name) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT sg_id FROM servicegroup WHERE sg_name = '".htmlentities($servicegroup_name, ENT_QUOTES)."' LIMIT 1");
		if ($res->numRows())	{
			$row =& $res->fetchRow();
			return $row["sg_id"];
		}
		return NULL;
	}

	function getMyContactID($contact_name = NULL)	{
		if (!$contact_name) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT contact_id FROM contact WHERE contact_name = '".htmlentities($contact_name, ENT_QUOTES)."' LIMIT 1");
		if ($res->numRows())	{
			$row =& $res->fetchRow();
			return $row["contact_id"];
		}
		return NULL;
	}

	function getMyContactGroupID($cg_name = NULL)	{
		if (!$cg_name) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT cg_id FROM contactgroup WHERE cg_name = '".htmlentities($cg_name, ENT_QUOTES)."' LIMIT 1");
		if ($res->numRows())	{
			$row =& $res->fetchRow();
			return $row["cg_id"];
		}
		return NULL;
	}

	function getMyCommandID($command_name = NULL)	{
		if (!$command_name) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT command_id FROM command WHERE command_name = '".htmlentities($command_name, ENT_QUOTES)."' LIMIT 1");
		if ($res->numRows())	{
			$row =& $res->fetchRow();
			return $row["command_id"];
		}
		$res->free();
		return NULL;
	}

	function getMyTPID($tp_name = NULL)	{
		if (!$tp_name) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT tp_id FROM timeperiod WHERE tp_name = '".htmlentities($tp_name, ENT_QUOTES)."' LIMIT 1");
		if ($res->numRows())	{
			$row =& $res->fetchRow();
			return $row["tp_id"];
		}
		return NULL;
	}

	function copyRrdDB($arg = NULL, $new_id = NULL, $host_id = NULL)	{
		if (!$arg || !$new_id || !$host_id) return;
		global $oreon;
		$evol = explode("_", $arg);
		if (isset($evol[1]))
			$service_id = $evol[1];
		else
			$service_id = $evol[0];
		if (is_file($oreon->optGen["oreon_path"]."filesUpload/rrd/".$arg.".rrd"))	{
			copy($oreon->optGen["oreon_path"]."filesUpload/rrd/".$arg.".rrd", $oreon->optGen["oreon_path"]."rrd/".$host_id."_".$new_id.".rrd");
		}
		else if (is_file($oreon->optGen["oreon_path"]."filesUpload/rrd/".$service_id.".rrd"))	{
			copy( $oreon->optGen["oreon_path"]."filesUpload/rrd/".$service_id.".rrd", $oreon->optGen["oreon_path"]."rrd/".$host_id."_".$new_id.".rrd");
		}
	}
	
	#
	## GRAPHS	
	#
	
	function getDefaultGraph ($service_id = NULL, $rrdType = NULL)	{
		// rrdType = 1 -> Graphs Perfparse
		// rrdType = 2 -> Graphs Plugins
		// rrdType = 3 -> Graphs Customs
		global $pearDB;
		if (!$rrdType)	$rrdType = 1;
		if ($rrdType != 3)	{			
			$gt["graph_id"] = getMyServiceGraphID($service_id);
			if ($gt["graph_id"])
				return $gt["graph_id"];
		}
		else	{
			$res =& $pearDB->query("SELECT grapht_graph_id FROM giv_graphs WHERE graph_id = '".$service_id."' LIMIT 1");
			$gt =& $res->fetchRow();
			if ($gt["grapht_graph_id"])
				return $gt["grapht_graph_id"];	
		}
		if ($rrdType != 2)	{
			$res =& $pearDB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
			if ($res->numRows())	{
				$gt =& $res->fetchRow();
				return $gt["graph_id"];
			}
		}	
		else	{
			$res =& $pearDB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl2 = '1' LIMIT 1");
			if ($res->numRows())	{
				$gt =& $res->fetchRow();
				return $gt["graph_id"];
			}
		}
		$res =& $pearDB->query("SELECT graph_id FROM giv_graphs_template LIMIT 1");
		if ($res->numRows())	{
			$gt =& $res->fetchRow();
			return $gt["graph_id"];
		}
		return NULL;
	}
	
	function getDefaultDS ($graph_id = NULL, $current_ds = NULL, $rrdType = NULL)	{
		// rrdType = 1 -> Graphs Perfparse
		// rrdType = 2 -> Graphs Plugins
		// rrdType = 3 -> Graphs Customs
		if (!$graph_id) return NULL;
		global $pearDB;
		if (!$rrdType)	$rrdType = 1;
		$ds = array();
		$res =& $pearDB->query("SELECT gct.compo_id FROM giv_components_template gct, giv_graphT_componentT_relation ggcr WHERE ggcr.gg_graph_id = '".$graph_id."' AND ggcr.gc_compo_id = gct.compo_id ORDER BY gct.ds_order");
		$cpt = 0;
		$sum = $res->numRows();
		while ($res->fetchInto($ds))	{
			if ($current_ds == $cpt)
				return $ds["compo_id"];
			$cpt++;				 
		}
		if ($rrdType != 2)	{
			$res =& $pearDB->query("SELECT compo_id FROM giv_components_template WHERE default_tpl1 = '1' LIMIT 1");
			if ($res->numRows())	{
				$ds =& $res->fetchRow();
				return $ds["compo_id"];
			}
		}
		else	{
			$res =& $pearDB->query("SELECT compo_id FROM giv_components_template WHERE default_tpl2 = '1' LIMIT 1");
			if ($res->numRows())	{
				$ds =& $res->fetchRow();
				return $ds["compo_id"];
			}
		}
		$res =& $pearDB->query("SELECT compo_id FROM giv_components_template LIMIT 1");
		if ($res->numRows())	{
			$ds =& $res->fetchRow();
			return $ds["compo_id"];
		}
		return NULL;
	}
?>