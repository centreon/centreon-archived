<?php

/*
 * Copyright 2005-2022 Centreon
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

/**
 * format and add debug in CentreonXML
 *
 * @param CentreonXML $xml
 * @param mixed[] $tabs
 * @return int
 */
function printDebug($xml, array $tabs): int
{
    global $pearDB, $ret, $centreon, $nagiosCFGPath;

    $DBRESULT_Servers = $pearDB->query(
        "SELECT `nagios_bin` FROM `nagios_server`
        WHERE `localhost` = '1' ORDER BY ns_activate DESC LIMIT 1"
    );
    $nagios_bin = $DBRESULT_Servers->fetch();
    $DBRESULT_Servers->closeCursor();
    $msg_debug = array();

    $tab_server = array();
    foreach ($tabs as $tab) {
        if (isset($ret["host"]) && ($ret["host"] == 0 || in_array($tab['id'], $ret["host"]))) {
            $tab_server[$tab["id"]] = array(
                "id" => $tab["id"],
                "name" => $tab["name"],
                "localhost" => $tab["localhost"]
            );
        }
    }

    foreach ($tab_server as $host) {
        $stdout = shell_exec(
            escapeshellarg($nagios_bin['nagios_bin']) . " -v " . $nagiosCFGPath .
            escapeshellarg($host['id']) . "/centengine.DEBUG 2>&1"
        );
        $stdout = htmlspecialchars($stdout, ENT_QUOTES, "UTF-8");
        $msg_debug[$host['id']] = str_replace("\n", "<br />", $stdout);
        $msg_debug[$host['id']] = str_replace(
            "Warning:",
            "<font color='orange'>Warning</font>",
            $msg_debug[$host['id']]
        );
        $msg_debug[$host['id']] = str_replace(
            "warning: ",
            "<font color='orange'>Warning</font> ",
            $msg_debug[$host['id']]
        );
        $msg_debug[$host['id']] = str_replace("Error:", "<font color='red'>Error</font>", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace("error:", "<font color='red'>Error</font>", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace("reading", "Reading", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace("running", "Running", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace(
            "Total Warnings: 0",
            "<font color='green'>Total Warnings: 0</font>",
            $msg_debug[$host['id']]
        );
        $msg_debug[$host['id']] = str_replace(
            "Total Errors:   0",
            "<font color='green'>Total Errors: 0</font>",
            $msg_debug[$host['id']]
        );
        $msg_debug[$host['id']] = str_replace("<br />License:", " - License:", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = preg_replace('/\[[0-9]+?\] /', '', $msg_debug[$host['id']]);

        $lines = preg_split("/\<br\ \/\>/", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = "";
        $i = 0;
        foreach ($lines as $line) {
            if (
                strncmp($line, "Processing object config file", strlen("Processing object config file"))
                && strncmp($line, "Website: http://www.nagios.org", strlen("Website: http://www.nagios.org"))
            ) {
                $msg_debug[$host['id']] .= $line . "<br>";
            }
            $i++;
        }
    }

    $xml->startElement("debug");
    $str = "";
    $returnCode = 0;
    foreach ($msg_debug as $pollerId => $message) {
        $show = "none";
        $toggler = "<label id='togglerp_" . $pollerId . "'>[ + ]</label><label id='togglerm_" . $pollerId .
            "' style='display: none'>[ - ]</label>";
        $pollerNameColor = "green";
        if (preg_match_all("/Total (Errors|Warnings)\:[ ]+([0-9]+)/", $message, $globalMatches, PREG_SET_ORDER)) {
            foreach ($globalMatches as $matches) {
                if ($matches[2] != "0") {
                    $show = "block";
                    $toggler = "<label id='togglerp_" . $pollerId .
                        "' style='display: none'>[ + ]</label><label id='togglerm_" . $pollerId . "'>[ - ]</label>";
                    if ($matches[1] == "Errors") {
                        $pollerNameColor = "red";
                        $returnCode = 1;
                    } elseif ($matches[1] == "Warnings") {
                        $pollerNameColor = "orange";
                    }
                }
            }
        } else {
            $show = "block";
            $pollerNameColor = "red";
            $toggler = "<label id='togglerp_" . $pollerId .
                "' style='display: none'>[ + ]</label><label id='togglerm_" . $pollerId . "'>[ - ]</label>";
            $returnCode = 1;
        }
        $str .= "<a href='#' onClick=\"toggleDebug('" . $pollerId . "'); return false;\">";
        $str .= $toggler . "</a> ";
        $str .= "<b><font color='$pollerNameColor'>" . $tab_server[$pollerId]['name'] . "</font></b><br/>";
        $str .= "<div style='display: $show;' id='debug_" . $pollerId . "'>" . htmlentities($message) . "</div><br/>";
    }
    $xml->text($str);
    $xml->endElement();
    return $returnCode;
}
