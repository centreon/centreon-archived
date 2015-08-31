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

/**
 * snmpd
 */

$help['tip_global_community'] = dgettext('help', 'SNMP global community.');
$help['tip_version'] = dgettext('help', 'SNMP version.');

/**
 * snmptrapd
 */

$help['tip_directory_of_traps_configuration_files'] = dgettext('help', 'Directory of trap configuration files.');
$help['tip_snmpttconvertmib_directory+binary'] = dgettext('help', 'snmpttconvertmib binary with complete path.');
$help['tip_snmptt_log_file'] = dgettext('help', 'SNMPTT log file.');
$help['tip_perl_library_directory'] = dgettext('help', 'Perl library directory.');
$help['tip_init_script_snmptt'] = dgettext('help', 'SNMPTT init script. This options is used only if snmptt is used in daemon mode.');