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
 * SVN : $URL
 * SVN : $Id
 *
 */

    ini_set("display_errors", "Off");

    include_once "@CENTREON_ETC@/centreon.conf.php";

    include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
    include_once $centreon_path . "www/include/monitoring/status/Common/common-Func.php";
    include_once $centreon_path . "www/include/common/common-Func.php";
    include_once $centreon_path . "www/class/centreonService.class.php";

    /*
     * Create XML Request Objects
     */
    $obj = new CentreonXMLBGRequest($_GET["sid"], 1, 1, 0, 1);
    $svcObj = new CentreonService($obj->DB);
    CentreonSession::start();

    if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
        print "Bad Session ID";
        exit();
    }

    /*
     * Set Default Poller
     */
    $obj->getDefaultFilters();

    /* **************************************************
     * Check Arguments From GET tab
     */
    $o = $obj->checkArgument("o", $_GET, "h");
    $p = $obj->checkArgument("p", $_GET, "2");
    $nc = $obj->checkArgument("nc", $_GET, "0");
    $num = $obj->checkArgument("num", $_GET, 0);
    $limit = $obj->checkArgument("limit", $_GET, 20);
    $instance = $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
    $hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
    $hSearch = $obj->checkArgument("host_search", $_GET, "");
    $sgSearch = $obj->checkArgument("sg_search", $_GET, "");
    $sort_type = $obj->checkArgument("sort_type", $_GET, "host_name");
    $order = $obj->checkArgument("order", $_GET, "ASC");
    $dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "d/m/Y H:i:s");

    /*
     * Backup poller selection
     */
    $obj->setInstanceHistory($instance);

    /** **********************************************
     * Prepare pagination
     */

    $s_search = "";
    /* Display service problems */
    if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb") {
        $s_search .= " AND s.state != 0 AND s.state != 4 " ;
    }

    /* Display acknowledged services */
    if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1") {
        $s_search .= " AND s.acknowledged = '1' ";
    }

    /* Display not acknowledged services */
    if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0") {
        $s_search .= " AND s.state != 0 AND s.state != 4 AND s.acknowledged = 0 " ;
    }

    $query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.servicegroup_id, h.host_id "
        . "FROM servicegroups sg, services_servicegroups sgm, hosts h, services s "
        . "WHERE sg.servicegroup_id = sgm.servicegroup_id AND sgm.host_id = h.host_id AND h.host_id = s.host_id ";

    /* Host ACL */
    $query .= $obj->access->queryBuilder("", "h.host_id", $obj->access->getHostsString("ID", $obj->DBC));

    /* Service ACL */
    $query .= $obj->access->queryBuilder("AND", "s.service_id", $obj->access->getServicesString("ID", $obj->DBC));

    /* Host search */
    if ($hSearch != ""){
        $query .= "AND h.name like '%" . $hSearch . "%' ";
    }

    /* Servicegroup search */    
    if ($sgSearch != ""){
        $query .= "AND sg.name = '" . $sgSearch . "' ";
    }

    /* Service search */
    $query .= $s_search;

    /* Poller search */
    if ($instance != -1) {
        $query .= " AND h.instance_id = " . $instance . " ";
    }

    $query .= "ORDER BY sg.name " . $order . " "
        . "LIMIT " . ($num * $limit) . "," . $limit;

    $DBRESULT = $obj->DBC->query($query);

    $numRows = $obj->DBC->numberRows();

    /* Construct query for servigroups search */
    $sg_search = "";
    if ($numRows > 0) {
        $sg_search .= "AND (";
        $servicegroups = array();
        while ($row = $DBRESULT->fetchRow()) {
            $servicesgroups[$row['servicegroup_id']][] = $row['host_id'];
        }
        $servicegroupsSql1 = array();
        foreach ($servicesgroups as $key => $value) {
            $hostsSql = array();
            foreach ($value as $hostId) {
                $hostsSql[] = $hostId;
            }
            $servicegroupsSql1[] = "(sg.servicegroup_id = " . $key . " AND h.host_id IN (" . implode(',', $hostsSql) . ")) ";
        }
        $sg_search .= implode(" OR ", $servicegroupsSql1);
        $sg_search .= ") ";
        if ($sgSearch != ""){
            $sg_search .= "AND sg.name = '" . $sgSearch . "' ";
        }
    }

    /** ***************************************************
     * Create XML Flow
     */
    $obj->XML = new CentreonXML();
    $obj->XML->startElement("reponse");
    $obj->XML->startElement("i");
    $obj->XML->writeElement("numrows", $numRows);
    $obj->XML->writeElement("num", $num);
    $obj->XML->writeElement("limit", $limit);
    $obj->XML->writeElement("host_name", _("Hosts"), 0);
    $obj->XML->writeElement("services", _("Services"), 0);
    $obj->XML->writeElement("p", $p);
    ($o == "svcOVSG") ? $obj->XML->writeElement("s", "1")  : $obj->XML->writeElement("s", "0");
    $obj->XML->endElement();
    
    $query2 = "SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.name AS sg_name, sg.alias, h.name as host_name, h.state as host_state, h.icon_image, h.host_id, s.state, s.description, s.service_id "
        . "FROM servicegroups sg, services_servicegroups sgm, services s, hosts h "
        . "WHERE h.host_id = s.host_id AND s.host_id = sgm.host_id AND s.service_id=sgm.service_id AND sg.servicegroup_id=sgm.servicegroup_id ";
    
    $query2 .= $s_search
        . $sg_search
        . $obj->access->queryBuilder("AND", "s.service_id", $obj->access->getServicesString("ID", $obj->DBC));
    $DBRESULT = $obj->DBC->query($query2);

    $ct = 0;
    $sg = "";
    $h = "";
    $flag = 0;
    $count = 0;
    while ($tab = $DBRESULT->fetchRow()) {
        if ($sg != $tab["sg_name"]) {
            $flag = 0;
            if ($sg != "") {
                $obj->XML->endElement();
                $obj->XML->endElement();
            }
            $sg = $tab["sg_name"];
            $h = "";
            $obj->XML->startElement("sg");
            $obj->XML->writeElement("sgn", $tab["sg_name"]);
            $obj->XML->writeElement("o", $ct);
        }
        $ct++;

        if ($h != $tab["host_name"]) {
            if ($h != "" && $flag) {
                $obj->XML->endElement();
            }
            $flag = 1;
            $h = $tab["host_name"];
            $hs = $tab["host_state"];
            $obj->XML->startElement("h");
            $obj->XML->writeAttribute("class", $obj->getNextLineClass());
            $obj->XML->writeElement("hn", $tab["host_name"], false);
            if ($tab["icon_image"]) {
                $obj->XML->writeElement("hico", $tab["icon_image"]);
            } else {
                $obj->XML->writeElement("hico", "none");
            }
            $obj->XML->writeElement("hnl", urlencode($tab["host_name"]));
            $obj->XML->writeElement("hid", $tab["host_id"]);
            $obj->XML->writeElement("hcount", $count);
            $obj->XML->writeElement("hs", _($obj->statusHost[$tab["host_state"]]));
            $obj->XML->writeElement("hc", $obj->colorHost[$tab["host_state"]]);
            $count++;
        }
        $obj->XML->startElement("svc");
        $obj->XML->writeElement("sn", $tab['description']);
        $obj->XML->writeElement("snl", urlencode($tab['description']));
        $obj->XML->writeElement("sc", $obj->colorService[$tab['state']]);
        $obj->XML->writeElement("svc_id", $tab['service_id']);
        $obj->XML->endElement();
    }
    $DBRESULT->free();

    if ($sg != "") {
        $obj->XML->endElement();
        $obj->XML->endElement();
    }
    $obj->XML->endElement();

    /*
     * Send Header
     */
    $obj->header();

    /*
     * Send XML
     */
    $obj->XML->output();
?>
