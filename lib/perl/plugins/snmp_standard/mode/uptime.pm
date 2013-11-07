package snmp_standard::mode::uptime;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use POSIX;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "warning:s"          => { name => 'warning', },
                                  "critical:s"         => { name => 'critical', },
                                  "seconds"            => { name => 'seconds', },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (($self->{perfdata}->threshold_validate(label => 'warning', value => $self->{option_results}->{warning})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning threshold '" . $self->{warn1} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical', value => $self->{option_results}->{critical})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical threshold '" . $self->{critical} . "'.");
       $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    
    my $oid_hrSystemUptime = '.1.3.6.1.2.1.25.1.1.0';
    my ($exit_snmp, $result) = $self->{snmp}->get_leef(oids => [ $oid_hrSystemUptime ]);
    
    my $exit_code = $self->{perfdata}->threshold_check(value => floor($result->{$oid_hrSystemUptime} / 100), 
                              threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);    
    $self->{output}->perfdata_add(label => 'uptime',
                                  value => floor($result->{$oid_hrSystemUptime} / 100),
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                  min => 0);

    $self->{output}->output_add(severity => $exit_code,
                                short_msg => sprintf("System uptime is: %s", 
                                    defined($self->{option_results}->{seconds}) ? floor($result->{$oid_hrSystemUptime} / 100) . " seconds" : floor($result->{$oid_hrSystemUptime} / 86400 / 100) . " days" ));

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check system uptime.

=over 8

=item B<--warning>

Threshold warning in seconds.

=item B<--critical>

Threshold critical in seconds.

=item B<--seconds>

Display uptime in seconds.

=back

=cut
