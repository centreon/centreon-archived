
package centreon::script::centreontrapd;

use strict;
use warnings;
use POSIX;
use centreon::script;
use centreon::common::db;
use centreon::trapd::lib;
use centreon::trapd::Log;

use base qw(centreon::script);
use vars qw(%centreontrapd_config);

my %handlers = ('TERM' => {}, 'HUP' => {}, 'DIE' => {}, 'CHLD' => {});

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreontrapd",
        centreon_db_conn => 0,
        centstorage_db_conn => 0
    );

    bless $self, $class;
    $self->add_options(
        "config-extra" => \$self->{opt_extra},
    );

    %{$self->{centreontrapd_default_config}} =
      (
       timeout_end => 30,
       spool_directory => "/var/spool/centreontrapd/",
       sleep => 2,
       use_trap_time => 1,
       net_snmp_perl_enable => 1,
       mibs_environment => '',
       remove_backslash_from_quotes => 1,
       dns_enable => 0,
       separator => ' ',
       strip_domain => 0,
       strip_domain_list => [],
       duplicate_trap_window => 1,
       date_format => "",
       time_format => "",
       date_time_format => "",
       cache_unknown_traps_enable => 1,
       cache_unknown_traps_retention => 600,
       # 0 = central, 1 = poller
       mode => 0,
       cmd_timeout => 10,
       centreon_user => "centreon",
       # 0 => skip if MySQL error | 1 => dont skip (block) if MySQL error (and keep order)
       policy_trap => 1,
       # Log DB
       log_trap_db => 0,
       log_transaction_request_max => 500,
       log_transaction_timeout => 10,
       log_purge_time => 600
    );
   
    $self->{htmlentities} = 0;
    @{$self->{var}} = undef;                      # Variables of trap received by SNMPTRAPD
    @{$self->{entvar}} = undef;                   # Enterprise variable values of trap received by SNMPTRAPD
    @{$self->{entvarname}} = undef;               # Enterprise variable names of trap received by SNMPTRAPD
    @{$self->{preexec}} = ();                     # Result from PREEXEC
    $self->{agent_dns_name} = undef;
    $self->{trap_date} = undef;
    $self->{trap_time} = undef;
    $self->{trap_date_time} = undef;
    $self->{trap_date_time_epoch} = undef;
    %{$self->{duplicate_traps}} = ();
    $self->{timetoreload} = 0;
    @{$self->{filenames}} = undef;
    $self->{oids_cache} = undef;
    $self->{last_cache_time} = undef;
    $self->{whoami} = undef;

    $self->{ref_oids} = undef;
    $self->{ref_hosts} = undef;
    $self->{ref_services} = undef;
    $self->{ref_macro_hosts} = undef;
    
    # Fork manage
    %{$self->{return_child}} = ();
    %{$self->{running_processes}} = ();
    %{$self->{last_time_exec}} = ('oid' => {}, 'host' => {});
    # Current ID of working
    $self->{current_host_id} = undef;
    $self->{current_hostname} = undef;
    $self->{current_server_id} = undef;
    $self->{current_service_id} = undef;
    $self->{current_server_ip_address} = undef;
    $self->{current_service_desc} = undef;
    $self->{current_trap_id} = undef;
    $self->{current_ip} = undef;
    $self->{current_oid} = undef;
    # From centreon DB
    $self->{current_trap_name} = undef;
    $self->{current_trap_log} = undef;
    $self->{current_vendor_name} = undef;
    
    #
    $self->{traps_global_output} = undef;
    $self->{traps_global_status} = undef;
    
    # For policy_trap = 1 (temp). To avoid doing the same thing twice
    # ID oid ===> Host ID ===> Service ID
    %{$self->{policy_trap_skip}} = ();
    $self->{digest_trap} = undef;
    
    $self->{cmdFile} = undef;
    
    # Pipe for log DB 
    %{$self->{logdb_pipes}} = (running => 0);
    $self->{pid_logdb_child} = undef;
    # For protocol
    $self->{id_logdb} = 0;
    
    # redefine to avoid out when we try modules
    $SIG{__DIE__} = undef;
    return $self;
}

sub init {
    my $self = shift;
    $self->SUPER::init();
    
    if (!defined($self->{opt_extra})) {
        $self->{opt_extra} = "/etc/centreon/centreontrapd.pm";
    }
    if (-f $self->{opt_extra}) {
        require $self->{opt_extra};
    } else {
        $self->{logger}->writeLogInfo("Can't find extra config file $self->{opt_extra}");
    }

    $self->{centreontrapd_config} = {%{$self->{centreontrapd_default_config}}, %centreontrapd_config};
    
    ($self->{centreontrapd_config}->{date_format}, $self->{centreontrapd_config}->{time_format}) = 
                                    centreon::trapd::lib::manage_params_conf($self->{centreontrapd_config}->{date_format},
                                                                             $self->{centreontrapd_config}->{time_format});
    centreon::trapd::lib::init_modules(logger => $self->{logger}, config => $self->{centreontrapd_config}, htmlentities => \$self->{htmlentities});
    
    $self->set_signal_handlers;
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{TERM}->{$self} = sub { $self->handle_TERM() };
    $SIG{HUP} = \&class_handle_HUP;
    $handlers{HUP}->{$self} = sub { $self->handle_HUP() };
    $SIG{__DIE__} = \&class_handle_DIE;
    $handlers{DIE}->{$self} = sub { $self->handle_DIE($_[0]) };
    $SIG{CHLD} = \&class_handle_CHLD;
    $handlers{CHLD}->{$self} = sub { $self->handle_CHLD() };
}

sub class_handle_TERM {
    foreach (keys %{$handlers{TERM}}) {
        &{$handlers{TERM}->{$_}}();
    }
}

sub class_handle_HUP {
    foreach (keys %{$handlers{HUP}}) {
        &{$handlers{HUP}->{$_}}();
    }
}

sub class_handle_CHLD {
    foreach (keys %{$handlers{CHLD}}) {
        &{$handlers{CHLD}->{$_}}();
    }
}

sub class_handle_DIE {
    my ($msg) = @_;

    foreach (keys %{$handlers{DIE}}) {
        &{$handlers{DIE}->{$_}}($msg);
    }
}

sub handle_TERM {
    my $self = shift;
    $self->{logger}->writeLogInfo("$$ Receiving order to stop...");
    die("Quit");
}

sub handle_HUP {
    my $self = shift;
    $self->{logger}->writeLogInfo("$$ Receiving order to reload...");
    $self->{timetoreload} = 1;
}

sub handle_DIE {
    my $self = shift;
    my $msg = shift;

    $self->{logger}->writeLogInfo($msg);

    ###
    # Send -TERM signal
    ###
    if (defined($self->{logdb_pipes}{'running'}) && $self->{logdb_pipes}{'running'} == 1) {
        $self->{logger}->writeLogInfo("Send -TERM signal to logdb process..");
        kill('TERM', $self->{pid_logdb_child});
    }
    
    # We're waiting n seconds
    for (my $i = 0; $i < $self->{centreontrapd_config}->{timeout_end}; $i++) {
        $self->manage_pool(0);
        if (keys %{$self->{running_processes}} == 0 && $self->{logdb_pipes}{'running'} == 0) {
                $self->{logger}->writeLogInfo("Main process exit.");
                exit(0);
        }
        sleep 1;
    }

    $self->{logger}->writeLogInfo("Dont handle gently. Send KILL Signals to childs");
    # Last check before
    $self->manage_pool(0);

    # We are killing
    foreach (keys %{$self->{running_processes}}) {
        kill('KILL', $_);
        $self->{logger}->writeLogInfo("Send -KILL signal to child process '$_'..");
    }
    if ($self->{logdb_pipes}{'running'} == 1) {
        kill('KILL', $self->{pid_logdb_child});
        $self->{logger}->writeLogInfo("Send -KILL signal to logdb process..");
    }

    exit(0);
}

sub handle_CHLD {
    my $self = shift;
    my $child_pid;

    while (($child_pid = waitpid(-1, &WNOHANG)) > 0) {
        $self->{return_child}{$child_pid} = {'exit_code' => $? >> 8};
        $self->{logger}->writeLogInfo("SIGCHLD received: $child_pid");
    }
    $SIG{CHLD} = \&class_handle_CHLD;
}

sub reload_config {
    my $self = shift;
    my $file = $_[0];
    
    unless (my $return = do $file) {
        $self->{logger}->writeLogError("couldn't parse $file: $@") if $@;
        $self->{logger}->writeLogError("couldn't do $file: $!") unless defined $return;
        $self->{logger}->writeLogError("couldn't run $file") unless $return;
    }
}

sub reload {
    my $self = shift;

    $self->{logger}->writeLogInfo("Reload in progress for main process...");
    # reopen file
    if ($self->{logger}->is_file_mode()) {
        $self->{logger}->file_mode($self->{logger}->{file_name});
    }
    $self->{logger}->redirect_output();
    
    centreon::common::misc::reload_db_config($self->{logger}, $self->{config_file}, $self->{cdb});
    centreon::common::misc::check_debug($self->{logger}, "debug_centreontrapd", $self->{cdb}, "centreontrapd main process");

    if ($self->{cdb}->type() =~ /SQLite/i) {
        $self->{logger}->writeLogInfo("Sqlite database. Need to disconnect and connect file.");
        $self->{cdb}->disconnect();
        $self->{cdb}->connect();
    }
    
    if ($self->{logdb_pipes}{'running'} == 1) {
        kill('HUP', $self->{pid_logdb_child});
        $self->{logger}->writeLogInfo("Send -HUP signal to logdb process..");
    }
    
    $self->reload_config($self->{opt_extra});
    ($self->{centreontrapd_config}->{date_format}, $self->{centreontrapd_config}->{time_format}) = 
                                    centreon::trapd::lib::manage_params_conf($self->{centreontrapd_config}->{date_format},
                                                                             $self->{centreontrapd_config}->{time_format});
    # redefine to avoid out when we try modules
    $SIG{__DIE__} = undef;
    centreon::trapd::lib::init_modules(logger => $self->{logger}, config => $self->{centreontrapd_config}, htmlentities => \$self->{htmlentities});
    $self->set_signal_handlers;

    centreon::trapd::lib::get_cache_oids(cdb => $self->{cdb}, oids_cache => \$self->{oids_cache}, last_cache_time => \$self->{last_cache_time});
    $self->{timetoreload} = 0;
}

sub create_logdb_child {
    my $self = shift;
    my ($reader_pipe, $writer_pipe);

    pipe($reader_pipe, $writer_pipe);
    $writer_pipe->autoflush(1);

    $self->{logdb_pipes}{'reader'} = \*$reader_pipe;
    $self->{logdb_pipes}{'writer'} = \*$writer_pipe;
    
    $self->{logger}->writeLogInfo("Create delete child");
    my $current_pid = fork();
    if (!$current_pid) {
        # Unhandle die in child
        $SIG{CHLD} = undef;
        $SIG{__DIE__} = undef;
        $self->{cdb}->set_inactive_destroy();

        close $self->{logdb_pipes}{'writer'};
        my $centreon_db_centstorage = centreon::common::db->new(db => $self->{centreon_config}->{centstorage_db},
                                                        host => $self->{centreon_config}->{db_host},
                                                        port => $self->{centreon_config}->{db_port},
                                                        user => $self->{centreon_config}->{db_user},
                                                        password => $self->{centreon_config}->{db_passwd},
                                                        force => 1,
                                                        logger => $self->{logger});
        $centreon_db_centstorage->connect();
        
        my $centreontrapd_log = centreon::trapd::Log->new($self->{logger});
        $centreontrapd_log->main($centreon_db_centstorage,
                                 $self->{logdb_pipes}{'reader'}, $self->{config_file}, $self->{centreontrapd_config});
        exit(0);
    }
    $self->{pid_logdb_child} = $current_pid;
    close $self->{logdb_pipes}{'reader'};
    $self->{logdb_pipes}{'running'} = 1;
}

sub manage_pool {
    my $self = shift;
    my ($create_pool) = @_;
    
    foreach my $child_pid (keys %{$self->{return_child}}) {
        if (defined($self->{running_processes}->{$child_pid})) {
            delete $self->{running_processes}->{$child_pid};
            delete $self->{return_child}->{$child_pid};
        }
        
        if (defined($self->{pid_logdb_child}) && $child_pid == $self->{pid_logdb_child}) {
            $self->{logger}->writeLogInfo("Logdb child is dead");
            $self->{logdb_pipes}{'running'} = 0;
            if ($self->{centreontrapd_config}->{log_trap_db} == 1 && defined($create_pool) && $create_pool == 1) {
                $self->create_logdb_child();
            }
            delete $self->{return_child}{$child_pid};
        }
    }
}

###############################
## Execute a command Nagios or Centcore
#
sub do_exec {
    my $self = shift;
    my $matching_result = 0;
    
    $self->{traps_global_status} = $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_status};
    # PREEXEC commands
    $self->execute_preexec();

    $self->{traps_global_output} = $self->substitute_string($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_args});    
    
    ######################################################################
    # Advanced matching rules
    if (defined($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_advanced_treatment}) && 
        $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_advanced_treatment} == 1) {
        $matching_result = $self->checkMatchingRules();
    }

    #####################################################################
    # Submit value to passive service
    if (defined($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_submit_result_enable}) && 
        $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_submit_result_enable} == 1 &&
        $matching_result == 0) {
        $self->submitResult();
    }

    ######################################################################
    # Force service execution with external command
    if (defined($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_reschedule_svc_enable}) && 
        $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_reschedule_svc_enable} == 1) {
        $self->forceCheck();
    }

    ######################################################################
    # Execute special command
    if (defined($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_execution_command_enable}) && 
        $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_execution_command_enable} == 1) {
        $self->executeCommand();
    }
    
    if ($self->{centreontrapd_config}->{log_trap_db} == 1 && $self->{current_trap_log} == 1) {
        centreon::trapd::lib::send_logdb(pipe => $self->{logdb_pipes}{'writer'},
                                        id => $self->{id_logdb},
                                        cdb => $self->{cdb},
                                        trap_time => $self->{trap_date_time_epoch},
                                        timeout => 0,
                                        host_name => ${$self->{var}}[0],
                                        ip_address => $self->{current_ip},
                                        agent_host_name => $self->{agent_dns_name},
                                        agent_ip_address => ${$self->{var}}[4],
                                        trap_oid => $self->{current_oid},
                                        trap_name => $self->{current_trap_name},
                                        vendor => $self->{current_vendor_name},
                                        severity => $self->{traps_global_status},
                                        output_message => $self->{traps_global_output},
                                        entvar => \@{$self->{entvar}},
                                        entvarname => \@{$self->{entvarname}});
    }
}

sub manage_exec {
    my $self = shift;

    $self->{id_logdb}++;
    
    #### Fork And manage exec ####
    ####### Check Interval ######
    if (defined($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval_type}) && 
        defined($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval})) {
        # OID type
        if ($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval_type} == 1 &&
            defined($self->{last_time_exec}{oid}->{$self->{current_oid}}) &&
            $self->{trap_date_time_epoch} < ($self->{last_time_exec}{oid}->{$self->{current_oid}} + $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval})) {
            $self->{logger}->writeLogInfo("Skipping trap '" . $self->{current_trap_id} . "': time interval");
            return 1;
        }
        
        # Host type
        if ($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval_type} == 2 &&
            defined($self->{last_time_exec}{host}->{$self->{current_host_id} . ";" . $self->{current_oid}}) &&
            $self->{trap_date_time_epoch} < ($self->{last_time_exec}{host}->{$self->{current_host_id} . ";" . $self->{current_oid}} + $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval})) {
            $self->{logger}->writeLogInfo("Skipping trap '" . $self->{current_trap_id} . "' for host ID '" . $self->{current_host_id} . "': time interval");
            return 1;
        }
    }
    
    my $current_pid = fork();
    if (!$current_pid) {
        # Unhandle die in child
        $SIG{CHLD} = undef;
        $SIG{__DIE__} = undef;
        $self->{cdb}->set_inactive_destroy();
        eval {
            my $alarm_timeout = $self->{centreontrapd_config}->{cmd_timeout};
            if (defined($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_timeout})) {
                $alarm_timeout = $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_timeout};
            }
            
            local $SIG{ALRM} = sub { die "TIMEOUT"; };
            alarm($alarm_timeout);
            $self->do_exec();
            alarm(0);
        };
        if ($@) {
            if ($self->{centreontrapd_config}->{log_trap_db} == 1 && $self->{current_trap_log} == 1) {
                centreon::trapd::lib::send_logdb(pipe => $self->{logdb_pipes}{'writer'},
                                                 id => $self->{id_logdb},
                                                 cdb => $self->{cdb},
                                                 trap_time => $self->{trap_date_time_epoch},
                                                 timeout => 1,
                                                 host_name => ${$self->{var}}[0],
                                                 ip_address => $self->{current_ip},
                                                 agent_host_name => $self->{agent_dns_name},
                                                 agent_ip_address => ${$self->{var}}[4],
                                                 trap_oid => $self->{current_oid},
                                                 trap_name => $self->{current_trap_name},
                                                 vendor => $self->{current_vendor_name},
                                                 severity => $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_status},
                                                 output_message => $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_args},
                                                 entvar => \@{$self->{entvar}},
                                                 entvarname => \@{$self->{entvarname}});
            }
            $self->{logger}->writeLogError("ERROR: Exec timeout");
            exit(0);
        }
        exit(1);
    }
    
    $self->{logger}->writeLogInfo("CHLD command launched: $current_pid");
    $self->{running_processes}->{$current_pid} = 1;
    $self->{last_time_exec}{oid}->{$self->{current_oid}} = $self->{trap_date_time_epoch};
    $self->{last_time_exec}{host}->{$self->{current_host_id} . ";" . $self->{current_oid}} = $self->{trap_date_time_epoch};
    return 1;
}

######################################
## Force a new check for selected services
#
sub forceCheck {
    my $self = shift;
    my $datetime = time();
    my $submit;

    my $str = "SCHEDULE_FORCED_SVC_CHECK;$self->{current_hostname};$self->{current_service_desc};$datetime";
    
    if ($self->{whoami} eq $self->{centreontrapd_config}->{centreon_user}) {
        $str =~ s/"/\\"/g;
        $submit = "/bin/echo \"EXTERNALCMD:$self->{current_server_id}:[$datetime] $str\" >> " . $self->{cmdFile};
    } else {
        $str =~ s/'/'\\''/g;
        $str =~ s/"/\\"/g;
        $submit = "su -l " . $self->{centreontrapd_config}->{centreon_user} . " -c '/bin/echo \"EXTERNALCMD:$self->{current_server_id}:[$datetime] $str\" >> " . $self->{cmdFile} . "' 2>&1";
    }
    my $stdout = `$submit`;

    $self->{logger}->writeLogInfo("FORCE: Reschedule linked service");
    $self->{logger}->writeLogInfo("FORCE: Launched command: $submit");
    if (defined($stdout)) {
        $self->{logger}->writeLogError("FORCE stdout: $stdout");
    }
}

#######################################
## Submit result via external command
#
sub submitResult {
    my $self = shift;
    my $datetime = time();
    
    my $str = "PROCESS_SERVICE_CHECK_RESULT;$self->{current_hostname};$self->{current_service_desc};" . $self->{traps_global_status} . ";" . $self->{traps_global_output};

    my $submit;
    if ($self->{whoami} eq $self->{centreontrapd_config}->{centreon_user}) {
        $str =~ s/"/\\"/g;
        $submit = "/bin/echo \"EXTERNALCMD:$self->{current_server_id}:[$datetime] $str\" >> " . $self->{cmdFile};
    } else {
        $str =~ s/'/'\\''/g;
        $str =~ s/"/\\"/g;
        $submit = "su -l " . $self->{centreontrapd_config}->{centreon_user} . " -c '/bin/echo \"EXTERNALCMD:$self->{current_server_id}:[$datetime] $str\" >> " . $self->{cmdFile} . "' 2>&1";
    }
    my $stdout = `$submit`;
    
    $self->{logger}->writeLogInfo("SUBMIT: Force service status via passive check update");
    $self->{logger}->writeLogInfo("SUBMIT: Launched command: $submit");
    if (defined($stdout)) {
        $self->{logger}->writeLogError("SUBMIT RESULT stdout: $stdout");
    }
}

sub execute_preexec {
    my $self = shift;

    foreach my $tpe_order (keys %{$self->{ref_oids}->{ $self->{current_trap_id} }->{traps_preexec}}) {
        my $tpe_string = $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_preexec}->{$tpe_order}->{tpe_string};
        $tpe_string = $self->substitute_string($tpe_string);
        $tpe_string = $self->substitute_centreon_var($tpe_string);
        
        my $output = `$tpe_string`;
        if ($? == -1) {
            $self->{logger}->writeLogError("EXEC: Execution error: $!");
        } elsif (($? >> 8) != 0) {
            $self->{logger}->writeLogInfo("EXEC: Exit command: " . ($? >> 8));
        }
        if (defined($output)) {
            chomp $output;
            push @{$self->{preexec}}, $output;
            $self->{logger}->writeLogInfo("EXEC: Output : $output");
        } else {
            push @{$self->{preexec}}, "";
        }
    }
}

##########################
## REPLACE
#
sub substitute_host_macro {
    my $self = shift;
    my $str = $_[0];
    
    if (defined($self->{ref_macro_hosts})) {
        foreach my $macro_name (keys %{$self->{ref_macro_hosts}}) {
            $str =~ s/\Q$macro_name\E/\Q$self->{ref_macro_hosts}->{$macro_name}\E/g;
        }
    }

    return $str;
}

sub substitute_string {
    my $self = shift;
    my $str = $_[0];
    
    # Substitute @{oid_value} and $1, $2,...
    for (my $i=0; $i <= $#{$self->{entvar}}; $i++) {
        my $x = $i + 1;
        $str =~ s/\@\{${$self->{entvarname}}[$i]\}/${$self->{entvar}}[$i]/g;
        $str =~ s/\$$x([^0-9]|$)/${$self->{entvar}}[$i]$1/g;
    }
    
    # Substitute preexec var
    for (my $i=0; $i <= $#{$self->{preexec}}; $i++) {
        my $x = $i + 1;
        $str =~ s/\$p$x([^0-9]|$)/${$self->{preexec}}[$i]$1/g;
    }

    # Substitute $*
    my $sub_str = join($self->{centreontrapd_config}->{separator}, @{$self->{entvar}});
    $str =~ s/\$\*/$sub_str/g;
    
    # $A
    $str =~ s/\$A/$self->{agent_dns_name}/g;
    
    # $aA (Trap agent IP Adress)
    $str =~ s/\$aA/${$self->{var}}[4]/g;
    
    # $R, $r (Trap Hostname)
    $str =~ s/\$R/${$self->{var}}[0]/g;
    $str =~ s/\$r/${$self->{var}}[0]/g;
    
    # $aR, $ar (IP Adress)
    $str =~ s/\$aR/${$self->{var}}[1]/g;
    $str =~ s/\$ar/${$self->{var}}[1]/g;
    
    # Clean OID
    $str =~ s/\@\{[\.0-9]*\}//g;
    return $str;
}

sub substitute_centreon_var {
    my $self = shift;
    my $str = $_[0];

    $str =~ s/\@HOSTNAME\@/$self->{current_hostname}/g;
    $str =~ s/\@HOSTADDRESS\@/$self->{current_ip}/g;
    $str =~ s/\@HOSTADDRESS2\@/$self->{agent_dns_name}/g;
    $str =~ s/\@SERVICEDESC\@/$self->{current_service_desc}/g;
    $str =~ s/\@TRAPOUTPUT\@/$self->{traps_global_output}/g;
    $str =~ s/\@OUTPUT\@/$self->{traps_global_output}/g;
    $str =~ s/\@STATUS\@/$self->{traps_global_status}/g;
    $str =~ s/\@TIME\@/$self->{trap_date_time_epoch}/g;
    $str =~ s/\@POLLERID\@/$self->{current_server_id}/g;
    $str =~ s/\@POLLERADDRESS\@/$self->{current_server_ip_address}/g;
    $str =~ s/\@CMDFILE\@/$self->{cmdFile}/g;
    $str = $self->substitute_host_macro($str);
    return $str;
}

sub substitute_centreon_functions {
    my $self = shift;
    my $str = $_[0];

    if ($str =~ /\@GETHOSTBYADDR\((.*?)\)\@/) {
        my $result = gethostbyaddr(Socket::inet_aton("$1"),Socket::AF_INET());
        $result = '' if (!defined($result));
        $str =~ s/\@GETHOSTBYADDR\(.*?\)\@/$result/;
    }
    if ($str =~ /\@GETHOSTBYNAME\((.*?)\)\@/) {
        my $result = gethostbyname("$1");
        $result = inet_ntoa($result) if (defined($result));
        $result = '' if (!defined($result));
        $str =~ s/\@GETHOSTBYNAME\(.*?\)\@/$result/;
    }

    return $str;
}

#######################################
## Check Advanced Matching Rules
#
sub checkMatchingRules {
    my $self = shift;
    my $matching_boolean = 0;
    
    # Check matching options 
    foreach my $tmo_id (keys %{$self->{ref_oids}->{ $self->{current_trap_id} }->{traps_matching_properties}}) {
        my $tmoString = $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_matching_properties}->{$tmo_id}->{tmo_string};
        my $regexp = $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_matching_properties}->{$tmo_id}->{tmo_regexp};
        my $tmoStatus = $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_matching_properties}->{$tmo_id}->{tmo_status};
        
        $self->{logger}->writeLogDebug("[$tmoString][$regexp] => $tmoStatus");
        
        my @temp = split(//, $regexp);
        my $i = 0;
        my $len = length($regexp);
        $regexp = "";
        foreach (@temp) {
            if ($i eq 0 && $_ =~ "/") {
                $regexp = $regexp . "";
            } elsif ($i eq ($len - 1) && $_ =~ "/") { 
                $regexp = $regexp . "";
            } else {
                $regexp = $regexp . $_;
            }
            $i++;
        }

        $tmoString = $self->substitute_string($tmoString);
        $tmoString = $self->substitute_centreon_var($tmoString);

        ##########################
        # REPLACE special Chars
        if ($self->{htmlentities} == 1) {
            $tmoString = decode_entities($tmoString);
        } else {
            $tmoString =~ s/\&quot\;/\"/g;
            $tmoString =~ s/\&#039\;\&#039\;/"/g;
        }

        # Integrate OID Matching            
        if (defined($tmoString) && $tmoString =~ m/$regexp/g) {
            $self->{traps_global_status} = $tmoStatus;
            $self->{logger}->writeLogInfo("Regexp: String:$tmoString => REGEXP:$regexp");
            $self->{logger}->writeLogInfo("Status: $self->{traps_global_status} ($tmoStatus)");
            $matching_boolean = 1;
            last;
        }    
    }
    
    # Dont do submit if no matching
    if ($matching_boolean == 0 && $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_advanced_treatment_default} == 1) {
        return 1;
    }
    return 0;
}

################################
## Execute a specific command
#
sub executeCommand {
    my $self = shift;
    my $datetime = time();
    my $traps_execution_command = $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_execution_command};
    
    $traps_execution_command = $self->substitute_string($traps_execution_command);
    
    ##########################
    # REPLACE MACROS
    if ($self->{htmlentities} == 1) {
        $traps_execution_command = decode_entities($traps_execution_command);
    } else {
        $traps_execution_command =~ s/\&quot\;/\"/g;
        $traps_execution_command =~ s/\&#039\;\&#039\;/"/g;
        $traps_execution_command =~ s/\&#039\;/'/g;
    }
    
    $traps_execution_command = $self->substitute_centreon_var($traps_execution_command);
    
    ##########################
    # SEND COMMAND
    if ($traps_execution_command) {
        $self->{logger}->writeLogInfo("EXEC: Launch specific command");
        $self->{logger}->writeLogInfo("EXEC: Launched command: $traps_execution_command");
    
        my $output = `$traps_execution_command`;
        if ($? == -1) {
            $self->{logger}->writeLogError("EXEC: Execution error: $!");
        } elsif (($? >> 8) != 0) {
            $self->{logger}->writeLogInfo("EXEC: Exit command: " . ($? >> 8));
        }
        if (defined($output)) {
            chomp $output;
            $self->{logger}->writeLogInfo("EXEC: Output : $output");
        }
    }
}


#######################################
## GET HOSTNAME AND SERVICE DESCRIPTION
#
sub getTrapsInfos {
    my $self = shift;
    my ($fstatus);

    $self->{current_ip} = ${$self->{var}}[1];
    $self->{current_oid} = ${$self->{var}}[3];
    # Use $self->{agent_dns_name} (IP or HOSTNAME with split)
    
    ### Get OIDS 
    ($fstatus, $self->{ref_oids}) = centreon::trapd::lib::get_oids($self->{cdb}, $self->{current_oid});
    return 0 if ($fstatus == -1);
    foreach my $trap_id (keys %{$self->{ref_oids}}) {
        $self->{current_trap_id} = $trap_id;
        $self->{current_trap_log} = $self->{ref_oids}->{$trap_id}->{traps_log};
        $self->{current_trap_name} = $self->{ref_oids}->{$trap_id}->{traps_name};
        $self->{current_vendor_name} = $self->{ref_oids}->{$trap_id}->{name};
        ($fstatus, $self->{ref_hosts}) = centreon::trapd::lib::get_hosts(logger => $self->{logger},
                                                                 cdb => $self->{cdb},
                                                                 trap_info => $self->{ref_oids}->{$trap_id},
                                                                 agent_dns_name => $self->{agent_dns_name},
                                                                 ip_address => $self->{current_ip},
                                                                 entvar => \@{$self->{entvar}},
                                                                 entvarname => \@{$self->{entvarname}},
                                                                 centreontrapd => $self);
        return 0 if ($fstatus == -1);
        foreach my $host_id (keys %{$self->{ref_hosts}}) {
            if (!defined($self->{ref_hosts}->{$host_id}->{nagios_server_id})) {
                $self->{logger}->writeLogError("Cant get server associated for host '" . $self->{ref_hosts}->{$host_id}->{host_name} . "'");
                next;
            }
            $self->{current_host_id} = $host_id;
            $self->{current_server_id} = $self->{ref_hosts}->{$host_id}->{nagios_server_id};
            $self->{current_server_ip_address} = $self->{ref_hosts}->{$host_id}->{ns_ip_address};
            $self->{current_hostname} = $self->{ref_hosts}->{$host_id}->{host_name};

            #### Get Services ####
            ($fstatus, $self->{ref_services}) = centreon::trapd::lib::get_services($self->{cdb}, $trap_id, $host_id);
            return 0 if ($fstatus == -1);
            
            #### If none, we stop ####
            my $size = keys %{$self->{ref_services}};
            if ($size < 1) {
                $self->{logger}->writeLogDebug("Trap without service associated. Skipping...");
                return 1;
            }
            
            #### Check if macro $_HOST*$ needed
            $self->{ref_macro_hosts} = undef;
            if (defined($self->{ref_oids}->{$trap_id}->{traps_execution_command_enable}) && $self->{ref_oids}->{$trap_id}->{traps_execution_command_enable} == 1 &&
                defined($self->{ref_oids}->{$trap_id}->{traps_execution_command}) && $self->{ref_oids}->{$trap_id}->{traps_execution_command} =~ /\$_HOST.*?\$/) {
                ($fstatus, $self->{ref_macro_hosts}) = centreon::trapd::lib::get_macros_host($self->{cdb}, $host_id);
                return 0 if ($fstatus == -1);
            }
            
            foreach my $service_id (keys %{$self->{ref_services}}) {
                $self->{current_service_id} = $service_id;
                $self->{current_service_desc} = $self->{ref_services}->{$service_id}->{service_description};
                $self->{logger}->writeLogDebug("Trap found on service '" . $self->{ref_services}->{$service_id}->{service_description} . "' for host '" . $self->{ref_hosts}->{$host_id}->{host_name} . "'.");
                $self->manage_exec();
            }
        }
    }
    
    return 1;
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->{logger}->redirect_output();
    
    $self->{logger}->writeLogDebug("centreontrapd launched....");
    $self->{logger}->writeLogDebug("PID: $$");

    $self->{cdb} = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                             type => $self->{centreon_config}->{db_type},
                                             host => $self->{centreon_config}->{db_host},
                                             port => $self->{centreon_config}->{db_port},
                                             user => $self->{centreon_config}->{db_user},
                                             password => $self->{centreon_config}->{db_passwd},
                                             force => 0,
                                             logger => $self->{logger});

    if ($self->{centreontrapd_config}->{mode} == 0) {
        $self->{cmdFile} = $self->{centreon_config}->{VarLib} . "/centcore.cmd";
    } else {
        # Dirty!!! Need to know the poller (not Dirty if you use SQLite database)
        my ($status, $sth) = $self->{cdb}->query("SELECT `command_file` FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
        my @conf = $sth->fetchrow_array();
        $self->{cmdFile} = $conf[0];
    }
    $self->{whoami} = getpwuid($<);
    
    if ($self->{centreontrapd_config}->{log_trap_db} == 1) {
        $self->create_logdb_child();
    }

    while (1) {
        centreon::trapd::lib::purge_duplicate_trap(config => $self->{centreontrapd_config},
                                                   duplicate_traps => \%{$self->{duplicate_traps}});
        while ((my $file = centreon::trapd::lib::get_trap(logger => $self->{logger}, 
                                                          config => $self->{centreontrapd_config},
                                                          filenames => \@{$self->{filenames}}))) {
            $self->{logger}->writeLogDebug("Processing file: $file");
            
            # Test can delete before. Dont go after if we cant
            if (! -w $self->{centreontrapd_config}->{spool_directory} . $file) {
                $self->{logger}->writeLogError("Dont have write permission on '" . $self->{centreontrapd_config}->{spool_directory} . $file . "' file.");
                if ($self->{centreontrapd_config}->{policy_trap} == 1) {
                    unshift @{$self->{filenames}}, $file;
                    # We're waiting. We are in a loop
                    sleep $self->{centreontrapd_config}->{sleep};
                    next;
                }
            }
            
            if (open FILE, $self->{centreontrapd_config}->{spool_directory} . $file) {
                my $unlink_trap = 1;
                my $trap_is_a_duplicate = 0;
                my $readtrap_result = centreon::trapd::lib::readtrap(logger => $self->{logger},
                                                                     config => $self->{centreontrapd_config},
                                                                     handle => \*FILE,
                                                                     agent_dns_name => \$self->{agent_dns_name},
                                                                     trap_date => \$self->{trap_date},
                                                                     trap_time => \$self->{trap_time},
                                                                     trap_date_time => \$self->{trap_date_time},
                                                                     trap_date_time_epoch => \$self->{trap_date_time_epoch},
                                                                     duplicate_traps => \%{$self->{duplicate_traps}},
                                                                     digest_trap => \$self->{digest_trap},
                                                                     var => \@{$self->{var}},
                                                                     entvar => \@{$self->{entvar}},
                                                                     entvarname => \@{$self->{entvarname}});
                
                if ($readtrap_result == 1) {
                    if (centreon::trapd::lib::check_known_trap(logger => $self->{logger},
                                                               config => $self->{centreontrapd_config},
                                                               oid2verif => ${$self->{var}}[3],      
                                                               cdb => $self->{cdb},
                                                               last_cache_time => \$self->{last_cache_time},
                                                               oids_cache => \$self->{oids_cache}) == 1) {
                        $unlink_trap = $self->getTrapsInfos();
                    }
                } elsif ($readtrap_result == 0) {
                    $self->{logger}->writeLogDebug("Error processing trap file $file.  Skipping...");
                } elsif ($readtrap_result == -1) {
                    $trap_is_a_duplicate = 1;
                    $self->{logger}->writeLogInfo("Duplicate trap detected in trap file $file.  Skipping...");
                }
                
                close FILE;
                if ($self->{centreontrapd_config}->{policy_trap} == 0 || ($self->{centreontrapd_config}->{policy_trap} == 1 && $unlink_trap == 1)) {
                    unless (unlink($self->{centreontrapd_config}->{spool_directory} . $file)) {
                        $self->{logger}->writeLogError("Unable to delete trap file $file from spool dir:$!");
                    }
                } else {
                    $self->{logger}->writeLogError("Dont skip trap. Need to solve the error.");
                    # we reput in AND we delete trap_digest (avoid skipping duplicate trap)
                    unshift @{$self->{filenames}}, $file;
                    if ($self->{centreontrapd_config}->{duplicate_trap_window}) {
                        delete $self->{duplicate_traps}->{$self->{digest_trap}};
                    }
                    sleep $self->{centreontrapd_config}->{sleep};
                }
            } else {
                $self->{logger}->writeLogError("Could not open trap file " . $self->{centreontrapd_config}->{spool_directory} . "$file: ($!)");
                if ($self->{centreontrapd_config}->{policy_trap} == 1) {
                    $self->{logger}->writeLogError("Dont skip trap. Need to solve the error.");
                    # we reput in
                    unshift @{$self->{filenames}}, $file;
                }
            }
            
            if ($self->{timetoreload} == 1) {
                $self->reload();
            }
        }
        
        $self->{logger}->writeLogDebug("Sleeping for " . $self->{centreontrapd_config}->{sleep} . " seconds");
        sleep $self->{centreontrapd_config}->{sleep};

        if ($self->{timetoreload} == 1) {
            $self->reload();
        }
        $self->manage_pool(1);
    }
}

1;

__END__
