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

if (!isset($centreon)) {
    exit();
}

require_once ($centreon_path . "/www/class/centreonService.class.php");
require_once ($centreon_path . "/www/class/centreonCriticality.class.php");

$criticality = new CentreonCriticality($pearDB);

/*
 * Create contact relation Cache
 */
$cgSvcCache = array();
$DBRESULT2 = $pearDB->query("SELECT s.service_id, cg.cg_id, cg.cg_name FROM contactgroup_service_relation csr, contactgroup cg, service s WHERE csr.service_service_id = s.service_id AND csr.contactgroup_cg_id = cg.cg_id");
while ($cg = $DBRESULT2->fetchRow())	{
    if (!isset($cgSvcCache[$cg["service_id"]]))		
        $cgSvcCache[$cg["service_id"]] = array();	
    $cgSvcCache[$cg["service_id"]][$cg["cg_id"]] = $cg["cg_name"];
}
$DBRESULT2->free();
unset($cg);

/*
 * Criticality cache
 */
$critCache = array();
$critRes = $pearDB->query("SELECT scr.sc_id, scr.service_service_id
                                   FROM service_categories_relation scr, service s, service_categories sc
                                   WHERE scr.service_service_id = s.service_id
                                   AND scr.sc_id = sc.sc_id
                                   AND s.service_register = '0'
                                   AND level IS NOT NULL
                                   ORDER BY level DESC");
while ($critRow = $critRes->fetchRow()) {
    $critCache[$critRow['service_service_id']] = $critRow['sc_id'];
}

/*
 * Initiate Service Template Cache
 */
$svcTplCache = array();

/*
 * Initiate checkPeriod Cache
 */
$cpCache = array();

/*
 * Initiate notifPeriod Cache
 */
$npCache = array();

/*
 * Create file
 */
$handle = create_file($nagiosCFGPath.$tab['id']."/serviceTemplates.cfg", $oreon->user->get_name());

/*
 * Get Service List
 */
$i = 1;
$str = NULL;
$service = array();
$DBRESULT = $pearDB->query("SELECT * FROM `service` WHERE `service_activate` = '1' AND `service_register` = '0' ORDER BY `service_description`");
while ($service = $DBRESULT->fetchRow()) {
    $LinkedToHost = 0;
    $strDef = "";
	
    /*
     * Convert sp�cial char
     */
    $service["service_description"] = convertServiceSpecialChar($service["service_description"]);
    $service["service_alias"] 		= convertServiceSpecialChar($service["service_alias"]);
		
    if (isset($gbArr[4][$service["service_id"]])) {
			
        /*
         * Fill the cache of template
         */
        $svcTplCache[$service["service_id"]] = $service["service_description"]; 
			
        /*
         *  Can merge multiple Host or HostGroup Definition
         */
        $strTMP = NULL;
        $parent = false;
        $ret["comment"] ? ($strTMP .= "# '" . $service["service_description"] . "' service definition " . $i . "\n") : NULL;
        if ($ret["comment"] && $service["service_comment"])	{
            $comment = array();
            $comment = explode("\n", $service["service_comment"]);
            foreach ($comment as $cmt) {
                $strTMP .= "# ".$cmt."\n";					
            }
        }

        $strTMP .= "define service{\n";

        if ($service["service_description"]) 
            $strTMP .= print_line("name", $service["service_description"]);
        if ($service["service_alias"]) 
            $strTMP .= print_line("service_description", $service["service_alias"]);
			
        /*
         * Criticality level
         */
        if (isset($critCache[$service['service_id']])) {
            $critData = $criticality->getData($critCache[$service['service_id']], true);
            if (!is_null($critData)) {
                $strTMP .= print_line("_CRITICALITY_LEVEL", $critData['level']);
                $strTMP .= print_line("_CRITICALITY_ID", $critData['sc_id']);
            }
        }
                        
        /*
         * Template Model Relation
         */
        if ($service["service_template_model_stm_id"]) {
            $serviceTemplate = array();
            $DBRESULT2 = $pearDB->query("SELECT service.service_description FROM service WHERE service.service_id = '".$service["service_template_model_stm_id"]."' LIMIT 1");
            while ($serviceTemplate = $DBRESULT2->fetchRow())
                $strTMP .= print_line("use", convertServiceSpecialChar($serviceTemplate["service_description"]));
            $DBRESULT2->free();
            unset($serviceTemplate);		
        }
			
        $serviceGroup = array();
        $strTMPTemp = NULL;
        $DBRESULT2 = $pearDB->query("SELECT DISTINCT sg.sg_id, sg.sg_name FROM servicegroup_relation sgr, servicegroup sg WHERE sgr.service_service_id = '".$service["service_id"]."' AND sgr.servicegroup_sg_id = sg.sg_id ORDER BY `sg_name`");
        while($serviceGroup = $DBRESULT2->fetchRow())	{
            if (isset($gbArr[5][$serviceGroup["sg_id"]]))
                $strTMPTemp != NULL ? $strTMPTemp .= ", ".$serviceGroup["sg_name"] : $strTMPTemp = $serviceGroup["sg_name"];
        }
        $DBRESULT2->free();
        unset($serviceGroup);
			
        if ($strTMPTemp) 
            $strTMP .= print_line("servicegroups", $strTMPTemp);
        unset($strTMPTemp);
		
        if ($service["service_is_volatile"] != 2) 
            $strTMP .= print_line("is_volatile", $service["service_is_volatile"] == 1 ? "1": "0");
			
        /*
         * Check Command
         */
        $command = NULL;
        $command = getMyCheckCmdParam($service["service_id"]);
        if ($command)
            $strTMP .= print_line("check_command", $command);
			
        if ($service["service_max_check_attempts"] != NULL) 
            $strTMP .= print_line("max_check_attempts", $service["service_max_check_attempts"]);
        if ($service["service_normal_check_interval"] != NULL) 
            $strTMP .= print_line("normal_check_interval", $service["service_normal_check_interval"]);
        if ($service["service_retry_check_interval"] != NULL) 
            $strTMP .= print_line("retry_check_interval", $service["service_retry_check_interval"]);
        if ($service["service_active_checks_enabled"] != 2) 
            $strTMP .= print_line("active_checks_enabled", $service["service_active_checks_enabled"] == 1 ? "1": "0");
        if ($service["service_passive_checks_enabled"] != 2) 
            $strTMP .= print_line("passive_checks_enabled", $service["service_passive_checks_enabled"] == 1 ? "1": "0");
			
        /*
         * Check Period
         */
        if ($service["timeperiod_tp_id"]) {
            $strTMP .= print_line("check_period", $timeperiods[$service["timeperiod_tp_id"]]);
            $cpCache[$service["service_id"]] = array("tp" => $service["timeperiod_tp_id"], "tpl" => $service["service_template_model_stm_id"]);
        } else {
            $cpCache[$service["service_id"]] = array("tpl" => $service["service_template_model_stm_id"]);
        }
					
        if ($service["service_parallelize_check"] != 2) 
            $strTMP .= print_line("parallelize_check", $service["service_parallelize_check"] == 1 ? "1": "0");
        if ($service["service_obsess_over_service"] != 2)
            $strTMP .= print_line("obsess_over_service", $service["service_obsess_over_service"] == 1 ? "1": "0");
        if ($service["service_check_freshness"] != 2) 
            $strTMP .= print_line("check_freshness", $service["service_check_freshness"] == 1 ? "1": "0");
        if ($service["service_freshness_threshold"] != NULL) 
            $strTMP .= print_line("freshness_threshold", $service["service_freshness_threshold"]);
			
        /*
         * Event_handler
         */ 
        if ($service["command_command_id2"])
            $strTMP .= print_line("event_handler", $commands[$service["command_command_id2"]].$service["command_command_id_arg2"]);
        if ($service["service_event_handler_enabled"] != 2) 
            $strTMP .= print_line("event_handler_enabled", $service["service_event_handler_enabled"] == 1 ? "1": "0");
        if ($service["service_low_flap_threshold"] != NULL) 
            $strTMP .= print_line("low_flap_threshold", $service["service_low_flap_threshold"]);
        if ($service["service_high_flap_threshold"] != NULL) 
            $strTMP .= print_line("high_flap_threshold", $service["service_high_flap_threshold"]);
        if ($service["service_flap_detection_enabled"] != 2) 
            $strTMP .= print_line("flap_detection_enabled", $service["service_flap_detection_enabled"] == 1 ? "1": "0");
        if ($service["service_process_perf_data"] != 2) 
            $strTMP .= print_line("process_perf_data", $service["service_process_perf_data"] == 1 ? "1": "0");
        if ($service["service_retain_status_information"] != 2) 
            $strTMP .= print_line("retain_status_information", $service["service_retain_status_information"] == 1 ? "1": "0");
        if ($service["service_retain_nonstatus_information"] != 2) 
            $strTMP .= print_line("retain_nonstatus_information", $service["service_retain_nonstatus_information"] == 1 ? "1": "0");
			
        /*
         * Notifications
         */
        if ($service["timeperiod_tp_id2"]) {
            $strTMP .= print_line("notification_period", $timeperiods[$service["timeperiod_tp_id2"]]);
            $npCache[$service["service_id"]] = array("tp" => $service["timeperiod_tp_id2"], "tpl" => $service["service_template_model_stm_id"]);
        } else {
            $npCache[$service["service_id"]] = array("tpl" => $service["service_template_model_stm_id"]);
        }
			
        if ($service["service_notification_interval"] != NULL) 
            $strTMP .= print_line("notification_interval", $service["service_notification_interval"]);
        if ($service["service_notification_options"]) 
            $strTMP .= print_line("notification_options", $service["service_notification_options"]);
        if ($service["service_notifications_enabled"] != 2) 
            $strTMP .= print_line("notifications_enabled", $service["service_notifications_enabled"] == 1 ? "1": "0");
        if ($service["service_first_notification_delay"] != NULL) 
            $strTMP .= print_line("first_notification_delay", $service["service_first_notification_delay"]);
		
        /*
         * Contact Group Relation
         */
        if (isset($cgSvcCache[$service["service_id"]])) {
            $strTMPTemp = "";
            foreach ($cgSvcCache[$service["service_id"]] as $cg_name) {
                if ($strTMPTemp != "") {
                    $strTMPTemp .= ",";
                }
                $strTMPTemp .= $cg_name;
            }
            if ($strTMPTemp) {
                if ($service['cg_additive_inheritance']) {
                    $strTMPTemp = "+".$strTMPTemp;
                }
                $strTMP .= print_line("contact_groups", str_replace(" ", "_", $strTMPTemp));
            }
        }
			
        /*
         * Contact Relation
         */
        $DBRESULT2 = $pearDB->query("SELECT c.contact_id, c.contact_name FROM contact_service_relation csr, contact c WHERE csr.service_service_id = '".$service["service_id"]."' AND csr.contact_id = c.contact_id AND c.contact_activate = '1' AND c.contact_register = 1 ORDER BY `contact_name`");
        $contact = array();
        $strTMPTemp = NULL;
        while ($contact = $DBRESULT2->fetchRow())	{
            if (isset($gbArr[0][$contact["contact_id"]])) {
                $strTMPTemp != NULL ? $strTMPTemp .= ", ".$contact["contact_name"] : $strTMPTemp = $contact["contact_name"];
            }
        }
        $DBRESULT2->free();
        if ($strTMPTemp) {
            if ($service['contact_additive_inheritance']) {
                $strTMPTemp = "+".$strTMPTemp;
            }
            $strTMP .= print_line("contacts", $strTMPTemp);
        }
        unset($contact);
			
        if ($service["service_stalking_options"]) {
            $strTMP .= print_line("stalking_options", $service["service_stalking_options"]);
        }
        if (!$service["service_register"]) {
            $strTMP .= print_line("register", "0");
        }
			
        if (isset($service["service_register"]) && $service["service_register"] == 0){
            $DBRESULT_TEMP = $pearDB->query("SELECT host_name FROM host, host_service_relation WHERE `service_service_id` = '".$service["service_id"]."' AND `host_id` = `host_host_id`");
            while ($template_link = $DBRESULT_TEMP->fetchRow())
                {
					$strTMP .= print_line(";TEMPLATE-HOST-LINK", $template_link["host_name"]);
                }
            unset($template_link);
            unset($DBRESULT_TEMP);
        }
				
			
        /*
         * On-demand macros
         */
        $rq = "SELECT svc_macro_name, svc_macro_value FROM on_demand_macro_service WHERE `svc_svc_id`=" . $service['service_id'];
        $DBRESULT3 = $pearDB->query($rq);
        while($od_macro = $DBRESULT3->fetchRow()) {
            $mac_name = str_replace("\$_SERVICE", "_", $od_macro['svc_macro_name']);
            $mac_name = str_replace("\$", "", $mac_name);
            $mac_name = str_replace("#S#", "/", $mac_name);
            $mac_name = str_replace("#BS##BS#", "\\", $mac_name);
            $mac_value = $od_macro['svc_macro_value'];
            $mac_value = str_replace("#S#", "/", $mac_value);
            $mac_value = str_replace("#BS##BS#", "\\", $mac_value);
            $strTMP .= print_line($mac_name, $mac_value);
        }
        $DBRESULT3->free();
		
        /*
         * Extended Informations
         */
        $svc_method = new CentreonService($pearDB);
			
        $DBRESULT3 = $pearDB->query("SELECT * FROM extended_service_information esi WHERE esi.service_service_id = '".$service["service_id"]."'");
        $esi = $DBRESULT3->fetchRow();
        if ($field = $esi["esi_notes"])
            $strTMP .= print_line("notes", $svc_method->replaceMacroInString($service["service_id"], $field));
        if ($field = $esi["esi_notes_url"])
            $strTMP .= print_line("notes_url", $svc_method->replaceMacroInString($service["service_id"], $field));
        if ($field = $esi["esi_action_url"])
            $strTMP .= print_line("action_url", $svc_method->replaceMacroInString($service["service_id"], $field));
        if ($field = getMyServiceExtendedInfoImage($service["service_id"], "esi_icon_image"))
            $strTMP .= print_line("icon_image", $svc_method->replaceMacroInString($service["service_id"], $field));
        if ($field = $esi["esi_icon_image_alt"])
            $strTMP .= print_line("icon_image_alt", $svc_method->replaceMacroInString($service["service_id"], $field));
			
        $strTMP .= "}\n\n";
        if (!$service["service_register"] || $LinkedToHost)	{
            $i++;
            $str .= $strTMP;
        }
        unset($parent);
        unset($strTMPTemp);
    }
}
unset($service);
write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/serviceTemplates.cfg");
fclose($handle);

setFileMod($nagiosCFGPath.$tab['id']."/serviceTemplates.cfg");

$DBRESULT->free();
unset($str);
unset($i);
