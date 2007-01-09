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

	if (isset($_GET["host_name"]) && $_GET["host_name"])
		$host_name = $_GET["host_name"];
	else
		foreach ($_GET["select"] as $key => $value)
			$host_name = $key;
	
	$lcaHost = getLcaHostByName($pearDB);
	
	isset($lcaHost["LcaHost"][$host_name]) || $oreon->user->admin || !$isRestreint ? $key = true : $key = NULL;
	if ($key == NULL){
		include_once("alt_error.php");
	} else {
		$res =& $pearDB->query("SELECT * FROM host WHERE host_name = '".$host_name."'");
		if (PEAR::isError($res))
			print "Mysql Error : ".$res->getMessage();
		$res->fetchInto($hostDB);
		$current_attempts = getMyHostField($hostDB["host_id"], "host_max_check_attempts");

		$res =& $pearDB->query("SELECT * FROM inventory_index WHERE host_id = '".$hostDB["host_name"]."'");
		if (PEAR::isError($res))
			print "Mysql Error : ".$res->getMessage();
		$res->fetchInto($inventory);

		if ($inventory["type_ressources"] == 0){
			$url_id = "p=7&o=t&host_id=" . $key;
		} else if ($inventory["type_ressources"] != 0 && $inventory["type_ressources"] != NULL){
			$url_id = "p=7&o=o&host_id=" . $key;
		} else
			$url_id = NULL;

		$path = "./include/monitoring/objectDetails/";

		# Smarty template Init
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl, "./");

		if (!file_exists($oreon->Nagioscfg["comment_file"]))
			print ("downtime file not found");
		else	{
			$log = fopen($oreon->Nagioscfg["comment_file"], "r");
			$tab_comments_host = array();
			$i = 0;
			while ($str = fgets($log))	{
				$res = preg_split("/;/", $str);
				if (preg_match("/^\[([0-9]*)\] HOST_COMMENT;/", $str, $matches)){
					if (!strcmp($res[2], $host_name)){
						$tab_comments_host[$i] = array();
						$tab_comments_host[$i]["id"] = $res[1];
						$tab_comments_host[$i]["host_name"] = $res[2];
						$tab_comments_host[$i]["time"] = date("d-m-Y G:i:s", $matches[1]);
						$tab_comments_host[$i]["author"] = $res[4];
						$tab_comments_host[$i]["comment"] = $res[5];
						$tab_comments_host[$i]["persistent"] = $res[3];
					}
				}
				$i++;
			}
		}
		
		$en = array("0" => "No", "1" => "Yes");
		
		$en_acknowledge_text = array("1" => $lang ["m_mon_disack"], "0" => $lang ["m_mon_ack"]);
		$en_acknowledge = array("1" => "0", "0" => "1");
		
		$en_inv = array("1" => "0", "0" => "1");
		$en_inv_text = array("1" => $lang ["m_mon_disable"], "0" => $lang ["m_mon_enable"]);
		$color_onoff = array("1" => "#00ff00", "0" => "#ff0000");
		$color_onoff_inv = array("0" => "#00ff00", "1" => "#ff0000");
		$en_disable = array("1" => $lang ["m_mon_enabled"], "0" => $lang ["m_mon_disabled"]);

		$img_en = array("0" => "<img src='./img/icones/16x16/element_next.gif' border='0'>", "1" => "<img src='./img/icones/16x16/element_previous.gif' border='0'>");

		$host_status[$host_name]["status_color"] = $oreon->optGen["color_".strtolower($host_status[$host_name]["current_state"])];
		$host_status[$host_name]["last_check"] = date($lang["date_time_format"], $host_status[$host_name]["last_check"]);
		$host_status[$host_name]["next_check"] = $host_status[$host_name]["next_check"] ? date($lang["date_time_format"], $host_status[$host_name]["next_check"]) : "";
		!$host_status[$host_name]["last_notification"] ? $host_status[$host_name]["last_notification"] = "": $host_status[$host_name]["last_notification"] = date($lang["date_time_format"], $host_status[$host_name]["last_notification"]);
		!$host_status[$host_name]["last_state_change"] ? $host_status[$host_name]["duration"] = "" : $host_status[$host_name]["duration"] = Duration::toString(time() - $host_status[$host_name]["last_state_change"]);
		!$host_status[$host_name]["last_state_change"] ? $host_status[$host_name]["last_state_change"] = "": $host_status[$host_name]["last_state_change"] = date($lang["date_time_format"],$host_status[$host_name]["last_state_change"]);
		$host_status[$host_name]["last_update"] = date($lang["date_time_format"], time());

		$tab_status = array("1" => "HARD", "0" => "SOFT");
		$host_status[$host_name]["state_type"] = $tab_status[$host_status[$host_name]["state_type"]];

		$host_status[$host_name]["is_flapping"] = $en[$host_status[$host_name]["is_flapping"]];

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
		$tpl->assign("en", $en);
		$tpl->assign("en_inv", $en_inv);
		$tpl->assign("current_attempts", $current_attempts);
		$tpl->assign("en_inv_text", $en_inv_text);
		$tpl->assign("img_en", $img_en);
		$tpl->assign("color_onoff", $color_onoff);
		$tpl->assign("color_onoff_inv", $color_onoff_inv);
		$tpl->assign("en_disable", $en_disable);
		$tpl->assign("img_en", $img_en);
		$tpl->assign("status", $status);
		$tpl->assign("en_acknowledge_text", $en_acknowledge_text);
		$tpl->assign("en_acknowledge", $en_acknowledge);
		
		$tpl->assign("h", $hostDB);
		$tpl->assign("url_id", $url_id);
		$tpl->assign("tab_comments_host", $tab_comments_host);
		$tpl->assign("host_data", $host_status[$host_name]);
		$tpl->assign("tools", "Tools");
		$tpl->display("hostDetails.ihtml");
	}
?>