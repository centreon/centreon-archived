package network::bluecoat::mode::hardware;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %device_status_msg = (2 => "unaivalable", 3 => "non operationnal");
my %device_units = (
                    1 => '', # other
                    2 => '', # truthvalue
                    3 => '', # specialEnum
                    4 => 'volts',
                    5 => 'celsius',
                    6 => 'rpm'
                    );
 my %device_code = (
                    1 => ["The device sensor '%s' is ok", 'OK'], 2 => ["The device sensor '%s' is unknown", 'UNKNOWN'], 3 => ["The device sensor '%s' is not installed", 'UNKNOWN'],
                    4 => ["The device sensor '%s' has a low voltage", 'WARNING'], 5 => ["The device sensor '%s' has a low voltage", 'CRITICAL'],
                    6 => ["The device sensor '%s' has no power", 'CRITICAL'],
                    7 => ["The device sensor '%s' has a high voltage", 'WARNING'],
                    8 => ["The device sensor '%s' has a high voltage", 'CRITICAL'],
                    9 => ["The device sensor '%s' has a very (!!!) high voltage", 'CRITICAL'],
                    10 => ["The device sensor '%s' has a high temperature", 'WARNING'],
                    11 => ["The device sensor '%s' has a high temperature", 'CRITICAL'],
                    12 => ["The device sensor '%s' has a very high (!!!) temperature", 'CRITICAL'],
                    13 => ["The fan '%s' is slow", 'WARNING'],
                    14 => ["The fan '%s' is slow", 'CRITICAL'],
                    15 => ["The fan '%s' is stopped", 'CRITICAL'],
                    );
my %disk_status = (
                    1 => ["Disk '%s' is present", 'OK'], 2 => ["Disk '%s' is initializing", 'OK'], 3 => ["Disk '%s' is inserted", 'OK'],
                    4 => ["Disk '%s' is offline", 'WARNING'], 5 => ["Disk '%s' is removed", 'WARNING'],
                    6 => ["Disk '%s' is not present", 'WARNING'],
                    7 => ["Disk '%s' is empty", 'WARNING'],
                    8 => ["Disk '%s' has io errors", 'CRITICAL'],
                    9 => ["Disk '%s' is unusable", 'CRITICAL'],
                    10 => ["Disk status '%s' is unknown", 'UNKNOWN'],
                    );

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "skip"        => { name => 'skip' },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    
    $self->{output}->output_add(severity => 'OK', 
                                short_msg => "All disks and sensors are ok.");
    
    my $oid_DeviceSensorValueEntry = '.1.3.6.1.4.1.3417.2.1.1.1.1.1';
    my ($exit, $result) = $self->{snmp}->get_table(oid => $oid_DeviceSensorValueEntry, nothing_quit => 1);
    
    for (my $i = 0; defined($result->{'.1.3.6.1.4.1.3417.2.1.1.1.1.1.9.' . $i}); $i++) {
        my $sensor_name = $result->{'.1.3.6.1.4.1.3417.2.1.1.1.1.1.9.' . $i};
        my $sensor_status = $result->{'.1.3.6.1.4.1.3417.2.1.1.1.1.1.7.' . $i};
        my $sensor_units = $result->{'.1.3.6.1.4.1.3417.2.1.1.1.1.1.3.' . $i};
        my $sensor_code = $result->{'.1.3.6.1.4.1.3417.2.1.1.1.1.1.4.' . $i};
        my $sensor_value = $result->{'.1.3.6.1.4.1.3417.2.1.1.1.1.1.5.' . $i};
        my $sensor_scale = $result->{'.1.3.6.1.4.1.3417.2.1.1.1.1.1.4.' . $i};
        
        $self->{output}->output_add(long_msg => "Device sensor '" .  $sensor_name . "' status = '" . $sensor_status  . "', code = '" . $sensor_code . "'");
        
        # Check 'nonoperationnal' and 'unavailable'
        if ($sensor_status == 2 || $sensor_status == 3) {
            if (!defined($self->{option_results}->{skip})) {
                $self->{output}->output_add(severity => 'CRITICAL',
                                            short_msg => "Device sensor '" . $sensor_name . "' is " . $sensor_status);
            }
            next;
        }
        
        if ($sensor_code != 1) {
            $self->{output}->output_add(severity => ${$device_code{$sensor_code}}[1],
                                        short_msg => sprintf(${$device_code{$sensor_code}}[0], $sensor_name));
        }
        
        $self->{output}->perfdata_add(label => $sensor_name, unit => $device_units{sensor_units},
                                      value => ($sensor_value * (10 ** $sensor_scale)));
    }
    
    ($exit, $result) = $self->{snmp}->get_table(oid => '.1.3.6.1.4.1.3417.2.2.1.1.1.1');
    for (my $i = 0; defined($result->{'.1.3.6.1.4.1.3417.2.2.1.1.1.1.8.' . $i}); $i++) {
        my $disk_serial = $result->{'.1.3.6.1.4.1.3417.2.2.1.1.1.1.8.' . $i};
        my $disk_status = $result->{'.1.3.6.1.4.1.3417.2.2.1.1.1.1.3.' . $i};
        
        if ($disk_status > 3) {
            $self->{output}->output_add(severity => ${$disk_status{$disk_status}}[1],
                                        short_msg => sprintf(${$disk_status{$disk_status}}[0], $disk_serial));
        }
        $self->{output}->output_add(long_msg => sprintf(${$disk_status{$disk_status}}[0], $disk_serial));
    }
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check bluecoat hardware sensors and disks.

=over 8

=item B<--skip>

Skip 'nonoperationnal' and 'unavailable' sensors.

=back

=cut
