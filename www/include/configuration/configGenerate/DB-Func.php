<?php
/**
Centreon is developped with GPL Licence 2.0 :
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

	function isHostOnThisInstance($host_id, $instance_id){
		global $pearDB;
		
		$DBRESULT_relation =& $pearDB->query("SELECT * FROM ns_host_relation WHERE host_host_id = '".$host_id."' AND nagios_server_id = '".$instance_id."'");
		if (PEAR::isError($DBRESULT_relation))
			print "DB Error : ".$DBRESULT_relation->getDebugInfo()."<br />";
		if ($DBRESULT_relation->numRows())
			return 1;
		else
			return 0;
	}
	
	function isLocalInstance($instance_id){
		global $pearDB;
		
		$DBRESULT_relation =& $pearDB->query("SELECT localhost FROM nagios_server WHERE id = '".$instance_id."'");
		if (PEAR::isError($DBRESULT_relation))
			print "DB Error : ".$DBRESULT_relation->getDebugInfo()."<br />";
		$data = $DBRESULT_relation->fetchRow();
		return $data["localhost"];
	}

	function manageDependencies($ret = array())	{
		global $pearDB, $form;
		$gbArr = array();
		/*if (!count($ret))
			$ret = $form->getSubmitValues();
		if ($ret["level"]["level"] == 1)
		*/	
		$gbArr = checkDependenciesStrong();
		/*else if ($ret["level"]["level"] == 2)
			$gbArr = checkDependenciesLite();
		else if (($ret["level"]["level"] == 3) && $ret["xml"]["xml"])
			$gbArr = checkNoDependencies();
		else
			$gbArr = NULL;*/
		return ($gbArr);
	}
	
	function checkDependenciesStrong()	{
		global $pearDB;
		global $oreon;
		$cctEnb = array();
		$cgEnb = array();
		$hostEnb = array();
		$hgEnb = array();
		$svEnb = array();
		$sgEnb = array();
		$oslEnb = array();
		$omsEnb = array();
		$gbEnb = array(0=>&$cctEnb, 1=>&$cgEnb, 2=>&$hostEnb, 3=>&$hgEnb, 4=>&$svEnb, 5=>&$sgEnb, 6=>&$oslEnb, 7=>&$omsEnb);
		
		# Contact
		$contact = array();
		$DBRESULT =& $pearDB->query("SELECT contact_id FROM contact WHERE contact_activate ='1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($contact))	{
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT cg.cg_activate FROM contactgroup_contact_relation cgcr, contactgroup cg WHERE cgcr.contact_contact_id = '".$contact["contact_id"]."' AND cg.cg_id = cgcr.contactgroup_cg_id");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while($DBRESULT2->fetchInto($contactGroup))	{
				if ($contactGroup["cg_activate"])
					$cctEnb[$contact["contact_id"]] = 1;
				unset($contactGroup);
			}
			unset($contact);
		}
		$DBRESULT->free();
		# ContactGroup
		$contactGroup = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT cgcr.contactgroup_cg_id, cgcr.contact_contact_id FROM contactgroup cg, contactgroup_contact_relation cgcr WHERE cg.cg_activate ='1' AND cgcr.contactgroup_cg_id = cg.cg_id");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($contactGroup))
			array_key_exists($contactGroup["contact_contact_id"], $cctEnb) ? $cgEnb[$contactGroup["contactgroup_cg_id"]] = 1 : NULL;
		unset($contactGroup);
		$DBRESULT->free();
		# Host Template Model
		$host = array();
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host WHERE host.host_register = '0' AND host.host_activate = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($host))
			$hostEnb[$host["host_id"]] = 1;
		$DBRESULT->free();
		# Host
		$host = array();
		# In Nagios V2 -> Contact Group are obligatory
		if ($oreon->user->get_version() == 2)	{
			$DBRESULT =& $pearDB->query("SELECT host_template_model_htm_id, host_id FROM host WHERE host.host_register = '1' AND host.host_activate = '1'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while($DBRESULT->fetchInto($host))	{
				# If the Host is link to a Template, we think that the dependencies are manage in the template			
				if ($host["host_template_model_htm_id"])	{
					if (array_key_exists($host["host_template_model_htm_id"], $hostEnb))
						$hostEnb[$host["host_id"]] = 1;
				}
				else	{
					$DBRESULT2 =& $pearDB->query("SELECT DISTINCT cghr.contactgroup_cg_id FROM contactgroup_host_relation cghr WHERE cghr.host_host_id = '".$host["host_id"]."'");
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
					while($DBRESULT2->fetchInto($valid))
						array_key_exists($valid["contactgroup_cg_id"], $cgEnb) ? $hostEnb[$host["host_id"]] = 1 : NULL;
					$DBRESULT2->free();
					unset($valid);
				}
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hg.hg_activate FROM hostgroup_relation hgr, hostgroup hg WHERE hgr.host_host_id = '".$host["host_id"]."' AND hg.hg_id = hgr.hostgroup_hg_id");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				while($DBRESULT2->fetchInto($hostGroup))	{
					if ($hostGroup["hg_activate"])
						$hostEnb[$host["host_id"]] = 1;
				}
				$DBRESULT2->free();
			}
			$DBRESULT->free();
		}	
		else	{
			$DBRESULT =& $pearDB->query("SELECT DISTINCT host_template_model_htm_id, host.host_id FROM host WHERE host.host_register = '1' AND host.host_activate = '1'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while($DBRESULT->fetchInto($host))	{/*
				# If the Host is link to a Template, we think that the dependencies are manage in the template			
				if ($host["host_template_model_htm_id"])	{
					if (array_key_exists($host["host_template_model_htm_id"], $hostEnb))
						$hostEnb[$host["host_id"]] = 1;
				}*/
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hg.hg_activate FROM hostgroup_relation hgr, hostgroup hg WHERE hgr.host_host_id = '".$host["host_id"]."' AND hg.hg_id = hgr.hostgroup_hg_id");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				if ($DBRESULT2->numRows())	{
					while($DBRESULT2->fetchInto($hostGroup))
						if ($hostGroup["hg_activate"])
							$hostEnb[$host["host_id"]] = 1;
				}
				else
					$hostEnb[$host["host_id"]] = 1;
				$DBRESULT2->free();
			}
			$DBRESULT->free();
		}
		unset($host);
		# Host Group
		$hostGroup = array();
		if ($oreon->user->get_version() == 1)	{
			$DBRESULT =& $pearDB->query("SELECT hg.hg_id FROM hostgroup hg WHERE hg.hg_activate = '1'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while($DBRESULT->fetchInto($hostGroup))	{
				$h = false;
				$cg = false;
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hgr.host_host_id, cghgr.contactgroup_cg_id FROM hostgroup_relation hgr, contactgroup_hostgroup_relation cghgr WHERE hgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."' AND cghgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				while($DBRESULT2->fetchInto($valid))	{
					array_key_exists($valid["host_host_id"], $hostEnb) ? $h = true : NULL;
					array_key_exists($valid["contactgroup_cg_id"], $cgEnb) ? $cg = true : NULL;
				}
				$h && $cg ? $hgEnb[$hostGroup["hg_id"]] = 1 : NULL;
				$DBRESULT2->free();
				unset($valid);
			}
			$DBRESULT->free();
		}
		else if ($oreon->user->get_version() == 2)	{
			$DBRESULT =& $pearDB->query("SELECT DISTINCT hg.hg_id FROM hostgroup hg WHERE hg.hg_activate = '1'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while($DBRESULT->fetchInto($hostGroup))	{						
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hgr.host_host_id, hgr.hostgroup_hg_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				while($DBRESULT2->fetchInto($hostGroup))
					array_key_exists($hostGroup["host_host_id"], $hostEnb) ? $hgEnb[$hostGroup["hostgroup_hg_id"]] = 1 : NULL;
				$DBRESULT2->free();
			}
			$DBRESULT->free();
		}
		unset($hostGroup);
		# Service Template Model
		$service = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT sv.service_id FROM service sv WHERE sv.service_activate = '1' AND service_register = '0'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($DBRESULT->fetchInto($service))
			$svEnb[$service["service_id"]] = 1;
		$DBRESULT->free();
		# Service
		$service = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT sv.service_id, sv.service_template_model_stm_id FROM service sv WHERE sv.service_activate = '1' AND service_register = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($DBRESULT->fetchInto($service))	{
			# If the Service is link to a Template, we think that the dependencies are manage in the template			
			if ($service["service_template_model_stm_id"] && array_key_exists($service["service_template_model_stm_id"], $svEnb))
				$svEnb[$service["service_id"]] = 1;
			else	{
				$h = false;
				$hg = false;
				$cg = false;
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hsr.host_host_id, hsr.hostgroup_hg_id, cgsr.contactgroup_cg_id FROM contactgroup_service_relation cgsr, host_service_relation hsr WHERE cgsr.service_service_id = '".$service["service_id"]."' AND hsr.service_service_id = '".$service["service_id"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				while ($DBRESULT2->fetchInto($valid))	{
					array_key_exists($valid["host_host_id"], $hostEnb) ? $h = true : NULL;
					array_key_exists($valid["hostgroup_hg_id"], $hgEnb) ? $hg = true : NULL;
					array_key_exists($valid["contactgroup_cg_id"], $cgEnb) ? $cg = true : NULL;		
				}
				($h || $hg) && $cg ? $svEnb[$service["service_id"]] = 1 : NULL;
				$DBRESULT2->free();
				unset($valid);
			}
		}
		$DBRESULT->free();
		# Service Group		
		$serviceGroup = array();
		$DBRESULT =& $pearDB->query("SELECT sg_id FROM servicegroup sg WHERE sg.sg_activate = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($serviceGroup))	{
			$DBRESULT2 =& $pearDB->query("SELECT sgr.service_service_id FROM servicegroup_relation sgr WHERE sgr.servicegroup_sg_id = '".$serviceGroup["sg_id"]."'");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while ($DBRESULT2->fetchInto($valid))
				array_key_exists($valid["service_service_id"], $svEnb) ? $sgEnb[$serviceGroup["sg_id"]] = 1 : NULL;
			$DBRESULT2->free();
		}
		unset($serviceGroup);
		$DBRESULT->free();
		
		# OSL
		if (is_dir("./modules/osl"))	{
			$osl = array();
			$DBRESULT =& $pearDB->query("SELECT osl_id FROM osl WHERE osl_activate = '1'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while($DBRESULT->fetchInto($osl))
				$oslEnb[$osl["osl_id"]] = 1;
			unset($osl);
			$DBRESULT->free();
		}
		
		# Meta Service		
		$oms = array();
		$DBRESULT =& $pearDB->query("SELECT meta_id FROM meta_service WHERE meta_activate = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($oms))
			$omsEnb[$oms["meta_id"]] = 1;
		unset($oms);
		$DBRESULT->free();
		
		return ($gbEnb);
	}
	
	function checkDependenciesLite()	{
		global $pearDB;
		global $oreon;
		$cctEnb = array();
		$cgEnb = array();
		$hostEnb = array();
		$hgEnb = array();
		$svEnb = array();
		$sgEnb = array();
		$oslEnb = array();
		$omsEnb = array();
		$gbEnb = array(0=>&$cctEnb, 1=>&$cgEnb, 2=>&$hostEnb, 3=>&$hgEnb, 4=>&$svEnb, 5=>&$sgEnb, 6=>&$oslEnb, 7=>&$omsEnb);
		
		# Contact
		$contact = array();
		$DBRESULT =& $pearDB->query("SELECT contact_id FROM contact WHERE contact_activate ='1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($contact))
			$cctEnb[$contact["contact_id"]] = 1;
		unset($contact);
		$DBRESULT->free();

		# ContactGroup
		$contactGroup = array();
		$DBRESULT =& $pearDB->query("SELECT cg_id FROM contactgroup WHERE cg_activate ='1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($contactGroup))
			$cgEnb[$contactGroup["cg_id"]] = 1;
		unset($contactGroup);
		$DBRESULT->free();

		# Host Template Model
		$host = array();
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host WHERE host_activate = '1' AND host_register = '0'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($host))
			$hostEnb[$host["host_id"]] = 1;
		$DBRESULT->free();
		unset($host);

		# Host
		$host = array();
		$DBRESULT =& $pearDB->query("SELECT host_id, host_template_model_htm_id FROM host WHERE host_activate = '1' AND host_register = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($host))	{
			if ($host["host_template_model_htm_id"])	{ 
				if (array_key_exists($host["host_template_model_htm_id"], $hostEnb))
					$hostEnb[$host["host_id"]] = 1;
			}
			else
				$hostEnb[$host["host_id"]] = 1;
		}
		$DBRESULT->free();
		unset($host);

		# Host Group
		$hostGroup = array();
		$DBRESULT =& $pearDB->query("SELECT hg.hg_id FROM hostgroup hg WHERE hg.hg_activate = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($hostGroup))
			$hgEnb[$hostGroup["hg_id"]] = 1;
		$DBRESULT->free();
		unset($hostGroup);

		# Service Template Model
		$service = array();
		$DBRESULT =& $pearDB->query("SELECT service_id FROM service WHERE service_activate = '1' AND service_register = '0'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($DBRESULT->fetchInto($service))
			$svEnb[$service["service_id"]] = 1;
		$DBRESULT->free();

		# Service
		$service = array();
		$DBRESULT =& $pearDB->query("SELECT service_id, service_template_model_stm_id FROM service WHERE service_activate = '1' AND service_register = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($DBRESULT->fetchInto($service))	{
			if ($service["service_template_model_stm_id"])	{
				if (array_key_exists($service["service_template_model_stm_id"], $svEnb))
					$svEnb[$service["service_id"]] = 1;
			}
			else
				$svEnb[$service["service_id"]] = 1;	
		}
		$DBRESULT->free();

		# Service Group		
		$serviceGroup = array();
		$DBRESULT =& $pearDB->query("SELECT sg_id FROM servicegroup WHERE sg_activate = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($serviceGroup))
			$sgEnb[$serviceGroup["sg_id"]] = 1;
		unset($serviceGroup);
		$DBRESULT->free();

	
		# OSL
		if (is_dir("./modules/osl"))	{			
			$osl = array();
			$DBRESULT =& $pearDB->query("SELECT osl_id FROM osl WHERE osl_activate = '1'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while($DBRESULT->fetchInto($osl))
				$oslEnb[$osl["osl_id"]] = 1;
			unset($osl);
			$DBRESULT->free();
		}
			
		# Meta Service		
		$oms = array();
		$DBRESULT =& $pearDB->query("SELECT meta_id FROM meta_service WHERE meta_activate = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($oms))
			$omsEnb[$oms["meta_id"]] = 1;
		unset($oms);
		$DBRESULT->free();
		
		return ($gbEnb);
	}
	
	function checkNoDependencies()	{
		global $pearDB;
		global $oreon;
		$cctEnb = array();
		$cgEnb = array();
		$hostEnb = array();
		$hgEnb = array();
		$svEnb = array();
		$sgEnb = array();
		$oslEnb = array();
		$omsEnb = array();
		$gbEnb = array(0=>&$cctEnb, 1=>&$cgEnb, 2=>&$hostEnb, 3=>&$hgEnb, 4=>&$svEnb, 5=>&$sgEnb, 6=>&$oslEnb, 7=>&$omsEnb);
		
		# Contact
		$contact = array();
		$DBRESULT =& $pearDB->query("SELECT contact_id FROM contact");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($contact))
			$cctEnb[$contact["contact_id"]] = 1;
		unset($contact);
		$DBRESULT->free();

		# ContactGroup
		$contactGroup = array();
		$DBRESULT =& $pearDB->query("SELECT cg_id FROM contactgroup");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($contactGroup))
			$cgEnb[$contactGroup["cg_id"]] = 1;
		unset($contactGroup);
		$DBRESULT->free();

		# Host
		$host = array();
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($host))
			$hostEnb[$host["host_id"]] = 1;
		$DBRESULT->free();
		unset($host);

		# Host Group
		$hostGroup = array();
		$DBRESULT =& $pearDB->query("SELECT hg.hg_id FROM hostgroup hg");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($hostGroup))
			$hgEnb[$hostGroup["hg_id"]] = 1;
		$DBRESULT->free();
		unset($hostGroup);

		# Service
		$service = array();
		$DBRESULT =& $pearDB->query("SELECT service_id FROM service");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($DBRESULT->fetchInto($service))
			$svEnb[$service["service_id"]] = 1;
		$DBRESULT->free();

		# Service Group		
		$serviceGroup = array();
		$DBRESULT =& $pearDB->query("SELECT sg_id FROM servicegroup");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($serviceGroup))
			$sgEnb[$serviceGroup["sg_id"]] = 1;
		unset($serviceGroup);
		$DBRESULT->free();

		# OSL
		if (is_dir("./modules/osl"))	{	
			$osl = array();
			$DBRESULT =& $pearDB->query("SELECT osl_id FROM osl");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while($DBRESULT->fetchInto($osl))
				$oslEnb[$osl["osl_id"]] = 1;
			unset($osl);
			$DBRESULT->free();
		}
		
		# Meta Service		
		$oms = array();
		$DBRESULT =& $pearDB->query("SELECT meta_id FROM meta_service");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while($DBRESULT->fetchInto($oms))
			$omsEnb[$oms["meta_id"]] = 1;
		unset($oms);
		$DBRESULT->free();
		return ($gbEnb);
	}
	
	function print_header($handle, $name)	{
		$time = date("F j, Y, g:i a");
		$by = $name;
		$str  = "###################################################################\n";
		$len = strlen($str); // Get line lenght
		$str .= "#                                                                 #\n";
		$str .= "#                       GENERATED BY CENTREON                     #\n";
		$str .= "#                                                                 #\n";
		$str .= "#               Developped by :                                   #\n";
		$str .= "#                   - Julien Mathis                               #\n";
		$str .= "#                   - Romain Le Merlus                            #\n";
		$str .= "#                                                                 #\n";
		$str .= "#                           www.centreon.com                      #\n";
		$str .= "#                For information : contact@oreon-project.org      #\n";
		$str .= "###################################################################\n";
		$str .= "#                                                                 #\n";
		$str .= "#         Last modification " . $time;
		
		$len_time = strlen($time);
		$DBRESULT = $len - 28 - $len_time - 2;
		
		// Add space to put text on center
		for ($i = 0; $i != $DBRESULT; $i++)
			$str  .= " ";
		
		$str .= "#\n";
		$str .= "#         By " . $by;
		$len_by = strlen($by);
		$DBRESULT = $len - 13 - $len_by - 2;
		
		// Add space to put text on center
		for ($i = 0; $i != $DBRESULT; $i++)
			$str  .= " ";
		$str .= "#\n";
		$str .= "#                                                                 #\n";
		$str .= "###################################################################\n\n";
		fwrite($handle, $str);
	}
	
	// Create File, print header and return handle. 	
	function create_file($filename, $name, $header = true)	{
		global $lang;
		if (!$handle = fopen($filename, 'w')) {         
	    	echo _("Can't access to needed file").$filename;         
	    	exit;
		}
		$header ? print_header($handle, $name) : NULL;
	   	return $handle;
	}
	
	// write data into the file	
	function write_in_file($handle, $content, $filename)	{
		if (strcmp($content, "") && !fwrite($handle, $content)) {
			echo _("Can't access to needed file").$filename; 
			exit();
		}
	}
	
	// Put text in good format	
	function print_line($data1, $data2)	{
	  $len = strlen($data1);
	  if ($len <= 9)
	    return "\t" . $data1 . "\t\t\t\t" . $data2 . "\n";
	  else if ($len > 9 && $len <= 18)
	    return "\t" . $data1 . "\t\t\t" . $data2 . "\n";
	  else if ($len >= 19 && $len <= 27)
	    return "\t" . $data1 . "\t\t" . $data2 . "\n";
	  else if ($len > 27)
	    return "\t" . $data1 . "\t" . $data2 . "\n";
	}
	
	function removeSpecialChar($str){
		$str = str_replace('#BR#', "\\n", $str);
		$str = str_replace('#T#', "\\t", $str);
		$str = str_replace('#R#', "\\r", $str);
		$str = str_replace('#S#', "/", $str);
		$str = str_replace('#BS#', "\\", $str);
		return $str;
	}
	
	function verifyIfMustBeGenerated($id, $gbArr, $ret){
		$BP = false;
		//if ($ret["level"]["level"] == 1)
			array_key_exists($id, $gbArr) ? $BP = true : NULL;
		/*else if ($ret["level"]["level"] == 2)
			array_key_exists($id, $gbArr) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;	*/
		return $BP;
	}
	
/*
Function to replace PHP's parse_ini_file() with much fewer restritions, and
a matching function to write to a .INI file, both of which are binary safe.

Version 1.0

Copyright (C) 2005 Justin Frim <phpcoder@cyberpimp.pimpdomain.com>

Sections can use any character excluding ASCII control characters and ASCII
DEL.  (You may even use [ and ] characters as literals!)

Keys can use any character excluding ASCII control characters, ASCII DEL,
ASCII equals sign (=), and not start with the user-defined comment
character.

Values are binary safe (encoded with C-style backslash escape codes) and may
be enclosed by double-quotes (to retain leading & trailing spaces).

User-defined comment character can be any non-white-space ASCII character
excluding ASCII opening bracket ([).

readINIfile() is case-insensitive when reading sections and keys, returning
an array with lower-case keys.
writeINIfile() writes sections and keys with first character capitalization.
Invalid characters are converted to ASCII dash / hyphen (-).  Values are
always enclosed by double-quotes.

writeINIfile() also provides a method to automatically prepend a comment
header from ASCII text with line breaks, regardless of whether CRLF, LFCR,
CR, or just LF line break sequences are used!  (All line breaks are
translated to CRLF)

Modified for Oreon by Christophe Coraboeuf
*/

	function readINIfile ($filename, $commentchar) {
	  $array1 = array();
	  $array2 = array();
	  $array1 = file($filename);
	  $section = '';
	  foreach ($array1 as $filedata) {
	   $dataline = trim($filedata);
	   $firstchar = substr($dataline, 0, 1);
	   if ($firstchar!=$commentchar && $dataline!='') {
	     //It's an entry (not a comment and not a blank line)
	     if ($firstchar == '[' && substr($dataline, -1, 1) == ']') {
	       //It's a section
	       $section = strtoupper(substr($dataline, 1, -1));
	     }else{
	       //It's a key...
	       $delimiter = strpos($dataline, '=');
	       if ($delimiter > 0) {
	         //...with a value
	         $key = strtoupper(trim(substr($dataline, 0, $delimiter)));
	         $value = trim(substr($dataline, $delimiter + 1));
	         if (substr($value, 0, 1) == '"' && substr($value, -1, 1) == '"') { $value = substr($value, 1, -1); }
	         $array2[$section][$key] = stripcslashes($value);
	       }else{
	         //...without a value
	         $array2[$section][strtoupper(trim($dataline))]='';
	       }
	     }
	   }else{
	     //It's a comment or blank line.  Ignore.
	   }
	  }
	  return $array2;
	}
	
	function writeINIfile ($filename, $array1, $commentchar, $commenttext) {
	  	$handle = fopen($filename, 'wb');
	  	if ($commenttext!='') {
	   		$comtext = $commentchar.
	     	str_replace($commentchar, "\r\n".$commentchar,
	       	str_replace ("\r", $commentchar,
	        str_replace("\n", $commentchar,
	        str_replace("\n\r", $commentchar,
	        str_replace("\r\n", $commentchar, $commenttext)))));
	   		if (substr($comtext, -1, 1)==$commentchar && substr($comtext, -1, 1)!=$commentchar) {
	     		$comtext = substr($comtext, 0, -1);
	   		}
	   		fwrite ($handle, $comtext."\r\n");
	  	}
	 	foreach ($array1 as $sections => $items) {
	   		//Write the section
	   		if (isset($section)) { fwrite ($handle, "\r\n"); }
	   		//$section = ucfirst(preg_replace('/[\0-\37]|[\177-\377]/', "-", $sections));
	   		$section = strtoupper(preg_replace('/[\0-\37]|\177/', "-", $sections));
	   		fwrite ($handle, "[".$section."]\r\n");
	   		foreach ($items as $keys => $values) {
	     		//Write the key/value pairs
	     		$key = strtoupper(preg_replace('/[\0-\37]|=|\177/', "-", $keys));
	     		if (substr($key, 0, 1)==$commentchar) { $key = '-'.substr($key, 1); }
	   			//  if (substr($values, 0, 1) == '"' && substr($values, -1, 1) == '"') { $values = substr($values, 1, -1); }
	     		$value = ucfirst(addcslashes($values,''));
	     		fwrite ($handle, '    '.$key.'='.$value."\r\n");
	   		}
	  	}
	  	fclose($handle);
	}
?>