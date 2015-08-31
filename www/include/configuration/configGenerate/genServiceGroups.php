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

global $generatedSG;

$generatedSG = array();

$handle = create_file($nagiosCFGPath.$tab['id']."/servicegroups.cfg", $oreon->user->get_name());
$DBRESULT = $pearDB->query("SELECT * FROM `servicegroup` WHERE sg_activate = '1' ORDER BY `sg_name`");

$serviceGroup = array();
$i = 1;
$str = NULL;
while ($serviceGroup = $DBRESULT->fetchRow()) {
    $generated = 0;
    $strDef = "";
    $strTemp = NULL;

    if (isset($gbArr[5][$serviceGroup["sg_id"]])) {
        $ret["comment"] ? ($strDef .= "# '" . $serviceGroup["sg_name"] . "' servicegroup definition " . $i . "\n") : NULL;
        if ($ret["comment"] && $serviceGroup["sg_comment"])	{
            $comment = array();
            $comment = explode("\n", $serviceGroup["sg_comment"]);
            foreach ($comment as $cmt)
                $strDef .= "# ".$cmt."\n";
        }
        $strDef .= "define servicegroup{\n";
        $serviceGroup["sg_name"] = str_replace("#S#", "/", $serviceGroup["sg_name"]);
        $serviceGroup["sg_name"] = str_replace("#BS#", "\\", $serviceGroup["sg_name"]);

        if ($serviceGroup["sg_name"]) {
            $strDef .= print_line("servicegroup_name", $serviceGroup["sg_name"]);
            $generated++;
        }
        if ($serviceGroup["sg_alias"])
            $strDef .= print_line("alias", $serviceGroup["sg_alias"]);

        /*
         * Service members
         */
        $service = array();
        $DBRESULT2 = $pearDB->query("SELECT service_description, service_id, host_name, host_id " .
									"FROM servicegroup_relation, service, host, host_service_relation " .
									"WHERE servicegroup_relation.servicegroup_sg_id = '".$serviceGroup["sg_id"]."' " .
                                    "AND service.service_id = servicegroup_relation.service_service_id " .
                                    "AND host.host_id = servicegroup_relation.host_host_id " .
                                    "AND service.service_activate = '1' " .
                                    "AND host.host_activate = '1' " .
                                    "AND host.host_id = host_service_relation.host_host_id  " .
                                    "AND host_service_relation.service_service_id = service.service_id  " .
                                    "AND servicegroup_relation.host_host_id IS NOT NULL");
        while ($service = $DBRESULT2->fetchRow()){
            if (isset($gbArr[4][$service["service_id"]]))	{
                if ($service["host_id"])	{
                    if (isset($gbArr[2][$service["host_id"]]) && isset($host_instance[$service["host_id"]])){

                        $service["service_description"] = str_replace("#S#", "/", $service["service_description"]);
                        $service["service_description"] = str_replace("#BS#", "\\", $service["service_description"]);
                        $strTemp != NULL ? $strTemp .= ", ".$service["host_name"].", ".$service["service_description"] : $strTemp = $service["host_name"].", ".$service["service_description"];
                        $generated++;
                    }
                }
            }
        }

        /*****************************************************************************************************************************************************/
        /* INOVEN ADDITION frb 2011/05/16 - allowing to add services via hostgroups (one group 3P for 3rd party) but then get service per 3rd party provider */
        /*****************************************************************************************************************************************************/
        // This is the same logic as the previous one except
        //   "FROM servicegroup_relation, service, host, host_service_relation, hostgroup_relation "
        //   "AND host.host_id = hostgroup_relation.host_host_id and host_service_relation.hostgroup_hg_id = hostgroup_relat    ion.hostgroup_hg_id  " .
        $DBRESULT2 = $pearDB->query("SELECT service_description, service_id, host_name, host_id " .
                                     "FROM servicegroup_relation, service, host, host_service_relation, hostgroup_relation " .
                                     "WHERE servicegroup_relation.servicegroup_sg_id = '".$serviceGroup["sg_id"]."' " .
                                     "AND service.service_id = servicegroup_relation.service_service_id " .
                                     "AND host.host_id = servicegroup_relation.host_host_id " .
                                     "AND service.service_activate = '1' " .
                                     "AND host.host_activate = '1' " .
                                     "AND host.host_id = hostgroup_relation.host_host_id and host_service_relation.hostgroup_hg_id = hostgroup_relation.hostgroup_hg_id  " .
                                     "AND host_service_relation.service_service_id = service.service_id  " .
                                     "AND servicegroup_relation.host_host_id IS NOT NULL");

        /* Standard addition of complete host groups to the service groups */
        while ($service =& $DBRESULT2->fetchRow()){
            if (isset($gbArr[4][$service["service_id"]]))   {
                if ($service["host_id"])        {
                    if (isset($gbArr[2][$service["host_id"]]) && isset($host_instance[$service["host_id"]])){

                        $service["service_description"] = str_replace("#S#", "/", $service["service_description"]);
                        $service["service_description"] = str_replace("#BS#", "\\", $service["service_description"]);
                        $strTemp != NULL ? $strTemp .= ", ".$service["host_name"].", ".$service["service_description"] : $strTemp = $service["host_name"].", ".$service["service_description"];
                        $generated++;
                    }
                }
            }
        }

        /*****************************************************************************************************************************************************/
        /* INOVEN ADDITION frb 2011/05/16 - END */
        /*****************************************************************************************************************************************************/

        $DBRESULT2 = $pearDB->query("SELECT service_description, service_id, hg_id " .
									"FROM servicegroup_relation, service, hostgroup " .
									"WHERE servicegroup_sg_id = '".$serviceGroup["sg_id"]."' " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND hostgroup.hg_id = servicegroup_relation.hostgroup_hg_id " .
									"AND service.service_activate = '1' " .
									"AND hostgroup.hg_activate = '1' " .
									"AND servicegroup_relation.hostgroup_hg_id IS NOT NULL ");
        while($service = $DBRESULT2->fetchRow()){
            if (isset($gbArr[4][$service["service_id"]]))	{
                if ($service["hg_id"])	{
                    if (isset($gbArr[3][$service["hg_id"]])){
                        $DBRESULT3 = $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$service["hg_id"]."'");
                        while($host = $DBRESULT3->fetchRow())	{
                            if (isset($gbArr[2][$host["host_host_id"]]) && isset($host_instance[$host["host_host_id"]])){
                                $service["service_description"] = str_replace("#S#", "/", $service["service_description"]);
                                $service["service_description"] = str_replace("#BS#", "\\", $service["service_description"]);
                                $strTemp != NULL ? $strTemp .= ", ".getMyHostName($host["host_host_id"]).", ".$service["service_description"] : $strTemp = getMyHostName($host["host_host_id"]).", ".$service["service_description"];
                                $generated++;
                            }
                        }
                        $DBRESULT3->free();
                    }
                }
            }
        }
        $DBRESULT2->free();
        unset($service);

        /* ******************************************
         * Generate service linked to servictemplates
         */
        $linkedToTpl = 0;
        $DBRESULT2 = $pearDB->query("SELECT service_description, service_id " .
									"FROM servicegroup_relation, service " .
									"WHERE servicegroup_sg_id = '".$serviceGroup["sg_id"]."' " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND service.service_activate = '1' AND service.service_register = '0'");
        while ($service = $DBRESULT2->fetchRow()) {
            $linkedToTpl++;
        }
        $DBRESULT2->free();
        unset($service);

        if ($strTemp) {
            $strDef .= print_line("members", $strTemp);
        }
        $strDef .= "}\n\n";
        $i++;
    }
    if (($generated && $strTemp) || $linkedToTpl) {
        $str .= $strDef;
        $generatedSG[$serviceGroup["sg_id"]] = $serviceGroup["sg_name"];
    }
    unset($serviceGroup);
}

write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/servicegroups.cfg");
fclose($handle);

setFileMod($nagiosCFGPath.$tab['id']."/servicegroups.cfg");

$DBRESULT->free();
unset($str);
unset($i);
