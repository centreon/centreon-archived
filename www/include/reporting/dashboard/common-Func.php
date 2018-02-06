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
 * return the interval of time which must be reported
 */
function getPeriodToReport()
{
    $period = (isset($_POST["period"])) ? $_POST["period"] : "";
    $period = (isset($_GET["period"])) ? $_GET["period"] : $period;
    $period_choice = (isset($_POST["period_choice"])) ? $_POST["period_choice"] : "";
    $end_date = 0;
    $start_date = 0;
    $start_date = (isset($_POST["StartDate"])) ? $_POST["StartDate"] : "";
    $start_date = (isset($_GET["start"])) ? $_GET["start"] : $start_date;
    $end_date = (isset($_POST["EndDate"])) ? $_POST["EndDate"] : "";
    $end_date = (isset($_GET["end"])) ? $_GET["end"] : $end_date;
    $interval = array(0, 0);
    if ($period_choice == "custom" && $start_date != "" && $end_date != "") {
        $period = "";
    }
    if ($period == "" && $start_date == "" && $end_date == "") {
        $period = "yesterday";
    }
    if ($period == "" && $start_date != "") {
        $interval = getDateSelectCustomized($start_date, $end_date);
    } else {
        $interval = getDateSelectPredefined($period);
    }
    $start_date = $interval[0];
    $end_date = $interval[1];
    return(array($start_date, $end_date));
}

/*
 * Return a table containing all stats information that will
 * be displayed on dashboard for host and hostgroup
 */
function getHostStatsValueName()
{
    return (
        array(
            "UP_T",
            "UP_A",
            "DOWN_T",
            "DOWN_A",
            "UNREACHABLE_T",
            "UNREACHABLE_A",
            "UNDETERMINED_T",
            "MAINTENANCE_T",
            "UP_TP",
            "DOWN_TP",
            "UNREACHABLE_TP",
            "UNDETERMINED_TP",
            "MAINTENANCE_TP"
        )
    );
}

/*
 * Return a table containing all stats information that will be
 * displayed on dashboard for services and servicegroup
 */
function getServicesStatsValueName()
{
    return (
        array(
            "OK_T",
            "OK_A",
            "WARNING_T",
            "WARNING_A",
            "CRITICAL_T",
            "CRITICAL_A",
            "UNKNOWN_T",
            "UNKNOWN_A",
            "UNDETERMINED_T",
            "MAINTENANCE_T",
            "OK_TP",
            "WARNING_TP",
            "CRITICAL_TP",
            "UNKNOWN_TP",
            "UNDETERMINED_TP",
            "MAINTENANCE_TP"
        )
    );
}
/*
 * return start and end date to report in timestamp
 * ==>> function must be optimized
 */
function getDateSelectPredefined($period)
{
    $time = time();
    $day = date("d", $time);
    $year = date("Y", $time);
    $month = date("m", $time);
    if (!is_null($period)) {
        if ($period == "yesterday") {
            $start_date = mktime(0, 0, 0, $month, $day - 1, $year);
            $end_date = mktime(24, 0, 0, $month, $day - 1, $year);
        } elseif ($period == "thisweek") {
            $dd = (date("D", mktime(24, 0, 0, $month, $day - 1, $year)));
            for ($ct = 1; $dd != "Mon"; $ct++) {
                $dd = (date("D", mktime(0, 0, 0, $month, ($day - $ct), $year)));
            }
            $start_date = mktime(0, 0, 0, $month, $day - $ct, $year);
            $end_date = mktime(24, 0, 0, $month, ($day - 1), $year);
        } elseif ($period == "last7days") {
            $start_date = mktime(0, 0, 0, $month, $day - 7, $year);
            $end_date = mktime(24, 0, 0, $month, $day - 1, $year);
        } elseif ($period == "last30days") {
            $start_date = mktime(0, 0, 0, $month, $day - 30, $year);
            $end_date = mktime(24, 0, 0, $month, $day - 1, $year);
        } elseif ($period == "lastyear") {
            $start_date = mktime(0, 0, 0, 1, 1, $year-1);
            $end_date = mktime(0, 0, 0, 1, 1, $year);
        } elseif ($period == "thismonth") {
            $start_date = mktime(0, 0, 0, $month, 1, $year);
            $end_date = mktime(24, 0, 0, $month, $day - 1, $year);
        } elseif ($period == "thisyear") {
            $start_date = mktime(0, 0, 0, 1, 1, $year);
            $end_date = mktime(24, 0, 0, $month, $day - 1, $year);
        } else { /* last month */
            $start_date = mktime(0, 0, 0, $month - 1, 1, $year);
            $end_date = mktime(0, 0, 0, $month, 1, $year);
        }
    } else {
        $start_date = mktime(0, 0, 0, $month, $day - 1, $year);
        $end_date = mktime(24, 0, 0, $month, $day - 1, $year);
    }
    if ($start_date > $end_date) {
        $start_date = $end_date;
    }
    return (array($start_date, $end_date));
}

function getDateSelectCustomized($start, $end)
{
    $time = time();
    $day = date("d", $time);
    $year = date("Y", $time);
    $month = date("m", $time);
    $end_time = mktime(0, 0, 0, $month, $day, $year);

    if (is_numeric($end)) {
        $end_time = $end;
    } elseif (isset($end) && $end != "") {
        list($m, $d, $y) = preg_split('/\//', $end);
        $end = mktime(24, 0, 0, $m, $d, $y);
        if ($end < $end_time) {
            $end_time = $end;
        }
    }
    if (!is_numeric($start) && isset($start) && $start != "") {
        list($m, $d, $y) = preg_split('/\//', $start);
        $start_time = mktime(0, 0, 0, $m, $d, $y);
    } else {
        $start_time = $start;
    }

    if ($start_time >= $end_time) {
        $start_time = $end_time - (60 * 60 * 24);
    }

    return (array($start_time, $end_time));
}
/*
 * Return time between two timestamp
 * excluding days and time which are not in the parameters
 * defined in menu "Options>General Options>Reporting"
 */
function getTotalTimeFromInterval($start, $end, $reportTimePeriod)
{
    $one_day_real_duration = 60 * 60 * 24;
    $totalTime = 0;
    $reportTime = 0;
    
    $reportTimePeriodEnd = mktime(
        $reportTimePeriod["report_hour_end"],
        $reportTimePeriod["report_minute_end"],
        0,
        0,
        0,
        0
    );
    $reportTimePeriodStart = mktime(
        $reportTimePeriod["report_hour_start"],
        $reportTimePeriod["report_minute_start"],
        0,
        0,
        0,
        0
    );
    $day_duration =  $reportTimePeriodEnd - $reportTimePeriodStart;
    while ($start < $end) {
        if ($day_duration > $end - $start) {
            $day_duration  = $end - $start;
        }
        if (isset($reportTimePeriod["report_".date("l", $start)])
            && $reportTimePeriod["report_".date("l", $start)]) {
            $reportTime += $day_duration;
        }# if the day is selected in the timeperiod
        $totalTime += $day_duration;
        //$start = $day_real_end;
        $start += $one_day_real_duration;
    }
    $tab = array("totalTime" => $totalTime, "reportTime" => $reportTime);
    return $tab;
}

function myGetTimeTamps($dateSTR)
{
    list($m, $d, $y) = preg_split('/\//', $dateSTR);
    return (mktime(0, 0, 0, $m, $d, $y));
}

function getPeriodList()
{
    $periodList = array();
    $periodList[""] = "";
    $periodList["yesterday"] = _("Yesterday");
    $periodList["thisweek"] = _("This Week");
    $periodList["last7days"] = _("Last 7 Days");
    $periodList["thismonth"] = _("This Month");
    $periodList["last30days"] = _("Last 30 Days");
    $periodList["lastmonth"] = _("Last Month");
    $periodList["thisyear"] = _("This Year");
    $periodList["lastyear"] = _("Last Year");

    return $periodList;
}

function createDateTimelineFormat($time_unix)
{
    $tab_month = array(
    "01" => "Jan",
    "02" => "Feb",
    "03"=> "Mar",
    "04"=> "Apr",
    "05" => "May",
    "06"=> "Jun",
    "07"=> "Jul",
    "08"=> "Aug",
    "09"=> "Sep",
    "10"=> "Oct",
    "11"=> "Nov",
    "12"=> "Dec");
    $date = $tab_month[date('m', $time_unix)].date(" d Y G:i:s", $time_unix);
    return $date;
}

function getTimeString($time, $reportTimePeriod)
{
    $min = 60;
    $hour = $min * 60;
    $day = mktime($reportTimePeriod["report_hour_end"], $reportTimePeriod["report_minute_end"], 0, 0, 0, 0)
            - mktime($reportTimePeriod["report_hour_start"], $reportTimePeriod["report_minute_start"], 0, 0, 0, 0);
    $str = "";
    if ($day && $time / $day >= 1) {
        $str .= floor($time / $day)."d ";
        $time = $time % $day;
    }
    if ($hour && $time / $hour >= 1) {
        $str .= floor($time / $hour)."h ";
        $time = $time % $hour;
    }
    if ($min && $time / $min >= 1) {
        $str .= floor($time / $min)."m ";
        $time = $time % $min;
    }
    if ($time) {
        $str .=  $time."s";
    }
    return $str;
}

function formatData($state, $time, $timeTOTAL, $time_none, $nb_alert, $color)
{
    $tab = array();
    $tab["state"] = _($state);
    $tab["time"] = CentreonDuration::toString($time);
    $tab["timestamp"] = $time;
    $tab["pourcentTime"] = round($time/($timeTOTAL+1)*100, 2);
    if ($state != "Undetermined") {
        $tab["pourcentkTime"] = round($time/($timeTOTAL-$time_none+1)*100, 2). "%";
    } else {
        $tab["pourcentkTime"] = null;
    }
    $tab["nbAlert"] = $nb_alert;
    $tab["style"] = "class='ListColCenter' style='background:" . $color."'";
    return $tab;
}
