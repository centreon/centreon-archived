<?php
$help = array();

/**
 * Centreon information
 */

$help['tip_directory'] = dgettext('help', 'Application directory of Centreon.');
$help['tip_centreon_web_directory'] = dgettext('help', 'Centreon Web URI.');

/**
 * Maximum page size
 */

$help['tip_limit_per_page'] = dgettext('help', 'Default number of displayed elements in listing pages.');
$help['tip_limit_per_page_for_monitoring'] = dgettext(
    'help',
    'Default number of displayed elements in monitoring consoles.'
);
$help['tip_graph_per_page_for_performances'] = dgettext('help', 'Number of performance graphs displayed per page.');
$help['tip_select_paginagion_size'] = dgettext('help', 'The number elements loaded in async select.');



/**
 * Sessions Properties
 */

$help['tip_sessions_expiration_time'] = dgettext('help', 'Life duration of sessions.');

/**
 * Refresh Properties
 */

$help['tip_refresh_interval'] = dgettext('help', 'Refresh interval.');
$help['tip_refresh_interval_for_statistics'] = dgettext('help', 'Refresh interval used for statistics (top counters).');
$help['tip_refresh_interval_for_monitoring'] = dgettext('help', 'Refresh interval used in monitoring consoles.');
$help['tip_first_refresh_delay_for_statistics'] = dgettext(
    'help',
    'First refresh delay for statistics (top counters).'
);
$help['tip_first_refresh_delay_for_monitoring'] = dgettext('help', 'First refresh delay for monitoring.');

/**
 * Display Options
 */

$help['tip_display_template'] = dgettext('help', 'CSS theme.');

/**
 * Problem display properties
 */

$help['tip_sort_problems_by'] = dgettext('help', 'Default sort in monitoring consoles.');
$help['tip_order_sort_problems'] = dgettext('help', 'Default order in monitoring consoles.');

/**
 * Authentication properties
 */

$help['tip_enable_autologin'] = dgettext('help', 'Enables Autologin.');
$help['tip_display_autologin_shortcut'] = dgettext('help', 'Displays Autologin shortcut.');

/**
 * Time Zone
 */

$help['tip_default_timezone'] = dgettext('help', 'Default host and contact timezone.');

/**
 * SSO
 */
$help['sso_enable'] = dgettext(
    'help',
    'Whether SSO authentication is enabled. SSO feature have only to be enabled in a secured and dedicated environment'
    . ' for SSO. Direct access to Centreon UI from users have to be disabled.'
);
$help['sso_mode'] = dgettext(
    'help',
    'Authentication can be solely based on SSO or it can work with both SSO and local authentication systems.'
);
$help['sso_trusted_clients'] = dgettext(
    'help',
    'IP/DNS of trusted clients. Use coma as delimiter in case of multiple clients.'
);
$help['sso_blacklist_clients'] = dgettext(
    'help',
    'IP/DNS of blacklist clients. Use coma as delimiter in case of multiple clients.'
);
$help['sso_header_username'] = dgettext(
    'help',
    'The header variable that will be used as login. i.e: $_SERVER[\'HTTP_AUTH_USER\']'
);
$help['sso_username_pattern'] = dgettext(
    'help',
    'The pattern to search for in the username. If i want to remove the domain of the email: /@.*/'
);
$help['sso_username_replace'] = dgettext(
    'help',
    'The string to replace.'
);

/**
 * UI bahvior
 */
$help['strict_hostParent_poller_management'] = dgettext(
    'help',
    'This option enable a strict mode for the management of parent links between hosts on different pollers.'
    . ' Some hosts can have a parent link between them even if they are not monitored by the same poller.'
    . ' Select yes if you want to block the UI in order to oblige user to let host with a declared relation on the'
    . ' same poller. if you select No, during the generation process, relation will be broken and not generated'
    . ' but kept with Centreon Broker Correlation module.'
);

/*
 * Support Informations
 */
$help['tip_centreon_support_email'] = dgettext(
    'help',
    'Centreon Support email: this email is uses in the Centreon footer in order to have a quick'
    . ' link in order to open an issue to your help desk.'
);

/*
 * Proxy options
 */
$help['tip_proxy_url'] = dgettext(
    'help',
    'URL of the proxy.'
);
$help['tip_proxy_port'] = dgettext(
    'help',
    'Port of the proxy.'
);
$help['tip_proxy_user'] = dgettext(
    'help',
    'User of the proxy (Only if you use basic authentication).'
);
$help['tip_proxy_password'] = dgettext(
    'help',
    'Password of the proxy (Only if you use basic authentication).'
);

/*
 * Chart options
 */
$help['tip_display_downtime_chart'] = dgettext(
    'help',
    'If this option is enable, the downtimes and acknowledgments will be displayed on metric chart.<br>' .
    '<b>Warning</b> : This option can slow down the display of chart.'
);
$help['tip_display_comment_chart'] = dgettext(
    'help',
    'If this option is enable, the comments will be displayed on status chart.<br>' .
    '<b>Warning</b> : This option can slow down the display of chart.'
);
