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
 * @param int    $serviceId         The ID of the service
 * @param int    $hostId            The ID of the host
 * @param string $commandName       The name of the command
 * @param string $replacementValue  The value to replace password
 *
 * @return string
 */
function hidePasswordInCommand(
    $serviceId,
    $hostId,
    $commandName,
    $replacementValue = '***'
): string {
    global $pearDB;

    $pearDBStorage = new CentreonDB('centstorage');

    // Get executed command lines
    $statement = $pearDBStorage->prepare("SELECT host_id, check_command, command_line
        FROM services
        WHERE host_id = :host_id
        AND service_id = :service_id");
    $statement->bindParam(':host_id', $hostId, PDO::PARAM_INT);
    $statement->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
    try {
        $statement->execute();
    } catch (\PDOException $e) {
        return _('Unable to get executed command');
    }

    if ($row = $statement->fetch()) {
        $monitoringCommand = $row['command_line'];
    } else {
        return _('Unable to get executed command');
    }


    // Get command line with macro
    $statement = $pearDB->prepare("SELECT command_line FROM command WHERE command_name = :command_name");
    $statement->bindParam(':command_name', $commandName, \PDO::PARAM_STR);
    try {
        $statement->execute();
    } catch (\PDOException $e) {
        return _('Unable to get configured command');
    }

    if ($row = $statement->fetch()) {
        $configurationCommand = $row['command_line'];
    } else {
        $statement = $pearDB->prepare("SELECT service_register FROM service WHERE service_id = :service_id");
        $statement->bindParam(':service_id', $serviceId, \PDO::PARAM_INT);
        try {
            $statement->execute();
        } catch (\PDOException $e) {
            return _('Unable to get service_register value');
        }

        if ($row = $statement->fetch() && $row['service_register'] == 2) {
            // For META SERVICE we can define the configuration command line with the monitoring command line
            return $monitoringCommand;
        } else {
            // The service is not a META SERVICE
            return _('Unable to get service_register value');
            // TODO: What about 'The configuration has changed. For security reasons we do not display the command line'
        }
    }
    
    // Get host and service password type macros
    $serviceMacrosPassword = getServiceMacrosPassword($serviceId);
    $hostMacrosPassword = getHostMacrosPassword($hostId);
    if (!empty($hostMacrosPassword) || !empty($serviceMacrosPassword)) {
        $macrosPassword = array_merge_recursive($hostMacrosPassword, $serviceMacrosPassword);
        $onDemandServiceMacro = getServiceOnDemandMacros($serviceId);
        $onDemandHostMacro = getHostOnDemandMacros($hostId);

        $configurationToken = explode(' ', $configurationCommand);
        $monitoringToken = explodeSpacesButKeepValuesByMacro(
            $configurationCommand,
            $monitoringCommand,
            $onDemandServiceMacro,
            $onDemandHostMacro
        );

        if (count($monitoringToken) === count($configurationToken)) {
            $patternMacrosPassword = implode('|', array_keys($macrosPassword));
            $patternMacrosPassword = str_replace(['$', '~'], ['\$', '\~'], $patternMacrosPassword);
            foreach ($configurationToken as $index => $token) {
                if (
                    preg_match_all('~' . $patternMacrosPassword . '~', $token, $matches, PREG_SET_ORDER)
                    && array_key_exists($matches[0][0], $macrosPassword)
                    && $macrosPassword[$matches[0][0]] !== null
                ) {
                    $monitoringToken[$index] = str_replace(
                        $macrosPassword[$matches[0][0]],
                        $replacementValue,
                        $monitoringToken[$index]
                    );
                }
            }
            return implode(' ', $monitoringToken);
        } else {
            return _('Unable to hide passwords in command');
        }
    } else {
        // No password type macros, return the monitoring command
        return $monitoringCommand;
    }
}

/**
 * Get the list of password macro type of a service
 *
 * @param int $serviceId The ID of the service
 *
 * @return array
 */
function getServiceMacrosPassword($serviceId): array
{
    global $pearDB;

    // Get list of templates of the service
    $arrtSvcTpl = getListTemplates($pearDB, $serviceId);
    $arrSvcTplID = array($serviceId);
    foreach ($arrtSvcTpl as $svc) {
        $arrSvcTplID[] = $svc['service_id'];
    }

    // Get list of custom password type macros from service and linked templates
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
    $query .= ")";
    $sth = $pearDB->prepare($query);
    for ($i = 0; $i < count($arrSvcTplID); $i++) {
        $sth->bindParam(':svc_id' . $i, $arrSvcTplID[$i], \PDO::PARAM_INT);
    }
    $sth->execute();

    $arrServiceMacrosPassword = [];
    while ($row = $sth->fetchRow()) {
        $arrServiceMacrosPassword = array_merge(
            $arrServiceMacrosPassword,
            [$row['svc_macro_name'] => $row['svc_macro_value']]
        );
    }

    return $arrServiceMacrosPassword;
}

/**
 * Get the list of password macro type of a host
 *
 * @param int $hostId The ID of the host
 *
 * @return array
 */
function getHostMacrosPassword($hostId): array
{
    global $pearDB;

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
    $query .= ")";
    $sth = $pearDB->prepare($query);
    for ($i = 0; $i < count($arrHostTplID); $i++) {
        $sth->bindParam(':host_id' . $i, $arrHostTplID[$i], \PDO::PARAM_INT);
    }
    $sth->execute();

    $arrHostMacrosPassword = [];
    while ($row = $sth->fetchRow()) {
        $arrHostMacrosPassword = array_merge(
            $arrHostMacrosPassword,
            [$row['host_macro_name'] => $row['host_macro_value']]
        );
    }

    return $arrHostMacrosPassword;
}

/**
 * Get the list of on demand macros of a service
 *
 * @param int $serviceId The ID of the service
 *
 * @return array
 */
function getServiceOnDemandMacros($serviceId): array
{
    global $pearDB;

    // Get list of templates of the service
    $arrtSvcTpl = getListTemplates($pearDB, $serviceId);
    $arrSvcTplID = array($serviceId);
    foreach ($arrtSvcTpl as $svc) {
        $arrSvcTplID[] = $svc['service_id'];
    }

    // Get list of custom password type macros from service and linked templates
    $query = "SELECT svc_macro_name, svc_macro_value "
        . "FROM on_demand_macro_service "
        . "WHERE is_password = 0 "
        . "AND svc_svc_id IN (";
    for ($i = 0; $i < count($arrSvcTplID); $i++) {
        if ($i === 0) {
            $query .= ':svc_id' . $i;
        } else {
            $query .= ', :svc_id' . $i;
        }
    }
    $query .= ")";
    $sth = $pearDB->prepare($query);
    for ($i = 0; $i < count($arrSvcTplID); $i++) {
        $sth->bindParam(':svc_id' . $i, $arrSvcTplID[$i], \PDO::PARAM_INT);
    }
    $sth->execute();

    $arrServiceOnDemandMacros = [];
    while ($row = $sth->fetchRow()) {
        $arrServiceOnDemandMacros = array_merge(
            $arrServiceOnDemandMacros,
            [$row['svc_macro_name'] => $row['svc_macro_value']]
        );
    }

    return $arrServiceOnDemandMacros;
}

/**
 * Get the list of on demand macros of a host
 *
 * @param int $hostId The ID of the host
 *
 * @return array
 */
function getHostOnDemandMacros($hostId): array
{
    global $pearDB;

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
    $query .= ")";
    $sth = $pearDB->prepare($query);
    for ($i = 0; $i < count($arrHostTplID); $i++) {
        $sth->bindParam(':host_id' . $i, $arrHostTplID[$i], \PDO::PARAM_INT);
    }
    $sth->execute();

    $arrHostOnDemandMacros = [];
    while ($row = $sth->fetchRow()) {
        $arrHostOnDemandMacros = array_merge(
            $arrHostOnDemandMacros,
            [$row['host_macro_name'] => $row['host_macro_value']]
        );
    }

    return $arrHostOnDemandMacros;
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
        while ($row = $sth->fetchRow()) {
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

/**
 * The purpose is to analyze the command line completed by Engine and to detect the value of a macro
 * that contains spaces to place the value only in one element of the array.
 *
 * <i><b>This process works only with the on-demand macros.</b></i><br\><br\>
 *
 * example: \-\-a='$\_HOSTVALUE$' with $\_HOSTVALUE$ = ' my value '
 *
 * Actually with a simple explode(' ', ...):
 * (array[0] => "\-\-a='", array[1] => "my", array[2] => "value", array[3] => "'")
 *
 * Transform into:
 * (array[0] => "\-\-a=' my value '")
 *
 * @param string $configurationCommand Configuration command line
 * @param string $monitoringCommand Monitoring command line
 * @param array  $onDemandServiceMacros List of on-demand service macros
 * @param array  $onDemandHostMacros List of on-demand host macros
 *
 * @return array<int, string>
 */
function explodeSpacesButKeepValuesByMacro(
    string $configurationCommand,
    string $monitoringCommand,
    array $onDemandServiceMacros,
    array $onDemandHostMacros
): array {
    $macrosByName = array_merge($onDemandServiceMacros, $onDemandHostMacros);
    $configurationTokens = explode(' ', $configurationCommand);
    $monitoringToken = explode(' ', $monitoringCommand);

    $indexMonitoring = 0;
    foreach ($configurationTokens as $indexConfiguration => $token) {
        if (preg_match_all('~\$_(HOST|SERVICE)[^$]*\$~', $token, $matches, PREG_SET_ORDER)) {
            if (array_key_exists($matches[0][0], $macrosByName)) {
                $macroToAnalyse = $macrosByName[$matches[0][0]];
                if (!empty($macroToAnalyse)) {
                    $numberSpacesInMacroValue = count(explode(' ', $macroToAnalyse)) - 1;
                    if ($numberSpacesInMacroValue > 0) {
                        $replacementValue = implode(
                            ' ',
                            array_slice(
                                $monitoringToken,
                                $indexMonitoring,
                                $numberSpacesInMacroValue + 1
                            )
                        );
                        array_splice(
                            $monitoringToken,
                            $indexMonitoring,
                            $numberSpacesInMacroValue + 1,
                            $replacementValue
                        );
                        $indexMonitoring += $numberSpacesInMacroValue + 1;
                    }
                }
            }
        } else {
            $indexMonitoring++;
        }
    }
    return $monitoringToken;
}
