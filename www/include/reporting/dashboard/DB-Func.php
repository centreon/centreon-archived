<?php
/*
 * Copyright 2005-2016 Centreon
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

/*
 * Get all hosts from DB
 */
function getAllHostsForReporting($is_admin, $lcaHoststr, $search = null)
{
    global $centreon;

    $hosts = array("NULL" => "");
    $hosts += $centreon->user->access->getHostAclConf($search, 'broker');
    return $hosts;
}

/*
 * returns days of week taken in account for reporting in a string
 */
function getReportDaysStr($reportTimePeriod)
{
    $tab = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $str = "";
    foreach ($tab as $key => $value) {
        if (isset($reportTimePeriod["report_" . $value]) && $reportTimePeriod["report_" . $value]) {
            if ($str != "") {
                $str .= ", '" . $value . "'";
            } else {
                $str .= "'" . $value . "'";
            }
        }
    }
    if ($str == "") {
        $str = "NULL";
    }
    return $str;
}

/*
 * Return a table a (which reference is given in parameter) that
 * contains stats on a given host defined by $host_id
 */
function getLogInDbForHost($host_id, $start_date, $end_date, $reportTimePeriod)
{
    global $pearDBO;

    /*
     * Initialising hosts stats values to 0
     */
    foreach (getHostStatsValueName() as $name) {
        $hostStats[$name] = 0;
    }

    $days_of_week = getReportDaysStr($reportTimePeriod);
    $rq = "SELECT sum(`UPnbEvent`) as UP_A, sum(`UPTimeScheduled`) as UP_T, " .
        " sum(`DOWNnbEvent`) as DOWN_A, sum(`DOWNTimeScheduled`) as DOWN_T, " .
        " sum(`UNREACHABLEnbEvent`) as UNREACHABLE_A, sum(`UNREACHABLETimeScheduled`) as UNREACHABLE_T, " .
        " sum(`UNDETERMINEDTimeScheduled`) as UNDETERMINED_T, " .
        " sum(`MaintenanceTime`) as MAINTENANCE_T " .
        "FROM `log_archive_host` " .
        "WHERE `host_id` = " . $host_id . " AND `date_start` >=  " . $start_date . " AND `date_end` <= " . $end_date .
        " " . "AND DATE_FORMAT( FROM_UNIXTIME( `date_start`), '%W') IN (" . $days_of_week . ") " .
        "GROUP BY `host_id` ";
    $DBRESULT = $pearDBO->query($rq);
    if ($row = $DBRESULT->fetchRow()) {
        $hostStats = $row;
    }

    /*
     * If there where no log in several days for this host, there is no
     * entry in log_archive_host for these days
     * So the following instructions count these missing days as undetermined time
     */
    $timeTab = getTotalTimeFromInterval($start_date, $end_date, $reportTimePeriod);
    if ($timeTab["reportTime"] > 0) {
        $hostStats["UNDETERMINED_T"] += $timeTab["reportTime"] - ($hostStats["UP_T"] + $hostStats["DOWN_T"]
                + $hostStats["UNREACHABLE_T"] + $hostStats["UNDETERMINED_T"]
                + $hostStats["MAINTENANCE_T"]);
    } else {
        $hostStats["UNDETERMINED_T"] = $timeTab["totalTime"];
    }

    /*
     * Calculate percentage of time (_TP => Total time percentage) for each status
     */
    $time = $hostStats["TOTAL_TIME"] = $hostStats["UP_T"] + $hostStats["DOWN_T"]
        + $hostStats["UNREACHABLE_T"] + $hostStats["UNDETERMINED_T"] + $hostStats["MAINTENANCE_T"];
    $hostStats["UP_TP"] = round($hostStats["UP_T"] / $time * 100, 2);
    $hostStats["DOWN_TP"] = round($hostStats["DOWN_T"] / $time * 100, 2);
    $hostStats["UNREACHABLE_TP"] = round($hostStats["UNREACHABLE_T"] / $time * 100, 2);
    $hostStats["UNDETERMINED_TP"] = round($hostStats["UNDETERMINED_T"] / $time * 100, 2);
    $hostStats["MAINTENANCE_TP"] = round($hostStats["MAINTENANCE_T"] / $time * 100, 2);
    /*
     * Calculate percentage of time (_MP => Mean Time percentage) for each status ignoring undetermined time
     */
    $time = $hostStats["MEAN_TIME"] = $hostStats["UP_T"] + $hostStats["DOWN_T"] + $hostStats["UNREACHABLE_T"];
    if ($time <= 0) {
        $hostStats["UP_MP"] = 0;
        $hostStats["DOWN_MP"] = 0;
        $hostStats["UNREACHABLE_MP"] = 0;
    } else {
        $hostStats["UP_MP"] = round($hostStats["UP_T"] / $time * 100, 2);
        $hostStats["DOWN_MP"] = round($hostStats["DOWN_T"] / $time * 100, 2);
        $hostStats["UNREACHABLE_MP"] = round($hostStats["UNREACHABLE_T"] / $time * 100, 2);
    }

    /*
     * Format time for each status (_TF => Time Formated), total time and mean time
     */
    $hostStats["MEAN_TIME_F"] = getTimeString($hostStats["MEAN_TIME"], $reportTimePeriod);
    $hostStats["TOTAL_TIME_F"] = getTimeString($hostStats["TOTAL_TIME"], $reportTimePeriod);
    $hostStats["UP_TF"] = getTimeString($hostStats["UP_T"], $reportTimePeriod);
    $hostStats["DOWN_TF"] = getTimeString($hostStats["DOWN_T"], $reportTimePeriod);
    $hostStats["UNREACHABLE_TF"] = getTimeString($hostStats["UNREACHABLE_T"], $reportTimePeriod);
    $hostStats["UNDETERMINED_TF"] = getTimeString($hostStats["UNDETERMINED_T"], $reportTimePeriod);
    $hostStats["MAINTENANCE_TF"] = getTimeString($hostStats["MAINTENANCE_T"], $reportTimePeriod);

    /*
     * Number Total of alerts
     */
    $hostStats["TOTAL_ALERTS"] = $hostStats["UP_A"] + $hostStats["DOWN_A"] + $hostStats["UNREACHABLE_A"];
    return ($hostStats);
}

/*
 * Return a table ($hostgroupStats) that contains availability (average with availability of all hosts from hostgroup)
 * and alerts (the sum of alerts of all hosts from hostgroup) for given hostgroup defined by $hostgroup_id
 */
function getLogInDbForHostGroup($hostgroup_id, $start_date, $end_date, $reportTimePeriod)
{
    global $centreon;

    $hostStatsLabels = getHostStatsValueName();

    /* Initialising hostgroup stats to 0 */
    foreach ($hostStatsLabels as $name) {
        $hostgroupStats["average"][$name] = 0;
    }

    $hosts_id = $centreon->user->access->getHostHostGroupAclConf($hostgroup_id, 'broker');
    if (count($hosts_id) == 0) {
        return $hostgroupStats;
    }

    /* get availability stats for each host */
    $count = 0;
    foreach ($hosts_id as $hostId => $host_name) {
        $host_stats = array();
        $host_stats = getLogInDbForHost($hostId, $start_date, $end_date, $reportTimePeriod);
        $hostgroupStats[$hostId] = $host_stats;
        $hostgroupStats[$hostId]["NAME"] = $host_name;
        $hostgroupStats[$hostId]["ID"] = $hostId;

        foreach ($hostStatsLabels as $name) {
            $hostgroupStats["average"][$name] += $host_stats[$name];
        }
        $count++;
    }

    /* the hostgroup availability is the average availability of all host from the the hostgroup */
    foreach ($hostStatsLabels as $name) {
        if ($name == "UP_T" || $name == "DOWN_T" || $name == "UNREACHABLE_T"
            || $name == "UNDETERMINED_T" || $name == "MAINTENANCE_T"
        ) {
            $hostgroupStats["average"][$name] /= $count;
        }
    }
    /*
     * Calculate percentage of time (_TP => Total time percentage) for each status
     */
    $hostgroupStats["average"]["TOTAL_TIME"] = $hostgroupStats["average"]["UP_T"] + $hostgroupStats["average"]["DOWN_T"]
        + $hostgroupStats["average"]["UNREACHABLE_T"] + $hostgroupStats["average"]["UNDETERMINED_T"]
        + $hostgroupStats["average"]["MAINTENANCE_T"];

    $time = $hostgroupStats["average"]["TOTAL_TIME"];
    $hostgroupStats["average"]["UP_TP"] = round($hostgroupStats["average"]["UP_T"] / $time * 100, 2);
    $hostgroupStats["average"]["DOWN_TP"] = round($hostgroupStats["average"]["DOWN_T"] / $time * 100, 2);
    $hostgroupStats["average"]["UNREACHABLE_TP"] = round($hostgroupStats["average"]["UNREACHABLE_T"] / $time * 100, 2);
    $hostgroupStats["average"]["UNDETERMINED_TP"] =
        round($hostgroupStats["average"]["UNDETERMINED_T"] / $time * 100, 2);
    $hostgroupStats["average"]["MAINTENANCE_TP"] = round($hostgroupStats["average"]["MAINTENANCE_T"] / $time * 100, 2);
    /*
     * Calculate percentage of time (_MP => Mean Time percentage) for each status ignoring undetermined time
     */
    $hostgroupStats["average"]["MEAN_TIME"] = $hostgroupStats["average"]["UP_T"] + $hostgroupStats["average"]["DOWN_T"]
        + $hostgroupStats["average"]["UNREACHABLE_T"];
    $time = $hostgroupStats["average"]["MEAN_TIME"];
    if ($time <= 0) {
        $hostgroupStats["average"]["UP_MP"] = 0;
        $hostgroupStats["average"]["DOWN_MP"] = 0;
        $hostgroupStats["average"]["UNREACHABLE_MP"] = 0;
    } else {
        $hostgroupStats["average"]["UP_MP"] = round($hostgroupStats["average"]["UP_T"] / $time * 100, 2);
        $hostgroupStats["average"]["DOWN_MP"] = round($hostgroupStats["average"]["DOWN_T"] / $time * 100, 2);
        $hostgroupStats["average"]["UNREACHABLE_MP"] =
            round($hostgroupStats["average"]["UNREACHABLE_T"] / $time * 100, 2);
    }
    /*
     * Number Total of alerts
     */
    $hostgroupStats["average"]["TOTAL_ALERTS"] = $hostgroupStats["average"]["UP_A"]
        + $hostgroupStats["average"]["DOWN_A"]
        + $hostgroupStats["average"]["UNREACHABLE_A"];
    return ($hostgroupStats);
}

/*
 * Return a table a (which reference is given in parameter) 
 * that contains stats on services for a given host defined by $host_id
 */
function getLogInDbForHostSVC($host_id, $start_date, $end_date, $reportTimePeriod)
{
    global $centreon, $pearDBO;

    $hostServiceStats = array();
    $services_ids = array();

    /*
     * Getting authorized services
     */
    $services_ids = $centreon->user->access->getHostServiceAclConf($host_id, 'broker');
    $svcStr = "";
    if (count($services_ids) > 0) {
        foreach ($services_ids as $id => $description) {
            if ($svcStr) {
                $svcStr .= ", ";
            }
            $svcStr .= $id;
        }
    } else {
        return ($hostServiceStats);
    }
    $status = array("OK", "WARNING", "CRITICAL", "UNKNOWN", "UNDETERMINED", "MAINTENANCE");

    /* initialising all host services stats to 0 */
    foreach ($services_ids as $id => $description) {
        foreach (getServicesStatsValueName() as $name) {
            $hostServiceStats[$id][$name] = 0;
        }
    }
    /*
     * $hostServiceStats["average"] will contain services stats average
     */
    foreach ($status as $key => $value) {
        $hostServiceStats["average"][$value . "_TP"] = 0;
        $hostServiceStats["average"][$value . "_MP"] = 0;
        if ($value != "UNDETERMINED" && $value != "MAINTENANCE") {
            $hostServiceStats["average"][$value . "_A"] = 0;
        }
    }
    $days_of_week = getReportDaysStr($reportTimePeriod);
    $rq = "SELECT DISTINCT las.service_id, " .
        "sum(OKTimeScheduled) as OK_T, " .
        "sum(OKnbEvent) as OK_A, " .
        "sum(WARNINGTimeScheduled)  as WARNING_T, " .
        "sum(WARNINGnbEvent) as WARNING_A, " .
        "sum(UNKNOWNTimeScheduled) as UNKNOWN_T, " .
        "sum(UNKNOWNnbEvent) as UNKNOWN_A, " .
        "sum(CRITICALTimeScheduled) as CRITICAL_T, " .
        "sum(CRITICALnbEvent) as CRITICAL_A, " .
        "sum(UNDETERMINEDTimeScheduled) as UNDETERMINED_T, " .
        "sum(MaintenanceTime) as MAINTENANCE_T " .
        "FROM log_archive_service " . (!$centreon->user->admin ? "las, centreon_acl acl " : "las ") .
        "WHERE las.host_id = " . $host_id . " " .
        (!$centreon->user->admin ? " AND las.host_id = acl.host_id AND las.service_id = acl.service_id " : "") .
        (!$centreon->user->admin ?
            " AND acl.group_id IN (" . $centreon->user->access->getAccessGroupsString() . ")" : "") .
        "AND date_start >= " . $start_date . " AND date_end <= " . $end_date . " " .
        "AND DATE_FORMAT(FROM_UNIXTIME(date_start), '%W') IN (" . $days_of_week . ") " .
        "GROUP BY las.service_id ";
    $DBRESULT = $pearDBO->query($rq);
    while ($row = $DBRESULT->fetchRow()) {
        if (isset($hostServiceStats[$row["service_id"]])) {
            $hostServiceStats[$row["service_id"]] = $row;
        }
    }
    $i = 0;
    foreach ($services_ids as $id => $description) {
        $hostServiceStats[$id]["DESCRIPTION"] = $description;
        $hostServiceStats[$id]["ID"] = $id;
        $timeTab = getTotalTimeFromInterval($start_date, $end_date, $reportTimePeriod);
        if ($timeTab["reportTime"]) {
            $hostServiceStats[$id]["UNDETERMINED_T"] += $timeTab["reportTime"]
                - ($hostServiceStats[$id]["OK_T"]
                    + $hostServiceStats[$id]["WARNING_T"]
                    + $hostServiceStats[$id]["CRITICAL_T"]
                    + $hostServiceStats[$id]["UNKNOWN_T"]
                    + $hostServiceStats[$id]["UNDETERMINED_T"]
                    + $hostServiceStats[$id]["MAINTENANCE_T"]);
        } else {
            foreach ($status as $key => $value) {
                $hostServiceStats[$id][$value . "_T"] = 0;
            }
            $hostServiceStats[$id]["UNDETERMINED_T"] = $timeTab["totalTime"];
        }
        /*
         * Calculate percentage of time (_TP => Total time percentage) for each status
         */
        $hostServiceStats[$id]["TOTAL_TIME"] = $hostServiceStats[$id]["OK_T"]
            + $hostServiceStats[$id]["WARNING_T"]
            + $hostServiceStats[$id]["CRITICAL_T"]
            + $hostServiceStats[$id]["UNKNOWN_T"]
            + $hostServiceStats[$id]["UNDETERMINED_T"]
            + $hostServiceStats[$id]["MAINTENANCE_T"];
        $time = $hostServiceStats[$id]["TOTAL_TIME"];
        foreach ($status as $key => $value) {
            $hostServiceStats[$id][$value . "_TP"] = round($hostServiceStats[$id][$value . "_T"] / $time * 100, 2);
        }
        /*
         * The same percentage (_MP => Mean Time percentage) is calculated ignoring undetermined time
         */
        $hostServiceStats[$id]["MEAN_TIME"] = $hostServiceStats[$id]["OK_T"]
            + $hostServiceStats[$id]["WARNING_T"]
            + $hostServiceStats[$id]["CRITICAL_T"]
            + $hostServiceStats[$id]["UNKNOWN_T"];
        $time = $hostServiceStats[$id]["MEAN_TIME"];
        if ($hostServiceStats[$id]["MEAN_TIME"] <= 0) {
            foreach ($status as $key => $value) {
                $hostServiceStats[$id][$value . "_MP"] = 0;
            }
        } else {
            foreach ($status as $key => $value) {
                if ($value != "UNDETERMINED") {
                    $hostServiceStats[$id][$value . "_MP"] = round(
                        $hostServiceStats[$id][$value . "_T"] / $time * 100,
                        2
                    );
                }
            }
        }
        /*
         * Format time for each status (_TF => Time Formated), mean time and total time
         */
        $hostServiceStats[$id]["MEAN_TIME_F"] = getTimeString($hostServiceStats[$id]["MEAN_TIME"], $reportTimePeriod);
        $hostServiceStats[$id]["TOTAL_TIME_F"] = getTimeString($hostServiceStats[$id]["TOTAL_TIME"], $reportTimePeriod);
        foreach ($status as $key => $value) {
            $hostServiceStats[$id][$value . "_TF"] =
                getTimeString($hostServiceStats[$id][$value . "_T"], $reportTimePeriod);
        }
        /*
         * Services status time sum and alerts sum
         */
        foreach ($status as $key => $value) {
            $hostServiceStats["average"][$value . "_TP"] += $hostServiceStats[$id][$value . "_TP"];
            if ($value != "UNDETERMINED" && $value != "MAINTENANCE") {
                $hostServiceStats["average"][$value . "_MP"] += $hostServiceStats[$id][$value . "_MP"];
                $hostServiceStats["average"][$value . "_A"] += $hostServiceStats[$id][$value . "_A"];
            }
        }
        $i++;
    }

    /*
     * Services status time average
     */
    if ($i) {
        foreach ($status as $key => $value) {
            $hostServiceStats["average"][$value . "_TP"] = round($hostServiceStats["average"][$value . "_TP"] / $i, 2);
            if ($value != "UNDETERMINED" && $value != "MAINTENANCE") {
                $hostServiceStats["average"][$value . "_MP"] = round(
                    $hostServiceStats["average"][$value . "_MP"] / $i,
                    2
                );
            }
        }
    }

    return ($hostServiceStats);
}

/*
 * Return a table a (which reference is given in parameter) that contains stats 
 * on services for a given host defined by $host_id and $service_id
 * me must specify the host id because one service can be linked to many hosts
 */
function getLogInDbForOneSVC($host_id, $service_id, $start_date, $end_date, $reportTimePeriod)
{
    global $pearDBO, $centreon;

    $status = array("OK", "WARNING", "CRITICAL", "UNKNOWN", "UNDETERMINED", "MAINTENANCE");

    foreach (getServicesStatsValueName() as $name) {
        $serviceStats[$name] = 0;
    }
    $days_of_week = getReportDaysStr($reportTimePeriod);
    $rq = "SELECT DISTINCT las.service_id, sum(OKTimeScheduled) as OK_T, sum(OKnbEvent) as OK_A, "
        . "sum(WARNINGTimeScheduled)  as WARNING_T, sum(WARNINGnbEvent) as WARNING_A, "
        . "sum(UNKNOWNTimeScheduled) as UNKNOWN_T, sum(UNKNOWNnbEvent) as UNKNOWN_A, "
        . "sum(CRITICALTimeScheduled) as CRITICAL_T, sum(CRITICALnbEvent) as CRITICAL_A, "
        . "sum(UNDETERMINEDTimeScheduled) as UNDETERMINED_T, "
        . "sum(MaintenanceTime) as MAINTENANCE_T "
        . "FROM log_archive_service las " . (!$centreon->user->admin ? ", centreon_acl acl " : " ")
        . "WHERE las.host_id = " . $host_id .
        (!$centreon->user->admin ? " AND las.host_id = acl.host_id AND las.service_id = acl.service_id " : "") .
        (!$centreon->user->admin ?
            " AND acl.group_id IN (" . $centreon->user->access->getAccessGroupsString() . ")" : "") .
        " AND las.service_id = " . $service_id . " AND `date_start` >= " . $start_date .
        " AND date_end <= " . $end_date . " "
        . "AND DATE_FORMAT(FROM_UNIXTIME(date_start), '%W') IN (" . $days_of_week . ") "
        . "GROUP BY las.service_id";
    $DBRESULT = $pearDBO->query($rq);

    if ($row = $DBRESULT->fetchRow()) {
        $serviceStats = $row;
    }
    $timeTab = getTotalTimeFromInterval($start_date, $end_date, $reportTimePeriod);
    if ($timeTab["reportTime"]) {
        $serviceStats["UNDETERMINED_T"] += $timeTab["reportTime"]
            - ($serviceStats["OK_T"] + $serviceStats["WARNING_T"] + $serviceStats["CRITICAL_T"]
                + $serviceStats["UNKNOWN_T"] + $serviceStats["UNDETERMINED_T"] + $serviceStats["MAINTENANCE_T"]);
    } else {
        foreach ($status as $key => $value) {
            $serviceStats[$value . "_T"] = 0;
        }
        $serviceStats["UNDETERMINED_T"] = $timeTab["totalTime"];
    }
    /*
     * Calculate percentage of time (_TP => Total time percentage) for each status
     */
    $serviceStats["TOTAL_TIME"] = $serviceStats["OK_T"] + $serviceStats["WARNING_T"] + $serviceStats["CRITICAL_T"]
        + $serviceStats["UNKNOWN_T"] + $serviceStats["UNDETERMINED_T"] + $serviceStats["MAINTENANCE_T"];
    $time = $serviceStats["TOTAL_TIME"];
    foreach ($status as $key => $value) {
        $serviceStats[$value . "_TP"] = round($serviceStats[$value . "_T"] / $time * 100, 2);
    }
    /*
     * The same percentage (_MP => Mean Time percentage) is calculated ignoring undetermined time
     */
    $serviceStats["MEAN_TIME"] = $serviceStats["OK_T"] + $serviceStats["WARNING_T"]
        + $serviceStats["CRITICAL_T"] + $serviceStats["UNKNOWN_T"];
    $time = $serviceStats["MEAN_TIME"];
    if ($serviceStats["MEAN_TIME"] <= 0) {
        foreach ($status as $key => $value) {
            if ($value != "UNDETERMINED" && $value != "MAINTENANCE") {
                $serviceStats[$value . "_MP"] = 0;
            }
        }
    } else {
        foreach ($status as $key => $value) {
            if ($value != "UNDETERMINED" && $value != "MAINTENANCE") {
                $serviceStats[$value . "_MP"] = round($serviceStats[$value . "_T"] / $time * 100, 2);
            }
        }
    }
    /*
     * Format time for each status (_TF => Time Formated), mean time and total time
     */
    $serviceStats["MEAN_TIME_F"] = getTimeString($serviceStats["MEAN_TIME"], $reportTimePeriod);
    $serviceStats["TOTAL_TIME_F"] = getTimeString($serviceStats["TOTAL_TIME"], $reportTimePeriod);
    foreach ($status as $key => $value) {
        $serviceStats[$value . "_TF"] = getTimeString($serviceStats[$value . "_T"], $reportTimePeriod);
    }

    $serviceStats["TOTAL_ALERTS"] = $serviceStats["OK_A"] + $serviceStats["WARNING_A"] + $serviceStats["CRITICAL_A"]
        + $serviceStats["UNKNOWN_A"];
    return $serviceStats;
}

/*
 * Return a table ($serviceGroupStats) that contains availability
 * (average with availability of all services from servicegroup)
 * and alerts (the sum of alerts of all services from servicegroup) for given servicegroup defined by $servicegroup_id
 */
function getLogInDbForServicesGroup($servicegroup_id, $start_date, $end_date, $reportTimePeriod)
{
    global $pearDBO;

    $serviceStatsLabels = array();
    $serviceStatsLabels = getServicesStatsValueName();
    $status = array("OK", "WARNING", "CRITICAL", "UNKNOWN", "UNDETERMINED", "MAINTENANCE");

    /* Initialising hostgroup stats to 0 */
    foreach ($serviceStatsLabels as $name) {
        $serviceGroupStats["average"][$name] = 0;
    }

    /* $count count the number of services in servicegroup */
    $count = 0;
    $services = getServiceGroupActivateServices($servicegroup_id);
    foreach ($services as $host_service_id => $host_service_name) {
        foreach ($serviceStatsLabels as $name) {
            $serviceGroupStats[$host_service_id][$name] = 0;
        }
        $servicesStats = array();
        $servicesStats = getLogInDbForOneSVC(
            $host_service_name['host_id'],
            $host_service_name['service_id'],
            $start_date,
            $end_date,
            $reportTimePeriod
        );

        if (isset($servicesStats)) {
            $serviceGroupStats[$host_service_id] = $servicesStats;
            $serviceGroupStats[$host_service_id]["HOST_ID"] = $host_service_name['host_id'];
            $serviceGroupStats[$host_service_id]["SERVICE_ID"] = $host_service_name['service_id'];
            $serviceGroupStats[$host_service_id]["HOST_NAME"] = $host_service_name['host_name'];
            $serviceGroupStats[$host_service_id]["SERVICE_DESC"] = $host_service_name['service_description'];
            foreach ($serviceStatsLabels as $name) {
                $serviceGroupStats["average"][$name] += $servicesStats[$name];
            }
        }
        $count++;
    }

    /*
     * Average time for all status (OK, Critical, Warning, Unknown)
     */
    foreach ($serviceStatsLabels as $name) {
        if ($name == "OK_T" || $name == "WARNING_T" || $name == "CRITICAL_T"
            || $name == "UNKNOWN_T" || $name == "UNDETERMINED_T" || $name == "MAINTENANCE_T"
        ) {
            if ($count) {
                $serviceGroupStats["average"][$name] /= $count;
            } else {
                $serviceGroupStats["average"][$name] = 0;
            }
        }
    }

    /*
     * Calculate percentage of time (_TP => Total time percentage) for each status
     */
    $serviceGroupStats["average"]["TOTAL_TIME"] = $serviceGroupStats["average"]["OK_T"]
        + $serviceGroupStats["average"]["WARNING_T"]
        + $serviceGroupStats["average"]["CRITICAL_T"]
        + $serviceGroupStats["average"]["UNKNOWN_T"]
        + $serviceGroupStats["average"]["UNDETERMINED_T"]
        + $serviceGroupStats["average"]["MAINTENANCE_T"];

    $time = $serviceGroupStats["average"]["TOTAL_TIME"];
    foreach ($status as $key => $value) {
        if ($time) {
            $serviceGroupStats["average"][$value . "_TP"] =
                round($serviceGroupStats["average"][$value . "_T"] / $time * 100, 2);
        } else {
            $serviceGroupStats["average"][$value . "_TP"] = 0;
        }
    }

    /*
     * Calculate percentage of time (_MP => Mean Time percentage) for each status ignoring undetermined time
     */
    $serviceGroupStats["average"]["MEAN_TIME"] =
        +$serviceGroupStats["average"]["OK_T"]
        + $serviceGroupStats["average"]["WARNING_T"]
        + $serviceGroupStats["average"]["CRITICAL_T"]
        + $serviceGroupStats["average"]["UNKNOWN_T"];

    /*
     * Calculate total of alerts
     */
    $serviceGroupStats["average"]["TOTAL_ALERTS"] =
        $serviceGroupStats["average"]["OK_A"]
        + $serviceGroupStats["average"]["WARNING_A"]
        + $serviceGroupStats["average"]["CRITICAL_A"]
        + $serviceGroupStats["average"]["UNKNOWN_A"];
    $time = $serviceGroupStats["average"]["MEAN_TIME"];
    if ($time <= 0) {
        foreach ($status as $key => $value) {
            if ($value != "UNDETERMINED" && $value != "MAINTENANCE") {
                $serviceGroupStats["average"][$value . "_MP"] = 0;
            }
        }
    } else {
        foreach ($status as $key => $value) {
            if ($value != "UNDETERMINED" && $value != "MAINTENANCE") {
                $serviceGroupStats["average"][$value . "_MP"] =
                    round($serviceGroupStats["average"][$value . "_T"] / $time * 100, 2);
            }
        }
    }
    return $serviceGroupStats;
}

/*
 * Returns all activated services from a servicegroup including services by host and services by hostgroup
 */
function getServiceGroupActivateServices($sg_id = null)
{
    global $centreon;

    if (!$sg_id) {
        return;
    }

    $svs = $centreon->user->access->getServiceServiceGroupAclConf($sg_id, 'broker');
    return $svs;
}

/*
 * Get timeperiods to take in account to retrieve log from nagios
 * report_hour_start, report_minute_start, report_hour_end, report_hour_end => restrict to a time period in given day
 * report_Monday, report_Tuesday, report_Wednesday,
 * report_Thursday, report_Friday, report_Sunday => days for which we can retrieve logs
 */
function getreportingTimePeriod()
{
    global $pearDB;

    $reportingTimePeriod = array();
    $query = "SELECT * FROM `contact_param` WHERE cp_contact_id is null";
    $DBRESULT = $pearDB->query($query);
    while ($res = $DBRESULT->fetchRow()) {
        if ($res["cp_key"] == "report_hour_start") {
            $reportingTimePeriod["report_hour_start"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_minute_start") {
            $reportingTimePeriod["report_minute_start"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_hour_end") {
            $reportingTimePeriod["report_hour_end"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_minute_end") {
            $reportingTimePeriod["report_minute_end"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_Monday") {
            $reportingTimePeriod["report_Monday"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_Tuesday") {
            $reportingTimePeriod["report_Tuesday"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_Wednesday") {
            $reportingTimePeriod["report_Wednesday"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_Thursday") {
            $reportingTimePeriod["report_Thursday"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_Friday") {
            $reportingTimePeriod["report_Friday"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_Saturday") {
            $reportingTimePeriod["report_Saturday"] = $res["cp_value"];
        }
        if ($res["cp_key"] == "report_Sunday") {
            $reportingTimePeriod["report_Sunday"] = $res["cp_value"];
        }
    }
    return $reportingTimePeriod;
}

/*
 * Get all hostgroups linked with at least one host
 */
function getAllHostgroupsForReporting($is_admin, $lcaHostGroupstr, $search = null)
{
    global $centreon;

    $hgs = array("NULL" => "");
    $hgs += $centreon->user->access->getHostGroupAclConf($search, 'broker');
    return $hgs;
}

/*
 * Get all servicesgroup with at least one service
 */
function getAllServicesgroupsForReporting($search = null)
{
    global $centreon;

    $sg_array = array("NULL" => "");
    $sg_array += $centreon->user->access->getServiceGroupAclConf($search, 'broker');
    return $sg_array;
}

/*
 * Functions to get objects names from their ID
 */
function getHostNameFromId($host_id)
{
    global $pearDB;
    $req = "SELECT  `host_name` FROM `host` WHERE `host_id` = " . $host_id;
    $DBRESULT = $pearDB->query($req);
    if ($row = $DBRESULT->fetchRow()) {
        return ($row["host_name"]);
    }
    return "undefined";
}

function getHostgroupNameFromId($hostgroup_id)
{
    global $pearDB;
    $req = "SELECT  `hg_name` FROM `hostgroup` WHERE `hg_id` = " . $hostgroup_id;
    $DBRESULT = $pearDB->query($req);
    if ($row = $DBRESULT->fetchRow()) {
        return ($row["hg_name"]);
    }
    return "undefined";
}

function getServiceDescriptionFromId($service_id)
{
    global $pearDB;
    $req = "SELECT  `service_description` FROM `service` WHERE `service_id` = " . $service_id;
    $DBRESULT = $pearDB->query($req);
    if ($row = $DBRESULT->fetchRow()) {
        return ($row["service_description"]);
    }
    return "undefined";
}

function getServiceGroupNameFromId($sg_id)
{
    global $pearDB;
    $req = "SELECT  `sg_name` FROM `servicegroup` WHERE `sg_id` = " . $sg_id;
    $DBRESULT = $pearDB->query($req);
    unset($req);
    if ($row = $DBRESULT->fetchRow()) {
        return ($row["sg_name"]);
    }
    $DBRESULT->free();
    return "undefined";
}
