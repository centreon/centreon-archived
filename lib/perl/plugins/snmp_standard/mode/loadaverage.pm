package snmp_standard::mode::loadaverage;

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
                                  "warning:s"               => { name => 'warning', default => '' },
                                  "critical:s"              => { name => 'critical', default => '' },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    ($self->{warn1}, $self->{warn5}, $self->{warn15}) = split /,/, $self->{option_results}->{warning};
    ($self->{crit1}, $self->{crit5}, $self->{crit15}) = split /,/, $self->{option_results}->{critical};
    
    if (($self->{perfdata}->threshold_validate(label => 'warn1', value => $self->{warn1})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning (1min) threshold '" . $self->{warn1} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warn5', value => $self->{warn5})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning (5min) threshold '" . $self->{warn5} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warn15', value => $self->{warn15})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning (15min) threshold '" . $self->{warn15} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'crit1', value => $self->{crit1})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical (1min) threshold '" . $self->{crit1} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'crit5', value => $self->{crit5})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical (5min) threshold '" . $self->{crit5} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'crit15', value => $self->{crit15})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical (15min) threshold '" . $self->{crit15} . "'.");
       $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    
    my $oid_CpuLoad1m = '.1.3.6.1.4.1.2021.10.1.3.1';
    my $oid_CpuLoad5m = '.1.3.6.1.4.1.2021.10.1.3.2';
    my $oid_CpuLoad15m = '.1.3.6.1.4.1.2021.10.1.3.3';

    my ($exit_snmp, $result) = $self->{snmp}->get_leef(oids => [$oid_CpuLoad1m, $oid_CpuLoad5m, $oid_CpuLoad15m]);
    
    my $exit1 = $self->{perfdata}->threshold_check(value => $result->{$oid_CpuLoad1m}, 
                               threshold => [ { label => 'crit1', 'exit_litteral' => 'critical' }, { label => 'warn1', exit_litteral => 'warning' } ]);
    my $exit2 = $self->{perfdata}->threshold_check(value => $result->{$oid_CpuLoad5m}, 
                               threshold => [ { label => 'crit5', 'exit_litteral' => 'critical' }, { label => 'warn5', exit_litteral => 'warning' } ]);
    my $exit3 = $self->{perfdata}->threshold_check(value => $result->{$oid_CpuLoad15m}, 
                               threshold => [ { label => 'crit15', 'exit_litteral' => 'critical' }, { label => 'warn15', exit_litteral => 'warning' } ]);
    
    my $exit = $self->{output}->get_most_critical(status => [ $exit1, $exit2, $exit3 ]);
    $self->{output}->output_add(severity => $exit,
                                short_msg => sprintf("Load average: %s, %s, %s", $result->{$oid_CpuLoad1m}, $result->{$oid_CpuLoad5m}, $result->{$oid_CpuLoad15m}));
            
    $self->{output}->perfdata_add(label => 'load1',
                                  value => $result->{$oid_CpuLoad1m},
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warn1'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'crit1'),
                                  min => 0);
    $self->{output}->perfdata_add(label => 'load5',
                                  value => $result->{$oid_CpuLoad5m},
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warn5'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'crit5'),
                                  min => 0);
    $self->{output}->perfdata_add(label => 'load15',
                                  value => $result->{$oid_CpuLoad15m},
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warn15'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'crit15'),
                                  min => 0);

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check system load-average.

=over 8

=item B<--warning>

Threshold warning (1min,5min,15min).

=item B<--critical>

Threshold critical (1min,5min,15min).

=back

=cut
