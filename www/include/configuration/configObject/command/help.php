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

$help = array();

$help["command_name"] = dgettext("help", "The command name is used to identify and display this command in contact, host and service definitions.");

$help["command_line_help"] = dgettext("help", "The command line specifies the real command line which is actually executed by Monitoring engine. Before execution, all valid macros are replaced with their respective values. To use the dollar sign (\$) on the command line, you have to escape it with another dollar sign (\$\$). A semicolon (;) is used as separator for config file comments and must be worked around by setting a \$USER\$ macro to a semicolon and then referencing it here in place of the semicolon. If you want to pass arguments to commands during runtime, you can use \$ARGn\$ macros in the command line.");
$help["enable_shell"] = dgettext("help", "If your command requires shell features like pipes, redirections, globbing etc. check this box. If you are using Monitoring Engine this option cannot be disabled. Note that commands that require shell are slowing down the poller server.");

$help["arg_example"] = dgettext("help", "The argument example defined here will be displayed together with the command selection and help in providing a hint of how to parametrize the command for execution.");
$help["command_type"] = dgettext("help", "Define the type of the command. The type will be used to show the command only in the relevant sections.");
$help["graph_template"] = dgettext("help", "The optional definition of a graph template will be used as default graph template, when no other is specified.");
$help["arg_description"] = dgettext("help", "The argument description provided here will be displayed instead of the technical names like ARGn.");
$help["macro_description"] = dgettext("help", "The macro description provided here will be displayed instead of the technical name.");
$help["command_comment"] = dgettext("help", "Comments regarding the command.");
$help["connectors"] = dgettext("help", "Connectors are run in background and execute specific commands without the need to execute a binary, thus enhancing performance. This feature is available in Centreon Engine (>= 1.3)");
