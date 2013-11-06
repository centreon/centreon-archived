package network::bluecoat::mode::clienttraffic;

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
                                  "warning-received:s"      => { name => 'warning_received' },
                                  "critical-received:s"     => { name => 'critical_received' },
                                  "warning-delivered:s"     => { name => 'warning_delivered' },
                                  "critical-delivered:s"    => { name => 'critical_delivered' },
                                });
    $self->{statefile_value} = centreon::plugins::statefile->new(%options);
                                
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);
    
    if (($self->{perfdata}->threshold_validate(label => 'warning_received', value => $self->{option_results}->{warning_received})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'received' threshold '" . $self->{option_results}->{warning_received} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical_received', value => $self->{option_results}->{critical_received})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'received' threshold '" . $self->{option_results}->{critical_received} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warning_delivered', value => $self->{option_results}->{warning_delivered})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'delivered' threshold '" . $self->{option_results}->{warning_delivered} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical_delivered', value => $self->{option_results}->{critical_delivered})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'delivered' threshold '" . $self->{option_results}->{critical_delivered} . "'.");
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

    $self->{statefile_value}->read(statefile => 'bluecoat_' . $self->{hostname}  . '_' . $self->{mode});

    my ($exit, $result) = $self->{snmp}->get_leef(oids => ['.1.3.6.1.4.1.3417.2.11.3.1.1.9.0', 
                                                           '.1.3.6.1.4.1.3417.2.11.3.1.1.10.0'], nothing_quit => 1);

    my $old_timestamp = $self->{statefile_value}->get(name => 'last_timestamp');
    my $old_client_in_bytes = $self->{statefile_value}->get(name => 'client_in_bytes');
    my $old_client_out_bytes = $self->{statefile_value}->get(name => 'client_out_bytes');

    my $new_datas = {};
    $new_datas->{last_timestamp} = time();
    $new_datas->{client_in_bytes} = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.1.9.0'};
    $new_datas->{client_out_bytes} = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.1.10.0'};
    
    $self->{statefile_value}->write(data => $new_datas);
        
    if (!defined($old_timestamp) || !defined($old_client_in_bytes)) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => "Buffer creation...");
        $self->{output}->exit();
    }
        
    if ($new_datas->{client_in_bytes} < $old_client_in_bytes) {
        # We set 0. Has reboot.
        $old_client_in_bytes = 0;
        $old_client_out_bytes = 0;
    }
    
    my $delta_time = $new_datas->{last_timestamp} - $old_timestamp;
    my $in_bytes_sec = sprintf("%.2f", ($new_datas->{client_in_bytes} - $old_client_in_bytes) / $delta_time);
    my $out_bytes_sec = sprintf("%.2f", ($new_datas->{client_out_bytes} - $old_client_out_bytes) / $delta_time);
    
    my $exit1 = $self->{perfdata}->threshold_check(value => $in_bytes_sec, threshold => [ { label => 'critical_received', 'exit_litteral' => 'critical' }, { label => 'warning_received', exit_litteral => 'warning' } ]);
    my $exit2 = $self->{perfdata}->threshold_check(value => $out_bytes_sec, threshold => [ { label => 'critical_delivered', 'exit_litteral' => 'critical' }, { label => 'warning_delivered', exit_litteral => 'warning' } ]);
    $exit = $self->{output}->get_most_critical(status => [ $exit1, $exit2 ]);
    
    my ($value_in, $unit_in) = $self->{perfdata}->change_bytes(value => $in_bytes_sec);
    my ($value_out, $unit_out) = $self->{perfdata}->change_bytes(value => $out_bytes_sec);

    $self->{output}->output_add(severity => $exit,
                                short_msg => "Traffic: In $value_in $unit_in/s, Out $value_out $unit_out/s");    
    $self->{output}->perfdata_add(label => 'traffic_in', unit => 'B/s',
                                  value => $in_bytes_sec,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning_received'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical_received'),
                                  min => 0);
    $self->{output}->perfdata_add(label => 'traffic_out', unit => 'B/s',
                                  value => $out_bytes_sec,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning_delivered'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical_delivered'),
                                  min => 0);

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check bytes/s received/delivered to clients

=over 8

=item B<--warning-received>

Threshold warning for received (in bytes/s).

=item B<--critical-received>

Threshold critical for received (in bytes/s).

=item B<--warning-delivered>

Threshold warning2 for delivered (in bytes/s).

=item B<--critical-delivered>

Threshold critical for delivered (in bytes/s).

=back

=cut
