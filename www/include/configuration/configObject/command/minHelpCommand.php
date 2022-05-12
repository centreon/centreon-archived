<?php

/*
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


if (!isset($centreon)) {
    exit();
}

require_once __DIR__ . "/minHelpCommandFunctions.php";

$commandId = filter_var(
    $_GET["command_id"] ?? $_POST["command_id"] ?? null,
    FILTER_VALIDATE_INT
);

$commandName = htmlspecialchars($_GET["command_name"] ?? $_POST["command_name"] ?? null);

if ($commandId !== false) {
    $commandLine = getCommandById($pearDB, (int) $commandId) ?? '';

    ['commandPath' => $commandPath, 'plugin' => $plugin, 'mode' => $mode] = getCommandElements($commandLine);

    $command = replaceMacroInCommandPath($pearDB, $commandPath);
} else {
    $command = $centreon->optGen["nagios_path_plugins"] . $commandName;
}

// Secure command
$search = ['#S#', '#BS#', '../', "\t"];
$replace = ['/', "\\", '/', ' '];
$command = str_replace($search, $replace, $command);

// Remove params
$explodedCommand = explode(' ', $command);
$commandPath = realpath($explodedCommand[0]) === false ? $explodedCommand[0] : realpath($explodedCommand[0]);

// Exec command only if located in allowed directories
$msg = "Command not allowed";
if (isCommandInAllowedResources($pearDB, $commandPath)) {
    $command = $commandPath . ' ' . ($plugin ?? '') . ' ' . ($mode ?? '') . ' --help';
    $command = escapeshellcmd($command);
    $stdout = shell_exec($command . " 2>&1");
    $msg = str_replace("\n", "<br />", $stdout);
}

$attrsText = array("size" => "25");
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
$form->addElement('header', 'title', _("Plugin Help"));

/*
 * Command information
 */
$form->addElement('header', 'information', _("Help"));
$form->addElement('text', 'command_line', _("Command Line"), $attrsText);
$form->addElement('text', 'command_help', _("Output"), $attrsText);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);

$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign('command_line', CentreonUtils::escapeSecure($command, CentreonUtils::ESCAPE_ALL));
$tpl->assign('msg', CentreonUtils::escapeAllExceptSelectedTags($msg, ['br']));

$tpl->display("minHelpCommand.ihtml");
