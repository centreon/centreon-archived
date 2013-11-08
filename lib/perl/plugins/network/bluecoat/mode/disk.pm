package network::bluecoat::mode::disk;

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
    
    my $disk_num = 1;
    my ($exit, $result) = $self->{snmp}->get_table(oid => '.1.3.6.1.4.1.3417.2.4.1.1.1');
    for (my $i = 1; defined($result->{'.1.3.6.1.4.1.3417.2.4.1.1.1.3.' . $i}); $i++) {
        if ($result->{'.1.3.6.1.4.1.3417.2.4.1.1.1.3.' . $i} !~ /^DISK$/i) {
            next;
        }
        
        my $disk_usage = $result->{'.1.3.6.1.4.1.3417.2.4.1.1.1.4.' . $i};
        $exit = $self->{perfdata}->threshold_check(value => $disk_usage, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        $self->{output}->output_add(severity => $exit,
                                    short_msg => sprintf("Disk $disk_num usage is %.2f%%", $disk_usage));
        $self->{output}->perfdata_add(label => 'disk_' . $disk_num, unit => '%',
                                      value => sprintf("%.2f", $disk_usage),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                      min => 0, max => 100);
        $disk_num++;
    }
    
    if ($disk_num == 1) {
        $self->{output}->add_option_msg(short_msg => "No disk information found...");
        $self->{output}->option_exit();
    }
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check disks usage.

=over 8

=item B<--warning>

Threshold warning in percent.

=item B<--critical>

Threshold critical in percent.

=back

=cut
