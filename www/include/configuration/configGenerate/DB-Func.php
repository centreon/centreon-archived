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

	function manageDependencies()	{
		global $pearDB;
		global $form;
		$gbArr = array();	
		$ret = $form->getSubmitValues();
		if ($ret["level"]["level"] == 1)
			$gbArr =& checkDependenciesStrong();
		else if ($ret["level"]["level"] == 2)
			$gbArr =& checkDependenciesLite();
		else if (($ret["level"]["level"] == 3) && $ret["xml"]["xml"])
			$gbArr =& checkNoDependencies();
		else
			$gbArr = NULL;
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
		$res =& $pearDB->query("SELECT contact_id FROM contact WHERE contact_activate ='1'");
		while($res->fetchInto($contact))	{
			$res2 =& $pearDB->query("SELECT DISTINCT cg.cg_activate FROM contactgroup_contact_relation cgcr, contactgroup cg WHERE cgcr.contact_contact_id = '".$contact["contact_id"]."' AND cg.cg_id = cgcr.contactgroup_cg_id");
			while($res2->fetchInto($contactGroup))	{
				if ($contactGroup["cg_activate"])
					$cctEnb[$contact["contact_id"]] = 1;
				unset($contactGroup);
			}
			unset($contact);
		}
		$res->free();
		# ContactGroup
		$contactGroup = array();
		$res =& $pearDB->query("SELECT DISTINCT cgcr.contactgroup_cg_id, cgcr.contact_contact_id FROM contactgroup cg, contactgroup_contact_relation cgcr WHERE cg.cg_activate ='1' AND cgcr.contactgroup_cg_id = cg.cg_id");
		while($res->fetchInto($contactGroup))
			array_key_exists($contactGroup["contact_contact_id"], $cctEnb) ? $cgEnb[$contactGroup["contactgroup_cg_id"]] = 1 : NULL;
		unset($contactGroup);
		$res->free();
		# Host Template Model
		$host = array();
		$res =& $pearDB->query("SELECT host_id FROM host WHERE host.host_register = '0' AND host.host_activate = '1'");
		while($res->fetchInto($host))
			$hostEnb[$host["host_id"]] = 1;
		$res->free();
		# Host
		$host = array();
		# In Nagios V2 -> Contact Group are obligatory
		if ($oreon->user->get_version() == 2)	{
			$res =& $pearDB->query("SELECT host_template_model_htm_id, host_id FROM host WHERE host.host_register = '1' AND host.host_activate = '1'");
			while($res->fetchInto($host))	{
				# If the Host is link to a Template, we think that the dependencies are manage in the template			
				if ($host["host_template_model_htm_id"])	{
					if (array_key_exists($host["host_template_model_htm_id"], $hostEnb))
						$hostEnb[$host["host_id"]] = 1;
				}
				else	{
					$res2 =& $pearDB->query("SELECT DISTINCT cghr.contactgroup_cg_id FROM contactgroup_host_relation cghr WHERE cghr.host_host_id = '".$host["host_id"]."'");
					while($res2->fetchInto($valid))
						array_key_exists($valid["contactgroup_cg_id"], $cgEnb) ? $hostEnb[$host["host_id"]] = 1 : NULL;
					$res2->free();
					unset($valid);
				}
				$res2 =& $pearDB->query("SELECT DISTINCT hg.hg_activate FROM hostgroup_relation hgr, hostgroup hg WHERE hgr.host_host_id = '".$host["host_id"]."' AND hg.hg_id = hgr.hostgroup_hg_id");
				while($res2->fetchInto($hostGroup))	{
					if ($hostGroup["hg_activate"])
						$hostEnb[$host["host_id"]] = 1;
				}
			}
			$res->free();
		}	
		else	{
			$res =& $pearDB->query("SELECT DISTINCT host_template_model_htm_id, host.host_id FROM host WHERE host.host_register = '1' AND host.host_activate = '1'");
			while($res->fetchInto($host))	{/*
				# If the Host is link to a Template, we think that the dependencies are manage in the template			
				if ($host["host_template_model_htm_id"])	{
					if (array_key_exists($host["host_template_model_htm_id"], $hostEnb))
						$hostEnb[$host["host_id"]] = 1;
				}*/
				$res2 =& $pearDB->query("SELECT DISTINCT hg.hg_activate FROM hostgroup_relation hgr, hostgroup hg WHERE hgr.host_host_id = '".$host["host_id"]."' AND hg.hg_id = hgr.hostgroup_hg_id");
				if ($res2->numRows())	{
					while($res2->fetchInto($hostGroup))
						if ($hostGroup["hg_activate"])
							$hostEnb[$host["host_id"]] = 1;
				}
				else
					$hostEnb[$host["host_id"]] = 1;
			}
			$res->free();
		}
		unset($host);
		# Host Group
		$hostGroup = array();
		if ($oreon->user->get_version() == 1)	{
			$res =& $pearDB->query("SELECT hg.hg_id FROM hostgroup hg WHERE hg.hg_activate = '1'");
			while($res->fetchInto($hostGroup))	{
				$h = false;
				$cg = false;
				$res2 =& $pearDB->query("SELECT DISTINCT hgr.host_host_id, cghgr.contactgroup_cg_id FROM hostgroup_relation hgr, contactgroup_hostgroup_relation cghgr WHERE hgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."' AND cghgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."'");
				while($res2->fetchInto($valid))	{
					array_key_exists($valid["host_host_id"], $hostEnb) ? $h = true : NULL;
					array_key_exists($valid["contactgroup_cg_id"], $cgEnb) ? $cg = true : NULL;
				}
				$h && $cg ? $hgEnb[$hostGroup["hg_id"]] = 1 : NULL;
				$res2->free();
				unset($valid);
			}
			$res->free();
		}
		else if ($oreon->user->get_version() == 2)	{
			$res =& $pearDB->query("SELECT DISTINCT hg.hg_id FROM hostgroup hg WHERE hg.hg_activate = '1'");
			while($res->fetchInto($hostGroup))	{						
				$res2 =& $pearDB->query("SELECT DISTINCT hgr.host_host_id, hgr.hostgroup_hg_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."'");
				while($res2->fetchInto($hostGroup))
					array_key_exists($hostGroup["host_host_id"], $hostEnb) ? $hgEnb[$hostGroup["hostgroup_hg_id"]] = 1 : NULL;
				$res2->free();
			}
			$res->free();
		}
		unset($hostGroup);
		# Service Template Model
		$service = array();
		$res =& $pearDB->query("SELECT DISTINCT sv.service_id FROM service sv WHERE sv.service_activate = '1' AND service_register = '0'");
		while ($res->fetchInto($service))
			$svEnb[$service["service_id"]] = 1;
		$res->free();
		# Service
		$service = array();
		$res =& $pearDB->query("SELECT DISTINCT sv.service_id, sv.service_template_model_stm_id FROM service sv WHERE sv.service_activate = '1' AND service_register = '1'");
		while ($res->fetchInto($service))	{
			# If the Service is link to a Template, we think that the dependencies are manage in the template			
			if ($service["service_template_model_stm_id"] && array_key_exists($service["service_template_model_stm_id"], $svEnb))
				$svEnb[$service["service_id"]] = 1;
			else	{
				$h = false;
				$hg = false;
				$cg = false;
				$res2 =& $pearDB->query("SELECT DISTINCT hsr.host_host_id, hsr.hostgroup_hg_id, cgsr.contactgroup_cg_id FROM contactgroup_service_relation cgsr, host_service_relation hsr WHERE cgsr.service_service_id = '".$service["service_id"]."' AND hsr.service_service_id = '".$service["service_id"]."'");
				while ($res2->fetchInto($valid))	{
					array_key_exists($valid["host_host_id"], $hostEnb) ? $h = true : NULL;
					array_key_exists($valid["hostgroup_hg_id"], $hgEnb) ? $hg = true : NULL;
					array_key_exists($valid["contactgroup_cg_id"], $cgEnb) ? $cg = true : NULL;		
				}
				($h || $hg) && $cg ? $svEnb[$service["service_id"]] = 1 : NULL;
				$res2->free();
				unset($valid);
			}
		}
		$res->free();
		# Service Group		
		$serviceGroup = array();
		$res =& $pearDB->query("SELECT sg_id FROM servicegroup sg WHERE sg.sg_activate = '1'");
		while($res->fetchInto($serviceGroup))	{
			$res2 =& $pearDB->query("SELECT sgr.service_service_id FROM servicegroup_relation sgr WHERE sgr.servicegroup_sg_id = '".$serviceGroup["sg_id"]."'");
			while ($res2->fetchInto($valid))
				array_key_exists($valid["service_service_id"], $svEnb) ? $sgEnb[$serviceGroup["sg_id"]] = 1 : NULL;
			$res2->free();
		}
		unset($serviceGroup);
		$res->free();
		# OSL		
		if (isset($oreon->modules["osl"]))	{
			$osl = array();
			$res =& $pearDB->query("SELECT osl_id FROM osl WHERE osl_activate = '1'");
			while($res->fetchInto($osl))
				$oslEnb[$osl["osl_id"]] = 1;
			unset($osl);
			$res->free();
		}
		# Meta Service		
		$oms = array();
		$res =& $pearDB->query("SELECT meta_id FROM meta_service WHERE meta_activate = '1'");
		while($res->fetchInto($oms))
			$omsEnb[$oms["meta_id"]] = 1;
		unset($oms);
		$res->free();
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
		$res =& $pearDB->query("SELECT contact_id FROM contact WHERE contact_activate ='1'");
		while($res->fetchInto($contact))
			$cctEnb[$contact["contact_id"]] = 1;
		unset($contact);
		$res->free();
		# ContactGroup
		$contactGroup = array();
		$res =& $pearDB->query("SELECT cg_id FROM contactgroup WHERE cg_activate ='1'");
		while($res->fetchInto($contactGroup))
			$cgEnb[$contactGroup["cg_id"]] = 1;
		unset($contactGroup);
		$res->free();
		# Host Template Model
		$host = array();
		$res =& $pearDB->query("SELECT host_id FROM host WHERE host_activate = '1' AND host_register = '0'");
		while($res->fetchInto($host))
			$hostEnb[$host["host_id"]] = 1;
		$res->free();
		unset($host);
		# Host
		$host = array();
		$res =& $pearDB->query("SELECT host_id, host_template_model_htm_id FROM host WHERE host_activate = '1' AND host_register = '1'");
		while($res->fetchInto($host))	{
			if ($host["host_template_model_htm_id"])	{ 
				if (array_key_exists($host["host_template_model_htm_id"], $hostEnb))
					$hostEnb[$host["host_id"]] = 1;
			}
			else
				$hostEnb[$host["host_id"]] = 1;
		}
		$res->free();
		unset($host);
		# Host Group
		$hostGroup = array();
		$res =& $pearDB->query("SELECT hg.hg_id FROM hostgroup hg WHERE hg.hg_activate = '1'");
		while($res->fetchInto($hostGroup))
			$hgEnb[$hostGroup["hg_id"]] = 1;
		$res->free();
		unset($hostGroup);
		# Service Template Model
		$service = array();
		$res =& $pearDB->query("SELECT service_id FROM service WHERE service_activate = '1' AND service_register = '0'");
		while ($res->fetchInto($service))
			$svEnb[$service["service_id"]] = 1;
		$res->free();
		# Service
		$service = array();
		$res =& $pearDB->query("SELECT service_id, service_template_model_stm_id FROM service WHERE service_activate = '1' AND service_register = '1'");
		while ($res->fetchInto($service))	{
			if ($service["service_template_model_stm_id"])	{
				if (array_key_exists($service["service_template_model_stm_id"], $svEnb))
					$svEnb[$service["service_id"]] = 1;
			}
			else
				$svEnb[$service["service_id"]] = 1;	
		}
		$res->free();
		# Service Group		
		$serviceGroup = array();
		$res =& $pearDB->query("SELECT sg_id FROM servicegroup WHERE sg_activate = '1'");
		while($res->fetchInto($serviceGroup))
			$sgEnb[$serviceGroup["sg_id"]] = 1;
		unset($serviceGroup);
		$res->free();
		# OSL		
		if (isset($oreon->modules["osm"]))	{
			$osl = array();
			$res =& $pearDB->query("SELECT osl_id FROM osl WHERE osl_activate = '1'");
			while($res->fetchInto($osl))
				$oslEnb[$osl["osl_id"]] = 1;
			unset($osl);
			$res->free();
		}
		# Meta Service		
		$oms = array();
		$res =& $pearDB->query("SELECT meta_id FROM meta_service WHERE meta_activate = '1'");
		while($res->fetchInto($oms))
			$omsEnb[$oms["meta_id"]] = 1;
		unset($oms);
		$res->free();
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
		$res =& $pearDB->query("SELECT contact_id FROM contact");
		while($res->fetchInto($contact))
			$cctEnb[$contact["contact_id"]] = 1;
		unset($contact);
		$res->free();
		# ContactGroup
		$contactGroup = array();
		$res =& $pearDB->query("SELECT cg_id FROM contactgroup");
		while($res->fetchInto($contactGroup))
			$cgEnb[$contactGroup["cg_id"]] = 1;
		unset($contactGroup);
		$res->free();
		# Host
		$host = array();
		$res =& $pearDB->query("SELECT host_id FROM host");
		while($res->fetchInto($host))
			$hostEnb[$host["host_id"]] = 1;
		$res->free();
		unset($host);
		# Host Group
		$hostGroup = array();
		$res =& $pearDB->query("SELECT hg.hg_id FROM hostgroup hg");
		while($res->fetchInto($hostGroup))
			$hgEnb[$hostGroup["hg_id"]] = 1;
		$res->free();
		unset($hostGroup);
		# Service
		$service = array();
		$res =& $pearDB->query("SELECT service_id FROM service");
		while ($res->fetchInto($service))
			$svEnb[$service["service_id"]] = 1;
		$res->free();
		# Service Group		
		$serviceGroup = array();
		$res =& $pearDB->query("SELECT sg_id FROM servicegroup");
		while($res->fetchInto($serviceGroup))
			$sgEnb[$serviceGroup["sg_id"]] = 1;
		unset($serviceGroup);
		$res->free();
		# OSL		
		if (isset($oreon->modules["osm"]))	{
			$osl = array();
			$res =& $pearDB->query("SELECT osl_id FROM osl");
			while($res->fetchInto($osl))
				$oslEnb[$osl["osl_id"]] = 1;
			unset($osl);
			$res->free();
		}
		# Meta Service		
		$oms = array();
		$res =& $pearDB->query("SELECT meta_id FROM meta_service");
		while($res->fetchInto($oms))
			$omsEnb[$oms["meta_id"]] = 1;
		unset($oms);
		$res->free();
		return ($gbEnb);
	}
	
	function print_header($handle, $name)	{
		$time = date("F j, Y, g:i a");
		$by = $name;
		$str  = "###################################################################\n";
		$len = strlen($str); // Get line lenght
		$str .= "#                                                                 #\n";
		$str .= "#                       GENERATED BY OREON                        #\n";
		$str .= "#                                                                 #\n";
		$str .= "#               Developped by :                                   #\n";
		$str .= "#                   - Julien Mathis                               #\n";
		$str .= "#                   - Romain Le Merlus                            #\n";
		$str .= "#                                                                 #\n";
		$str .= "#                          www.oreon-project.org                  #\n";
		$str .= "#                For information : contact@oreon-project.org      #\n";
		$str .= "###################################################################\n\n";
		$str .= "###################################################################\n";
		$str .= "#                                                                 #\n";
		$str .= "#         Last modification " . $time;
		
		$len_time = strlen($time);
		$res = $len - 28 - $len_time - 2;
		
		// Add space to put text on center
		for ($i = 0; $i != $res; $i++)
			$str  .= " ";
		
		$str .= "#\n";
		$str .= "#         By " . $by;
		$len_by = strlen($by);
		$res = $len - 13 - $len_by - 2;
		
		// Add space to put text on center
		for ($i = 0; $i != $res; $i++)
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
	    	echo $lang['ErrGenFileProb'].$filename;         
	    	exit;
		}
		$header ? print_header($handle, $name) : NULL;
	   	return $handle;
	}
	
	// write data into the file	
	function write_in_file($handle, $content, $filename)	{
		if (strcmp($content, "") && !fwrite($handle, $content)) {
			echo $lang['ErrGenFileProb'].$filename; 
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
?>