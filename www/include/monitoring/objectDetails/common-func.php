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
    $sth = $pearDB->prepare("SELECT command_line FROM command WHERE command_name = :command_name");
    $sth->bindParam(':command_name', preg_replace('/!.*/', '', $commandName), PDO::PARAM_STR);
    $sth->execute();
    $row = $sth->fetch();
    $commandLineWithMacros = $row['command_line'];
    if (strlen($commandLineWithMacros) === 0) {
        return _('Unable to retrieve command') . ": '" . $commandName . "'";
    }

    // Get executed command lines
    $sth = $pearDBStorage->prepare(
        "SELECT command_line FROM services "
        . "WHERE host_id = :host_id "
        . "AND service_id = :service_id "
        . "AND check_command = :check_command"
    );
    $sth->bindParam(':host_id', $hostId, PDO::PARAM_INT);
    $sth->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
    $sth->bindParam(':check_command', $commandName, PDO::PARAM_STR);
    $sth->execute();
    $row = $sth->fetch();
    $commandLineExecuted = $row['command_line'];
    if (strlen($commandLineExecuted) === 0) {
        return _('Unable to retrieve executed command');
    }

    // Get list of templates
    $arrtSvcTpl = getListTemplates($pearDB, $serviceId);
    $arrSvcTplID = array($serviceId);
    foreach ($arrtSvcTpl as $svc) {
        $arrSvcTplID[] = $svc['service_id'];
    }

    // Get custom macros from services and templates
    $query = "SELECT svc_macro_name, svc_macro_value "
        . "FROM on_demand_macro_service "
        . "WHERE is_password = 1 "
        . "AND svc_svc_id IN (";
    for ($i = 0; $i < count($arrSvcTplID); $i++) {
        if ($i === 0) {
            $query .= ':svc_id' . $i;
        } else {
            $query .= ', :svc_id' . $i;
        }
    }
    $query .= ") ORDER BY svc_macro_name, LENGTH(svc_macro_value) DESC";
    $sth = $pearDB->prepare($query);
    for ($i = 0; $i < count($arrSvcTplID); $i++) {
        $sth->bindParam(':svc_id' . $i, $arrSvcTplID[$i], PDO::PARAM_INT);
    }
    $sth->execute();

    $currentMacro = "";
    $replacedMacros = 0;
    $replacedExecuted = 0;
    while ($row = $sth->fetch()) {
        if (strcmp($currentMacro, $row['svc_macro_name']) !== 0) {
            if ($replacedMacros != $replacedExecuted) {
                break;
            } else {
                $currentMacro = $row['svc_macro_name'];
                $commandLineWithMacros = str_replace(
                    $row['svc_macro_name'],
                    '$pw',
                    $commandLineWithMacros,
                    $replacedMacros
                );
                $replacedExecuted = 0;
            }
        }
        if ($replacedMacros > 0) {
            $commandLineExecuted = str_replace($row['svc_macro_value'], '$pw', $commandLineExecuted, $r);
            $replacedExecuted += $r;
        }
    }
    // Be sure to replace password strings the required amount of times, otherwise we could give clues to find it
    if ($replacedMacros != $replacedExecuted) {
        return _('Unable to hide passwords in command');
    }

    // Get custom macros from hosts and templates
    $arrHostTplID = getHostsTemplates($hostId);
    $query = "SELECT host_macro_name, host_macro_value "
        . "FROM on_demand_macro_host "
        . "WHERE is_password = 1 "
        . "AND host_host_id IN (";
    for ($i = 0; $i < count($arrHostTplID); $i++) {
        if ($i === 0) {
            $query .= ':host_id' . $i;
        } else {
            $query .= ', :host_id' . $i;
        }
    }
    $query .= ") ORDER BY host_macro_name, LENGTH(host_macro_value) DESC";
    $sth = $pearDB->prepare($query);
    for ($i = 0; $i < count($arrHostTplID); $i++) {
        $sth->bindParam(':host_id' . $i, $arrHostTplID[$i], PDO::PARAM_INT);
    }
    $sth->execute();

    $currentMacro = "";
    $replacedMacros = 0;
    $replacedExecuted = 0;
    while ($row = $sth->fetch()) {
        if (strcmp($currentMacro, $row['host_macro_name']) != 0) {
            if ($replacedMacros != $replacedExecuted) {
                break;
            } else {
                $currentMacro = $row['host_macro_name'];
                $commandLineWithMacros = str_replace(
                    $row['host_macro_name'],
                    '$pw',
                    $commandLineWithMacros,
                    $replacedMacros
                );
                $replacedExecuted = 0;
            }
        }
        if ($replacedMacros > 0) {
            $commandLineExecuted = str_replace($row['host_macro_value'], '$pw', $commandLineExecuted, $r);
            $replacedExecuted += $r;
        }
    }
    // Be sure to replace password strings the required amount of times, otherwise we could give clues to find it
    if ($replacedMacros != $replacedExecuted) {
        return _('Unable to hide passwords in command');
    }

    // Also hide as much as possible passwords from Centreon Plugins options which may be used in non-password macros
    $commandLineExecuted = preg_replace(
        '/(--([0-9a-z]+-)*password=|--snmp-community=)[^ ]+/',
        '$1$pw',
        $commandLineExecuted
    );

    return $commandLineExecuted;
}

/**
 * Get the list of hosttemplate ID of an host
 *
 * @param int $hostId The ID of the host
 *
 * @return array
 */
function getHostsTemplates($hostId)
{
    global $pearDB;

    $sth = $pearDB->prepare("SELECT host_tpl_id
        FROM host_template_relation
        WHERE host_host_id = :host_id");
    $sth->bindParam(':host_id', $hostId, PDO::PARAM_INT);
    $sth->execute();

    if ($sth->numRows() == 0) {
        return array($hostId);
    } else {
        $arrHostTpl = array();
        while ($row = $sth->fetch()) {
            $arrHostTpl = array_merge(
                $arrHostTpl,
                getHostsTemplates($row['host_tpl_id'])
            );
            $arrHostTpl = array_merge($arrHostTpl, array($hostId));
        }
        return $arrHostTpl;
    }
    return $arrHostTpl;
}
