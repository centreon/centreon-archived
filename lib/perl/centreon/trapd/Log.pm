
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
                                                                                       $self->{dbcentreon}, $self->{dbcentstorage});

    if ($status_csdb == 1) {
        $self->{dbcentstorage}->disconnect();
        $self->{dbcentstorage}->connect();
    }
    centreon::common::misc::check_debug($self->{logger}, "debug_centstorage", $self->{dbcentreon}, "centstorage delete process");

    $self->{reload} = 1;
}

sub main {
    my $self = shift;
    my ($dbcentstorage, $pipe_read, $config_file) = @_;
    my $status;

    $self->{dbcentstorage} = $dbcentstorage;
    $self->{config_file} = $config_file;

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
                    $self->{logger}->writeLogInfo("=== test $readline");
                }
            }
        } else {
            # Here we have to check if we do transaction
        }
        
        if ($self->{reload} == 0) {
            $self->reload();
        }
    }
}

1;
