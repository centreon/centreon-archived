<?php

/*
 * Copyright 2005-2022 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

$nagios_comment = array();

$nagios_comment["log_file"] = "This is the main log file where service "
    . "and host events are logged for historical purposes.  "
    . "This should be the first option specified in the config file!!!";

$nagios_comment["cfg_file"] = " This is the configuration file in which you define "
    . "hosts, host groups, contacts, contact groups, services, etc.  "
    . "I guess it would be better called an object definition file, "
    . "but for historical reasons it isn\'t.  "
    . "You can split object definitions into several "
    . "different config files by using multiple cfg_file statements here. "
    . "Nagios will read and process all the config files you define. "
    . "This can be very useful if you want to keep command definitions "
    . "separate from host and contact definitions... "
    . "Plugin commands (service and host check commands) "
    . "Arguments are likely to change between different releases of the "
    . "plugins, so you should use the same config file provided "
    . "with the plugin release rather than the one provided with Nagios. ";

$nagios_comment["status_file"] = "This is where the current status of all monitored services and "
    . "hosts is stored.  Its contents are read and processed by the CGIs. "
    . "The contentsof the status file are deleted every time Nagios "
    . "restarts. ";

$nagios_comment["check_external_commands"] = "This option allows you to specify whether or not Nagios should check "
    . "for external commands (in the command file defined below).  By default "
    . "Nagios will *not* check for external commands, just to be on the "
    . "cautious side.  If you want to be able to use the CGI command interface "
    . "you will have to enable this.  Setting this value to 0 disables command "
    . "checking (the default), other values enable it. ";

$nagios_comment["command_check_interval"] = "This is the interval at which Nagios should check for external commands. "
    . "This value works of the interval_length you specify later.  If you leave "
    . "that at its default value of 60 (seconds), a value of 1 here will cause "
    . "Nagios to check for external commands every minute.  If you specify a "
    . "number followed by an &laquo;s&raquo; (i.e. 15s), this will be interpreted to mean "
    . "actual seconds rather than a multiple of the interval_length variable. "
    . "Note: In addition to reading the external command file at regularly  "
    . "scheduled intervals, Nagios will also check for external commands after "
    . "event handlers are executed. "
    . "NOTE: Setting this value to -1 causes Nagios to check the external "
    . "command file as often as possible. ";

$nagios_comment["command_file"] = "This is the file that Nagios checks for external command requests. "
    . "It is also where the command CGI will write commands that are submitted "
    . "by users, so it must be writeable by the user that the web server "
    . "is running as (usually &laquo;nobody&raquo;).  Permissions should be set at the  "
    . "directory level instead of on the file, as the file is deleted every "
    . "time its contents are processed. ";

$nagios_comment["use_syslog"] = "If you want messages logged to the syslog facility, as well as the "
    . "NetAlarm log file set this option to 1.  If not, set it to 0. ";

$nagios_comment["log_notifications"] = "If you don\'t want notifications to be logged, set this value to 0. "
    . "If notifications should be logged, set the value to 1. ";

$nagios_comment["log_service_retries"] = "If you don\'t want service check retries to be logged, set this value "
    . "to 0.  If retries should be logged, set the value to 1. ";

$nagios_comment["log_host_retries"] = "If you don\'t want host check retries to be logged, set this value to "
    . "0.  If retries should be logged, set the value to 1. ";

$nagios_comment["log_event_handlers"] = "If you don\'t want host and service event handlers to be logged, set "
    . "this value to 0.  If event handlers should be logged, set the value "
    . "to 1.";

$nagios_comment["log_external_commands"] = "If you don\'t want Nagios to log external commands, set this value "
    . "to 0.  If external commands should be logged, set this value to 1. "
    . "Note: This option does not include logging of passive service "
    . "checks - see the option below for controlling whether or not "
    . "passive checks are logged. ";

$nagios_comment["log_passive_service_checks"] = "If you don\'t want Nagios to log passive service checks, set this "
    . "value to 0.  If passive service checks should be logged, set this "
    . "value to 1. ";

$nagios_comment["inter_check"] = "This is the method that Nagios should use when initially "
    . "&laquo;spreading out&raquo; service checks when it starts monitoring.  The "
    . "default is to use smart delay calculation, which will try to "
    . "space all service checks out evenly to minimize CPU load. "
    . "Using the dumb setting will cause all checks to be scheduled "
    . "at the same time (with no delay between them)!  This is not a "
    . "good thing for production, but is useful when testing the "
    . "parallelization functionality. <br />"
    . "n	= None - don\'t use any delay between checks <br />"
    . "d	= Use a &laquo;dumb&raquo; delay of 1 second between checks <br />"
    . "s	= Use &laquo;smart&raquo; inter-check delay calculation <br />"
    . "   x.xx    = Use an inter-check delay of x.xx seconds ";

$nagios_comment["service_inter_check"] = "This option allows you to control "
    . "how service checks are initially &laquo;spread out&laquo; in the event queue.<br />"
    . "Using a &laquo;smart&laquo; delay calculation (the default) will cause Nagios "
    . "to calculate an average check interval and spread initial checks of all services out over that interval, "
    . "thereby helping to eliminate CPU load spikes.<br />"
    . "Using no delay is generally not recommended unless you are testing "
    . "the service check parallelization functionality.<br />"
    . "no delay will cause all service checks to be scheduled for execution at the same time.<br />"
    . "This means that you will generally have large CPU spikes when the services are all executed in parallel.<br />"
    . "Values are as follows :<br /> "
    . "n	= None - don\'t use any delay between checks to run immediately (i.e. at the same time!) <br />"
    . "d	= Use a &laquo;dumb&raquo; delay of 1 second between checks <br />"
    . "s	= Use &laquo;smart&raquo; inter-check delay calculation to spread service checks out evenly (default)<br />"
    . "   x.xx    = Use a user-supplied inter-check delay of x.xx seconds";

$nagios_comment["host_inter_check"] = "This option allows you to control "
    . "how host checks that are scheduled to be checked on a regular basis "
    . "are initially &laquo;spread out&laquo; in the event queue.<br />"
    . "Using a &laquo;smart&laquo; delay calculation (the default) "
    . "will cause Nagios to calculate an average check interval "
    . "and spread initial checks of all hosts out over that interval, "
    . "thereby helping to eliminate CPU load spikes.<br />"
    . "Using no delay is generally not recommended.<br />"
    . "Using no delay will cause all host checks to be scheduled for execution at the same time.<br />"
    . "Values are as follows :<br /> "
    . "n	= None - don\'t use any delay - schedule all host checks to run immediately (i.e. at the same time!) <br />"
    . "d	= Use a &laquo;dumb&raquo; delay of 1 second between host checks <br />"
    . "s	= Use &laquo;smart&raquo; delay calculation to spread host checks out evenly (default) <br />"
    . "   x.xx    = Use a user-supplied inter-check delay of x.xx seconds";

$nagios_comment["service_interleave_factor"] = "This variable determines how service checks are interleaved. "
    . "Interleaving the service checks allows for a more even "
    . "distribution of service checks and reduced load on remote"
    . "hosts.  Setting this value to 1 is equivalent to how versions "
    . "of Nagios previous to 0.0.5 did service checks.  Set this "
    . "value to s (smart) for automatic calculation of the interleave "
    . "factor unless you have a specific reason to change it.<br /> "
    . "      s       = Use &laquo;smart&raquo; interleave factor calculation<br /> "
    . "      x       = Use an interleave factor of x, where x is a <br />"
    . "               number greater than or equal to 1. ";

$nagios_comment["max_concurrent_checks"] = "This option allows you to specify the maximum number of  "
    . "service checks that can be run in parallel at any given time. "
    . "Specifying a value of 1 for this variable essentially prevents "
    . "any service checks from being parallelized.  A value of 0 "
    . "will not restrict the number of concurrent checks that are "
    . "being executed. ";

$nagios_comment["max_service_check_spread"] = "This option determines the maximum number of minutes "
    . "from when Nagios starts that all services "
    . "(that are scheduled to be regularly checked) are checked.<br />"
    . "This option will automatically adjust the service inter-check delay (if necessary) "
    . "to ensure that the initial checks of all services occur within the timeframe you specify.<br />"
    . "In general, this option will not have an effect on service check "
    . "scheduling if scheduling information is being retained using the use_retained_scheduling_info option.<br />"
    . "Default value is 30 (minutes). ";

$nagios_comment["max_host_check_spread"] = "This option determines the maximum number of minutes "
    . "from when Nagios starts that all hosts "
    . "(that are scheduled to be regularly checked) are checked.<br />"
    . "This option will automatically adjust the host inter-check delay (if necessary) "
    . "to ensure that the initial checks of all hosts occur within the timeframe you specify.<br />"
    . "In general, this option will not have an effect on host check scheduling "
    . "if scheduling information is being retained using the use_retained_scheduling_info option.<br />"
    . "Default value is 30 (minutes). ";

$nagios_comment["check_result_reaper_frequency"] = "This is the frequency (in seconds!) that Nagios will process "
    . "the results of services that have been checked. ";

$nagios_comment["sleep_time"] = "This is the number of seconds to sleep between checking for system "
    . "events and service checks that need to be run.  I would recommend "
    . "*not* changing this from its default value of 1 second. ";

$nagios_comment["timeout"] = "These options control how much time Nagios will allow various "
    . "types of commands to execute before killing them off.  Options "
    . "are available for controlling maximum time allotted for "
    . "service checks, host checks, event handlers, notifications, the "
    . "ocsp command, and performance data commands.  All values are in "
    . "seconds. ";

$nagios_comment["retain_state_information"] = "This setting determines whether or not Nagios will save state "
    . "information for services and hosts before it shuts down.  Upon "
    . "startup Nagios will reload all saved service and host state "
    . "information before starting to monitor.  This is useful for  "
    . "maintaining long-term data on state statistics, etc, but will "
    . "slow Nagios down a bit when it re starts.  Since its only "
    . "a one-time penalty, I think its well worth the additional "
    . "startup delay. ";

$nagios_comment["state_retention_file"] = "This is the file that Nagios should use to store host and "
    . "service state information before it shuts down. The state  "
    . "information in this file is also read immediately prior to "
    . "starting to monitor the network when Nagios is restarted. "
    . "This file is used only if the preserve_state_information "
    . "variable is set to 1. ";

$nagios_comment["retention_update_interval"] = "This setting determines how often (in minutes) that Nagios "
    . "will automatically save retention data during normal operation. "
    . "If you set this value to 0, Nagios will not save retention "
    . "data at regular interval, but it will still save retention "
    . "data before shutting down or restarting.  If you have disabled "
    . "state retention, this option has no effect. ";

$nagios_comment["use_retained_program_state"] = "This setting determines whether or not Nagios will set  "
    . "program status variables based on the values saved in the "
    . "retention file. If you want to use retained program status "
    . "information, set this value to 1.  If not, set this value "
    . "to 0. ";

$nagios_comment["use_retained_scheduling_info"] = "This setting determines whether or not "
    . "Nagios will retain scheduling info (next check times) for hosts and services when it restarts.<br />"
    . "If you are adding a large number (or percentage) of hosts and services, "
    . "I would recommend disabling this option when you first restart Nagios, "
    . "as it can adversely skew the spread of initial checks.<br />"
    . "Otherwise you will probably want to leave it enabled.";

$nagios_comment["execute_service_checks"] = "This determines whether or not Nagios will actively execute "
    . "service checks when it initially starts.  If this option is  "
    . "disabled, checks are not actively made, but Nagios can still "
    . "receive and process passive check results that come in.  Unless "
    . "you\'re implementing redundant hosts or have a special need for "
    . "disabling the execution of service checks, leave this enabled! "
    . "Values: 1 = enable checks, 0 = disable checks ";

$nagios_comment["accept_passive_service_checks"] = "This determines whether or not Nagios will accept passive "
    . "service checks results when it initially (re)starts. "
    . "Values: 1 = accept passive checks, 0 = reject passive checks ";

$nagios_comment["log_passive_checks"] = "This variable determines whether or not "
    . "Nagios will log passive host and service checks that "
    . "it receives from the external command file.<br />"
    . "If you are setting up a distributed monitoring environment "
    . "or plan on handling a large number of passive checks on a regular basis, "
    . "you may wish to disable this option so your log file doesn\'t get too large.";

$nagios_comment["execute_host_checks"] = "This option determines whether or not "
    . "Nagios will execute on-demand and regularly scheduled host checks when it initially (re)starts. "
    . "If this option is disabled, Nagios will not actively execute any host checks, "
    . "although it can still accept passive host checks unless you\'ve disabled them).<br />"
    . "This option is most often used when configuring backup monitoring servers, "
    . "as described in the documentation on redundancy, or when setting up a distributed monitoring environment.";

$nagios_comment["accept_passive_host_checks"] = "This option determines whether or not Nagios will accept "
    . "passive host checks when it initially (re)starts.<br />"
    . "If this option is disabled, Nagios will not accept any passive host checks.";

$nagios_comment["enable_notifications"] = "This determines whether or not Nagios will sent out any host or "
    . "service notifications when it is initially (re)started. "
    . "Values: 1 = enable notifications, 0 = disable notifications ";

$nagios_comment["enable_event_handlers"] = "This determines whether or not Nagios will run any host or "
    . "service event handlers when it is initially (re)started.  Unless "
    . "you\'re implementing redundant hosts, leave this option enabled. "
    . "Values: 1 = enable event handlers, 0 = disable event handlers ";

$nagios_comment["check_for_orphaned_services"] = "This determines whether or not Nagios will periodically  "
    . "check for orphaned services.  Since service checks are not "
    . "rescheduled until the results of their previous execution  "
    . "instance are processed, there exists a possibility that some "
    . "checks may never get rescheduled.  This seems to be a rare "
    . "problem and should not happen under normal circumstances. "
    . "If you have problems with service checks never getting "
    . "rescheduled, you might want to try enabling this option. "
    . "Values: 1 = enable checks, 0 = disable checks ";

$nagios_comment["check_service_freshness"] = "This option determines whether or not Nagios will periodically "
    . "check the freshness of service results.  Enabling this option "
    . "is useful for ensuring passive checks are received in a timely "
    . "manner. "
    . "Values: 1 = enabled freshness checking, 0 = disable freshness checking ";

$nagios_comment["service_freshness_check_interval"] = "This setting determines how often (in seconds) "
    . "Nagios will periodically check the &laquo;freshness&laquo; of service check results.<br />"
    . "If you have disabled service freshness checking (with the check_service_freshness option), "
    . "this option has no effect.";

$nagios_comment["check_host_freshness"] = "This option determines whether or not Nagios "
    . "will periodically check the &laquo;freshness&laquo; of host checks.<br />"
    . "Enabling this option is useful for helping to ensure that passive host checks are received in a timely manner.";

$nagios_comment["host_freshness_check_interval"] = "This setting determines how often (in seconds) "
    . "Nagios will periodically check the &laquo;freshness&laquo; of host check results.<br />"
    . "If you have disabled host freshness checking (with the check_host_freshness option), this option has no effect.";

$nagios_comment["freshness_check_interval"] = "This setting determines how often (in seconds) Nagios will "
    . "check the freshness of service check results.  If you have "
    . "disabled service freshness checking, this option has no effect. ";

$nagios_comment["status_update_interval"] = "Combined with the aggregate_status_updates option, "
    . "this option determines the frequency (in seconds!) that "
    . "Nagios will periodically dump program, host, and  "
    . "service status data.  If you are not using aggregated "
    . "status data updates, this option has no effect. ";

$nagios_comment["enable_flap_detection"] = "This option determines whether or not Nagios will try "
    . "and detect hosts and services that are flapping.   "
    . "Flapping occurs when a host or service changes between "
    . "states too frequently.  When Nagios detects that a  "
    . "host or service is flapping, it will temporarily supress "
    . "notifications for that host/service until it stops "
    . "flapping.  Flap detection is very experimental, so read "
    . "the HTML documentation before enabling this feature! "
    . "Values: 1 = enable flap detection "
    . "        0 = disable flap detection (default) ";

$nagios_comment["flap_threshold"] = "Read the HTML documentation on flap detection for "
    . "an explanation of what this option does.  This option "
    . "has no effect if flap detection is disabled. ";

$nagios_comment["date_format"] = "This option determines how short dates are displayed. Valid options "
    . "include:<br /> "
    . "us               (MM-DD-YYYY HH:MM:SS) <br />"
    . "euro             (DD-MM-YYYY HH:MM:SS) <br />"
    . "iso8601          (YYYY-MM-DD HH:MM:SS) <br />"
    . "strict-iso8601   (YYYY-MM-DDTHH:MM:SS) ";

$nagios_comment["illegal_object_name_chars"] = "This options allows you "
    . "to specify illegal characters that cannot "
    . "be used in host names, service descriptions, or names of other "
    . "object types. ";

$nagios_comment["use_regexp_matching"] = "If you\'ve enabled regular expression matching of various object directives "
    . "using the use_regexp_matching option, this option will determine "
    . "when object directives are treated as regular expressions.<br />"
    . "If this option is disabled (the default), directives will only be treated "
    . "as regular expressions if the contain a * or ? wildcard character.<br />"
    . "If this option is enabled, all appropriate directives will be treated "
    . "as regular expression - be careful when enabling this!<br />"
    . "0 = Don\'t use true regular expression matching "
    . "(default)<br />1 = Use true regular expression matching ";

$nagios_comment["use_true_regexp_matching"] = "If you\'ve enabled regular expression matching "
    . "of various object directives using the use_regexp_matching option, "
    . "this option will determine when object directives are treated as regular expressions.<br />"
    . "If this option is disabled (the default), directives will only be treated "
    . "as regular expressions if the contain a * or ? wildcard character..<br />"
    . "If this option is enabled, all appropriate directives will be treated "
    . "as regular expression - be careful when enabling this!<br />"
    . "0 = Don\'t use regular expression matching (default)<br />1 = Use regular expression matching ";

$nagios_comment["illegal_macro_output_chars"] = "This options allows you to specify illegal characters that are "
    . "stripped from macros before being used in notifications, event "
    . "handlers, etc.  This DOES NOT affect macros used in service or "
    . "host check commands. "
    . "The following macros are stripped of the characters you specify: "
    . "	\$OUTPUT\$, \$PERFDATA\$ ";

$nagios_comment["admin_email"] = "The email address of the administrator of *this* machine (the one "
    . "doing the monitoring).  Nagios never uses this value itself, but "
    . "you can access this value by using the \$ADMINEMAIL\$ macro in your "
    . "notification commands. ";

$nagios_comment["admin_pager"] = "The pager number/address for the administrator of *this* machine. "
    . "Nagios never uses this value itself, but you can access this "
    . "value by using the \$ADMINPAGER\$ macro in your notification "
    . "commands. ";

$nagios_comment["auto_reschedule_checks"] = "This option determines whether or not Nagios will attempt "
    . "to automatically reschedule active host and service checks to  &laquo;smooth&laquo; them out over time.<br />"
    . "This can help to balance the load on the monitoring server, as it will attempt "
    . "to keep the time between consecutive checks consistent, at the expense of executing checks "
    . "on a more rigid schedule.";

$nagios_comment["auto_rescheduling_interval"] = "This option determines how often (in seconds) "
    . "Nagios will attempt to automatically reschedule checks.<br />"
    . "This option only has an effect if the auto_reschedule_checks option is enabled.<br />"
    . "Default is 30 seconds.";

$nagios_comment["auto_rescheduling_window"] = "This option determines the &laquo;window&laquo; of time (in seconds) "
    . "that Nagios will look at when automatically rescheduling checks.<br />"
    . "Only host and service checks that occur in the next X seconds "
    . "(determined by this variable) will be rescheduled.<br />"
    . "This option only has an effect if the auto_reschedule_checks option is enabled.<br />"
    . "Default is 180 seconds (3 minutes).";
