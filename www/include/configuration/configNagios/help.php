<?php
/*
 * Copyright 2005-2015 Centreon
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

$help = array();
$help["use_timezone"] = dgettext(
    "help",
    "Define the poller timezone. If not set, default Centreon timezone is used (parameters). " .
    "This timezone is used for hosts which have not configured timezone."
);
$help["status_file"] = dgettext(
    "help",
    "This is the file that Monitoring Engine uses to store the current status, "
    . "comment, and downtime information. "
    . "This file is deleted every time Monitoring Engine stops and recreated when it starts."
);
$help["status_update_interval"] = dgettext(
    "help",
    "Combined with the aggregate_status_updates option, this option determines "
    . "the frequency (in seconds!) that Nagios will periodically dump program, "
    . "host, and service status data.  If you are not using aggregated status data updates, "
    . "this option has no effect. The minimum update interval is 2 seconds."
);
$help["log_file"] = dgettext(
    "help",
    "Location (path and filename) where Monitoring Engine should create its main log file."
);
$help["cfg_dir"] = dgettext(
    "help",
    "Directory where Centreon will export Monitoring Engine object configuration files to. "
    . "Monitoring Engine will parse all .cfg files in this directory."
);
$help["temp_file"] = dgettext(
    "help",
    "This is a temporary file that Monitoring Engine periodically creates and "
    . "uses when updating comment data, status data, etc. "
    . "The file is deleted when it is no longer needed."
);
$help["enable_notifications"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will send out notifications "
    . "when it initially (re)starts. If this option is disabled, Monitoring Engine "
    . "will not send out notifications for any host or service. Note: If you have "
    . "state retention enabled, Monitoring Engine will ignore this setting when "
    . "it (re)starts and use the last known setting for this option (as stored in the state "
    . "retention file), unless you disable the use_retained_program_state option. "
    . "If you want to change this option when state retention is active "
    . "(and the use_retained_program_state is enabled), you'll have to use the appropriate "
    . "external command or change it via the web interface. Notifications are enabled by default."
);
$help["execute_service_checks"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will execute service checks "
    . "when it initially (re)starts. If this option is disabled, Monitoring Engine "
    . "will not actively execute any service checks and will remain in a sort of \"sleep\" mode "
    . "(it can still accept passive checks unless you've disabled them). This option is most "
    . "often used when configuring backup monitoring servers, as described in the documentation "
    . "on redundancy, or when setting up a distributed monitoring environment. Note: "
    . "If you have state retention enabled, Monitoring Engine will ignore this setting "
    . "when it (re)starts and use the last known setting for this option (as stored in the state "
    . "retention file), unless you disable the use_retained_program_state option. If you want "
    . "to change this option when state retention is active "
    . "(and the use_retained_program_state is enabled), you'll have to use the appropriate "
    . "external command or change it via the web interface. Service checks are enabled by default."
);
$help["execute_host_checks"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will execute host checks "
    . "when it initially (re)starts. If this option is disabled, Monitoring Engine "
    . "will not actively execute any host checks and will remain in a sort of \"sleep\" mode "
    . "(it can still accept passive checks unless you've disabled them). This option is most "
    . "often used when configuring backup monitoring servers, as described in the documentation "
    . "on redundancy, or when setting up a distributed monitoring environment. Note: If you have "
    . "state retention enabled, Monitoring Engine will ignore this setting when it (re)starts "
    . "and use the last known setting for this option (as stored in the state retention file), "
    . "unless you disable the use_retained_program_state option. If you want to change "
    . "this option when state retention is active (and the use_retained_program_state is enabled), "
    . "you'll have to use the appropriate external command or change it via the web interface. "
    . "Host checks are enabled by default."
);
$help["accept_passive_service_checks"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will accept passive service checks "
    . "when it initially (re)starts. If this option is disabled, Monitoring Engine will not "
    . "accept any passive service checks. Note: If you have state retention enabled, "
    . "Monitoring Engine will ignore this setting when it (re)starts and use the last known "
    . "setting for this option (as stored in the state retention file), unless you disable "
    . "the use_retained_program_state option. If you want to change this option when state "
    . "retention is active (and the use_retained_program_state is enabled), you'll have to "
    . "use the appropriate external command or change it via the web interface. "
    . "Option is enabled by default."
);
$help["accept_passive_host_checks"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will accept passive host checks "
    . "when it initially (re)starts. If this option is disabled, Monitoring Engine will not "
    . "accept any passive host checks. Note: If you have state retention enabled, "
    . "Monitoring Engine will ignore this setting when it (re)starts and use the last known "
    . "setting for this option (as stored in the state retention file), unless you disable the "
    . "use_retained_program_state option. If you want to change this option when state retention "
    . "is active (and the use_retained_program_state is enabled), you'll have to use the "
    . "appropriate external command or change it via the web interface. Option is enabled by default."
);
$help["enable_event_handlers"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will run event handlers when it "
    . "initially (re)starts. If this option is disabled, Monitoring Engine will not run any host "
    . "or service event handlers. Note: If you have state retention enabled, Monitoring Engine "
    . "will ignore this setting when it (re)starts and use the last known setting for this option "
    . "(as stored in the state retention file), unless you disable the use_retained_program_state "
    . "option. If you want to change this option when state retention is active (and the "
    . "use_retained_program_state is enabled), you'll have to use the appropriate external command "
    . "or change it via the web interface. Option is enabled by default."
);
$help["log_archive_path"] = dgettext(
    "help",
    "This is the directory where Monitoring Engine should place log files that have been rotated. "
    . "This option is ignored if you choose to not use the log rotation functionality."
);
$help["check_external_commands"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will check the command file "
    . "for commands that should be executed. This option must be enabled if you plan "
    . "on using Centreon to issue commands via the web interface."
);
$help["command_check_interval"] = dgettext(
    "help",
    "If you specify a number with an \"s\" appended to it (i.e. 30s), this is the number "
    . "of seconds to wait between external command checks. If you leave off the \"s\", "
    . "this is the number of \"time units\" to wait between external command checks. "
    . "Unless you've changed the interval_length value (as defined below) from the default "
    . "value of 60, this number will mean minutes. By setting this value to -1, Monitoring Engine "
    . "will check for external commands as often as possible. Each time Monitoring Engine checks "
    . "for external commands it will read and process all commands present in the command file "
    . "before continuing on with its other duties."
);
$help["command_file"] = dgettext(
    "help",
    "This is the file that Monitoring Engine will check for external commands to process. "
    . "Centreon writes commands to this file. The external command file is implemented "
    . "as a named pipe (FIFO), which is created when Monitoring Engine starts and removed "
    . "when it shuts down. If the file exists when Monitoring Engine starts, the Monitoring Engine "
    . "process will terminate with an error message."
);
$help["external_command_buffer_slots"] = dgettext(
    "help",
    "This is an advanced feature. This option determines how many buffer slots Monitoring Engine "
    . "will reserve for caching external commands that have been read from the external command file "
    . "by a worker thread, but have not yet been processed by the main thread of the Monitoring Engine "
    . "daemon. This option essentially determines how many commands can be buffered. For installations "
    . "where you process a large number of passive checks (e.g. distributed setups), you may need to "
    . "increase this number. You should consider using MRTG to graph Monitoring Engine' usage of "
    . "external command buffers."
);
$help["retain_state_information"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will retain state information for hosts "
    . "and services between program restarts. If you enable this option, you should supply a value "
    . "for the state_retention_file variable. When enabled, Monitoring Engine will save all state "
    . "information for hosts and service before it shuts down (or restarts) and will read in "
    . "previously saved state information when it starts up again. Option is enabled by default."
);
$help["state_retention_file"] = dgettext(
    "help",
    "This is the file that Monitoring Engine will use for storing status, downtime, and comment "
    . "information before it shuts down. When Monitoring Engine is restarted it will use the "
    . "information stored in this file for setting the initial states of services and hosts before "
    . "it starts monitoring anything. In order to make Monitoring Engine retain state information "
    . "between program restarts, you must enable the retain_state_information option."
);
$help["retention_update_interval"] = dgettext(
    "help",
    "This setting determines how often (in minutes) that Monitoring Engine will automatically "
    . "save retention data during normal operation. If you set this value to 0, Monitoring Engine "
    . "will not save retention data at regular intervals, but it will still save retention data "
    . "before shutting down or restarting. If you have disabled state retention (with the "
    . "retain_state_information option), this option has no effect."
);
$help["use_retained_program_state"] = dgettext(
    "help",
    "This setting determines whether or not Monitoring Engine will set various program-wide state "
    . "variables based on the values saved in the retention file. Some of these program-wide state "
    . "variables that are normally saved across program restarts if state retention is enabled "
    . "include the enable_notifications, enable_flap_detection, enable_event_handlers, "
    . "execute_service_checks, and accept_passive_service_checks options. If you do not have state "
    . "retention enabled, this option has no effect. This option is enabled by default."
);
$help["use_retained_scheduling_info"] = dgettext(
    "help",
    "This setting determines whether or not Monitoring Engine will retain scheduling info "
    . "(next check times) for hosts and services when it restarts. If you are adding a large number "
    . "(or percentage) of hosts and services, I would recommend disabling this option "
    . "when you first restart Monitoring Engine, as it can adversely skew the spread of "
    . "initial checks. Otherwise you will probably want to leave it enabled."
);


$help["use_syslog"] = dgettext(
    "help",
    "This option determines whether messages are logged to the syslog facility "
    . "on your local host."
);
$help["log_notifications"] = dgettext(
    "help",
    "This option determines whether or not notification messages are logged. "
    . "If you have a lot of contacts or regular service failures your log file will "
    . "grow relatively quickly. Use this option to keep contact notifications "
    . "from being logged."
);
$help["log_service_retries"] = dgettext(
    "help",
    "This option determines whether or not service check retries are logged. "
    . "Service check retries occur when a service check results in a non-OK state, "
    . "but you have configured Monitoring Engine to retry the service more than once "
    . "before responding to the error. Services in this situation are considered "
    . "to be in \"soft\" states. Logging service check retries is mostly useful "
    . "when attempting to debug Monitoring Engine or test out service event handlers."
);
$help["log_host_retries"] = dgettext(
    "help",
    "This option determines whether or not host check retries are logged. Logging host "
    . "check retries is mostly useful when attempting to debug Monitoring Engine "
    . "or test out host event handlers."
);
$help["log_event_handlers"] = dgettext(
    "help",
    "This option determines whether or not service and host event handlers are logged. "
    . "Event handlers are optional commands that can be run whenever a service or hosts "
    . "changes state. Logging event handlers is most useful when debugging Monitoring "
    . "Engine or first trying out your event handler scripts."
);
$help["log_external_commands"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will log external "
    . "commands that it receives from the external command file. This option is "
    . "enabled by default."
);
$help["log_passive_checks"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will log passive host "
    . "and service checks that it receives from the external command file. "
    . "This option is enabled by default. If you are setting up a distributed "
    . "monitoring environment or plan on handling a large number of passive checks "
    . "on a regular basis, you may wish to disable this option so your log file "
    . "doesn't get too large."
);
$help["global_host_event_handler"] = dgettext(
    "help",
    "This option allows you to specify a host event handler command that is to be "
    . "run for every host state change. The global event handler is executed "
    . "immediately prior to the event handler that you have optionally specified "
    . "in each host definition. The maximum amount of time that this command can "
    . "run is controlled by the event_handler_timeout option."
);
$help["global_service_event_handler"] = dgettext(
    "help",
    "This option allows you to specify a service event handler command that is to "
    . "be run for every service state change. The global event handler is executed "
    . "immediately prior to the event handler that you have optionally specified "
    . "in each service definition. The maximum amount of time that this command "
    . "can run is controlled by the event_handler_timeout option."
);
$help["sleep_time"] = dgettext(
    "help",
    "This is the number of seconds that Monitoring Engine will sleep before "
    . "checking to see if the next service or host check in the scheduling queue "
    . "should be executed. Note that Monitoring Engine will only sleep after "
    . "it \"catches up\" with queued service checks that have fallen behind."
);
$help["max_concurrent_checks"] = dgettext(
    "help",
    "This option allows you to specify the maximum number of service checks that "
    . "can be run in parallel at any given time. Specifying a value of 1 for "
    . "this variable essentially prevents any service checks from being run in parallel. "
    . "Specifying a value of 0 (the default) does not place any restrictions on "
    . "the number of concurrent checks. You'll have to modify this value based on "
    . "the system resources you have available on the machine that runs Monitoring Engine, "
    . "as it directly affects the maximum load that will be imposed on the "
    . "system (processor utilization, memory, etc.)."
);
$help["max_host_check_spread"] = dgettext(
    "help",
    "This option determines the maximum number of minutes from when Monitoring Engine "
    . "starts that all hosts (that are scheduled to be regularly checked) are checked. "
    . "This option will automatically adjust the host inter-check delay method "
    . "(if necessary) to ensure that the initial checks of all hosts occur within "
    . "the timeframe you specify. In general, this option will not have an effect "
    . "on host check scheduling if scheduling information is being retained using "
    . "the use_retained_scheduling_info option. Default value is 30 (minutes)."
);
$help["max_service_check_spread"] = dgettext(
    "help",
    "This option determines the maximum number of minutes from when Monitoring Engine starts until all services "
    . "(that are scheduled to be regularly checked) are checked. This option will automatically adjust the service "
    . "inter-check delay method (if necessary) to ensure that the initial checks of all services occur within the "
    . "timeframe you specify. In general, this option will not have an effect on service check scheduling if "
    . "scheduling information is being retained using the use_retained_scheduling_info option. Default value in "
    . "centengine is 5 (minutes) but it should be raised to 30 if the poller monitors more than 5000 services to "
    . "avoid load issues."
);
$help["service_interleave_factor"] = dgettext(
    "help",
    "This option determines how service checks are interleaved. Interleaving allows for a more even " .
    "distribution of service checks, reduced load on remote hosts, and faster overall detection of " .
    "host problems. By default this value is set to s (smart) for automatic calculation of the interleave factor. " .
    "Don't change unless you have a specific reason to change it. Setting this value to a number greater " .
    "than or equal to 1 specifies the interleave factor to use. A value of 1 is equivalent to not interleaving " .
    "the service checks."
);
$help["service_inter_check_delay_method"] = dgettext(
    "help",
    "This option allows you to control how service checks are initially \"spread out\" in the event queue. " .
    "Enter \"s\" for using a \"smart\" delay calculation (the default), which will cause " .
    "Monitoring Engine to calculate an average check interval and spread initial checks of all " .
    "services out over that interval, thereby helping to eliminate CPU load spikes. Using no " .
    "delay (\"n\") is generally not recommended, as it will cause all service checks to be scheduled " .
    "for execution at the same time. This means that you will generally have large CPU spikes when the " .
    "services are all executed in parallel. Use a \"d\" for a \"dumb\" delay of 1 second between service " .
    "checks or supply a fixed value of x.xx seconds for the inter-check delay."
);
$help["host_inter_check_delay_method"] = dgettext(
    "help",
    "This option allows you to control how host checks are initially \"spread out\" in the event queue. " .
    "Enter \"s\" for using a \"smart\" delay calculation (the default), which will cause Monitoring Engine " .
    "to calculate an average check interval and spread initial checks of all hosts out over that interval, " .
    "thereby helping to eliminate CPU load spikes. Using no delay (\"n\") is generally not recommended, " .
    "as it will cause all host checks to be scheduled for execution at the same time. This means that you will " .
    "generally have large CPU spikes when the hosts are all executed in parallel. " .
    "Use a \"d\" for a \"dumb\" delay of 1 second between host checks or supply a fixed value of x.xx seconds " .
    "for the inter-check delay."
);
$help["check_result_reaper_frequency"] = dgettext(
    "help",
    "This option allows you to control the frequency in seconds of check result \"reaper\" events. " .
    "\"Reaper\" events process the results from host and service checks that have finished executing. These events " .
    "constitute the core of the monitoring logic in Monitoring Engine."
);
$help["translate_passive_host_checks"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will translate DOWN/UNREACHABLE passive host " .
    "check results to their \"correct\" state from the viewpoint of the local Monitoring Engine instance. " .
    "This can be very useful in distributed and failover monitoring installations. Option is disabled by default."
);
$help["passive_host_checks_are_soft"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will treat passive host checks as HARD states or " .
    "SOFT states. By default, a passive host check result will put a host into a HARD state type. This option is " .
    "disabled by default."
);
$help["auto_reschedule_checks"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will attempt to automatically reschedule active " .
    "host and service checks to \"smooth\" them out over time. This can help to balance the load on " .
    "the monitoring server, as it will attempt to keep the time between consecutive checks consistent, " .
    "at the expense of executing checks on a more rigid schedule.<br><b>Warning:</b> this is an experimental " .
    "feature and may be removed in future versions. Enabling this option can degrade performance - rather than " .
    "increase it - if used improperly!"
);
$help["auto_rescheduling_interval"] = dgettext(
    "help",
    "This option determines how often Monitoring Engine will attempt to automatically reschedule checks. " .
    "This option only has an effect if the auto_reschedule_checks option is enabled. Default is 30 seconds."
);
$help["auto_rescheduling_window"] = dgettext(
    "help",
    "This option determines the \"window\" of time that Monitoring Engine will look at when automatically " .
    "rescheduling checks. Only host and service checks that occur in the next X seconds " .
    "(determined by this variable) will be rescheduled. This option only has an effect if the " .
    "auto_reschedule_checks option is enabled. Default is 180 seconds (3 minutes)."
);
$help["enable_flap_detection"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will try and detect hosts and services " .
    "that are \"flapping\". Flapping occurs when a host or service changes between states too frequently, " .
    "resulting in a barrage of notifications being sent out. When Monitoring Engine detects that a host " .
    "or service is flapping, it will temporarily suppress notifications for that host/service until it stops " .
    "flapping. Flap detection is very experimental at this point, so use this feature with caution! " .
    "More information on how flap detection and handling works can be found here. Note: If you have " .
    "state retention enabled, Monitoring Engine will ignore this setting when it (re)starts and use the " .
    "last known setting for this option (as stored in the state retention file), unless you disable the " .
    "use_retained_program_state option. This option is disabled by default."
);
$help["low_service_flap_threshold"] = dgettext(
    "help",
    "This option is used to set the low threshold for detection of service flapping. For more information " .
    "read the Monitoring Engine section about flapping."
);
$help["high_service_flap_threshold"] = dgettext(
    "help",
    "This option is used to set the high threshold for detection of service flapping. For more " .
    "information read the Monitoring Engine section about flapping."
);
$help["low_host_flap_threshold"] = dgettext(
    "help",
    "This option is used to set the low threshold for detection of host flapping. For more information " .
    "read the Monitoring Engine section about flapping."
);
$help["high_host_flap_threshold"] = dgettext(
    "help",
    "This option is used to set the high threshold for detection of host flapping. For more information " .
    "read the Monitoring Engine section about flapping."
);
$help["soft_state_dependencies"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will use soft state information when checking " .
    "host and service dependencies. Normally Monitoring Engine will only use the latest hard host or " .
    "service state when checking dependencies. If you want it to use the latest state (regardless of " .
    "whether its a soft or hard state type), enable this option."
);
$help["service_check_timeout"] = dgettext(
    "help",
    "This is the maximum number of seconds that Monitoring Engine will allow service checks to run. " .
    "If checks exceed this limit, they are killed and a CRITICAL state is returned. A timeout error will " .
    "also be logged. This option is meant to be used as a last ditch mechanism to kill off plugins " .
    "which are misbehaving and not exiting in a timely manner. It should be set to something high " .
    "(like 60 seconds or more), so that each service check normally finishes executing within this " .
    "time limit. If a service check runs longer than this limit, Monitoring Engine will kill it off " .
    "thinking it is a runaway processes."
);
$help["host_check_timeout"] = dgettext(
    "help",
    "This is the maximum number of seconds that Monitoring Engine will allow host checks to run. " .
    "If checks exceed this limit, they are killed and a CRITICAL state is returned and the host will be assumed " .
    "to be DOWN. A timeout error will also be logged. This option is meant to be used as a last ditch mechanism " .
    "to kill off plugins which are misbehaving and not exiting in a timely manner. It should be set to " .
    "something high (like 60 seconds or more), so that each host check normally finishes executing within " .
    "this time limit. If a host check runs longer than this limit, Monitoring Engine will kill it off thinking " .
    "it is a runaway processes."
);
$help["event_handler_timeout"] = dgettext(
    "help",
    "This is the maximum number of seconds that Monitoring Engine will allow event handlers to be run. " .
    "If an event handler exceeds this time limit it will be killed and a warning will be logged. This option " .
    "is meant to be used as a last ditch mechanism to kill off commands which are misbehaving and not exiting " .
    "in a timely manner. It should be set to something high (like 60 seconds or more), so that each event handler " .
    "command normally finishes executing within this time limit. If an event handler runs longer than this limit, " .
    "Monitoring Engine will kill it off thinking it is a runaway processes."
);
$help["notification_timeout"] = dgettext(
    "help",
    "This is the maximum number of seconds that Monitoring Engine will allow notification commands to be run. " .
    "If a notification command exceeds this time limit it will be killed and a warning will be logged. " .
    "This option is meant to be used as a last ditch mechanism to kill off commands which are misbehaving " .
    "and not exiting in a timely manner. It should be set to something high (like 60 seconds or more), so that " .
    "each notification command finishes executing within this time limit. If a notification command runs " .
    "longer than this limit, Monitoring Engine will kill it off thinking it is a runaway processes."
);
$help["check_for_orphaned_services"] = dgettext(
    "help",
    "This option allows you to enable or disable checks for orphaned service checks. Orphaned service checks " .
    "are checks which have been executed and have been removed from the event queue, but have not had any results " .
    "reported in a long time. Since no results have come back in for the service, it is not rescheduled in " .
    "the event queue. This can cause service checks to stop being executed. Normally it is very rare for " .
    "this to happen - it might happen if an external user or process killed off the process that was " .
    "being used to execute a service check. If this option is enabled and Monitoring Engine finds that results " .
    "for a particular service check have not come back, it will log an error message and reschedule the " .
    "service check. This option is enabled by default."
);
$help["check_for_orphaned_hosts"] = dgettext(
    "help",
    "This option allows you to enable or disable checks for orphaned hoste checks. Orphaned host checks are " .
    "checks which have been executed and have been removed from the event queue, but have not had any results " .
    "reported in a long time. Since no results have come back in for the host, it is not rescheduled in " .
    "the event queue. This can cause host checks to stop being executed. Normally it is very rare for " .
    "this to happen - it might happen if an external user or process killed off the process that was being " .
    "used to execute a host check. If this option is enabled and Monitoring Engine finds that results for a " .
    "particular host check have not come back, it will log an error message and reschedule the host check. " .
    "This option is enabled by default."
);
$help["check_service_freshness"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will periodically check the \"freshness\" of " .
    "service checks. Enabling this option is useful for helping to ensure that passive service checks are " .
    "received in a timely manner. If the check results is found to be not fresh, Monitoring Engine will " .
    "force an active check of the host or service by executing the command specified by in the host or " .
    "service definition. This option is enabled by default."
);
$help["service_freshness_check_interval"] = dgettext(
    "help",
    "This setting determines how often (in seconds) Monitoring Engine will periodically check the \"freshness\" of " .
    "service check results. If you have disabled service freshness checking (with the " .
    "check_service_freshness option), this option has no effect."
);
$help["check_host_freshness"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will periodically check the \"freshness\" of " .
    "host checks. Enabling this option is useful for helping to ensure that passive host checks are " .
    "received in a timely manner. If the check results is found to be not fresh, Monitoring Engine will " .
    "force an active check of the host or service by executing the command specified by in the host or " .
    "service definition. This option is enabled by default."
);
$help["host_freshness_check_interval"] = dgettext(
    "help",
    "This setting determines how often (in seconds) Monitoring Engine will periodically check the \"freshness\" of " .
    "host check results. If you have disabled host freshness checking (with the check_host_freshness option), " .
    "this option has no effect."
);
$help["additional_freshness_latency"] = dgettext(
    "help",
    "This option determines the number of seconds Monitoring Engine will add to any host or services " .
    "freshness threshold it automatically calculates (e.g. those not specified explicitly by the user)."
);
$help["date_format"] = dgettext(
    "help",
    "This option allows you to specify what kind of date/time format Monitoring Engine should use in the web " .
    "interface and date/time macros."
);
$help["instance_heartbeat_interval"] = dgettext(
    "help",
    "Time interval in seconds between two heartbeat events. This event is the one responsible of the 'Last Update' "
    . "column update in the Pollers listing. Value must be between 5 and 600. Default value is 30."
);
$help["admin_email"] = dgettext(
    "help",
    "This is the email address for the administrator of the local machine (i.e. the one that Monitoring " .
    "Engine is running on). This value can be used in notification commands by using the \$ADMINEMAIL\$ macro."
);
$help["admin_pager"] = dgettext(
    "help",
    "This is the pager number (or pager email gateway) for the administrator of the local machine (i.e. " .
    "the one that Monitoring Engine is running on). The pager number/address can be used in notification " .
    "commands by using the \$ADMINPAGER\$ macro."
);
$help["illegal_object_name_chars"] = dgettext(
    "help",
    "This option allows you to specify illegal characters that cannot be used in host names, service " .
    "descriptions, or names of other object types. Monitoring Engine will allow you to use most characters " .
    "in object definitions, but I recommend not using the characters set by default. Doing may give you problems " .
    "in the web interface, notification commands, etc."
);
$help["illegal_macro_output_chars"] = dgettext(
    "help",
    "This option allows you to specify illegal characters that should be stripped from macros before " .
    "being used in notifications, event handlers, and other commands. This DOES NOT affect macros used " .
    "in service or host check commands. Some of these characters are interpreted by the shell (i.e. the backtick) " .
    "and can lead to security problems."
);
$help["use_regexp_matching"] = dgettext(
    "help",
    "This option determines whether or not various directives in your object definitions will be processed " .
    "as regular expressions. More information on how this works can be found in Monitoring Engine section " .
    "on object tricks. This option is disabled by default."
);
$help["use_true_regexp_matching"] = dgettext(
    "help",
    "If you've enabled regular expression matching of various object directives using the use_regexp_matching " .
    "option, this option will determine when object directives are treated as regular expressions. " .
    "If this option is disabled (the default), directives will only be treated as regular expressions if " .
    "they contain *, ?, +, or \\.. If this option is enabled, all appropriate directives will be treated " .
    "as regular expression."
);
$help["event_broker_options"] = dgettext(
    "help",
    "This option controls what (if any) data gets sent to the event broker and, in turn, to any loaded event " .
    "broker module. Centreon relies heavily on the broker and needs this value to be set as -1."
);
$help["enable_macros_filter"] = dgettext(
    "help",
    "This options is an advanced configuration of engine that enables or not the filtering of macros sent to broker." .
    "If you don't understand the purpose of enabling this option, please do not enable it."
);
$help["macros_filter"] = dgettext(
    "help",
    "This field should be filled only if filtering option is enabled. You can define the list of macros that " .
    "should be sent to Centreon Broker."
);
$help["broker_module"] = dgettext(
    "help",
    "This directive is used to specify an event broker module that should by loaded by Monitoring Engine " .
    "at startup. Use multiple directives if you want to load more than one module. Arguments that should be " .
    "passed to the module at startup are separated from the module path by a space."
);
$help["enable_predictive_host_dependency_checks"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will execute predictive checks of hosts that are " .
    "being depended upon (as defined in host dependencies) for a particular host when it changes state. " .
    "Predictive checks help ensure that the dependency logic is as accurate as possible. This option is " .
    "enabled by default."
);
$help["enable_predictive_service_dependency_checks"] = dgettext(
    "help",
    "This option determines whether or not Monitoring Engine will execute predictive checks of services that are " .
    "being depended upon (as defined in service dependencies) for a particular service when it changes state. " .
    "Predictive checks help ensure that the dependency logic is as accurate as possible. More information on " .
    "how predictive checks work can be found here. This option is enabled by default."
);
$help["cached_host_check_horizon"] = dgettext(
    "help",
    "This option determines the maximum amount of time (in seconds) that the state of a previous host check is " .
    "considered current. Cached host states (from host checks that were performed more recently than the time " .
    "specified by this value) can improve host check performance immensely. Too high of a value for this " .
    "option may result in (temporarily) inaccurate host states, while a low value may result in a performance " .
    "hit for host checks. Use a value of 0 if you want to disable host check caching."
);
$help["cached_service_check_horizon"] = dgettext(
    "help",
    "This option determines the maximum amount of time (in seconds) that the state of a previous service " .
    "check is considered current. Cached service states (from service checks that were performed more " .
    "recently than the time specified by this value) can improve service check performance when a lot of " .
    "service dependencies are used. Too high of a value for this option may result in inaccuracies in the " .
    "service dependency logic. Use a value of 0 if you want to disable service check caching."
);
$help["enable_environment_macros"] = dgettext(
    "help",
    "This option determines whether or not the Monitoring Engine daemon will make all standard macros available " .
    "as environment variables to your check, notification, event hander, etc. commands. In large Monitoring " .
    "Engine installations this can be problematic because it takes additional memory and (more importantly) " .
    "CPU to compute the values of all macros and make them available to the environment. " .
    "This option is enabled by default."
);
$help["debug_file"] = dgettext(
    "help",
    "This option determines where Monitoring Engine should write debugging information. What (if any) " .
    "information is written is determined by the debug_level and debug_verbosity options. You can have " .
    "Monitoring Engine automaticaly rotate the debug file when it reaches a certain size by using the " .
    "max_debug_file_size option."
);
$help["max_debug_file_size"] = dgettext(
    "help",
    "This option determines the maximum size (in bytes) of the debug file. If the file grows larger than this " .
    "size, it will be renamed with a .old extension. If a file already exists with a .old extension it will " .
    "automatically be deleted. This helps ensure your disk space usage doesn't get out of control when debugging " .
    "Monitoring Engine."
);
$help["daemon_dumps_core"] = dgettext(
    "help",
    "This option allows dumping core in case a segmentation fault occurs. Warning: Make sure that server has " .
    "sufficient disk space for these dumps (ulimit). This option is discarded when using Centreon Engine."
);
$help["debug_verbosity"] = dgettext(
    "help",
    "This option determines how much debugging information Monitoring Engine should write to the debug_file. " .
    "By default the verbosity is set to level 1."
);
$help["Monitoring Engine_debug_level"] = dgettext(
    "help",
    "This option determines what type of information Monitoring Engine should write to the debug_file."
);
$help["Monitoring Engine_name"] = dgettext("help", "Description or name used to identify this configuration set.");
$help["Monitoring Engine_activate"] = dgettext(
    "help",
    "Specify whether this configuration is currently active or not. "
    . "This way you can test different configuration sets for one monitoring node."
);
$help["Monitoring Engine_server_id"] = dgettext(
    "help",
    "Choose the Monitoring Engine server instance this configuration is defined for."
);
$help["log_pid"] = dgettext(
    "help",
    "Enable the possibility to log pid information in engine log file (option only for Centreon Engine)"
);

/*
 * unsupported in centreon
 *//*
status_file
status_update_interval

check_for_updates
bare_update_checks
retained_*_attribute_mask
max_check_result_reaper_time
*_perfdata_file_mode are missing p (pipe) mode
use_timezone

*/;
