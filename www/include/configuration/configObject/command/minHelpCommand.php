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

if (!isset($oreon)) {
    exit();
}

$commandId = filter_var(
    $_GET["command_id"] ?? $_POST["command_id"] ?? null,
    FILTER_VALIDATE_INT
);

$commandName = filter_var(
    $_GET["command_name"] ?? $_POST["command_name"] ?? null,
    FILTER_SANITIZE_STRING
);

if ($commandId !== false) {
    //Get command information
    $sth = $pearDB->prepare('SELECT * FROM `command` WHERE `command_id` = :command_id LIMIT 1');
    $sth->bindParam(':command_id', $commandId, PDO::PARAM_INT);
    $sth->execute();
    $cmd = $sth->fetch();
    unset($sth);

    $aCmd = explode(" ", $cmd["command_line"]);
    $fullLine = $aCmd[0];
    $plugin = array_values(preg_grep('/^\-\-plugin\=(\w+)/i', $aCmd))[0];
    $mode = array_values(preg_grep('/^\-\-mode\=(\w+)/i', $aCmd))[0];
    $aCmd = explode("/", $fullLine);
    $resourceInfo = $aCmd[0];

    $prepare = $pearDB->prepare(
        'SELECT `resource_line` FROM `cfg_resource` WHERE `resource_name` = :resource LIMIT 1'
    );
    $prepare->bindValue(':resource', $resourceInfo, \PDO::PARAM_STR);
    $prepare->execute();
    //Match if the first part of the path is a MACRO
    if ($resource = $prepare->fetch()) {
        $resourcePath = $resource["resource_line"];
        unset($aCmd[0]);
        $command = rtrim($resourcePath, "/") . "#S#" . implode("#S#", $aCmd);
    } else {
        $command = $fullLine;
    }
} else {
    $command = $oreon->optGen["nagios_path_plugins"] . $commandName;
}

// Secure command
$search = ['#S#', '#BS#', '../', "\t"];
$replace = ['/', "\\", '/', ' '];
$command = str_replace($search, $replace, $command);
$command = escapeshellcmd($command);

$tab = explode(' ', $command);
if (realpath($tab[0])) {
    $command = realpath($tab[0]) . ' ' . $plugin . ' ' . $mode . ' --help';
} else {
    $command = $tab[0] . ' ' . $plugin . ' ' . $mode . ' --help';
}

$stdout = shell_exec($command . " 2>&1");
$msg = str_replace("\n", "<br />", $stdout);

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
if (isset($msg) && $msg) {
    $tpl->assign('msg', CentreonUtils::escapeAllExceptSelectedTags($msg, ['br']));
}

$tpl->display("minHelpCommand.ihtml");
