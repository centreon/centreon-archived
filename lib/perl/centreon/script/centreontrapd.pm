################################################################################
# Copyright 2005-2013 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::script::centreontrapd;

use strict;
use warnings;
use POSIX;
use Socket;
use Storable;
use centreon::script;
use centreon::common::db;
use centreon::common::misc;
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
        "config-extra=s" => \$self->{opt_extra},
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
       # secure mode: 1 => cannot use customcode option for traps
       secure_mode => 1,
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
       log_purge_time => 600,
       # unknown
       unknown_trap_enable => 0,
       unknown_trap_mode => 0,
       unknown_trap_file => '/var/log/centreon/centreontrapd_unknown.log',
       unknown_trap_facility => undef,
    );
   
    # save trap_data
    @{$self->{trap_data_save}} = ();
   
    $self->{trap_data} = {
        var => [],                      # Variables of trap received by SNMPTRAPD
        entvar => [],                   # Enterprise variable values of trap received by SNMPTRAPD
        entvarname => [],               # Enterprise variable names of trap received by SNMPTRAPD
        preexec => [],                  # Result from PREEXEC
        agent_dns_name => undef,
        trap_date => undef,
        trap_time => undef,
        trap_date_time => undef,
        trap_date_time_epoch => undef,
        
        ref_oids => undef,
        ref_hosts => undef,
        ref_services => undef,
        ref_macro_hosts => undef,
        
        current_trap_id => undef,
        current_host_id => undef,
        current_service_id => undef
    };
   
    $self->{htmlentities} = 0;   
    %{$self->{duplicate_traps}} = ();
    $self->{timetoreload} = 0;
    @{$self->{filenames}} = undef;
    $self->{oids_cache} = undef;
    $self->{last_cache_time} = undef;
    $self->{whoami} = undef;

 
    # Fork manage
    %{$self->{return_child}} = ();
    %{$self->{running_processes}} = ();
    $self->{sequential_processes} = {
                                     pid => {},
                                     trap_id => {}
                                    };
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
    
    $self->{current_alarm_timeout} = undef;
    
    # After in fork
    $self->{traps_global_output} = undef;
    $self->{traps_global_status} = undef;
    $self->{traps_global_severity_id} = undef;
    $self->{traps_global_severity_name} = undef;
    $self->{traps_global_severity_level} = undef;
    
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
    $SIG{__DIE__} = 'IGNORE';
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
    
    $self->{logger}->withpid(1);
    if ($self->{centreontrapd_config}->{unknown_trap_enable} == 1) {
        $self->{logger_unknown} = centreon::common::logger->new();
        if ($self->{centreontrapd_config}->{unknown_trap_mode} == 1) {
            $self->{logger_unknown}->file_mode($self->{centreontrapd_config}->{unknown_trap_file});
        }
        $self->{logger_unknown}->severity("info");
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
    if (defined($self->{logdb_pipes}{running}) && $self->{logdb_pipes}{running} == 1) {
        $self->{logger}->writeLogInfo("Send -TERM signal to logdb process..");
        kill('TERM', $self->{pid_logdb_child});
    }
    
    # We're waiting n seconds
    for (my $i = 0; $i < $self->{centreontrapd_config}->{timeout_end}; $i++) {
        $self->manage_pool(0);
        if (keys %{$self->{running_processes}} == 0 && $self->{logdb_pipes}{running} == 0) {
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
    if ($self->{centreontrapd_config}->{unknown_trap_enable} == 1 && $self->{centreontrapd_config}->{unknown_trap_mode} == 1) {
        $self->{logger_unknown}->file_mode($self->{centreontrapd_config}->{unknown_trap_file});
    }
    
    centreon::common::misc::reload_db_config($self->{logger}, $self->{config_file}, $self->{cdb}, $self->{csdb});
    centreon::common::misc::check_debug($self->{logger}, "debug_centreontrapd", $self->{cdb}, "centreontrapd main process");

    if ($self->{cdb}->type() =~ /SQLite/i) {
        $self->{logger}->writeLogInfo("Sqlite database. Need to disconnect and connect file.");
        $self->{cdb}->disconnect();
        $self->{cdb}->connect();
    }
    
    if ($self->{logdb_pipes}{running} == 1) {
        kill('HUP', $self->{pid_logdb_child});
        $self->{logger}->writeLogInfo("Send -HUP signal to logdb process..");
    }
    
    $self->reload_config($self->{opt_extra});
    ($self->{centreontrapd_config}->{date_format}, $self->{centreontrapd_config}->{time_format}) = 
                                    centreon::trapd::lib::manage_params_conf($self->{centreontrapd_config}->{date_format},
                                                                             $self->{centreontrapd_config}->{time_format});
    # redefine to avoid out when we try modules
    $SIG{__DIE__} = 'IGNORE';
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

    $self->{logdb_pipes}{reader} = \*$reader_pipe;
    $self->{logdb_pipes}{writer} = \*$writer_pipe;
    
    $self->{logger}->writeLogInfo("Create logdb child");
    my $current_pid = fork();
    if (!$current_pid) {
        # Unhandle die in child
        $SIG{CHLD} = 'IGNORE';
        $SIG{__DIE__} = 'IGNORE';
        $self->{cdb}->set_inactive_destroy();

        close $self->{logdb_pipes}{writer};
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
                                 $self->{logdb_pipes}{reader}, $self->{config_file}, $self->{centreontrapd_config});
        exit(0);
    }
    $self->{pid_logdb_child} = $current_pid;
    close $self->{logdb_pipes}{reader};
    $self->{logdb_pipes}{running} = 1;
}

sub manage_pool {
    my $self = shift;
    my ($create_pool) = @_;
        
    foreach my $child_pid (keys %{$self->{return_child}}) {
    
        if (defined($self->{sequential_processes}->{pid}->{$child_pid})) {
            delete $self->{sequential_processes}->{trap_id}->{ $self->{sequential_processes}->{pid}->{$child_pid} };
            delete $self->{sequential_processes}->{pid}->{$child_pid};
        }
    
        if (defined($self->{running_processes}->{$child_pid})) {
            delete $self->{running_processes}->{$child_pid};
            delete $self->{return_child}->{$child_pid};
        }
        
        if (defined($self->{pid_logdb_child}) && $child_pid == $self->{pid_logdb_child}) {
            $self->{logger}->writeLogInfo("Logdb child is dead");
            $self->{logdb_pipes}{running} = 0;
            if ($self->{centreontrapd_config}->{log_trap_db} == 1 && defined($create_pool) && $create_pool == 1) {
                $self->create_logdb_child();
            }
            delete $self->{return_child}{$child_pid};
        }
    }
}

###############################
## Save and Play functions
#

sub set_current_values {
    my $self = shift;
    
    $self->{current_trap_id} = $self->{trap_data}->{current_trap_id};
    $self->{current_host_id} = $self->{trap_data}->{current_host_id};
    $self->{current_service_id} = $self->{trap_data}->{current_service_id};
    
    $self->{current_ip} = ${$self->{trap_data}->{var}}[1];
    $self->{current_oid} = ${$self->{trap_data}->{var}}[3];
    $self->{current_trap_log} = $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_log};
    $self->{current_trap_name} = $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_name};
    $self->{current_vendor_name} = $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{name};
    $self->{current_server_id} = $self->{trap_data}->{ref_hosts}->{ $self->{current_host_id} }->{nagios_server_id};
    $self->{current_server_ip_address} = $self->{trap_data}->{ref_hosts}->{ $self->{current_host_id} }->{ns_ip_address};
    $self->{current_hostname} = $self->{trap_data}->{ref_hosts}->{ $self->{current_host_id} }->{host_name};
    $self->{current_service_desc} = $self->{trap_data}->{ref_services}->{ $self->{current_service_id} }->{service_description};
}

sub check_sequential_can_exec {
    my $self = shift;

    if (defined($self->{sequential_processes}->{trap_id}->{$self->{current_host_id} . "_" . $self->{current_trap_id}})) {
        # We save data
        $self->{logger}->writeLogInfo("Put trap in queue...");
        push @{$self->{trap_data_save}}, Storable::dclone($self->{trap_data});
        return 1;
    }
    return 0;
}

sub check_sequential_todo {
    my $self = shift;
    
    for (my $i = 0; $i <= $#{$self->{trap_data_save}}; $i++) {
        if (!defined($self->{sequential_processes}->{trap_id}->{ ${$self->{trap_data_save}}[$i]->{current_host_id} . "_" . ${$self->{trap_data_save}}[$i]->{current_trap_id} })) {
            $self->{logger}->writeLogInfo("Exec trap in queue...");
            $self->{trap_data} = splice @{$self->{trap_data_save}}, $i, 1;
            $i--;
            $self->manage_exec();
        }
    }
}

###############################
## Execute a command Nagios or Centcore
#
sub do_exec {
    my $self = shift;
    my $matching_result = 0;
    
    $self->{traps_global_status} = $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_status};
    $self->{traps_global_severity_id} = $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{sc_id};
    $self->{traps_global_severity_name} = $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{sc_name};
    $self->{traps_global_severity_level} = $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{level};
    
    # PREEXEC commands
    $self->execute_preexec();

    $self->{traps_global_output} = $self->substitute_string($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_args});
    # Check if a transform is needed
    if (defined($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_output_transform}) &&
        $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_output_transform} ne '') {
        eval "\$self->{traps_global_output} =~ $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_output_transform};";
        if ($@) {
            $self->{logger}->writeLogError("Output transform not valid for " . $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_name});
        }
    }
    
    ######################################################################
    # Advanced matching rules
    if (defined($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_advanced_treatment}) && 
        $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_advanced_treatment} == 1) {
        $matching_result = $self->checkMatchingRules();
    }

    #####################################################################
    # Submit value to passive service
    if (defined($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_submit_result_enable}) && 
        $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_submit_result_enable} == 1 &&
        $matching_result == 0) {
        $self->submitResult();
    }

    ######################################################################
    # Force service execution with external command
    if (defined($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_reschedule_svc_enable}) && 
        $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_reschedule_svc_enable} == 1) {
        $self->forceCheck();
    }

    ######################################################################
    # Execute special command
    if (defined($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_execution_command_enable}) && 
        $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_execution_command_enable} == 1) {
        $self->executeCommand();
    }
    
    if ($self->{centreontrapd_config}->{log_trap_db} == 1 && $self->{current_trap_log} == 1) {
        centreon::trapd::lib::send_logdb(pipe => $self->{logdb_pipes}{writer},
                                        id => $self->{id_logdb},
                                        cdb => $self->{cdb},
                                        trap_time => $self->{trap_data}->{trap_date_time_epoch},
                                        timeout => 0,
                                        host_name => ${$self->{trap_data}->{var}}[0],
                                        ip_address => $self->{current_ip},
                                        agent_host_name => $self->{trap_data}->{agent_dns_name},
                                        agent_ip_address => ${$self->{trap_data}->{var}}[4],
                                        trap_oid => $self->{current_oid},
                                        trap_name => $self->{current_trap_name},
                                        vendor => $self->{current_vendor_name},
                                        status => $self->{traps_global_status},
                                        severity_id => $self->{traps_global_severity_id},
                                        severity_name => $self->{traps_global_severity_name},
                                        output_message => $self->{traps_global_output},
                                        entvar => \@{$self->{trap_data}->{entvar}},
                                        entvarname => \@{$self->{trap_data}->{entvarname}});
    }
}

sub manage_exec {
    my $self = shift;

    $self->set_current_values();
    
    #### Fork And manage exec ####
    ####### Check Interval ######
    if (defined($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval_type}) && 
        $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval_type} ne '' &&
        defined($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval})) {
        # OID type
        if ($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval_type} == 1 &&
            defined($self->{last_time_exec}{oid}->{$self->{current_oid}}) &&
            $self->{trap_date_time_epoch} < ($self->{last_time_exec}{oid}->{$self->{current_oid}} + $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval})) {
            $self->{logger}->writeLogInfo("Skipping trap '" . $self->{current_trap_id} . "': time interval");
            return 1;
        }
        
        # Host type
        if ($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval_type} == 2 &&
            defined($self->{last_time_exec}{host}->{$self->{current_host_id} . ";" . $self->{current_oid}}) &&
            $self->{trap_date_time_epoch} < ($self->{last_time_exec}{host}->{$self->{current_host_id} . ";" . $self->{current_oid}} + $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_interval})) {
            $self->{logger}->writeLogInfo("Skipping trap '" . $self->{current_trap_id} . "' for host ID '" . $self->{current_host_id} . "': time interval");
            return 1;
        }
    }
    
    ### Check Sequential exec ###
    return 1 if ($self->check_sequential_can_exec() == 1);
    
    $self->{id_logdb}++;
    
    my $current_pid = fork();
    if (!$current_pid) {
        # Unhandle die in child
        $SIG{CHLD} = 'IGNORE';
        $SIG{__DIE__} = 'IGNORE';
        $self->{cdb}->set_inactive_destroy();
        if (defined($self->{csdb})) {
            $self->{csdb}->set_inactive_destroy();
        }
        
        $self->{current_alarm_timeout} = $self->{centreontrapd_config}->{cmd_timeout};
        if (defined($self->{ref_oids}->{ $self->{current_trap_id} }->{traps_timeout}) && $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_timeout} != 0) {
            $self->{current_alarm_timeout} = $self->{ref_oids}->{ $self->{current_trap_id} }->{traps_timeout};
        }
        $self->do_exec();

        exit(1);
    }
    
    $self->{logger}->writeLogInfo("CHLD command launched: $current_pid");
    
    ####
    # Save to say it's sequential
    if (defined($self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_method}) && 
        $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_exec_method} == 1) {
        $self->{sequential_processes}->{pid}->{$current_pid} = $self->{current_host_id} . "_" . $self->{current_trap_id};
        $self->{sequential_processes}->{trap_id}->{$self->{current_host_id} . "_" . $self->{current_trap_id}} = $current_pid;
    }

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
    my $prefix = "";
    if ($self->{centreontrapd_config}->{mode} == 0) {
        $prefix = "EXTERNALCMD:$self->{current_server_id}:";
    }
    
    if ($self->{whoami} eq $self->{centreontrapd_config}->{centreon_user}) {
        $str =~ s/"/\\"/g;
        $submit = '/bin/echo "' . $prefix .  "[$datetime] $str\" >> " . $self->{cmdFile};
    } else {
        $str =~ s/'/'\\''/g;
        $str =~ s/"/\\"/g;
        $submit = "su -l " . $self->{centreontrapd_config}->{centreon_user} . " -c '/bin/echo \"" . $prefix . "[$datetime] $str\" >> " . $self->{cmdFile} . "' 2>&1";
    }
    
    my ($lerror, $stdout) = centreon::common::misc::backtick(command => $submit,
                                                             logger => $self->{logger},
                                                             timeout => $self->{current_alarm_timeout}
                                                             );
    $self->{logger}->writeLogInfo("FORCE: Reschedule linked service");
    $self->{logger}->writeLogInfo("FORCE: Launched command: $submit");
    if (defined($stdout) && $stdout ne "") {
        $self->{logger}->writeLogError("FORCE stdout: $stdout");
    }
}

#######################################
## Submit result via external command
#

sub submitResult_do {
    my $self = shift;
    my $str = $_[0];
    my $datetime = time();
    my $submit;

    my $prefix = "";
    if ($self->{centreontrapd_config}->{mode} == 0) {
        $prefix = "EXTERNALCMD:$self->{current_server_id}:";
    }
    
    if ($self->{whoami} eq $self->{centreontrapd_config}->{centreon_user}) {
        $str =~ s/"/\\"/g;
        $submit = '/bin/echo "' . $prefix . "[$datetime] $str\" >> " . $self->{cmdFile};
    } else {
        $str =~ s/'/'\\''/g;
        $str =~ s/"/\\"/g;
        $submit = "su -l " . $self->{centreontrapd_config}->{centreon_user} . " -c '/bin/echo \"" . $prefix . "[$datetime] $str\" >> " . $self->{cmdFile} . "' 2>&1";
    }
    my ($lerror, $stdout) = centreon::common::misc::backtick(command => $submit,
                                                             logger => $self->{logger},
                                                             timeout => $self->{current_alarm_timeout}
                                                             );
    
    $self->{logger}->writeLogInfo("SUBMIT: Force service status via passive check update");
    $self->{logger}->writeLogInfo("SUBMIT: Launched command: $submit");
    if (defined($stdout) && $stdout ne "") {
        $self->{logger}->writeLogError("SUBMIT RESULT stdout: $stdout");
    }
}

sub submitResult {
    my $self = shift;
    
    my $str = "PROCESS_SERVICE_CHECK_RESULT;$self->{current_hostname};$self->{current_service_desc};" . $self->{traps_global_status} . ";" . $self->{traps_global_output};
    $self->submitResult_do($str);
    
    #####
    # Severity
    #####
    return if (!defined($self->{traps_global_severity_id}) || $self->{traps_global_severity_id} eq ''); 
    $str = "CHANGE_CUSTOM_SVC_VAR;$self->{current_hostname};$self->{current_service_desc};CRITICALITY_ID;" . $self->{traps_global_severity_id};
    $self->submitResult_do($str);
    $str = "CHANGE_CUSTOM_SVC_VAR;$self->{current_hostname};$self->{current_service_desc};CRITICALITY_LEVEL;" . $self->{traps_global_severity_level};
    $self->submitResult_do($str);
}

sub execute_preexec {
    my $self = shift;

    foreach my $row (@{$self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_preexec}}) {
        my $tpe_string = $row->{tpe_string};
        $tpe_string = $self->substitute_string($tpe_string);
        $tpe_string = $self->substitute_centreon_var($tpe_string);
        
        my ($lerror, $output, $exit_code) = centreon::common::misc::backtick(command => $tpe_string,
                                                                             logger => $self->{logger},
                                                                             timeout => $self->{current_alarm_timeout},
                                                                             wait_exit => 1
                                                                            );
        if ($exit_code == -1) {
            $self->{logger}->writeLogError("EXEC prexec: Execution error: $!");
        } elsif (($exit_code >> 8) != 0) {
            $self->{logger}->writeLogInfo("EXEC preexec: Exit command: " . ($exit_code >> 8));
        }
        if (defined($output)) {
            chomp $output;
            push @{$self->{trap_data}->{preexec}}, $output;
            $self->{logger}->writeLogInfo("EXEC preexec: Output : $output");
        } else {
            push @{$self->{trap_data}->{preexec}}, "";
        }
    }
}

sub execute_customcode {
    my ($self, %options) = @_;

    if ($self->{centreontrapd_config}->{secure_mode} == 1) {
        $self->{logger}->writeLogInfo("Cannot exec customcode with secure_mode option to '1'. Need to change the option.");
        return ;
    }
    {
        my $error;
        local $SIG{__DIE__} = sub { $error = $_[0]; };
        eval "$self->{trap_data}->{ref_oids}->{ $self->{trap_data}->{current_trap_id} }->{traps_customcode}";
        if (defined($error)) {
            $self->{logger}->writeLogError("Customcode execution problem: " . $error);
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
    for (my $i=0; $i <= $#{$self->{trap_data}->{entvar}}; $i++) {
        my $x = $i + 1;
        $str =~ s/\@\{${$self->{trap_data}->{entvarname}}[$i]\}/${$self->{trap_data}->{entvar}}[$i]/g;
        $str =~ s/\$$x([^0-9]|$)/${$self->{trap_data}->{entvar}}[$i]$1/g;
    }
    
    # Substitute preexec var
    for (my $i=0; $i <= $#{$self->{trap_data}->{preexec}}; $i++) {
        my $x = $i + 1;
        $str =~ s/\$p$x([^0-9]|$)/${$self->{trap_data}->{preexec}}[$i]$1/g;
    }

    # Substitute $*
    my $sub_str = join($self->{centreontrapd_config}->{separator}, @{$self->{trap_data}->{entvar}});
    $str =~ s/\$\*/$sub_str/g;
    
    # $A
    $str =~ s/\$A/$self->{trap_data}->{agent_dns_name}/g;
    
    # $aA (Trap agent IP Adress)
    $str =~ s/\$aA/${$self->{trap_data}->{var}}[4]/g;
    
    # $R, $r (Trap Hostname)
    $str =~ s/\$R/${$self->{trap_data}->{var}}[0]/g;
    $str =~ s/\$r/${$self->{trap_data}->{var}}[0]/g;
    
    # $aR, $ar (IP Adress)
    $str =~ s/\$aR/${$self->{trap_data}->{var}}[1]/g;
    $str =~ s/\$ar/${$self->{trap_data}->{var}}[1]/g;
    
    # Clean OID
    $str =~ s/\@\{[\.0-9]*\}//g;
    return $str;
}

sub substitute_centreon_var {
    my $self = shift;
    my $str = $_[0];

    $str =~ s/\@HOSTNAME\@/$self->{current_hostname}/g;
    $str =~ s/\@HOSTADDRESS\@/$self->{current_ip}/g;
    $str =~ s/\@HOSTADDRESS2\@/$self->{trap_data}->{agent_dns_name}/g;
    $str =~ s/\@SERVICEDESC\@/$self->{current_service_desc}/g;
    $str =~ s/\@TRAPOUTPUT\@/$self->{traps_global_output}/g;
    $str =~ s/\@OUTPUT\@/$self->{traps_global_output}/g;
    $str =~ s/\@STATUS\@/$self->{traps_global_status}/g;
    $str =~ s/\@SEVERITYNAME\@/$self->{traps_global_severity_name}/g;
    $str =~ s/\@SEVERITYLEVEL\@/$self->{traps_global_severity_level}/g;
    $str =~ s/\@TIME\@/$self->{trap_data}->{trap_date_time_epoch}/g;
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
    foreach my $row (@{$self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_matching_properties}}) {
        my $tmoString = $row->{tmo_string};
        my $regexp = $row->{tmo_regexp};
        my $tmoStatus = $row->{tmo_status};
        my $severity_level = $row->{level};
        my $severity_name = $row->{sc_name};
        my $severity_id = $row->{sc_id};
        
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
            $tmoString = HTML::Entities::decode_entities($tmoString);
        } else {
            $tmoString =~ s/\&quot\;/\"/g;
            $tmoString =~ s/\&#039\;\&#039\;/"/g;
        }

        # Integrate OID Matching            
        if (defined($tmoString) && $tmoString =~ m/$regexp/g) {
            $self->{traps_global_status} = $tmoStatus;
            $self->{traps_global_severity_name} = $severity_name;
            $self->{traps_global_severity_level} = $severity_level;
            $self->{traps_global_severity_id} = $severity_id;
            $self->{logger}->writeLogInfo("Regexp: String:$tmoString => REGEXP:$regexp");
            $self->{logger}->writeLogInfo("Status: $self->{traps_global_status} ($tmoStatus)");
            $self->{logger}->writeLogInfo("Severity id: " . (defined($self->{traps_global_severity_id}) ? $self->{traps_global_severity_id} : "null"));
            $self->{logger}->writeLogInfo("Severity name: " . (defined($self->{traps_global_severity_name}) ? $self->{traps_global_severity_name} : "null"));
            $self->{logger}->writeLogInfo("Severity level: " . (defined($self->{traps_global_severity_level}) ? $self->{traps_global_severity_level} : "null"));
            $matching_boolean = 1;
            last;
        }    
    }
    
    # Dont do submit if no matching
    if ($matching_boolean == 0 && $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_advanced_treatment_default} == 1) {
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
    my $traps_execution_command = $self->{trap_data}->{ref_oids}->{ $self->{current_trap_id} }->{traps_execution_command};
    
    $traps_execution_command = $self->substitute_string($traps_execution_command);
    
    ##########################
    # REPLACE MACROS
    if ($self->{htmlentities} == 1) {
        $traps_execution_command = HTML::Entities::decode_entities($traps_execution_command);
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
    
        my ($lerror, $output, $exit_code) = centreon::common::misc::backtick(command => $traps_execution_command,
                                                                             logger => $self->{logger},
                                                                             timeout => $self->{current_alarm_timeout},
                                                                             wait_exit => 1
                                                                            );
        if ($exit_code == -1) {
            $self->{logger}->writeLogError("EXEC: Execution error: $!");
        } elsif (($exit_code >> 8) != 0) {
            $self->{logger}->writeLogInfo("EXEC: Exit command: " . ($exit_code >> 8));
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
    
    ### Get OIDS 
    ($fstatus, $self->{trap_data}->{ref_oids}) = centreon::trapd::lib::get_oids($self->{cdb}, ${$self->{trap_data}->{var}}[3]);
    return 0 if ($fstatus == -1);
    foreach my $trap_id (keys %{$self->{trap_data}->{ref_oids}}) {
        $self->{trap_data}->{current_trap_id} = $trap_id;

        ($fstatus, $self->{trap_data}->{ref_hosts}) = centreon::trapd::lib::get_hosts(logger => $self->{logger},
                                                                 cdb => $self->{cdb},
                                                                 trap_info => $self->{trap_data}->{ref_oids}->{$trap_id},
                                                                 agent_dns_name => $self->{trap_data}->{agent_dns_name},
                                                                 ip_address => ${$self->{trap_data}->{var}}[1],
                                                                 centreontrapd => $self);
        return 0 if ($fstatus == -1);
        foreach my $host_id (keys %{$self->{trap_data}->{ref_hosts}}) {
            if (!defined($self->{trap_data}->{ref_hosts}->{$host_id}->{nagios_server_id})) {
                $self->{logger}->writeLogError("Cant get server associated for host '" . $self->{ref_hosts}->{$host_id}->{host_name} . "'");
                next;
            }
            $self->{trap_data}->{current_host_id} = $host_id;
            
            #### Get Services ####
            ($fstatus, $self->{trap_data}->{ref_services}) = centreon::trapd::lib::get_services($self->{cdb}, $trap_id, $host_id);
            return 0 if ($fstatus == -1);
            
            #### Check Host and Services downtimes ###
            if (defined($self->{trap_data}->{ref_oids}->{$trap_id}->{traps_downtime}) && 
                $self->{trap_data}->{ref_oids}->{$trap_id}->{traps_downtime} ne '' && $self->{trap_data}->{ref_oids}->{$trap_id}->{traps_downtime} > 0 &&
                $self->{centreontrapd_config}->{mode} == 0) {
                ($fstatus) = centreon::trapd::lib::check_downtimes($self->{csdb}, 
                                                                   $self->{trap_data}->{ref_oids}->{$trap_id}->{traps_downtime},
                                                                   $self->{trap_data}->{trap_date_time_epoch},
                                                                   $host_id,
                                                                   $self->{trap_data}->{ref_services},
                                                                   $self->{logger});
                return 0 if ($fstatus == -1);
                # Host in downtime - If no services anymore, condition will match it.
                next if ($fstatus == 1);
            }
            
            #### If none, we stop ####
            my $size = keys %{$self->{trap_data}->{ref_services}};
            if ($size < 1) {
                $self->{logger}->writeLogDebug("Trap without service associated for host " . $self->{trap_data}->{ref_hosts}->{$host_id}->{host_name} . ". Skipping...");
                next;
            }
            
            #### Check if macro $_HOST*$ needed
            $self->{trap_data}->{ref_macro_hosts} = undef;
            if (defined($self->{trap_data}->{ref_oids}->{$trap_id}->{traps_execution_command_enable}) && $self->{trap_data}->{ref_oids}->{$trap_id}->{traps_execution_command_enable} == 1 &&
                defined($self->{trap_data}->{ref_oids}->{$trap_id}->{traps_execution_command}) && $self->{trap_data}->{ref_oids}->{$trap_id}->{traps_execution_command} =~ /\$_HOST.*?\$/) {
                ($fstatus, $self->{trap_data}->{ref_macro_hosts}) = centreon::trapd::lib::get_macros_host($self->{cdb}, $host_id);
                return 0 if ($fstatus == -1);
            }
            
            # Eval custom code
            if (defined($self->{trap_data}->{ref_oids}->{$trap_id}->{traps_customcode}) && 
                $self->{trap_data}->{ref_oids}->{$trap_id}->{traps_customcode} ne '') {
                $self->execute_customcode();
            }
            
            foreach my $service_id (keys %{$self->{trap_data}->{ref_services}}) {
                $self->{trap_data}->{current_service_id} = $service_id;
                $self->{logger}->writeLogDebug("Trap found on service '" . 
                                        $self->{trap_data}->{ref_services}->{$service_id}->{service_description} . 
                                        "' for host '" . 
                                        $self->{trap_data}->{ref_hosts}->{$host_id}->{host_name} . "'.");
                # Routing filter service
                if ($self->{trap_data}->{ref_oids}->{$trap_id}->{traps_routing_mode} == 1 &&
                    defined($self->{trap_data}->{ref_oids}->{$trap_id}->{traps_routing_filter_services}) && 
                    $self->{trap_data}->{ref_oids}->{$trap_id}->{traps_routing_filter_services} ne '') {
                    my $search_str = $self->substitute_string($self->{trap_data}->{ref_oids}->{$trap_id}->{traps_routing_filter_services});
                    if ($self->{trap_data}->{ref_services}->{$service_id}->{service_description} ne $search_str) {
                        $self->{logger}->writeLogDebug("Skipping trap for service '" . 
                                                        $self->{trap_data}->{ref_services}->{$service_id}->{service_description} . 
                                                        "' for host '" . 
                                                        $self->{trap_data}->{ref_hosts}->{$host_id}->{host_name} . "' (match: $search_str).");
                        next;
                    }
                }
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
        $self->{csdb} = centreon::common::db->new(db => $self->{centreon_config}->{centstorage_db},
                                                 host => $self->{centreon_config}->{db_host},
                                                 port => $self->{centreon_config}->{db_port},
                                                 user => $self->{centreon_config}->{db_user},
                                                 password => $self->{centreon_config}->{db_passwd},
                                                 force => 0,
                                                 logger => $self->{logger});
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
            if (! -w $self->{centreontrapd_config}->{spool_directory} . '/' . $file) {
                $self->{logger}->writeLogError("Dont have write permission on '" . $self->{centreontrapd_config}->{spool_directory} . '/' . $file . "' file.");
                if ($self->{centreontrapd_config}->{policy_trap} == 1) {
                    unshift @{$self->{filenames}}, $file;
                    # We're waiting. We are in a loop
                    sleep $self->{centreontrapd_config}->{sleep};
                    next;
                }
            }
            
            ### Check pool finish and check old ones
            $self->manage_pool(1);
            $self->check_sequential_todo();
            
            if (open FILE, $self->{centreontrapd_config}->{spool_directory} . '/' . $file) {
                my $unlink_trap = 1;
                my $trap_is_a_duplicate = 0;
                my $readtrap_result = centreon::trapd::lib::readtrap(logger => $self->{logger},
                                                                     config => $self->{centreontrapd_config},
                                                                     handle => \*FILE,
                                                                     agent_dns_name => \$self->{trap_data}->{agent_dns_name},
                                                                     trap_date => \$self->{trap_data}->{trap_date},
                                                                     trap_time => \$self->{trap_data}->{trap_time},
                                                                     trap_date_time => \$self->{trap_data}->{trap_date_time},
                                                                     trap_date_time_epoch => \$self->{trap_data}->{trap_date_time_epoch},
                                                                     duplicate_traps => \%{$self->{duplicate_traps}},
                                                                     digest_trap => \$self->{digest_trap},
                                                                     var => \@{$self->{trap_data}->{var}},
                                                                     entvar => \@{$self->{trap_data}->{entvar}},
                                                                     entvarname => \@{$self->{trap_data}->{entvarname}});
                
                if ($readtrap_result == 1) {
                    if (centreon::trapd::lib::check_known_trap(logger => $self->{logger},
                                                               logger_unknown => $self->{logger_unknown},
                                                               config => $self->{centreontrapd_config},
                                                               trap_data => $self->{trap_data},
                                                               oid2verif => ${$self->{trap_data}->{var}}[3],      
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
                    unless (unlink($self->{centreontrapd_config}->{spool_directory} . '/' . $file)) {
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
                $self->{logger}->writeLogError("Could not open trap file " . $self->{centreontrapd_config}->{spool_directory} . '/' . "$file: ($!)");
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
        
        ### Check pool finish and check old ones
        $self->manage_pool(1);
        $self->check_sequential_todo();
    }
}

1;

__END__
