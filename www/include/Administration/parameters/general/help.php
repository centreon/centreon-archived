<?php
$help = array();

/**
 * Centreon statistics
 */
$help['tip_send_statistics'] = dgettext(
    'help',
    'I agree to participate to the Centreon Customer Experience Improvement Program whereby anonymous information ' .
    'about the usage of this server may be sent to Centreon. This information will solely be used to improve the ' .
    'software user experience. I will be able to opt-out at anytime. Refer to ' .
    '<a style="text-decoration: underline" href="http://ceip.centreon.com/">ceip.centreon.com</a> ' .
    ' for further details.'
);

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

$help['tip_sessions_expiration_time'] = dgettext(
    'help',
    'Life duration of sessions. The value in minutes cannot be greater than the session.gc_maxlifetime value set ' .
    'in your php centreon.ini file.'
);

/**
 * Refresh Properties
 */

$help['tip_refresh_interval'] = dgettext('help', 'Refresh interval.');
$help['tip_refresh_interval_for_statistics'] = dgettext('help', 'Refresh interval used for statistics (top counters). Minimum value: 10');
$help['tip_refresh_interval_for_monitoring'] = dgettext('help', 'Refresh interval used in monitoring consoles. Minimum value: 10');
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
 * Notification
 */

$help['tip_notification_inheritance'] = dgettext(
    'help',
    'Notification inheritance for hosts and services. Vertical for the legacy mode, Close for the first valid object '
    . 'and Cumulative for all valid object.'
);

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
 * OpenId Connect
 */
$help['openid_connect_enable'] = dgettext(
    'help',
    'Enable login with OpenId Connect.'
);
$help['openid_connect_mode'] = dgettext(
    'help',
    'Authentication can be solely based on OpenId Connect or it can work with both OpenId Connect and local authentication systems.'
);
$help['openid_connect_trusted_clients'] = dgettext(
    'help',
    'IP/DNS of trusted clients. Use coma as delimiter in case of multiple clients.'
);
$help['openid_connect_blacklist_clients'] = dgettext(
    'help',
    'IP/DNS of blacklist clients. Use coma as delimiter in case of multiple clients.'
);
$help['openid_connect_base_url'] = dgettext(
    'help',
    'Your OpenId Connect base Url (for example http://IP:8080/auth/realms/master/protocol/openid-connect).'
);
$help['openid_connect_authorization_endpoint'] = dgettext(
    'help',
    'Your OpenId Connect Authorization endpoint (for example /auth).'
);
$help['openid_connect_token_endpoint'] = dgettext(
    'help',
    'Your OpenId Connect Token endpoint (for example /token).'
);
$help['openid_connect_introspection_endpoint'] = dgettext(
    'help',
    'Your OpenId Connect Introspection Token endpoint (for example /token/introspect).'
);
$help['openid_connect_userinfo_endpoint'] = dgettext(
    'help',
    'Your OpenId Connect User Information endpoint (for example /userinfo).'
);
$help['openid_connect_end_session_endpoint'] = dgettext(
    'help',
    'Your OpenId Connect End Session endpoint (for example /logout).'
);
$help['openid_connect_scope'] = dgettext(
    'help',
    'Your OpenId Connect Scope (for example openid).'
);
$help['openid_connect_redirect_url'] = dgettext(
    'help',
    'Your OpenId Connect redirect url (this server, {$scheme}, {$hostname} and {$port} can be used for substitions).'
);
$help['openid_connect_client_id'] = dgettext(
    'help',
    'Your OpenId Connect client ID.'
);
$help['openid_connect_client_secret'] = dgettext(
    'help',
    'Your OpenId Connect client secret.'
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
