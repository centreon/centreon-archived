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

$help['tip_group_name'] = dgettext('help', 'Name of access group.');
$help['tip_alias'] = dgettext('help', 'Alias of access group.');

/**
 * Relations
 */

$help['tip_linked_contacts'] = dgettext('help', 'Implied users.');
$help['tip_linked_contact_groups'] = dgettext('help', 'Implied user groups.');

/**
 * Additional Information
 */

$help['tip_status'] = dgettext('help', 'Enable or disable the access group.');

/**
 * Resources access list link
 */

$help['tip_resources_access'] = dgettext('help', 'ACL resource rules that are linked to the access group.');

/**
 * Menu access list link
 */

$help['tip_menu_access'] = dgettext('help', 'ACL menu rules that are linked to the access group.');

/**
 * Action access list link
 */

$help['tip_actions_access'] = dgettext('help', 'ACL action rules that are linked to the access group.');