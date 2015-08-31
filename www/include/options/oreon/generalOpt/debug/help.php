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
 * Debug
 */

$help['tip_logs_directory'] = dgettext('help', 'Directory of log files.');
$help['tip_authentication_debug'] = dgettext('help', 'Enables authentication debug.');
$help['tip_nagios_import_debug'] = dgettext('help', 'Enables Monitoring Engine import debug.');
$help['tip_rrdtool_debug'] = dgettext('help', 'Enables RRDTool debug.');
$help['tip_ldap_user_import_debug'] = dgettext('help', 'Enables LDAP user import debug.');
$help['tip_sql_debug'] = dgettext('help', 'Enables SQL debug.');
$help['tip_centcore_debug'] = dgettext('help', 'Enables Centcore debug.');
$help['tip_centstorage_debug'] = dgettext('help', 'Enables Centstorage debug.');
$help['tip_centreontrapd_debug'] = dgettext('help', 'Enables Centreontrapd debug.');
