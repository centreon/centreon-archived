<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

	if (!isset($oreon))
		exit();
			
	if (isset($_GET["host_name"]) && $_GET["host_name"] && isset($_GET["service_description"]) && $_GET["service_description"]){
		$host_name = $_GET["host_name"];
		$svc_description = $_GET["service_description"];
	} else {
		foreach ($_GET["select"] as $key => $value)
			$host_service_key = $key;
		$tab_data = split(";", $host_service_key);
		$host_name = $tab_data[0];
		$svc_description = $tab_data[1];
	}
	
	if (!isset($_GET["service_description"]))
		$_GET["service_description"] = $svc_description;
	
	$lcaHost = getLcaHostByName($pearDB);
	
	isset($lcaHost["LcaHost"][$host_name]) || $oreon->user->admin || !$isRestreint ? $key = true : $key = NULL;
	if ($key == NULL){
		include_once("alt_error.php");
	} else {
		$res =& $pearDB->query("SELECT * FROM host WHERE host_name = '".$host_name."'");
		if (PEAR::isError($res)) 
			print "Mysql Error : ".$res->getMessage();
		$res->fetchInto($host);	
		$host_id = getMyHostID($host["host_name"]);
		$service_id = getMyServiceID($_GET["service_description"], $host_id);
		$total_current_attempts = getMyServiceField($service_id, "service_max_check_attempts");
		
		$path = "./include/monitoring/objectDetails/";
		
		# Smarty template Init
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl, "./");
		
		if (!file_exists($oreon->Nagioscfg["comment_file"]))
			print ("downtime file not found");
		else	{
			$tab_comments_svc = array();
			$i = 0;
			$log = fopen($oreon->Nagioscfg["comment_file"], "r");
			if ($oreon->user->get_version() == 1){
				while ($str = fgets($log))	{
					print $str . "<br>";
					$res = preg_split("/;/", $str);
					if (preg_match("/^\[([0-9]*)\] SERVICE_COMMENT;/", $str, $matches)){
						if (!strcmp($res[2], $host_name)){
							print $res[6];
							$tab_comments_svc[$i] = array();
							$tab_comments_svc[$i]["id"] = $res[1];
							$tab_comments_svc[$i]["host_name"] = $res[2];
							$tab_comments_svc[$i]["service_descr"] = $res[3];
							$tab_comments_svc[$i]["time"] = date("d-m-Y G:i:s", $matches[1]);
							$tab_comments_svc[$i]["author"] = $res[5];
							$tab_comments_svc[$i]["comment"] = $res[6];
							$tab_comments_svc[$i]["persistent"] = $res[4];
						}
					}
					$i++;	
				}
			} else {
				while ($str = fgets($log))	{
                if (preg_match("/^hostcomment/", $str)){
                	$tab_comments_host[$i] = array();
                    $flag_host = 1;
                } else if (preg_match("/^servicecomment /", $str)){
                	$tab_comments_svc[$i] = array();
                    $flag_svc = 1;
                } else {
                    if(isset($flag_svc) && $flag_svc == 1) {
                      	$res = preg_split("/=/", $str);
                      	$res[0] = trim($res[0]);
                      	if (isset($res[1]))
                      		$res[1] = trim($res[1]);
                        if (preg_match('`comment_id$`', $res[0]))
                            $tab_comments_svc[$i]["id"] = $res[1];
                        if (preg_match('`service_description$`', $res[0])){
                          $tab_comments_svc[$i]["service_description"] = $res[1];}
                        if (preg_match('`host_name$`', $res[0]))
                          $tab_comments_svc[$i]["host_name"] = $res[1];
                        if (preg_match('`entry_time$`', $res[0]))
                        	$tab_comments_svc[$i]["time"] = date("d-m-Y G:i:s", $res[1]);
                        if (preg_match('`author$`', $res[0]))
                        	$tab_comments_svc[$i]["author"] = $res[1];
                        if (preg_match('`comment_data$`', $res[0]))
                        	$tab_comments_svc[$i]["comment"] = $res[1];
                        if (preg_match('`persistent$`', $res[0]))
                        	$tab_comments_svc[$i]["persistent"] = $res[1];
                        if (preg_match('`}$`', $str)){
                            $flag_svc = 0;
                        	$i++;
                        }
                    }
                }
			}
			
			}
		}

		foreach ($tab_comments_svc as $key => $value){
			if ( ($value["host_name"] == $_GET["host_name"]) && ($value["service_description"] == $_GET["service_description"]))
				;
			else
				unset($tab_comments_svc[$key]);
		}
		
		$en = array("0" => "No", "1" => "Yes");
		
		$en_acknowledge_text = array("1" => $lang ["m_mon_disack"], "0" => $lang ["m_mon_ack"]);
		$en_acknowledge = array("1" => "0", "0" => "1");
		
		$en_disable = array("1" => $lang ["m_mon_enabled"], "0" => $lang ["m_mon_disabled"]);
		$en_inv = array("1" => "0", "0" => "1");
		$en_inv_text = array("1" => $lang ["m_mon_disable"], "0" => $lang ["m_mon_enable"]);
		$color_onoff = array("1" => "#00ff00", "0" => "#ff0000");		
		$color_onoff_inv = array("0" => "#00ff00", "1" => "#ff0000");		
		$img_en = array("0" => "<img src='./img/icones/16x16/element_next.gif' border='0'>", "1" => "<img src='./img/icones/16x16/element_previous.gif' border='0'>");
		
		/*
		 * Ajust data for beeing displayed in template
		 */
		
		 $service_status[$host_name."_".$svc_description]["status_color"] = $oreon->optGen["color_".strtolower($service_status[$host_name."_".$svc_description]["current_state"])];
		 $service_status[$host_name."_".$svc_description]["last_check"] = date($lang["date_time_format"], $service_status[$host_name."_".$svc_description]["last_check"]);
		 $service_status[$host_name."_".$svc_description]["next_check"] = date($lang["date_time_format"], $service_status[$host_name."_".$svc_description]["next_check"]);
		!$service_status[$host_name."_".$svc_description]["check_latency"] ? $service_status[$host_name."_".$svc_description]["check_latency"] = "< 1 second" : $service_status[$host_name."_".$svc_description]["check_latency"] = $service_status[$host_name."_".$svc_description]["check_latency"] . " seconds";
		!$service_status[$host_name."_".$svc_description]["check_execution_time"] ? $service_status[$host_name."_".$svc_description]["check_execution_time"] = "< 1 second" : $service_status[$host_name."_".$svc_description]["check_execution_time"] = $service_status[$host_name."_".$svc_description]["check_execution_time"] . " seconds";
		!$service_status[$host_name."_".$svc_description]["last_notification"] ? $service_status[$host_name."_".$svc_description]["notification"] = "": $service_status[$host_name."_".$svc_description]["last_notification"] = date($lang["date_time_format"], $service_status[$host_name."_".$svc_description]["last_notification"]);
		!$service_status[$host_name."_".$svc_description]["last_state_change"] ? $service_status[$host_name."_".$svc_description]["duration"] = Duration::toString($service_status[$host_name."_".$svc_description]["last_time_".strtolower($service_status[$host_name."_".$svc_description]["current_state"])]) : $service_status[$host_name."_".$svc_description]["duration"] = Duration::toString(time() - $service_status[$host_name."_".$svc_description]["last_state_change"]);
		!$service_status[$host_name."_".$svc_description]["last_state_change"] ? $service_status[$host_name."_".$svc_description]["last_state_change"] = "": $service_status[$host_name."_".$svc_description]["last_state_change"] = date($lang["date_time_format"],$service_status[$host_name."_".$svc_description]["last_state_change"]);
		 $service_status[$host_name."_".$svc_description]["last_update"] = date($lang["date_time_format"], time());
		!$service_status[$host_name."_".$svc_description]["is_flapping"] ? $service_status[$host_name."_".$svc_description]["is_flapping"] = $en[$service_status[$host_name."_".$svc_description]["is_flapping"]] : $service_status[$host_name."_".$svc_description]["is_flapping"] = date($lang["date_time_format"], $service_status[$host_name."_".$svc_description]["is_flapping"]);
		
		$tab_status = array();
		foreach ($tab_host_service[$host_name] as $key_name => $s){
			if (!isset($tab_status[$service_status[$host_name."_".$key_name]["current_state"]]))
				$tab_status[$service_status[$host_name."_".$key_name]["current_state"]] = 0;
			$tab_status[$service_status[$host_name."_".$key_name]["current_state"]]++;
		}
		$status = NULL;
		foreach ($tab_status as $key => $value)
			$status .= "&value[".$key."]=".$value;
		
		$tpl->assign("lang", $lang);
		$tpl->assign("p", $p);
		$tpl->assign("o", $o);
		$tpl->assign("en", $en);
		$tpl->assign("en_inv", $en_inv);
		$tpl->assign("en_inv_text", $en_inv_text);
		$tpl->assign("img_en", $img_en);
		$tpl->assign("color_onoff", $color_onoff);
		$tpl->assign("color_onoff_inv", $color_onoff_inv);
		$tpl->assign("en_disable", $en_disable);
		$tpl->assign("total_current_attempt", $total_current_attempts);
		$tpl->assign("en_acknowledge_text", $en_acknowledge_text);
		$tpl->assign("en_acknowledge", $en_acknowledge);
		$tpl->assign("actpass", array("0"=>$lang["m_mon_active"], "1"=>$lang["m_mon_passive"]));
		$tpl->assign("harsof", array("0"=>$lang["m_mon_soft"], "1"=>$lang["m_mon_hard"]));
		$tpl->assign("status", $status);
		$tpl->assign("h", $host);
		$tpl->assign("lcaTopo", $oreon->user->lcaTopo);
		$tpl->assign("count_comments_svc", count($tab_comments_svc));
		$tpl->assign("tab_comments_svc", $tab_comments_svc);
		$tpl->assign("service_id", getMyServiceID($svc_description, $host["host_id"]));
		$tpl->assign("host_data", $host_status[$host_name]);
		$tpl->assign("service_data", $service_status[$host_name."_".$svc_description]);
		$tpl->assign("svc_description", $svc_description);
		$tpl->display("serviceDetails.ihtml");
	}
?>