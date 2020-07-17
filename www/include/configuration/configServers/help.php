<?php
$help = array();
$help['name'] = dgettext("help", "Used for identifying the poller");
$help['ns_ip_address'] = dgettext("help", "IP address of the poller");
$help['localhost'] = dgettext("help", "Whether the poller is local");
$help['is_default'] = dgettext("help", "Main poller");
$help['remote_id'] = dgettext("help", "Master Remote Server to which this server will be attached");
$help['remote_additional_id'] = dgettext("help", "Additional Remote Server to which this server will be attached");
$help['ssh_port'] = dgettext("help", "SSH legacy port used by Centreon extensions or tools (see Gorgone Information for communication port between monitoring servers)");
$help['gorgone_communication_type'] = dgettext("help", "Gorgone communication protocol (ZMQ or SSH)");
$help['gorgone_port'] = dgettext("help", "Gorgone port of the remote poller (5556 or 22)");
$help['engine_start_command'] = dgettext("help", "Command to start Centreon Engine process");
$help['engine_stop_command'] = dgettext("help", "Command to stop Centreon Engine process");
$help['engine_restart_command'] = dgettext("help", "Command to restart Centreon Engine process");
$help['engine_reload_command'] = dgettext("help", "Command to reload Centreon Engine process");
$help['init_script_centreontrapd'] = dgettext("help", "Path of init script of centreontrapd");
$help['nagios_bin'] = dgettext("help", "Path of binary of the scheduler");
$help['nagiostats_bin'] = dgettext("help", "Path of stats binary of the scheduler");
$help['nagios_perfdata'] = dgettext("help", "Perfdata script");
$help['broker_reload_command'] = dgettext("help", "Command to reload Centreon Broker process");
$help['centreonbroker_cfg_path'] = dgettext("help", "Path of the configuration file for Centreon Broker");
$help['centreonbroker_module_path'] = dgettext("help", "Path with modules for Centreon Broker");
$help['centreonbroker_logs_path'] = dgettext("help", "Path of the Centreon Broker log file");
$help['centreonconnector_path'] = dgettext("help", "Path with Centreon Connector binaries");
$help['ns_activate'] = dgettext("help", "Enable or disable poller");
$help['centreontrapd_init_script'] = dgettext("help", "Centreontrapd init script to restart process on poller.");
$help['snmp_trapd_path_conf'] = dgettext(
    "help",
    "Light databases will be stored in the specified directory. "
    . "They are used for synchronizing trap definitions on pollers."
);
$help['pollercmd'] = dgettext(
    "help",
    "Those commands can be executed at the end of the file generation generation/restart process. "
    . "Do not specify macros in the commands, for they will not be replaced. "
    . "Make sure to have sufficient rights for the Apache user to run these commands."
);
$help['description'] = dgettext("help", "Short description of the poller");
$help['http_method'] = dgettext(
    "help",
    "What kind of method is needed to reach the Remote Server, HTTP or HTTPS?"
);
$help['http_port'] = dgettext(
    "help",
    "On which TCP port is listening the Remote Server?"
);
$help['no_check_certificate'] = dgettext(
    "help",
    "If checked, it won't check the validity of the SSL certificate of the Remote Server."
);
$help['no_proxy'] = dgettext(
    "help",
    "If checked, it won't use the proxy configured in 'Administration > Parameters > Centreon UI' "
    . "to connect to the Remote Server."
);
$help['remote_server_use_as_proxy'] = dgettext(
    "help",
    "If disabled, the Central server will send configuration and external commands directly to the poller "
    . "and will not use the Remote Server as a proxy."
);
