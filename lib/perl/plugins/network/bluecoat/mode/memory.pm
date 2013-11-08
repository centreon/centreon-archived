package network::bluecoat::mode::memory;

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
                                  "nocache"        => { name => 'nocache' },
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
    
    if ($self->{snmp}->is_snmpv1()) {
        $self->{output}->add_option_msg(short_msg => "Need to use SNMP v2c or v3.");
        $self->{output}->option_exit();
    }

    my ($exit, $result) = $self->{snmp}->get_table(oid => '.1.3.6.1.4.1.3417.2.11.2.3', nothing_quit => 1);

    my $mem_total = $result->{'.1.3.6.1.4.1.3417.2.11.2.3.1.0'};
    my $mem_cache = $result->{'.1.3.6.1.4.1.3417.2.11.2.3.2.0'};
    my $mem_sys = $result->{'.1.3.6.1.4.1.3417.2.11.2.3.3.0'};
    my $mem_used;
    
    if (defined($self->{option_results}->{nocache})) {
        $mem_used = $mem_sys;
    } else {
        $mem_used = $mem_sys + $mem_cache;
    }
    
    my $prct_used = sprintf("%.2f", $mem_used * 100 / $mem_total);
    $exit = $self->{perfdata}->threshold_check(value => $prct_used, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
    my ($used_value, $used_unit) = $self->{perfdata}->change_bytes(value => $mem_used);
    my ($total_value, $total_unit) = $self->{perfdata}->change_bytes(value => $mem_total);
    
    $self->{output}->output_add(severity => $exit,
                                short_msg => sprintf("Memory used : %s - size : %s - percent : " . $prct_used . " %", 
                                                     $used_value . " " . $used_unit, $total_value . " " . $total_unit));
    
    $self->{output}->perfdata_add(label => 'used',
                                  value => sprintf("%.2f", $mem_used),
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning', total => $mem_total),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical', total => $mem_total),
                                  min => 0, max => $mem_total);
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check bluecoat memory.

=over 8

=item B<--warning>

Threshold warning in percent.

=item B<--critical>

Threshold critical in percent.

=item B<--nocache>

Skip cache value.

=back

=cut
