<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

if (!isset($centreon)) {
    exit();
}

function getMyHostRow($host_id = null, $rowdata)
{
    global $pearDB;

    if (!$host_id) {
        exit();
    }
    while (1) {
        $DBRESULT = $pearDB->query("SELECT host_" . $rowdata .
            ", host_template_model_htm_id FROM host WHERE host_id = '" . CentreonDB::escape($host_id) . "' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        if ($row["host_" . $rowdata]) {
            return $row["host_$rowdata"];
        } elseif ($row["host_template_model_htm_id"]) {
            $host_id = $row["host_template_model_htm_id"];
        } else {
            break;
        }
    }
}

function get_user_param($user_id, $pearDB)
{
    $list_param = array(
        'ack_sticky',
        'ack_notify',
        'ack_persistent',
        'ack_services',
        'force_active',
        'force_check'
    );
    $tab_row = array();
    foreach ($list_param as $param) {
        if (isset($_SESSION[$param])) {
            $tab_row[$param] = $_SESSION[$param];
        }
    }
    return $tab_row;
}

function set_user_param($user_id, $pearDB, $key, $value)
{
    $_SESSION[$key] = $value;
}

function get_notified_infos_for_host($host_id)
{
    global $pearDB;

    // Init vars
    $hostStack = array();
    $contacts = array();
    $contactGroups = array();

    // Get Host Notifications options
    $additive = false;
    $DBRESULT = $pearDB->query("SELECT contact_additive_inheritance, cg_additive_inheritance
            FROM host WHERE host_id = '" . CentreonDB::escape($host_id) . "'");
    $hostParam = $DBRESULT->fetchRow();

    $hostStack[] = array("host_id" => $host_id, "hostParam" => $hostParam);

    $firstTime = true;
    while (count($hostStack) > 0) {
        $myHost = $hostStack[count($hostStack) - 1];
        $currentHost = $myHost["host_id"];
        $hostParam = $myHost["hostParam"];
        array_pop($hostStack);

        $DBRESULT = $pearDB->query("SELECT host_tpl_id, contact_additive_inheritance, cg_additive_inheritance
                FROM host h, host_template_relation htr
                WHERE htr.host_host_id=h.host_id 
                AND h.host_id = '" . $currentHost . "'");

        // Look for contactgroups
        if (($hostParam['cg_additive_inheritance'] == 1) || $firstTime) {
            if (!$firstTime || ($hostParam['cg_additive_inheritance'] == 1)) {
                $additive = true;
            }
            get_contactgroups_for_hosts($currentHost, $contactGroups, true);
        }

        // Look for contacts
        if (($hostParam['contact_additive_inheritance'] == 1) || $firstTime) {
            if (!$firstTime || ($hostParam['contact_additive_inheritance'] == 1)) {
                $additive = true;
            }
            get_contacts_for_hosts($currentHost, $contacts, true);
        }

        $firstTime = false;

        if (((count($contacts) == 0) && (count($contactGroups) == 0) || ($additive))) {
            for ($i = 0; $h = $DBRESULT->fetchRow(); $i++) {
                if ($h["host_tpl_id"] != "") {
                    $hostStack[] = array("host_id" => $h["host_tpl_id"],
                        "hostParam" => array(
                            "contact_additive_inheritance" => $h["contact_additive_inheritance"],
                            "cg_additive_inheritance" => $h["cg_additive_inheritance"])
                    );
                }
            }
            $DBRESULT->free();
        }
    }

    return array('contacts' => $contacts,
        'contactGroups' => $contactGroups);
}

function get_contactgroups_for_hosts($host_list, &$contactGroups, $withTpl = false)
{
    global $pearDB;

    if (!is_array($host_list)) {
        $host_list = array($host_list);
    }

    if ($withTpl) {
        $host = new CentreonHost($pearDB);
        $host_list2 = $host_list;
        foreach ($host_list2 as $host_id) {
            $templates = $host->getTemplateChain($host_id, array(), -1, true, "host_name,host_id");
            foreach ($templates as $template) {
                $host_list[] = $template['host_id'];
            }
        }
    }
    
    $DBRESULT = $pearDB->query("SELECT cg_name FROM contactgroup cg, contactgroup_host_relation cghr
            WHERE cghr.contactgroup_cg_id = cg.cg_id AND cghr.host_host_id IN (" . implode(',', $host_list) . ")
            GROUP BY cg_name");
    for ($i = 0; $cg = $DBRESULT->fetchRow(); $i++) {
        if (!in_array($cg["cg_name"], $contactGroups)) {
            $contactGroups[] = $cg["cg_name"];
        }
    }
    $DBRESULT->free();
}

function get_contacts_for_hosts($host_list, &$contacts, $withTpl = false)
{
    global $pearDB;

    if (!is_array($host_list)) {
        $host_list = array($host_list);
    }
    
    
    if ($withTpl) {
        $host = new CentreonHost($pearDB);
        $host_list2 = $host_list;
        foreach ($host_list2 as $host_id) {
            $templates = $host->getTemplateChain($host_id, array(), -1, true, "host_name,host_id");
            foreach ($templates as $template) {
                $host_list[] = $template['host_id'];
            }
        }
    }
    
    
    
    $DBRESULT = $pearDB->query("SELECT contact_name FROM contact c, contact_host_relation chr
            WHERE chr.contact_id = c.contact_id AND chr.host_host_id IN (" . implode(',', $host_list) . ")
            GROUP BY contact_name");
    for ($i = 0; $c = $DBRESULT->fetchRow(); $i++) {
        if (!in_array($c["contact_name"], $contacts)) {
            $contacts[] = $c["contact_name"];
        }
    }
    $DBRESULT->free();
}

function get_notified_infos_for_service($service_id, $host_id)
{
    global $pearDB;

    // Init vars
    $serviceStack = array();
    $contacts = array();
    $contactGroups = array();

    // Get Service Notifications options
    $additive = false;
    $DBRESULT = $pearDB->query("SELECT contact_additive_inheritance, cg_additive_inheritance, service_inherit_contacts_from_host
            FROM service WHERE service_id = '" . CentreonDB::escape($service_id) . "'");
    $serviceParam = $DBRESULT->fetchRow();
    $inherit_from_host = $serviceParam["service_inherit_contacts_from_host"];

    $serviceStack[] = array(
        "service_id" => $service_id,
        "serviceParam" => $serviceParam
    );

    $firstTime = true;
    while (count($serviceStack) > 0) {
        $myService = $serviceStack[count($serviceStack) - 1];
        $currentservice = $myService["service_id"];
        $serviceParam = $myService["serviceParam"];
        array_pop($serviceStack);


        $DBRESULT = $pearDB->query("SELECT contact_additive_inheritance, "
                . "cg_additive_inheritance, service_template_model_stm_id "
                . "FROM service WHERE service_id = '" . $currentservice . "'");

        // Look for contacts
        if ($serviceParam['contact_additive_inheritance'] == 1 || $firstTime) {
            if (!$firstTime || ($serviceParam['contact_additive_inheritance'] == 1)) {
                $additive = true;
            }
            get_contacts_for_services($currentservice, $contacts, true);
        }

        // Look for contactgroups
        if ($serviceParam['cg_additive_inheritance'] == 1 || $firstTime) {
            if (!$firstTime || ($serviceParam['cg_additive_inheritance'] == 1)) {
                $additive = true;
            }
            get_contactgroups_for_services($currentservice, $contactGroups, true);
        }

        $firstTime = false;

        if ((count($contacts) == 0) || (count($contactGroups) == 0) || ($additive)) {
            for ($i = 0; $s = $DBRESULT->fetchrow(); $i++) {
                if ($s["service_template_model_stm_id"] != "") {
                    $serviceStack[] = array("service_id" => $s["service_template_model_stm_id"],
                        "serviceParam" => array(
                            "contact_additive_inheritance" => $s["contact_additive_inheritance"],
                            "cg_additive_inheritance" => $s["cg_additive_inheritance"])
                    );
                }
            }
            $additive = false;
            $DBRESULT->free();
        }
    }

    if ((count($contacts) == 0) && (count($contactGroups) == 0) && ($inherit_from_host)) {
        return get_notified_infos_for_host($host_id);
    } else {
        return array('contacts' => $contacts,
            'contactGroups' => $contactGroups);
    }
}

function get_contactgroups_for_services($service_list, &$contactGroups, $withTpl = false)
{
    global $pearDB;

    if (!is_array($service_list)) {
        if (!$service_list) {
            return;
        }
        $service_list = array($service_list);
    }
    
    if ($withTpl) {
        $service_list2 = $service_list;
        foreach ($service_list2 as $service_id) {
            $templates = getListTemplates($pearDB, $service_id);
            foreach ($templates as $template) {
                $service_list[] = $template['service_id'];
            }
        }
    }

    $DBRESULT = $pearDB->query("SELECT cg_name FROM contactgroup cg, contactgroup_service_relation cgsr
            WHERE cgsr.contactgroup_cg_id = cg.cg_id AND cgsr.service_service_id IN (" . implode(',', $service_list) . ")
            GROUP BY cg_name");
    for ($i = 0; $cg = $DBRESULT->fetchRow(); $i++) {
        if (!in_array($cg["cg_name"], $contactGroups)) {
            $contactGroups[] = $cg["cg_name"];
        }
    }
    $DBRESULT->free();
}

function get_contacts_for_services($service_list, &$contacts, $withTpl = false)
{
    global $pearDB;

    if (!is_array($service_list)) {
        if (!$service_list) {
            return;
        }
        $service_list = array($service_list);
    }

    if ($withTpl) {
        $service_list2 = $service_list;
        foreach ($service_list2 as $service_id) {
            $templates = getListTemplates($pearDB, $service_id);
            foreach ($templates as $template) {
                $service_list[] = $template['service_id'];
            }
        }
    }

    $DBRESULT = $pearDB->query("SELECT contact_name FROM contact c, contact_service_relation csr
            WHERE csr.contact_id = c.contact_id AND csr.service_service_id IN (" . implode(',', $service_list) . ")
            GROUP BY contact_name");
    for ($i = 0; $c = $DBRESULT->fetchRow(); $i++) {
        if (!in_array($c["contact_name"], $contacts)) {
            $contacts[] = $c["contact_name"];
        }
    }
    $DBRESULT->free();
}
