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
 * @param string $commandName The name of the command
 * @param int    $hostId      The ID of the host
 * @param int    $serviceId   The ID of the service
 * 
 * @return string
 */
function hidePasswordInCommand($commandName, $hostId, $serviceId)
{
    global $pearDB;

    if (!isset($commandName) && !isset($serviceId)) {
        return 1;
    }

    $pearDBStorage = new CentreonDB('centstorage');

    // Get command line with macro
    $res = $pearDB->query(
        "SELECT command_line
        FROM command
        WHERE command_name = '" . $pearDB->escape($commandName) . "'"
    );
    $row = $res->fetchRow();
    $commandLineWithMacros = $row['command_line'];

    // Get executed command lines
    $res = $pearDBStorage->query(
        "SELECT host_id, check_command, command_line
        FROM services
        WHERE host_id = '" . $pearDBStorage->escape($hostId) . "'
        AND service_id = '" . $pearDBStorage->escape($serviceId) . "'"
    );
    $row = $res->fetchRow();
    $commandLineExecuted = $row['command_line'];

    // Get list of templates
    $arrtSvcTpl = getListTemplates($pearDB, $serviceId);
    $arrSvcTplID = array($serviceId);
    foreach ($arrtSvcTpl as $svc) {
        $arrSvcTplID[] = $svc['service_id'];
    }

    /* Get list of custom macros from services and templates */
    $res = $pearDB->query(
        "SELECT svc_macro_name
        FROM on_demand_macro_service
        WHERE is_password = 1
        AND svc_svc_id IN ('" . implode('\', \'', $arrSvcTplID) . "')"
    );

    $arrServiceMacroPassword = array();
    while ($row = $res->fetchRow()) {
        $arrServiceMacroPassword = array_merge(
            $arrServiceMacroPassword,
            array($row['svc_macro_name'])
        );
    }

    // Get custom macros from hosts and templates
    $res = $pearDB->query(
        "SELECT host_macro_name
        FROM on_demand_macro_host
        WHERE is_password = 1
        AND host_host_id IN ('" . implode('\', \'', getHostsTemplates($host_id)) . "')"
    );

    $arrHostMacroPassword = array();
    while ($row = $sth->fetchRow()) {
        $arrHostMacroPassword = array_merge(
            $arrHostMacroPassword,
            array($row['host_macro_name'])
        );
    }

    $command = '';
    $patternMacro = '';
    $aCommandLineWithMacros = explode(' ', $commandLineWithMacros);
    $aCommandLineExecuted = explode(' ', $commandLineExecuted);
    $arrMacroPassword = array_merge($arrServiceMacroPassword, $arrHostMacroPassword);
    $patternMacro = implode('|', $arrMacroPassword);
    $patternMacro = str_replace('$', '\\$', $patternMacro);

    if (count($arrMacroPassword) && preg_match('/(' . $patternMacro . ')/', $commandLineWithMacros)) {
        if (count($aCommandLineWithMacros) == count($aCommandLineExecuted)) {
            for ($i = 0; $i < count($aCommandLineWithMacros); $i++) {
                if (preg_match_all('/(' . $patternMacro . ')/', $aCommandLineWithMacros[$i], $matches)) {
                    $pattern = $aCommandLineWithMacros[$i];
                    foreach ($matches as $match) {
                        if ($arrMacroPassword[$match[0]]) {
                            $pattern = preg_replace($match, $pattern);
                        }
                    }
                    $command .= ' ' . preg_replace('/\$_(HOST|SERVICE)[a-zA-Z0-9_-]+\$/', '***', $pattern);
                } else {
                    $command .= ' ' . $aCommandLineExecuted[$i];
                }
            }
            return preg_replace('/^ /', '', $command);
        } else {
            return _('Unable to hide passwords in command');
        }
    } else {
        return $commandLineExecuted;
    }
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

    $res = $pearDBCentreon->query(
        "SELECT host_tpl_id
        FROM host_template_relation
        WHERE host_host_id = " . (int) $hostId;
    );

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
