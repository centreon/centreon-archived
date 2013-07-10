
package centreon::trapd::Log;

use strict;
use warnings;
use centreon::common::misc;
use IO::Select;
my %handlers = ('TERM' => {}, 'HUP' => {});

sub new {
    my $class = shift;
    my $self  = {};

    $self->{logger} = shift;
    
    # reload flag
    $self->{reload} = 1;
    $self->{config_file} = undef;
    $self->{centreontrapd_config} = undef;
    $self->{construct_log} = {};
    $self->{request_log} = {};
    $self->{last_transaction} = time;
    
    $self->{"save_read"} = [];

    bless $self, $class;
    $self->set_signal_handlers;
    return $self;
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{'TERM'}->{$self} = sub { $self->handle_TERM() };
    $SIG{HUP} = \&class_handle_HUP;
    $handlers{HUP}->{$self} = sub { $self->handle_HUP() };
}

sub handle_HUP {
    my $self = shift;
    $self->{reload} = 0;
}

sub handle_TERM {
    my $self = shift;
    $self->{'logger'}->writeLogInfo("$$ Receiving order to stop...");

    $self->{'dbcentstorage'}->disconnect() if (defined($self->{'dbcentstorage'}));
    $self->{'cdb'}->disconnect() if (defined($self->{'cdb'}));
}

sub class_handle_TERM {
    foreach (keys %{$handlers{'TERM'}}) {
        &{$handlers{'TERM'}->{$_}}();
    }
    exit(0);
}

sub class_handle_HUP {
    foreach (keys %{$handlers{HUP}}) {
        &{$handlers{HUP}->{$_}}();
    }
}

sub reload {
    my $self = shift;
    
    $self->{logger}->writeLogInfo("Reload in progress for logdb process...");
    # reopen file
    if ($self->{logger}->is_file_mode()) {
        $self->{logger}->file_mode($self->{logger}->{file_name});
    }
    $self->{logger}->redirect_output();
    
    my ($status, $status_cdb, $status_csdb) = centreon::common::misc::reload_db_config($self->{logger}, $self->{config_file},
                                                                                       $self->{dbcentstorage}, $self->{cdb});

    if ($status_csdb == 1) {
        $self->{dbcentstorage}->disconnect();
        $self->{dbcentstorage}->connect();
    }
    if ($status_cdb == 1) {
        $self->{cdb}->disconnect();
        $self->{cdb}->connect();
    }
    centreon::common::misc::check_debug($self->{logger}, "debug_centreontrapd", $self->{cdb}, "centreontrapd logdb process");

    $self->{reload} = 1;
}

sub compute_request {
    my $self = shift;
    
    if (scalar(keys(%{$self->{request_log}})) > $self->{centreontrapd_config}->{log_transaction_request_max} ||
        (time() - $self->{last_transaction}) > $self->{centreontrapd_config}->{log_transaction_timeout}) {
        $self->{dbcentstorage}->transaction_mode(1);
        eval {
            foreach my $id (keys %{$self->{request_log}}) {
                $self->{dbcentstorage}->query("INSERT INTO log_traps (`trap_time`, `timeout`, `host_name`, `ip_address`, `agent_host_name`, `agent_ip_address`, `trap_oid`, `trap_name`, `vendor`, `severity`, `output_message`) VALUES (" . $self->{request_log}->{$id}->{value} . ")");
                $self->{dbcentstorage}->query("SET \@last_id_trap = LAST_INSERT_ID();");
                if (defined($self->{request_log}->{$id}->{args})) {
                    foreach (@{$self->{request_log}->{$id}->{args}}) {
                        $self->{dbcentstorage}->query("INSERT INTO log_traps_args (`fk_log_traps`, `arg_number`, `arg_oid`, `arg_value`, `trap_time`) VALUES (\@last_id_trap, " . $_ . ")");
                    }
                }
            }
            $self->{dbcentstorage}->commit;
        };
        if ($@) {
            $self->{dbcentstorage}->rollback;
        } else {
             $self->{request_log} = {};
        }
        $self->{last_transaction} = time;
    }
    
    # Check time purge
    foreach my $id (keys %{$self->{construct_log}}) {
        if ((time() - $self->{construct_log}->{$id}->{time}) > $self->{centreontrapd_config}->{log_purge_time}) {
            delete $self->{construct_log}->{$id};
        }
    }
}

####
# Protocol description:
#      First: ID_UNIQUE:0:num_args:value
#      Args:  ID_UNIQUE:1:arg_pos:value

sub main {
    my $self = shift;
    my ($dbcentstorage, $pipe_read, $config_file, $centreontrapd_config) = @_;
    my $status;

    $self->{dbcentstorage} = $dbcentstorage;
    $self->{config_file} = $config_file;
    $self->{centreontrapd_config} = $centreontrapd_config;

    # We have to manage if you don't need infos
    $self->{'dbcentstorage'}->force(0);
    
    my $read_select = new IO::Select();
    $read_select->add($pipe_read);
    while (1) {
        my @rh_set = $read_select->can_read(5);
        if (scalar(@rh_set) > 0) {
            foreach my $rh (@rh_set) {
                my $read_done = 0;
                while ((my ($status_line, $readline) = centreon::common::misc::get_line_pipe($rh, \@{$self->{'save_read'}}, \$read_done))) {
                    class_handle_TERM() if ($status_line == -1);
                    last if ($status_line == 0);
                    $readline =~ /^(.*?):(.*?):(.*?):(.*)/;
                    my ($id, $type, $num, $value) = ($1, $2, $3, $4);
                    $value =~ s/\\n/\n/g;
                    
                    if ($type == 0) {
                        if ($num <= 0) {
                            $self->{request_log}->{$id} = { value => $value };
                        } else {
                            $self->{construct_log}->{$id} = { time => time(), value => $value, current_args => 0, num_args => $num, args => [] };
                        }
                    } elsif ($type == 1) {
                        if (defined($self->{construct_log}->{$id})) {
                            if ($self->{construct_log}->{$id}->{current_args} + 1 == $self->{construct_log}->{$id}->{num_args}) {
                                $self->{request_log}->{$id} = { value => $self->{construct_log}->{$id}->{value}, args => $self->{construct_log}->{$id}->{args} };
                                delete $self->{construct_log}->{$id};
                            } else {
                                push @{$self->{construct_log}->{$id}->{args}}, $value;
                                $self->{construct_log}->{$id}->{current_args}++;
                            }
                        }
                    }
                }
            }
        } 
        
        $self->compute_request();

        if ($self->{reload} == 0) {
            $self->reload();
        }
    }
}

1;
