package network::bluecoat::mode::cpu;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::statefile;

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
    $self->{statefile_value} = centreon::plugins::statefile->new(%options);
                                
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
    
    $self->{statefile_value}->check_options(%options);
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    $self->{hostname} = $self->{snmp}->get_hostname();
    
    if ($self->{snmp}->is_snmpv1()) {
        $self->{output}->add_option_msg(short_msg => "Need to use SNMP v2c or v3.");
        $self->{output}->option_exit();
    }

    my $new_datas = {};
    my $old_timestamp = undef;
    
    $self->{statefile_value}->read(statefile => 'cache_' . $self->{hostname}  . '_' . $self->{mode});
    
    my ($exit, $result) = $self->{snmp}->get_table(oid => '.1.3.6.1.4.1.3417.2.11.2.4.1', nothing_quit => 1);
    $old_timestamp = $self->{statefile_value}->get(name => 'last_timestamp');
    $new_datas->{last_timestamp} = time();
    for (my $i = 1; defined($result->{'.1.3.6.1.4.1.3417.2.11.2.4.1.3.' . $i}); $i++) {
        $new_datas->{'cpu_' . $i . '_busy'} = $result->{'.1.3.6.1.4.1.3417.2.11.2.4.1.3.' . $i};
        $new_datas->{'cpu_' . $i . '_idle'} = $result->{'.1.3.6.1.4.1.3417.2.11.2.4.1.4.' . $i};
        
        if (!defined($old_timestamp)) {
            next;
        }
        
        my $old_cpu_busy = $self->{statefile_value}->get(name => 'cpu_' . $i . '_busy');
        my $old_cpu_idle = $self->{statefile_value}->get(name => 'cpu_' . $i . '_idle');
        if (!defined($old_cpu_busy) || !defined($old_cpu_idle)) {
            next;
        }
        
        if ($new_datas->{'cpu_' . $i . '_busy'} < $old_cpu_busy) {
            # We set 0. Has reboot.
            $old_cpu_busy = 0;
            $old_cpu_idle = 0;
        }
        
        my $total_elapsed = (($new_datas->{'cpu_' . $i . '_busy'} - $old_cpu_busy) + ($new_datas->{'cpu_' . $i . '_idle'} - $old_cpu_idle));
        my $prct_usage =  (($new_datas->{'cpu_' . $i . '_busy'} - $old_cpu_busy) * 100 / ($total_elapsed));
        $exit = $self->{perfdata}->threshold_check(value => $prct_usage, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        $self->{output}->output_add(severity => $exit,
                                    short_msg => sprintf("CPU $i Usage is %.2f%%", $prct_usage));
        $self->{output}->perfdata_add(label => 'cpu_' . $i, unit => '%',
                                      value => sprintf("%.2f", $prct_usage),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                      min => 0, max => 100);
    }
    
    $self->{statefile_value}->write(data => $new_datas);
    if (!defined($old_timestamp)) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => "Buffer creation...");
    }
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check CPU Usage

=over 8

=item B<--warning>

Threshold warning in percent.

=item B<--critical>

Threshold critical in percent.

=back

=cut
