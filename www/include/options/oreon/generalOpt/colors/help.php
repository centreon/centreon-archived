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
 * Hosts status colors
 */

$help['tip_host_up_color'] = dgettext('help', 'Host UP Color.');
$help['tip_host_down_color'] = dgettext('help', 'Host DOWN Color.');
$help['tip_host_unreachable_color'] = dgettext('help', 'Host UNREACHABLE Color.');

/**
 * Services status colors
 */

$help['tip_service_ok_color'] = dgettext('help', 'Service OK Color.');
$help['tip_service_warning_color'] = dgettext('help', 'Service WARNING Color.');
$help['tip_service_critical_color'] = dgettext('help', 'Service CRITICAL Color.');
$help['tip_service_pending_color'] = dgettext('help', 'Service PENDING Color.');
$help['tip_service_unknown_color'] = dgettext('help', 'Service UNKNOWN Color.');
$help['tip_row_color_for_service_critical'] = dgettext('help', 'Row Color for Service CRITICAL.');

/**
 * Miscelleneous
 */

$help['tip_acknowledge_host_or_service_color'] = dgettext('help', 'Color of Hosts / Services that are acknowledged.');
$help['tip_downtime_host_or_service_color'] = dgettext('help', 'Color of Hosts / Services that are on downtime.');

/**
 * Specifics for hosts
 */

$help['tip_color_for_host_down_in__service_view'] = dgettext('help', 'Color for Host Down (in Service monitoring consoles).');
$help['tip_color_for_host_unreachable_in__service_view'] = dgettext('help', 'Color for Host Unreachable (in Service monitoring consoles).');
