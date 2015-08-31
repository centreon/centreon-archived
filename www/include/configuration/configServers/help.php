<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

$help = array();
$help['name'] = dgettext("help", "Used for identifying the poller");
$help['ns_ip_address'] = dgettext("help", "IP address of the poller");
$help['localhost'] = dgettext("help", "Whether the poller is local");
$help['is_default'] = dgettext("help", "Main poller");
$help['ssh_port'] = dgettext("help", "SSH port of the remote poller");
$help['monitoring_engine'] = dgettext("help", "Monitoring engine");
$help['init_script'] = dgettext("help", "Path of init script of the scheduler");
$help['init_script_centreontrapd'] = dgettext("help", "Path of init script of centreontrapd");
$help['nagios_bin'] = dgettext("help", "Path of binary of the scheduler");
$help['nagiostats_bin'] = dgettext("help", "Path of stats binary of the scheduler");
$help['nagios_perfdata'] = dgettext("help", "Perfdata script");
$help['centreonbroker_cfg_path'] = dgettext("help", "Path of the configuration file for Centreon Broker");
$help['centreonbroker_module_path'] = dgettext("help", "Path with modules for Centreon Broker");
$help['centreonconnector_path'] = dgettext("help", "Path with Centreon Connector binaries");
$help['ns_activate'] = dgettext("help", "Enable or disable poller");
$help['centreontrapd_init_script'] = dgettext("help", "SNMP Trap Translator init script");
$help['snmp_trapd_path_conf'] = dgettext("help", "Light databases will be stored in the specified directory. They are used for synchronizing trap definitions on pollers.");
$help['pollercmd'] = dgettext("help", "Those commands can be executed at the end of the file generation generation/restart process. Do not specify macros in the commands, for they will not be replaced. Make sure to have sufficient rights for the Apache user to run these commands.");
$help['description'] = dgettext("help", "Short description of the poller");
?>
