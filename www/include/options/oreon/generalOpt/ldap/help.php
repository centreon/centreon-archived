<?php
$help = array();

/*
 * LDAP Informations
 */
$help['ldap_auth_enable'] = dgettext('help', 'Enable LDAP authentification');
$help['ldap_store_password'] = dgettext('help', 'Whether or not the password should be stored in database');
$help['ldap_auto_import'] = dgettext('help', 'Can connect with LDAP without import');
$help['ldap_contact_tmpl'] = dgettext('help', 'The contact template for auto imported user.<br/>This template is applied for Monitoring Engine contact configuration and ACLs');
$help['ldap_srv_dns'] = dgettext('help', 'Use the DNS service for get LDAP host');
$help['ldap_srv_dns_ssl'] = dgettext('help', 'Enable SSL connection');
$help['ldap_srv_dns_tls'] = dgettext('help', 'Enable TLS connection');
$help['ldap_dns_use_domain'] = dgettext('help', 'Set the domain for search the service');
$help['ldap_search_limit'] = dgettext('help', 'Search size limit');
$help['ldap_search_timeout'] = dgettext('help', 'Search timeout');

/*
 * LDAP configuration
 */
$longString =
'<b>' ._('Bind user'). '</b><br/>' . _('User DN for connect to LDAP in read only') . '<br/><br/>' .
'<b>' ._('Bind password'). '</b><br/>' . _('Password for connect to LDAP in read only') . '<br/><br/>' .
'<b>' ._('Protocol version'). '</b><br/>' . _('The version protocol for connect to LDAP<br/>Use version 3 for Active Directory') . '<br/><br/>' .
'<b>' ._('Search user base DN'). '</b><br/>' . _('The base DN for search users') . '<br/><br/>' .
'<b>' ._('Search group base DN'). '</b><br/>' . _('The base DN for search groups') . '<br/><br/>' .
'<b>' ._('User filter'). '</b><br/>' . _('The LDAP search filter for users<br/>Use %s in filter. The %s will replaced by login in autologin or * in LDAP import') . '<br/><br/>' .
'<b>' ._('Group filter'). '</b><br/>' . _('The LDAP search filter for groups<br/>Use %s in filter. The %s will replaced by group name in autologin or * in contactgroup field') . '<br/><br/>' .
'<b>' ._('Login attribute'). '</b><br/>' . _('The login attribute<br/>In Centreon : Alias / Login') . '<br/><br/>' .
'<b>' ._('User group attribute'). '</b><br/>' . _('The group attribute for user') . '<br/><br/>' .
'<b>' ._('User displayname attribute'). '</b><br/>' . _('The user name<br/>In Centreon : Full Name') . '<br/><br/>' .
'<b>' ._('User firstname attribute'). '</b><br/>' . _('The user firstname<br/>In Centreon : givenname') . '<br/><br/>' .
'<b>' ._('User lastname attribute'). '</b><br/>' . _('The user lastname<br/>In Centreon : sn') . '<br/><br/>' .
'<b>' ._('User email attribute'). '</b><br/>' . _('The user email<br/>In Centreon : Email') . '<br/><br/>' .
'<b>' ._('User pager attribute'). '</b><br/>' . _('The user pager<br/>In Centreon : Pager') . '<br/><br/>' .
'<b>' ._('Group attribute'). '</b><br/>' . _('The group name<br/>In Centreon : Contact Group Name') . '<br/><br/>' .
'<b>' ._('Group member attribute'). '</b><br/>' . _('The LDAP attribute for relation between group and user');

$help['ldapConf'] = dgettext('help', $longString);
/*
$help['addNewHost'] = dgettext('help', $longString);
$help['ldap_binduser'] = dgettext('help', 'User DN for connect to LDAP in read only');
$help['ldap_bindpass'] = dgettext('help', 'Password for connect to LDAP in read only');
$help['ldap_version_protocol'] = dgettext('help', 'The version protocol for connect to LDAP<br/>Use version 3 for Active Directory');
$help['ldap_template'] = dgettext('help', 'Template for LDAP attribute');
$help['ldap_user_basedn'] = dgettext('help', 'The base DN for search users');
$help['ldap_group_basedn'] = dgettext('help', 'The base DN for search groups');
$help['ldap_user_filter'] = dgettext('help', 'The LDAP search filter for users<br/>Use %s in filter. The %s will replaced by login in autologin or * in LDAP import');
$help['ldap_group_filter'] = dgettext('help', 'The LDAP search filter for groups<br/>Use %s in filter. The %s will replaced by group name in autologin or * in contactgroup field');
$help['ldap_user_uid_attr'] = dgettext('help', 'The login attribute<br/>In Centreon : Alias / Login');
$help['ldap_user_group'] = dgettext('help', 'The group attribute for user');
$help['ldap_user_name'] = dgettext('help', 'The user name<br/>In Centreon : Full Name');
$help['ldap_user_firstname'] = dgettext('help', 'The user firstname<br/>In Centreon : givenname');
$help['ldap_user_lastname'] = dgettext('help', 'The user lastname<br/>In Centreon : sn');
$help['ldap_user_email'] = dgettext('help', 'The user email<br/>In Centreon : Email');
$help['ldap_user_pager'] = dgettext('help', 'The user pager<br/>In Centreon : Pager');
$help['ldap_group_gid_attr'] = dgettext('help', 'The group name<br/>In Centreon : Contact Group Name');
$help['ldap_group_member'] = dgettext('help', 'The LDAP attribute for relation between group and user');
*/