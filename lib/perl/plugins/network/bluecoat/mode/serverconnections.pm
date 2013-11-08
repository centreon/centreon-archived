package network::bluecoat::mode::serverconnections;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "warning:s"      => { name => 'warning' },
                                  "critical:s"     => { name => 'critical' },
                                });
                                
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);
    
    if (($self->{perfdata}->threshold_validate(label => 'warning', value => $self->{option_results}->{warning})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning threshold '" . $self->{option_results}->{warning} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical', value => $self->{option_results}->{critical})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical threshold '" . $self->{option_results}->{critical} . "'.");
        $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    
    my ($exit, $result) = $self->{snmp}->get_leef(oids => ['.1.3.6.1.4.1.3417.2.11.3.1.3.4.0', 
                                                           '.1.3.6.1.4.1.3417.2.11.3.1.3.5.0',
                                                           '.1.3.6.1.4.1.3417.2.11.3.1.3.6.0'], nothing_quit => 1);
    my $server_connections = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.3.4.0'};
    my $server_connections_active = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.3.5.0'};
    my $server_connections_idle = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.3.6.0'};
    
    $exit = $self->{perfdata}->threshold_check(value => $server_connections_active, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
    $self->{output}->output_add(severity => $exit,
                                short_msg => "Server connections: Active " . $server_connections_active . ", Idle " . $server_connections_idle);
    $self->{output}->perfdata_add(label => 'con',
                                  value => $server_connections,
                                  min => 0);
    $self->{output}->perfdata_add(label => 'con_active',
                                  value => $server_connections_active,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                  min => 0);
    $self->{output}->perfdata_add(label => 'con_idle',
                                  value => $server_connections_idle,
                                  min => 0);
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check current client connections.

=over 8

=item B<--warning>

Threshold warning (on active connections).

=item B<--critical>

Threshold critical (on active connections.

=back

=cut
