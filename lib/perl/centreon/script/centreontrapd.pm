
package centreon::script::centreontrapd;

use strict;
use warnings;
use centreon::script;
use centreon::common::db;
use centreon::trapd::lib;

use base qw(centreon::script);
use vars qw(%centreontrapd_config);

my %handlers = ('TERM' => {}, 'HUP' => {});

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

    my %centreontrapd_default_config =
      (
       daemon => 0,
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
       cache_unknown_traps_file => "/tmp/centreontrapd.cache",
       mode => 0,
       cmdFile => "/var/lib/centreon/centcore.cmd",
       cmd_timeout => 10,
       centreon_user => "centreon"
    );
   
    $self->{htmlentities} = 0;
    @{$self->{var}} = undef;                      # Variables of trap received by SNMPTRAPD
    @{$self->{entvar}} = undef;                   # Enterprise variable values of trap received by SNMPTRAPD
    @{$self->{entvarname}} = undef;               # Enterprise variable names of trap received by SNMPTRAPD
    $self->{agent_dns_name} = undef;
    $self->{trap_date} = undef;
    $self->{trap_time} = undef;
    $self->{trap_date_time} = undef;
    $self->{trap_date_time_epoch} = undef;
    %{$self->{duplicate_traps}} = ();
    $self->{timetoreload} = 0;
    $self->{timetodie} = 0;
    @{$self->{filenames}} = undef;
    $self->{oids_cache} = undef;
    $self->{last_cache_time} = undef;
    $self->{whoami} = undef;

    $self->{cmdFile} = undef;
    
    # redefine to avoid out when we try modules
    $SIG{__DIE__} = undef;
    return $self;
}

sub init {
    my $self = shift;
    
    if (!defined($self->{opt_extra})) {
        $self->{opt_extra} = "/etc/centreon/centreontrapd.pm";
    }
    if (-f $self->{opt_extra}) {
        require $self->{opt_extra};
    } else {
        $self->{logger}->writeLogInfo("Can't find extra config file $self->{opt_extra}");
    }

    $self->{centreontrapd_config} = {%centreontrapd_default_config, %centreontrapd_config};
    
    # Daemon Only
    if ($self->{centreontrapd_config}->{daemon} == 1) {
        $self->set_signal_handlers;
    }
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{TERM}->{$self} = sub { $self->handle_TERM() };
    $SIG{HUP} = \&class_handle_HUP;
    $handlers{HUP}->{$self} = sub { $self->handle_HUP() };
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

sub handle_TERM {
    my $self = shift;
    $self->{timetodie} = 1;
}

sub handle_HUP {
    my $self = shift;
    $self->{timetoreload} = 1;
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

###############################
## Execute a command Nagios or Centcore
#
sub send_command {
    my $self = shift;

    eval {
        local $SIG{ALRM} = sub { die "TIMEOUT"; };
        alarm($self->{centreontrapd_config}->{cmd_timeout});
        system @_;
        alarm(0);
    };
    if ($@) {
        if ($@ =~ "TIMEOUT") {
            $self->{logger}->writeLogError("ERROR: Send command timeout");
            return 0;
        }
    }
    return 1;
}

###############################
## GET HOSTNAME FROM IP ADDRESS
#
sub get_hostinfos($$) {
    my $self = shift;
    my %host = ();

    my ($status, $sth) = $self->{cdb}->query("SELECT host_id, host_name FROM host WHERE host_address='$_[0]' OR host_address='$_[1]'");
    while (my ($host_id, $host_name) = $sth->fetchrow_array()) {
        $host{$host_id} = $host_name;
    }
    return %host;
}

###############################
## GET host location
#
sub get_hostlocation($) {
    my $self = shift;

    my ($status, $sth) = $self->{cdb}->query("SELECT localhost FROM host, `ns_host_relation`, nagios_server WHERE host.host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = nagios_server.id AND host.host_name = '".$_[0]."'");
    if ($sth->rows()){
        my $temp = $sth->fetchrow_array();
        $sth->finish();
        return $temp;
    } else {
        return 0;
    }
}

##################################
## GET nagios server id for a host
#
sub get_hostNagiosServerID($) {
    my $self = shift;

    my ($status, $sth) = $self->{cdb}->query("SELECT id FROM host, `ns_host_relation`, nagios_server WHERE host.host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = nagios_server.id AND (host.host_name = '".$_[0]."' OR host.host_address = '".$_[0]."')");
    if ($sth->rows()){
        my $temp = $sth->fetchrow_array();
        $sth->finish();
        return $temp;
    } else {
        return 0;
    }
}

#####################################################################
## GET SERVICES FOR GIVEN HOST (GETTING SERVICES TEMPLATES IN ACCOUNT)
#
sub getServicesIncludeTemplate($$$) {
    my $self = shift;
    my $status;
    my ($sth_st, $host_id, $trap_id) = @_;
    my @service;
    
    while (my @temp = $sth_st->fetchrow_array()) {
        my $tr_query = "SELECT `traps_id` FROM `traps_service_relation` WHERE `service_id` = '".$temp[0]."' AND `traps_id` = '".$trap_id."'";
        ($status, my $sth_st3) = $self->{cdb}->query($tr_query);
        my @trap = $sth_st3->fetchrow_array();
        if (defined($trap[0])) {
            $service[scalar(@service)] = $temp[1];
        } else {
            if (defined($temp[2])) {
                my $found = 0;
                my $service_template = $temp[2];
                while (!$found) {
                    my $st1_query = "SELECT `service_id`, `service_template_model_stm_id`, `service_description` FROM service s WHERE `service_id` = '".$service_template."'";
                    ($status, my $sth_st1) = $self->{cdb}->query($st1_query);
                    my @st1_result = $sth_st1->fetchrow_array();
                    if (defined($st1_result[0])) {
                        ($status, my $sth_st2) = $self->{cdb}->query("SELECT `traps_id` FROM `traps_service_relation` WHERE `service_id` = '".$service_template."' AND `traps_id` = '".$trap_id."'");
                        my @st2_result = $sth_st2->fetchrow_array();
                        if (defined($st2_result[0])) {
                            $found = 1;
                            $service[scalar(@service)] = $temp[1];
                        } else {
                            $found = 1;
                            if (defined($st1_result[1]) && $st1_result[1]) {
                                $service_template = $st1_result[1];
                                $found = 0;
                            }
                        }
                        $sth_st2->finish;            
                    }
                    $sth_st1->finish;
                }
            }
        }
        $sth_st3->finish;
    }
    return (@service);
}



##########################
# GET SERVICE DESCRIPTION
#
sub getServiceInformations($$)    {
    my $self = shift;
    my $status;
    
    ($status, my $sth) = $self->{cdb}->query("SELECT `traps_id`, `traps_status`, `traps_submit_result_enable`, `traps_execution_command`, `traps_reschedule_svc_enable`, `traps_execution_command_enable`, `traps_advanced_treatment`, `traps_args` FROM `traps` WHERE `traps_oid` = '$_[0]'");
    my ($trap_id, $trap_status, $traps_submit_result_enable, $traps_execution_command, $traps_reschedule_svc_enable, $traps_execution_command_enable, $traps_advanced_treatment, $traps_output) = $sth->fetchrow_array();
    return(undef) if (!defined $trap_id);
    $sth->finish();

    ######################################################
    # getting all "services by host" for given host
    my $st_query = "SELECT s.service_id, service_description, service_template_model_stm_id FROM service s, host_service_relation h";
    $st_query .= " where  s.service_id = h.service_service_id and h.host_host_id='$_[1]'";
    ($status, my $sth_st) = $self->{cdb}->query($st_query); 
    my @service = $self->getServicesIncludeTemplate($sth_st, $_[1], $trap_id);
    $sth_st->finish;

    ######################################################
    # getting all "services by hostgroup" for given host
    my $query_hostgroup_services = "SELECT s.service_id, service_description, service_template_model_stm_id FROM hostgroup_relation hgr,  service s, host_service_relation hsr";
    $query_hostgroup_services .= " WHERE hgr.host_host_id = '".$_[1]."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id";
    $query_hostgroup_services .= " AND s.service_id = hsr.service_service_id";
    ($status, $sth_st) = $self->{cdb}->query($query_hostgroup_services);
    @service = (@service, $self->getServicesIncludeTemplate($sth_st, $_[1], $trap_id));
    $sth_st->finish;

    return $trap_id, $trap_status, $traps_submit_result_enable, $traps_execution_command, $traps_reschedule_svc_enable, $traps_execution_command_enable, $traps_advanced_treatment, $traps_output, \@service;
}

######################################
## Force a new check for selected services
#
sub forceCheck($$$) {
    my $self = shift;
    my ($this_host, $this_service, $datetime) = @_;
    my $result;

    my $id = $self->get_hostNagiosServerID($this_host);
    if (defined($id) && $id != 0) {
        my $submit;
        
        if ($self->{whoami} eq $self->{centreontrapd_config}->{centreon_user}) {
            $submit = "/bin/echo \"EXTERNALCMD:$id:[$datetime] SCHEDULE_FORCED_SVC_CHECK;$this_host;$this_service;$datetime\" >> " . $self->{centreontrapd_config}->{cmdFile};
        } else {
            $submit = "su -l " . $self->{centreontrapd_config}->{centreon_user} . " -c '/bin/echo \"EXTERNALCMD:$id:[$datetime] SCHEDULE_FORCED_SVC_CHECK;$this_host;$this_service;$datetime\" >> " . $self->{centreontrapd_config}->{cmdFile} . "'";
        }
        $result = $self->send_command($submit);
    
        $self->{logger}->writeLogInfo("FORCE: Reschedule linked service");
        $self->{logger}->writeLogInfo("FORCE: Launched command: $submit");
    }
    return $result;
}

#######################################
## Submit result via external command
#
sub submitResult($$$$$) {
    my $self = shift;
    my ($this_host, $this_service, $datetime, $status, $traps_output) = @_;
    my $result;

    # No matching rules
    my $id = $self->get_hostNagiosServerID($this_host);
    if (defined($id) && $id != 0) {
        my $str = "PROCESS_SERVICE_CHECK_RESULT;$this_host;$this_service;$status;$traps_output";

        my $submit;
        if ($self->{whoami} eq $self->{centreontrapd_config}->{centreon_user}) {
            $str =~ s/"/\\"/g;
            $submit = "/bin/echo \"EXTERNALCMD:$id:[$datetime] $str\" >> " . $self->{centreontrapd_config}->{cmdFile};
        } else {
            $str =~ s/'/'\\''/g;
            $str =~ s/"/\\"/g;
            $submit = "su -l " . $self->{centreontrapd_config}->{centreon_user} . " -c '/bin/echo \"EXTERNALCMD:$id:[$datetime] $str\" >> " . $self->{centreontrapd_config}->{cmdFile} . "'";
        }
        $result = $self->send_command($submit);
    
        $self->{logger}->writeLogInfo("SUBMIT: Force service status via passive check update");
        $self->{logger}->writeLogInfo("SUBMIT: Launched command: $submit");
    }
    return $result;
}

##########################
## REPLACE
#
sub substitute_string {
    my $self = shift;
    my $str = $_[0];
    
    # Substitute @{oid_value} and $1, $2,...
    for (my $i=0; $i <= $#{$self->{entvar}}; $i++) {
        my $x = $i + 1;
        $str =~ s/\@\{${$self->{entvarname}}[$i]\}/${$self->{entvar}}[$i]/g;
        $str =~ s/\$$x(\s|$)/${$self->{entvar}}[$i]/g;
    }
    
    # Substitute $*
    my $sub_str = join($self->{centreontrapd_config}->{seperator}, @{$self->{entvar}});
    $str =~ s/\$\*/$sub_str/g;
    
    # Clean OID
    $str =~ s/\@\{[\.0-9]*\}//g;
    return $str;
}

#######################################
## Check Advanced Matching Rules
#
sub checkMatchingRules($$$$$$$$$) {
    my $self = shift;
    my ($trap_id, $this_host, $this_service, $ip, $hostname, $traps_output, $datetime, $status) = @_;
    
    # Check matching options 
    my ($dstatus, $sth) = $self->{cdb}->query("SELECT tmo_regexp, tmo_status, tmo_string FROM traps_matching_properties WHERE trap_id = '".$trap_id."' ORDER BY tmo_order");
    while (my ($regexp, $tmoStatus, $tmoString) = $sth->fetchrow_array()) {
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

        $tmoString = substitute_string($tmoString);

        ##########################
        # REPLACE special Chars
        if ($self->{htmlentities} == 1) {
            $tmoString = decode_entities($tmoString);
        } else {
            $tmoString =~ s/\&quot\;/\"/g;
            $tmoString =~ s/\&#039\;\&#039\;/"/g;
        }
        $tmoString =~ s/\@HOSTNAME\@/$this_host/g;
        $tmoString =~ s/\@HOSTADDRESS\@/$ip/g;
        $tmoString =~ s/\@HOSTADDRESS2\@/$hostname/g;
        $tmoString =~ s/\@SERVICEDESC\@/$this_service/g;
        $tmoString =~ s/\@TRAPOUTPUT\@/$traps_output/g;
        $tmoString =~ s/\@OUTPUT\@/$traps_output/g;
        $tmoString =~ s/\@TIME\@/$datetime/g;

        # Integrate OID Matching            
        if (defined($tmoString) && $tmoString =~ m/$regexp/g) {
            $status = $tmoStatus;
            $self->{logger}->writeLogInfo("Regexp: String:$tmoString => REGEXP:$regexp");
            $self->{logger}->writeLogInfo("Status: $status ($tmoStatus)");
            last;
        }    
    }
    return $status;
}

################################
## Execute a specific command
#
sub executeCommand($$$$$$$$) {
    my $self = shift;
    my ($traps_execution_command, $this_host, $this_service, $ip, $hostname, $traps_output, $datetime, $status) = @_;
    
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
    $traps_execution_command =~ s/\@HOSTNAME\@/$this_host/g;
    $traps_execution_command =~ s/\@HOSTADDRESS\@/$_[1]/g;
    $traps_execution_command =~ s/\@HOSTADDRESS2\@/$_[2]/g;
    $traps_execution_command =~ s/\@SERVICEDESC\@/$this_service/g;
    $traps_execution_command =~ s/\@TRAPOUTPUT\@/$traps_output/g;
    $traps_execution_command =~ s/\@OUTPUT\@/$traps_output/g;
    $traps_execution_command =~ s/\@STATUS\@/$status/g;
    $traps_execution_command =~ s/\@TIME\@/$datetime/g;

    ##########################
    # SEND COMMAND
    if ($traps_execution_command) {
        $self->{logger}->writeLogInfo("EXEC: Launch specific command");
        $self->{logger}->writeLogInfo("EXEC: Launched command: $traps_execution_command");
    
        my $output = `$traps_execution_command`;
        if ($?) {
            $self->{logger}->writeLogError("EXEC: Execution error: $!");
        }
        if ($output) {
            $self->{logger}->writeLogInfo("EXEC: Output : $output");
        }
    }
}


#######################################
## GET HOSTNAME AND SERVICE DESCRIPTION
#
sub getTrapsInfos($$$) {
    my $self = shift;
    my $ip = shift;
    my $hostname = shift;
    my $oid = shift;
    
    my $status;

    my %host = $self->get_hostinfos($ip, $hostname);
    foreach my $host_id (keys %host) {
        my $this_host = $host{$host_id};
        my ($trap_id, $status, $traps_submit_result_enable, $traps_execution_command, $traps_reschedule_svc_enable, $traps_execution_command_enable, $traps_advanced_treatment, $traps_output, $ref_servicename) = $self->getServiceInformations($oid, $host_id);
        if (!defined($trap_id)) {
            return ;
        }
        my @servicename = @{$ref_servicename};
    
        foreach (@servicename) {
            my $this_service = $_;

            $self->{logger}->writeLogDebug("Trap found on service \'$this_service\' for host \'$this_host\'.");

            my $datetime = `date +%s`;
            chomp($datetime);

            $traps_output = $self->substitute_string($traps_output);

            ######################################################################
            # Advanced matching rules
            if (defined($traps_advanced_treatment) && $traps_advanced_treatment eq 1) {
                $status = $self->checkMatchingRules($trap_id, $this_host, $this_service, $ip, $hostname, $traps_output, $datetime, $status);
            }

            #####################################################################
            # Submit value to passive service
            if (defined($traps_submit_result_enable) && $traps_submit_result_enable eq 1) { 
                $self->submitResult($this_host, $this_service, $datetime, $status, $traps_output);
            }

            ######################################################################
            # Force service execution with external command
            if (defined($traps_reschedule_svc_enable) && $traps_reschedule_svc_enable eq 1) {
                $self->forceCheck($this_host, $this_service, $datetime);
            }
        
            ######################################################################
            # Execute special command
            if (defined($traps_execution_command_enable) && $traps_execution_command_enable) {
                $self->executeCommand($traps_execution_command, $this_host, $this_service, $ip, $hostname, $traps_output, $datetime, $status);
            }
        }
    }
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->init();
    $self->{logger}->redirect_output();

    ($self->{centreontrapd_config}->{date_format}, $self->{centreontrapd_config}->{time_format}) = 
                                    centreon::trapd::lib::manage_params_conf($self->{centreontrapd_config}->{date_format},
                                                                             $self->{centreontrapd_config}->{time_format});
    centreon::trapd::lib::init_modules(logger => $self->{logger}, config => $self->{centreontrapd_config}, htmlentities => \$self->{htmlentities});
    
    $self->{logger}->writeLogDebug("centreontrapd launched....");
    $self->{logger}->writeLogDebug("PID: $$");

    $self->{cdb} = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                             host => $self->{centreon_config}->{db_host},
                                             port => $self->{centreon_config}->{db_port},
                                             user => $self->{centreon_config}->{db_user},
                                             password => $self->{centreon_config}->{db_passwd},
                                             force => 0,
                                             logger => $self->{logger});
    if ($self->{centreontrapd_config}->{mode} == 0) {
        $self->{cmdFile} = $self->{centreon_config}->{cmdFile};
    } else {
        # Dirty!!! Need to know the poller
        my ($status, $sth) = $self->{cdb}->query("SELECT `command_file` FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
        my @conf = $sth->fetchrow_array();
        $self->{cmdFile} = $conf[0];
    }
    $self->{whoami} = getpwuid($<);

    if ($self->{centreontrapd_config}->{daemon} == 1) {
        while (!$self->{timetodie}) {
            centreon::trapd::lib::purge_duplicate_trap(config => $self->{centreontrapd_config},
                                                       duplicate_traps => \%{$self->{duplicate_traps}});
            while ((my $file = centreon::trapd::lib::get_trap(logger => $self->{logger}, 
                                                              config => $self->{centreontrapd_config},
                                                              filenames => \@{$self->{filenames}}))) {
                $self->{logger}->writeLogDebug("Processing file: $file");
                
                if (open FILE, $self->{centreontrapd_config}->{spool_directory} . $file) {
                    my $trap_is_a_duplicate = 0;
                    my $readtrap_result = centreon::trapd::lib::readtrap(logger => $self->{logger},
                                                                         config => $self->{centreontrapd_config},
                                                                         handle => \*FILE,
                                                                         agent_dns => \$self->{agent_dns},
                                                                         trap_date => \$self->{trap_date},
                                                                         trap_time => \$self->{trap_time},
                                                                         trap_date_time => \$self->{trap_date_time},
                                                                         trap_date_time_epoch => \$self->{trap_date_time_epoch},
                                                                         duplicate_traps => \%{$self->{duplicate_traps}},
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
                            $self->getTrapsInfos(${$self->{var}}[1], ${$self->{var}}[2], ${$self->{var}}[3]);
                        }
                    } elsif ($readtrap_result == 0) {
                        $self->{logger}->writeLogDebug("Error processing trap file $file.  Skipping...");
                    } elsif ($readtrap_result == -1) {
                        $trap_is_a_duplicate = 1;
                        $self->{logger}->writeLogInfo("Duplicate trap detected in trap file $file.  Skipping...");
                    }
                    
                    close FILE;
                    unless (unlink($file)) {
                        $self->{logger}->writeLogError("Unable to delete trap file $file from spool dir:$!");
                    }  
                } else {
                    $self->{logger}->writeLogError("Could not open trap file " . $self->{centreontrapd_config}->{spool_directory} . "$file: ($!)");
                }
            }
            
            $self->{logger}->writeLogDebug("Sleeping for " . $self->{centreontrapd_config}->{sleep} . " seconds");
            sleep $self->{centreontrapd_config}->{sleep};
                    
            if ($self->{timetoreload} == 1) {
                $self->{logger}->writeLogDebug("Reloading configuration file");
                $self->reload_config($self->{opt_extra});
                ($self->{centreontrapd_config}->{date_format}, $self->{centreontrapd_config}->{time_format}) = 
                                    centreon::trapd::lib::manage_params_conf($self->{centreontrapd_config}->{date_format},
                                                                             $self->{centreontrapd_config}->{time_format});
                centreon::trapd::lib::init_modules();
                centreon::trapd::lib::get_cache_oids();
                $self->{timetoreload} = 0;
            }
        }
    } else {
        my $readtrap_result = centreon::trapd::lib::readtrap(logger => $self->{logger},
                                                             config => $self->{centreontrapd_config},
                                                             handle => \*STDIN,
                                                             agent_dns => \$self->{agent_dns},
                                                             trap_date => \$self->{trap_date},
                                                             trap_time => \$self->{trap_time},
                                                             trap_date_time => \$self->{trap_date_time},
                                                             trap_date_time_epoch => \$self->{trap_date_time_epoch},
                                                             duplicate_traps => \%{$self->{duplicate_traps}},
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
                $self->getTrapsInfos(${$self->{var}}[1], ${$self->{var}}[2], ${$self->{var}}[3]);
            }
        } elsif ($readtrap_result == 0) {
            $self->{logger}->writeLogDebug("Error processing trap file.  Skipping...");
        }
    }
}

1;

__END__
