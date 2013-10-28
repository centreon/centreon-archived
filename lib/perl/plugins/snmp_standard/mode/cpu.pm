package snmp_standard::mode::cpu;

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
                                  "warning:s"               => { name => 'warning', },
                                  "critical:s"              => { name => 'critical', },
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
    
    my $oid_cputable = '.1.3.6.1.2.1.25.3.3.1.2';
    my $result = $self->{snmp}->get_table(oid => $oid_cputable);
    
    my $cpu = 0;
    my $i = 0;
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        $key =~ /\.([0-9]+)$/;
        my $cpu_num = $1;
        
        $cpu += $result->{$key};
        $i++;
        
        $self->{output}->output_add(long_msg => sprintf("CPU $i Usage is %.2f%%", $result->{$key}));
        $self->{output}->perfdata_add(label => 'cpu' . $cpu_num,
                                  value => sprintf("%.2f", $result->{$key}),
                                  min => 0, max => 100);
    }

    my $avg_cpu = $cpu / $i;
    my $exit_code = $self->{perfdata}->threshold_check(value => $avg_cpu, 
                               threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
    $self->{output}->output_add(severity => $exit_code,
                                short_msg => sprintf("CPU(s) average usage is: %.2f%%", $avg_cpu));
    $self->{output}->perfdata_add(label => 'total_cpu_avg',
                                  value => sprintf("%.2f", $avg_cpu),
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                  min => 0, max => 100);

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check system CPUs.

=over 8

=item B<--warning>

Threshold warning in percent.

=item B<--critical>

Threshold critical in percent.

=back

=cut
