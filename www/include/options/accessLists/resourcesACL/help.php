<?php
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
