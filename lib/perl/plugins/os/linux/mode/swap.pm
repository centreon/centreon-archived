package os::linux::mode::swap;

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
                                  "warning:s"               => { name => 'warning' },
                                  "critical:s"              => { name => 'critical' },
                                });

    $self->{swap_memory_id} = undef;
    
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

    my $oid_hrStorageDescr = '.1.3.6.1.2.1.25.2.3.1.3';
    
    my ($exit, $result) = $self->{snmp}->get_table(oid => $oid_hrStorageDescr);
    
    foreach my $key (keys %$result) {
        next if ($key !~ /\.([0-9]+)$/);
        my $oid = $1;
        if ($result->{$key} =~ /^Swap space$/i) {
            $self->{swap_memory_id} = $oid;
        }
    }
    
    if (!defined($self->{swap_memory_id})) {
        $self->{output}->add_option_msg(short_msg => "Cannot find swap space informations.");
        $self->{output}->option_exit();
    }
    
    my $oid_hrStorageAllocationUnits = '.1.3.6.1.2.1.25.2.3.1.4';
    my $oid_hrStorageSize = '.1.3.6.1.2.1.25.2.3.1.5';
    my $oid_hrStorageUsed = '.1.3.6.1.2.1.25.2.3.1.6';

    $self->{snmp}->load(oids => [$oid_hrStorageAllocationUnits, $oid_hrStorageSize, $oid_hrStorageUsed], 
                        instances => [$self->{swap_memory_id}]);
    $result = $self->{snmp}->get_leef();

    my $swap_used = $result->{$oid_hrStorageUsed . "." . $self->{swap_memory_id}} * $result->{$oid_hrStorageAllocationUnits . "." . $self->{swap_memory_id}};
    my $total_size = $result->{$oid_hrStorageSize . "." . $self->{swap_memory_id}} * $result->{$oid_hrStorageAllocationUnits . "." . $self->{swap_memory_id}};
    
    my $prct_used = $swap_used * 100 / $total_size;
    $exit = $self->{perfdata}->threshold_check(value => $prct_used, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);

    my ($total_value, $total_unit) = $self->{perfdata}->change_bytes(value => $total_size);
    my ($swap_used_value, $swap_used_unit) = $self->{perfdata}->change_bytes(value => $swap_used);
    my ($swap_free_value, $swap_free_unit) = $self->{perfdata}->change_bytes(value => ($total_size - $swap_used));
    
    $self->{output}->output_add(severity => $exit,
                                short_msg => sprintf("Swap Total: %s Used: %s (%.2f%%) Free: %s (%.2f%%)",
                                            $total_value . " " . $total_unit,
                                            $swap_used_value . " " . $swap_used_unit, $prct_used,
                                            $swap_free_value . " " . $swap_free_unit, (100 - $prct_used)));
    
    $self->{output}->perfdata_add(label => "used",
                                  value => $swap_used,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning', total => $total_size),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical', total => $total_size),
                                  min => 0, max => $total_size);

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check Linux swap memory.

=over 8

=item B<--warning>

Threshold warning in percent.

=item B<--critical>

Threshold critical in percent.

=back

=cut
