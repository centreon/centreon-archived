<?php
$help = array();

$help["name"] = dgettext("help", "Name used for identification of this meta service.");
$help["display"] = dgettext("help", "Optional format string used for displaying the status of this meta service. The variable '%d' may be used and will be replaced by the calculated value.");
$help["warning"] = dgettext("help", "Absolute value for warning level (low threshold).");
$help["critical"] = dgettext("help", "Absolute value for critical level (low threshold).");
$help["calcul_type"] = dgettext("help", "Function to be applied to calculate the meta service status.");
$help["data_source_type"] = dgettext("help", "Data source type of the meta service.");
$help["select_mode"] = dgettext("help", "Selection mode for services to be considered for this meta service. In service list mode, mark selected services in the options on meta service list. In SQL matching mode, specify a search string to be used in an SQL query.");
$help["regexp"] = dgettext("help", "Search string to be used in a SQL LIKE query for service selection.");
$help["metric"] = dgettext("help", "Select the metric to measure for meta service status.");

$help["check_period"] = dgettext("help", "Specify the time period during which the meta service status is measured.");
$help["max_check_attempts"] = dgettext("help", "Define the number of times that Centreon will retry the service check command if it returns any state other than an OK state. Setting this value to 1 will cause Centreon to generate an alert without retrying the service check again.");
$help["check_interval"] = dgettext("help", "Define the number of minutes between regularly scheduled checks of the meta service. \"Regular\" checks are those that occur when the service is in an OK state or when the service is in a non-OK state, but has already been rechecked max check attempts number of times.");
$help["retry_interval"] = dgettext("help", "Define the number of minutes to wait before scheduling a re-check for this service after a non-OK state was detected. Once the service has been retried max check attempts times without a change in its status, it will revert to being scheduled at its \"normal\" check interval rate.");

$help["notifications_enabled"] = dgettext("help", "Specify whether or not notifications for this meta service are enabled.");
$help["contact_groups"] = dgettext("help", "This is a list of contact groups that should be notified whenever there are problems (or recoveries) with this service.");
$help["notification_interval"] = dgettext("help", "Define the number of minutes to wait before re-notifying a contact that this service is still in a non-OK condition. A value of 0 disables re-notifications of contacts about problems for this service - only one problem notification will be sent out.");
$help["notification_period"] = dgettext("help", "Specify the time period during which notifications of events for this service can be sent out to contacts. If a state change occurs during a time which is not covered by the time period, no notifications will be sent out.");
$help["notification_options"] = dgettext("help", "Define the states of the service for which notifications should be sent out. If you do not specify any notification options, Centreon will assume that you want notifications to be sent out for all possible states.");

$help["graph_template"] = dgettext("help", "The optional definition of a graph template will be used as default graph template for this service.");
