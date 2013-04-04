
package centreon::script::centstorage;

use strict;
use warnings;
use POSIX;
use IO::Select;
use IO::Handle;
use centreon::script;
use centreon::common::db;
use centreon::common::misc;
use centreon::centstorage::CentstorageLib;
use centreon::centstorage::CentstoragePool;
use centreon::centstorage::CentstoragePerfdataFile;
use centreon::centstorage::CentstorageAction;
use centreon::centstorage::CentstorageAction;
use centreon::centstorage::CentstorageRRD;

use base qw(centreon::script);
use vars qw(%centstorage_config);

my %handlers = ('TERM' => {}, 'CHLD' => {}, 'DIE' => {}, 'HUP' => {});

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centstorage",
        centreon_db_conn => 0,
        centstorage_db_conn => 0
    );

    bless $self, $class;
    $self->add_options(
        "config-extra" => \$self->{opt_extra},
    );

    %{$self->{pool_pipes}} = ();
    %{$self->{return_child}} = ();
    %{$self->{routing_services}} = ();
    $self->{roundrobin_pool_current} = 0;
    $self->{read_select} = undef;
    $self->{pid_delete_child} = undef;
    %{$self->{delete_pipes}} = ();
    %{$self->{fileno_save_read}} = ();
    $self->{centreon_db_centreon} = undef;
    $self->{centstorage_perfdata_file} = undef;

    # When you lost a pool: to say that a rebuild in progress
    $self->{rebuild_progress} = 0;
    $self->{rebuild_pool_choosen} = 0;

    # reload flag
    $self->{reload} = 1;
    
    %{$self->{centstorage_default_config}} =
      (
       pool_childs => 4,
       TIMEOUT => 60,
       rrd_cache_mode => 0,
       rrd_flush_time => 60 * 10,
       centreon_23_compatibility => 0,
       perfdata_parser_stop => 1
    );

    $self->set_signal_handlers;

    return $self;
}

sub init {
    my $self = shift;
    $self->SUPER::init();

    if (!defined($self->{opt_extra})) {
        $self->{opt_extra} = "/etc/centreon/centstorage.pm";
    }
    if (-f $self->{opt_extra}) {
        require $self->{opt_extra};
    } else {
        $self->{logger}->writeLogInfo("Can't find extra config file $self->{opt_extra}");
    }
    $self->{centstorage_config} = {%{$self->{centstorage_default_config}}, %centstorage_config};
}

sub reload {
    my $self = shift;
    
    $self->{logger}->writeLogInfo("Reload in progress for main process...");
    # reopen file
    if (defined($self->{logger}->is_file_mode())) {
        $self->{logger}->file_mode($self->{logger}->{file_name});
    }
    $self->{logger}->redirect_output();
    
    centreon::common::misc::reload_db_config($self->{logger}, $self->{config_file}, $self->{centreon_db_centreon});
    centreon::common::misc::check_debug($self->{logger}, "debug_centstorage", $self->{centreon_db_centreon}, "centstorage main process");
    
    # Not needed anymore
    $self->{centreon_db_centreon}->disconnect();
    
    # Send HUP to childs
    for (my $i = 0; $i < $self->{centstorage_config}->{pool_childs}; $i++) {
        if ($self->{pool_pipes}{$i}->{'running'} == 1) {
            kill('HUP', $self->{pool_pipes}{$i}->{'pid'});
            $self->{logger}->writeLogInfo("Send -HUP signal to pool process..");
        }
    }
    if ($self->{delete_pipes}{'running'} == 1) {
        kill('HUP', $self->{pid_delete_child});
        $self->{logger}->writeLogInfo("Send -HUP signal to delete process..");
    }

    $self->{reload} = 1;
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{TERM}->{$self} = sub { $self->handle_TERM() };
    $SIG{__DIE__} = \&class_handle_DIE;
    $handlers{DIE}->{$self} = sub { $self->handle_DIE($_[0]) };
    $SIG{CHLD} = \&class_handle_CHLD;
    $handlers{CHLD}->{$self} = sub { $self->handle_CHLD() };
    $SIG{HUP} = \&class_handle_HUP;
    $handlers{HUP}->{$self} = sub { $self->handle_HUP() };
}

sub class_handle_TERM {
    foreach (keys %{$handlers{TERM}}) {
        &{$handlers{TERM}->{$_}}();
    }
    exit(0);
}

sub class_handle_DIE {
    my ($msg) = @_;

    foreach (keys %{$handlers{DIE}}) {
        &{$handlers{DIE}->{$_}}($msg);
    }
}

sub class_handle_CHLD {
    foreach (keys %{$handlers{CHLD}}) {
        &{$handlers{CHLD}->{$_}}();
    }
}

sub class_handle_HUP {
    foreach (keys %{$handlers{HUP}}) {
        &{$handlers{HUP}->{$_}}();
    }
}

sub handle_DIE {
    my $self = shift;
    my $msg = shift;

    # We get SIGCHLD signals
    $self->{logger}->writeLogInfo($msg);
    
    ###
    # Send -TERM signal
    ###
    for (my $i = 0; $i < $self->{centstorage_config}->{pool_childs}; $i++) {
        if (defined($self->{pool_pipes}{$i}) && $self->{pool_pipes}{$i}->{'running'} == 1) {
            kill('TERM', $self->{pool_pipes}{$i}->{'pid'});
            $self->{logger}->writeLogInfo("Send -TERM signal to pool process..");
        }
    }
    if (defined($self->{delete_pipes}{'running'}) && $self->{delete_pipes}{'running'} == 1) {
        $self->{logger}->writeLogInfo("Send -TERM signal to delete process..");
        kill('TERM', $self->{pid_delete_child});
    }

    ### Write file
    if (defined($self->{centstorage_perfdata_file})) {
        $self->{centstorage_perfdata_file}->finish();
    }

    if (scalar(keys %{$self->{pool_pipes}}) == 0) {
        exit(0);
    }

    my $kill_or_not = 1;
    for (my $i = 0; $i < $self->{centstorage_config}->{TIMEOUT}; $i++) {
        $self->verify_pool(0);
        my $running = 0;
        for (my $i = 0; $i < $self->{centstorage_config}->{pool_childs}; $i++) {
            $running += $self->{pool_pipes}{$i}->{'running'} == 1;
        }
        $running += $self->{delete_pipes}{'running'};
        if ($running == 0) {
            $kill_or_not = 0;
            last;
        }
        sleep(1);
    }

    if ($kill_or_not == 1) {
        for (my $i = 0; $i < $self->{centstorage_config}->{pool_childs}; $i++) {
            if ($self->{pool_pipes}{$i}->{'running'} == 1) {
                kill('KILL', $self->{pool_pipes}{$i}->{'pid'});
                $self->{logger}->writeLogInfo("Send -KILL signal to pool process..");
            }
        }
        if ($self->{delete_pipes}{'running'} == 1) {
            kill('KILL', $self->{pid_delete_child});
            $self->{logger}->writeLogInfo("Send -KILL signal to delete process..");
        }
    }
    
    exit(0);
}

sub handle_TERM {
    my $self = shift;
    $self->{logger}->writeLogInfo("$$ Receiving order to stop...");
    die("Quit");
}

sub handle_HUP {
    my $self = shift;
    $self->{reload} = 0;
}

####
# First Part
#    - Create Pool of child process
####

sub verify_pool {
    my $self = shift;
    my ($create_pool) = @_;

    foreach my $child_pid (keys %{$self->{return_child}}) {
        foreach my $pool_num (keys %{$self->{pool_pipes}}) {
            if ($self->{pool_pipes}{$pool_num}->{'pid'} == $child_pid) {
                $self->{logger}->writeLogInfo("Pool child '$pool_num' is dead");
                $self->{read_select}->remove($self->{pool_pipes}{$pool_num}->{'reader_one'});
                $self->{pool_pipes}{$pool_num}->{'running'} = 0;
                if (defined($create_pool) && $create_pool == 1) {
                    # We have lost one. And if it's the pool rebuild, send progress finish
                    if ($pool_num == $self->{rebuild_pool_choosen}) {
                        centreoncentstorage::CentstorageLib::call_pool_rebuild_finish(\%{$self->{pool_pipes}}, $self->{centstorage_config}->{pool_childs}, \%{$self->{delete_pipes}}, \$self->{rebuild_progress}, \$self->{rebuild_pool_choosen});
                    }
                    $self->create_pool_child($pool_num);
                }
                delete $self->{return_child}{$child_pid};
                last;
            }
        }
        if ($child_pid == $self->{pid_delete_child}) {
            $self->{logger}->writeLogInfo("Delete child is dead");
            $self->{read_select}->remove($self->{delete_pipes}{'reader_one'});
            $self->{delete_pipes}{'running'} = 0;
            if (defined($create_pool) && $create_pool == 1) {
                $self->create_delete_child();
            }
            delete $self->{return_child}{$child_pid};
        }
    }
}

sub create_pool_child {
    my $self = shift;
    my $pool_num = $_[0];
    
    my ($reader_pipe_one, $writer_pipe_one);
    my ($reader_pipe_two, $writer_pipe_two);

    pipe($reader_pipe_one, $writer_pipe_one);
    pipe($reader_pipe_two, $writer_pipe_two);
    $writer_pipe_one->autoflush(1);
    $writer_pipe_two->autoflush(1);

    $self->{pool_pipes}{$pool_num} = {};
    $self->{pool_pipes}{$pool_num}->{'reader_one'} = \*$reader_pipe_one;
    $self->{pool_pipes}{$pool_num}->{'writer_one'} = \*$writer_pipe_one;
    $self->{pool_pipes}{$pool_num}->{'reader_two'} = \*$reader_pipe_two;
    $self->{pool_pipes}{$pool_num}->{'writer_two'} = \*$writer_pipe_two;

    $self->{logger}->writeLogInfo("Create Pool child '$pool_num'");
    my $current_pid = fork();
    if (!$current_pid) {
        close $self->{pool_pipes}{$pool_num}->{'reader_one'};
        close $self->{pool_pipes}{$pool_num}->{'writer_two'};
        my $centreon_db_centreon = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                                     host => $self->{centreon_config}->{db_host},
                                                     port => $self->{centreon_config}->{db_port},
                                                     user => $self->{centreon_config}->{db_user},
                                                     password => $self->{centreon_config}->{db_passwd},
                                                     force => 1,
                                                     logger => $self->{logger});
        $centreon_db_centreon->connect();
        my $centreon_db_centstorage = centreon::common::db->new(db => $self->{centreon_config}->{centstorage_db},
                                                        host => $self->{centreon_config}->{db_host},
                                                        port => $self->{centreon_config}->{db_port},
                                                        user => $self->{centreon_config}->{db_user},
                                                        password => $self->{centreon_config}->{db_passwd},
                                                        force => 1,
                                                        logger => $self->{logger});
        $centreon_db_centstorage->connect();

        my $centstorage_rrd = centreon::centstorage::CentstorageRRD->new($self->{logger});

        my $centstorage_pool = centreon::centstorage::CentstoragePool->new($self->{logger}, $centstorage_rrd,  $self->{rebuild_progress});
        $centstorage_pool->main($centreon_db_centreon, $centreon_db_centstorage,
                    $self->{pool_pipes}{$pool_num}->{'reader_two'}, $self->{pool_pipes}{$pool_num}->{'writer_one'}, $pool_num,
                    $self->{centstorage_config}->{rrd_cache_mode}, $self->{centstorage_config}->{rrd_flush_time}, $self->{centstorage_config}->{perfdata_parser_stop}, $self->{config_file});
        exit(0);
    }
    $self->{pool_pipes}{$pool_num}->{'pid'} = $current_pid;
    $self->{pool_pipes}{$pool_num}->{'running'} = 1;
    close $self->{pool_pipes}{$pool_num}->{'writer_one'};
    close $self->{pool_pipes}{$pool_num}->{'reader_two'};
    $self->{fileno_save_read}{fileno($self->{pool_pipes}{$pool_num}->{'reader_one'})} = [];
    $self->{read_select}->add($self->{pool_pipes}{$pool_num}->{'reader_one'});
}

sub create_delete_child {
    my $self = shift;
    my ($reader_pipe_one, $writer_pipe_one);
    my ($reader_pipe_two, $writer_pipe_two);

    
    pipe($reader_pipe_one, $writer_pipe_one);
    pipe($reader_pipe_two, $writer_pipe_two);
    $writer_pipe_one->autoflush(1);
    $writer_pipe_two->autoflush(1);

    $self->{delete_pipes}{'reader_one'} = \*$reader_pipe_one;
    $self->{delete_pipes}{'writer_one'} = \*$writer_pipe_one;
    $self->{delete_pipes}{'reader_two'} = \*$reader_pipe_two;
    $self->{delete_pipes}{'writer_two'} = \*$writer_pipe_two;

    $self->{logger}->writeLogInfo("Create delete child");
    my $current_pid = fork();
    if (!$current_pid) {
        close $self->{delete_pipes}{'reader_one'};
        close $self->{delete_pipes}{'writer_two'};
        my $centreon_db_centreon = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                                     host => $self->{centreon_config}->{db_host},
                                                     port => $self->{centreon_config}->{db_port},
                                                     user => $self->{centreon_config}->{db_user},
                                                     password => $self->{centreon_config}->{db_passwd},
                                                     force => 1,
                                                     logger => $self->{logger});
        $centreon_db_centreon->connect();
        my $centreon_db_centstorage = centreon::common::db->new(db => $self->{centreon_config}->{centstorage_db},
                                                        host => $self->{centreon_config}->{db_host},
                                                        port => $self->{centreon_config}->{db_port},
                                                        user => $self->{centreon_config}->{db_user},
                                                        password => $self->{centreon_config}->{db_passwd},
                                                        force => 1,
                                                        logger => $self->{logger});
        $centreon_db_centstorage->connect();
        
        my $centstorage_action = centreon::centstorage::CentstorageAction->new($self->{logger}, $self->{rebuild_progress}, $self->{centstorage_config}->{centreon_23_compatibility});
        $centstorage_action->main($centreon_db_centreon, $centreon_db_centstorage,
                    $self->{delete_pipes}{'reader_two'}, $self->{delete_pipes}{'writer_one'}, $self->{config_file});
        exit(0);
    }
    $self->{pid_delete_child} = $current_pid;
    close $self->{delete_pipes}{'writer_one'};
    close $self->{delete_pipes}{'reader_two'};
    $self->{delete_pipes}{'running'} = 1;
    $self->{fileno_save_read}{fileno($self->{delete_pipes}{'reader_one'})} = [];
    $self->{read_select}->add($self->{delete_pipes}{'reader_one'});
}

sub handle_CHLD {
    my $self = shift;
    my $child_pid;

    while (($child_pid = waitpid(-1, &WNOHANG)) > 0) {
        $self->{return_child}{$child_pid} = {'exit_code' => $? >> 8};
    }
    $SIG{CHLD} = \&class_handle_CHLD;
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->init();
    $self->{logger}->redirect_output();

    ####
    # Get Main perfdata and status
    ####
    my $main_perfdata;
    my $status;
    my $pools_perfdata_filename;

    $self->{centreon_db_centreon} = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                                     host => $self->{centreon_config}->{db_host},
                                                     port => $self->{centreon_config}->{db_port},
                                                     user => $self->{centreon_config}->{db_user},
                                                     password => $self->{centreon_config}->{db_passwd},
                                                     force => 1,
                                                     logger => $self->{logger});
    $self->{centreon_db_centreon}->connect();
    $self->handle_DIE("Censtorage option is '0'. Don't have to start") if (centreon::centstorage::CentstorageLib::start_or_not($self->{centreon_db_centreon}) == 0);
    while (!defined($main_perfdata) || $main_perfdata eq "") {
        ($status, $main_perfdata) = centreon::centstorage::CentstorageLib::get_main_perfdata_file($self->{centreon_db_centreon});
        if (defined($main_perfdata)) {
            $pools_perfdata_filename = centreon::centstorage::CentstorageLib::check_pool_old_perfdata_file($main_perfdata, $self->{centstorage_config}->{pool_childs});
        }
    }
    $self->{centreon_db_centreon}->disconnect();

    ###
    # Check write
    ###
    if (defined($pools_perfdata_filename)) {
        foreach (@$pools_perfdata_filename) {    
            $self->handle_DIE("Don't have righs on file '$_' (or the directory)") if (centreon::centstorage::CentstorageLib::can_write($_) == 0);
        }
    }
    $self->handle_DIE("Don't have righs on file '$main_perfdata' (or the directory)") if (centreon::centstorage::CentstorageLib::can_write($main_perfdata) == 0);

    ###
    # Create Childs
    ###
    $self->{read_select} = new IO::Select();
    for (my $i = 0; $i < $self->{centstorage_config}->{pool_childs}; $i++) {
        $self->create_pool_child($i);
    }
    $self->create_delete_child();

    ##################
    ##################


    ####
    # Main loop
    ####
    while (1) {
        $self->verify_pool(1);

        ###
        # Do pool perfdata if needed 
        ###
        if (defined($pools_perfdata_filename)) {
            foreach (@$pools_perfdata_filename) {
                $self->{centstorage_perfdata_file} = centreon::centstorage::CentstoragePerfdataFile->new($self->{logger});
                $self->{centstorage_perfdata_file}->compute($_, \%{$self->{pool_pipes}}, \%{$self->{routing_services}}, \$self->{roundrobin_pool_current}, $self->{centstorage_config}->{pool_childs});
            }
            $pools_perfdata_filename = undef;
        }

        ###
        # Do main file
        ###
        $self->{centstorage_perfdata_file} = centreon::centstorage::CentstoragePerfdataFile->new($self->{logger});
        $self->{centstorage_perfdata_file}->compute($main_perfdata, \%{$self->{pool_pipes}}, \%{$self->{routing_services}}, \$self->{roundrobin_pool_current}, $self->{centstorage_config}->{pool_childs});

        ###
        # Check response from rebuild
        ###
        my @rh_set = $self->{read_select}->can_read(10);
        foreach my $rh (@rh_set) {
            my $read_done = 0;
            while ((my ($status_line, $data_element) = centreon::centstorage::CentstorageLib::get_line_pipe($rh, \@{$self->{fileno_save_read}{fileno($rh)}}, \$read_done))) {
                last if ($status_line <= 0);
                if ($data_element =~ /^REBUILDBEGIN/) {
                    centreon::centstorage::CentstorageLib::call_pool_rebuild($data_element, \%{$self->{pool_pipes}}, \%{$self->{routing_services}}, \$self->{roundrobin_pool_current}, $self->{centstorage_config}->{pool_childs}, \$self->{rebuild_progress}, \$self->{rebuild_pool_choosen});
                } elsif ($data_element =~ /^REBUILDFINISH/) {
                    centreon::centstorage::CentstorageLib::call_pool_rebuild_finish(\%{$self->{pool_pipes}}, $self->{centstorage_config}->{pool_childs}, \%{$self->{delete_pipes}}, \$self->{rebuild_progress}, \$self->{rebuild_pool_choosen});
                } elsif ($data_element =~ /^RENAMECLEAN/) {
                    centreon::centstorage::CentstorageLib::call_pool_rename_clean($data_element, \%{$self->{pool_pipes}}, \%{$self->{routing_services}}, \$self->{roundrobin_pool_current}, $self->{centstorage_config}->{pool_childs});
                } elsif ($data_element =~ /^RENAMEFINISH/) {
                    centreon::centstorage::CentstorageLib::call_pool_rename_finish($data_element, \%{$self->{pool_pipes}}, \%{$self->{routing_services}}, \$self->{roundrobin_pool_current}, $self->{centstorage_config}->{pool_childs});
                } elsif ($data_element =~ /^DELETECLEAN/) {
                    centreon::centstorage::CentstorageLib::call_pool_delete_clean($data_element, \%{$self->{pool_pipes}}, \%{$self->{routing_services}}, \$self->{roundrobin_pool_current}, $self->{centstorage_config}->{pool_childs});
                }
            }
        }
        
        if ($self->{reload} == 0) {
            $self->reload();
        }
    }
}

1;

__END__
