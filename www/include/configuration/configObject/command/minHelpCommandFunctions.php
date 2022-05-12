<?php

/*
 * Copyright 2005-2022 Centreon
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

/**
 * @param string $command
 * @return bool
 */
function isCommandInAllowedResources(CentreonDB $pearDB, string $command): bool
{
    $allowedResources = getAllResources($pearDB);

    foreach ($allowedResources as $path) {
        if (substr($command, 0, strlen($path)) === $path) {
            return true;
        }
    }
    return false;
}

/**
 * @param CentreonDB $pearDB
 * @param int $commandId
 * @return string|null
 */
function getCommandById(CentreonDB $pearDB, int $commandId): ?string
{
    $sth = $pearDB->prepare('SELECT command_line FROM `command` WHERE `command_id` = :command_id');
    $sth->bindParam(':command_id', $commandId, PDO::PARAM_INT);
    $sth->execute();
    $command = $sth->fetchColumn();

    return $command !== false ? $command : null;
}

/**
 * @param CentreonDB $pearDB
 * @param string $resourceName
 * @return string|null
 */
function getResourcePathByName(CentreonDB $pearDB, string $resourceName): ?string
{
    $prepare = $pearDB->prepare(
        'SELECT `resource_line` FROM `cfg_resource` WHERE `resource_name` = :resource LIMIT 1'
    );
    $prepare->bindValue(':resource', $resourceName, \PDO::PARAM_STR);
    $prepare->execute();
    $resourcePath = $prepare->fetchColumn();

    return $resourcePath !== false ? $resourcePath : null;
}

/**
 * @param CentreonDB $pearDB
 * @return string[]
 */
function getAllResources(CentreonDB $pearDB): array
{
    $dbResult = $pearDB->query('SELECT `resource_line` FROM `cfg_resource`');

    return $dbResult->fetchAll(\PDO::FETCH_COLUMN);
}

/**
 * @param string $commandLine
 * @return array{commandPath:string,plugin:string|null,mode:string|null}
 */
function getCommandElements(string $commandLine): array
{
    $commandElements = explode(" ", $commandLine);

    $matchPluginOption = array_values(preg_grep('/^\-\-plugin\=(\w+)/i', $commandElements) ?? []);
    $plugin = $matchPluginOption[0] ?? null;
    $matchModeOption = array_values(preg_grep('/^\-\-mode\=(\w+)/i', $commandElements) ?? []);
    $mode = $matchModeOption[0] ?? null;

    return ['commandPath' => $commandElements[0], 'plugin' => $plugin, 'mode' => $mode];
}

/**
 * @param CentreonDB $pearDB
 * @param string $commandPath
 * @return string
 */
function replaceMacroInCommandPath(CentreonDB $pearDB, string $commandPath): string
{
    $explodedCommandPath = explode("/", $commandPath);
    $resourceName = $explodedCommandPath[0];

    //Match if the first part of the path is a MACRO
    if ($resourcePath = getResourcePathByName($pearDB, $resourceName)) {
        unset($explodedCommandPath[0]);
        return rtrim($resourcePath, "/") . "/" . implode("/", $explodedCommandPath);
    }

    return $commandPath;
}
