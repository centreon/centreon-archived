<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once $centreon_path . "www/class/centreonHost.class.php";

/**
 * Hide value of custom macros defined as password
 * 
 * @param string $command_name The name of the command
 * @param int    $host_id      The ID of the host
 * @param int    $service_id   The ID of the service
 * 
 * @return string
 */
function hidePasswordInCommand($command_name, $host_id, $service_id)
{
    global $pearDB;

    if (!isset($command_name) && !isset($service_id)) {
        return 1;
    }

    $pearDBStorage = new CentreonDB('centstorage');

    /* Get command line with macro */
    $query_command_line = "SELECT command_line FROM command WHERE command_name = '" .
    $pearDB->escape($command_name) . "'";
    $res = $pearDB->query($query_command_line);
    $row = $res->fetchRow();
    $command_line_with_macro = $row['command_line'];

    /* Get executed command lines */
    $query_command_name = "SELECT host_id, check_command, command_line "
        . "FROM services "
        . "WHERE host_id = '" . $host_id . "' "
        . "AND service_id = '" . $service_id . "'";
    $res = $pearDBStorage->query($query_command_name);
    $row = $res->fetchRow();
    $executed_check_command = $row['command_line'];

    /* Get list of templates */
    $arrtSvcTpl = getListTemplates($pearDB, $service_id);
    $arrSvcTplID = array($service_id);
    foreach ($arrtSvcTpl as $svc) {
        $arrSvcTplID[] = $svc['service_id'];
    }

    /* Get list of custom macros from services and templates */
    $query_custom_macro_svc = "SELECT svc_macro_name "
        . "FROM on_demand_macro_service "
        . "WHERE is_password = 1 "
        . "AND svc_svc_id IN ('" . implode('\', \'', $arrSvcTplID) . "')";
    $res = $pearDB->query($query_custom_macro_svc);

    $arrMacroPassword = array();
    while ($row = $res->fetchRow()) {
        $arrMacroPassword = array_merge (
            $arrMacroPassword, 
            array($row['svc_macro_name'])
        );
        $executed_check_command = getOptionName(
            $command_line_with_macro, 
            $executed_check_command, 
            $row['svc_macro_name']
        );
    }

    /* Get custom macros from hosts and templates */
    $query_custom_macro_host = "SELECT host_macro_name "
        . "FROM on_demand_macro_host "
        . "WHERE is_password = 1 "
        . "AND host_host_id IN ('" . implode('\', \'', getHostsTemplates($host_id)) . "')";
    $res = $pearDB->query($query_custom_macro_host);

    while ($row = $res->fetchRow()) {
        $arrMacroPassword = array_merge(
            $arrMacroPassword, 
            array($row['host_macro_name'])
        );
        $executed_check_command = getOptionName(
            $command_line_with_macro, 
            $executed_check_command, 
            $row['host_macro_name']
        );
    }

    return $executed_check_command;
}

/**
 * Get the name of the option in the command line corresponding 
 * to the custom macro password type 
 *
 * @param string $command_with_macro Configuration command line
 * @param string $executed_command   Executed command line
 * @param string $macro              The custom macro password type
 * 
 * @return string
 */
function getOptionName($command_with_macro, $executed_command, $macro) {
    $macro = str_replace('$', '\$', $macro);
    $pattern = "/(\-\-?[a-zA-Z0-9\-\_]+=?\W+?)\'?" . $macro . "\'?/";
    if (preg_match($pattern, $command_with_macro, $matches)) {
        for ($i = 1; $i < count($matches); $i++) {
            /* Prepare pattern */
            $pattern = $matches[$i];
            $pattern = str_replace('/', '\/', $pattern);
            $pattern = str_replace('-', '\-', $pattern);
            $pattern = str_replace('.', '\.', $pattern);
            $pattern = "/(.*\s)?" . $pattern . "\'?([\\x21-\\x7E]+)\'?(\s.*)?/";
             /* Replace value of custom macro password type 
                in executed command line */
            $executed_command = preg_replace($pattern, "\$1" . $matches[$i] . "***\$3", $executed_command);
        }
    }

    return $executed_command;
}

/**
 * Get the list of hosttemplate ID of an host 
 * 
 * @param int $host_id The ID of the host
 * 
 * @return array
 */
function getHostsTemplates($host_id) {
    $pearDBCentreon = new CentreonDB();

    $query = "SELECT host_tpl_id FROM host_template_relation "
        . "WHERE host_host_id = '" . $host_id . "'";
    $res = $pearDBCentreon->query($query);
    if ($res->numRows() == 0) {
        return array($host_id);
    } else {
        $arrHostTpl = array();
        while ($row = $res->fetchRow()) {
            $arrHostTpl = array_merge(
                $arrHostTpl, 
                getHostsTemplates($row['host_tpl_id'])
            );
            $arrHostTpl = array_merge($arrHostTpl, array($host_id));
        }
        return $arrHostTpl;
    }
    return $arrHostTpl;
}
