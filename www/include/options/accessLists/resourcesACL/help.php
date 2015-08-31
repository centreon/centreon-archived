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
 * General Information
 */

$help['tip_access_list_name'] = dgettext('help', 'Name of resource rule.');
$help['tip_description'] = dgettext('help', 'Description of resource rule.');

/**
 * People linked to this Access list
 */

$help['tip_linked_groups'] = dgettext('help', 'Implied ACL groups.');

/**
 * Additional Information
 */

$help['tip_status'] = dgettext('help', 'Enable or disable the ACL resource rule.');
$help['tip_comments'] = dgettext('help', 'Comments regarding this resource rule.');

/**
 * Shared Host Resouces
 */

$help['tip_hosts'] = dgettext('help', 'Hosts that will be displayed to users. Services that belong to these hosts will also be visible.');
$help['tip_host_groups'] = dgettext('help', 'Host groups that will be displayed to users. Hosts that belong to these host groups will also be visible.');
$help['tip_exclude_hosts_from_selected_host_groups'] = dgettext('help', 'Excluding hosts from the selected host groups will hide them from users.');

/**
 * Shared Service Resouces
 */

$help['tip_service_groups'] = dgettext('help', 'Service groups that will be displayed to users. Services that belong to these service groups will also be visible.');

/**
 * Shared Meta Services Resouces
 */

$help['tip_meta_services'] = dgettext('help', 'Meta services that will be displayed to users.');

/**
 * Filters
 */

$help['tip_poller_filter'] = dgettext('help', 'Will only display resources that are monitored by these pollers. When blank, no filter is applied.');
$help['tip_host_category_filter'] = dgettext('help', 'Will only display hosts that belong to these host categories. When blank, no filter is applied.');
$help['tip_service_category_filter'] = dgettext('help', 'Will only display services that belong to these service categories. When blank, no filter is applied.');
