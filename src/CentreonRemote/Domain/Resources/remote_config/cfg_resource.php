<?php

// To easily configure the monitoring, the user can create global resources (variables) for a Centreon Engine.

// Not clear in doc if we get IDs of existing resources here or if we add new ones?
//  - also do says we only need $USER1$ and $CENTREONPLUGINS$

// This is the default added during the installation of a new Centreon:
// *************************** 1. row ***************************
//     resource_name: $USER1$
//     resource_line: /usr/lib/nagios/plugins
//  resource_comment: path to the plugins
// resource_activate: 1
// *************************** 2. row ***************************
//     resource_name: $USER2$
//     resource_line: public
//  resource_comment: SNMP Community
// resource_activate: 1
// *************************** 3. row ***************************
//     resource_name: $CENTREONPLUGINS$
//     resource_line: /usr/lib/centreon/plugins
//  resource_comment: Centreon Plugin Path
// resource_activate: 1
