<?php
/*
 * Copyright 2005-2015 Centreon
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

if (!isset($centreon)) {
    exit();
}

require_once(_CENTREON_PATH_ . "www/include/common/common-Func.php");

$error_msg = "";
$command = urldecode($_GET["command_line"]);
$example = $_GET["command_example"];
$args = preg_split("/\!/", $example);

$command = str_replace(array('&#39;', '&#34;'), array("'", '"'), $command);

for ($i = 0; $i < count($args); $i++) {
    $args[$i] = escapeshellarg($args[$i]);
}
$resource_def = str_replace('$', '@DOLLAR@', $command);
$resource_def = escapeshellcmd($resource_def);

/* Get resources in DB and replace by the value */
while (preg_match("/@DOLLAR@USER([0-9]+)@DOLLAR@/", $resource_def, $matches) and $error_msg == "") {
    $DBRESULT = $pearDB->query("SELECT resource_line FROM cfg_resource WHERE resource_name = '\$USER".$matches[1]."\$' LIMIT 1");
    $resource = $DBRESULT->fetchRow();
    if (!isset($resource["resource_line"])) {
        $error_msg .= "\$USER".$matches[1]."\$";
    } else {
        $resource_def = str_replace("@DOLLAR@USER". $matches[1] ."@DOLLAR@", $resource["resource_line"], $resource_def);
    }
}

/* Replace HOSTADDRESS by the real content */
while (preg_match("/@DOLLAR@HOSTADDRESS@DOLLAR@/", $resource_def, $matches) and $error_msg == "") {
    if (isset($_GET["command_hostaddress"]) && $_GET["command_hostaddress"] != "") {
        $resource_def = str_replace("@DOLLAR@HOSTADDRESS@DOLLAR@", $_GET["command_hostaddress"], $resource_def);
    } else {
        $error_msg .= "\$HOSTADDRESS\$";
    }
}

/* Replace $POLLERID$ by the poller id */
while (preg_match("/@DOLLAR@ARG([0-9]+)@DOLLAR@/", $resource_def, $matches) and $error_msg == "") {
    $match_id = $matches[1];
    if (isset($args[$match_id])) {
        $resource_def = str_replace("@DOLLAR@ARG". $match_id ."@DOLLAR@", $args[$match_id], $resource_def);
        $resource_def = str_replace('$', '@DOLLAR@', $resource_def);
        if (preg_match("/@DOLLAR@USER([0-9]+)@DOLLAR@/", $resource_def, $matches)) {
            $DBRESULT = $pearDB->query("SELECT resource_line FROM cfg_resource WHERE resource_name = '\$USER".$matches[1]."\$' LIMIT 1");
            $resource = $DBRESULT->fetchRow();
            if (!isset($resource["resource_line"])) {
                $error_msg .= "\$USER".$match_id."\$";
            } else {
                $resource_def = str_replace("@DOLLAR@USER". $matches[1] ."@DOLLAR@", $resource["resource_line"], $resource_def);
            }
        }
        if (preg_match("/@DOLLAR@HOSTADDRESS@DOLLAR@/", $resource_def, $matches)) {
            if (isset($_GET["command_hostaddress"])) {
                $resource_def = str_replace("@DOLLAR@HOSTADDRESS@DOLLAR@", $_GET["command_hostaddress"], $resource_def);
            } else {
                $error_msg .= "\$HOSTADDRESS\$";
            }
        }
    } else {
        $error_msg = "\$USER" . $match_id . "\$";
    }
}

/* Execute */
if ($error_msg != "") {
    $command = $resource_def;
    $command = str_replace('@DOLLAR@', '$', $command);
    $msg = _("Could not find macro ") . $error_msg;
    $status = _("ERROR");
} else {
    $command = $resource_def;
    $command = str_replace('@DOLLAR@', '$', $command);
    $splitter = preg_split("/\;/", $command);
    $command = $splitter[0];
    $stdout = array();
    unset($stdout);

    /*
     * for security reasons, we do not allow the execution of any command unless it is located in path $USER1$
     */
    $DBRESULT = $pearDB->query("SELECT `resource_line` FROM `cfg_resource` WHERE `resource_name` = '\$USER1\$' LIMIT 1");
    $resource = $DBRESULT->fetchRow();
    $user1Path = $resource["resource_line"];
    $pathMatch = str_replace('/', '\/', $user1Path);

    if (preg_match("/^$pathMatch/", $command)) {
        if (preg_match("/\.\./", $command)) {
            $msg = _("Directory traversal detected");
        } else {
            $msg = exec($command, $stdout, $status);
            $msg = join("<br/>", $stdout);
            if ($status == 1) {
                $status = _("WARNING");
            } elseif ($status == 2) {
                $status = _("CRITICAL");
            } elseif ($status == 0) {
                        $status = _("OK");
            } else {
                $status = _("UNKNOWN");
            }
        }
    } else {
        $msg = _("Plugin has to be in : ") . $user1Path;
    }
}

$attrsText  = array("size" => "25");
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
$form->addElement('header', 'title', _("Plugin Test"));

/*
 * Command information
 */
$form->addElement('header', 'information', _("Plugin test"));
$form->addElement('text', 'command_line', _("Command Line"), $attrsText);
$form->addElement('text', 'command_help', _("Output"), $attrsText);
$form->addElement('text', 'command_status', _("Status"), $attrsText);

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
$tpl->assign('command_line', $command);

if (isset($msg) && $msg) {
    $tpl->assign('msg', $msg);
}
if (isset($status)) {
    $tpl->assign('status', $status);
}
$tpl->display("minPlayCommand.ihtml");
