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

require_once "@CENTREON_ETC@/centreon.conf.php";
require_once $centreon_path."www/class/centreonXMLBGRequest.class.php";

if (!isset($_GET['sid']) || !isset($_GET['refresh_rate'])) {
    exit;
}

$refresh_rate = (int)$_GET['refresh_rate'] / 1000;
$refresh_rate += ($refresh_rate / 2);

$obj = new CentreonXMLBGRequest($_GET['sid'], 1, 1, 0, 1);

CentreonSession::start();
if (!isset($_SESSION['centreon'])) {
    exit;
}
$centreon = $_SESSION['centreon'];
if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    exit;               
}

$service_state_label = array(0 => "OK", 1 => "Warning", 2 => "Critical", 3 => "Unknown");
$service_class_label = array(0 => "success", 1 => "warning", 2 => "error", 3 => "alert");
$host_state_label = array(0 => "Up", 1 => "Down", 2 => "Unreachable");
$host_class_label = array(0 => "success", 1 => "error", 2 => "alert");

$sql = "SELECT name, description, s.state
        FROM services s, hosts h %s
        WHERE h.host_id = s.host_id
        AND s.last_hard_state_change > (UNIX_TIMESTAMP(NOW()) - ".(int)$refresh_rate.")
        %s
        UNION
        SELECT name, NULL, h.state
        FROM hosts h %s
        WHERE h.last_hard_state_change > (UNIX_TIMESTAMP(NOW()) - ".(int)$refresh_rate.")
        %s";
if ($obj->is_admin) {
    $sql = sprintf($sql, "", "", "", "");
} else {
    $sql = sprintf(
               $sql, 
               ", centreon_acl acl", 
               "AND acl.service_id = s.service_id AND acl.host_id = h.host_id ",
               ", centreon_acl acl",
               "AND acl.host_id = h.host_id"
           );
}
$res = $obj->DBC->query($sql);
$obj->XML->startElement("data");
if (!isset($_SESSION['centreon_notification_preferences'])) {
     $user_id = $centreon->user->get_id();
     $res_pref = $obj->DB->query("SELECT cp_key, cp_value
         FROM contact_param 
         WHERE cp_key LIKE 'monitoring%notification%'
         AND cp_contact_id = '".$obj->DB->escape($user_id)."'");
     $notification_preferences = array();
     while ($row_pref = $res_pref->fetchRow()) {
         $notification_preferences[$row_pref['cp_key']] = $row_pref['cp_value'];
     }
     $_SESSION['centreon_notification_preferences'] = $notification_preferences;
} else {
    $notification_preferences = $_SESSION['centreon_notification_preferences'];
}
while ($row = $res->fetchRow()) {
    $obj->XML->startElement("message");
    if ($row['description']) {
        if (isset($notification_preferences['monitoring_svc_notification_'.$row['state']])) {
            $obj->XML->writeAttribute(
                "output", 
                sprintf(
                    "%s / %s is %s", 
                    $row['name'], 
                    $row['description'], 
                    $service_state_label[$row['state']]
                )
            );
            $obj->XML->writeAttribute("class", $service_class_label[$row['state']]);
        }
        if (!isset($_SESSION['disable_sound']) && isset($notification_preferences['monitoring_sound_svc_notification_'.$row['state']]) && 
            $notification_preferences['monitoring_sound_svc_notification_'.$row['state']]) {
            $obj->XML->writeAttribute("sound", $notification_preferences['monitoring_sound_svc_notification_'.$row['state']]);
        }
    } else {
        if (isset($notification_preferences['monitoring_host_notification_'.$row['state']])) {
            $obj->XML->writeAttribute(
                "output",
                sprintf(
                    "%s is %s", 
                    $row['name'], 
                    $host_state_label[$row['state']]
                )
            );
            $obj->XML->writeAttribute("class", $host_class_label[$row['state']]);
        }
        if (!isset($_SESSION['disable_sound']) && isset($notification_preferences['monitoring_sound_host_notification_'.$row['state']]) && 
            $notification_preferences['monitoring_sound_host_notification_'.$row['state']]) {
            $obj->XML->writeAttribute("sound", $notification_preferences['monitoring_sound_host_notification_'.$row['state']]);
        }
    }
    $obj->XML->endElement();
}
$obj->XML->endElement();
$obj->header();
$obj->XML->output();
