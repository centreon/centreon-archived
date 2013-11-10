################################################################################
# Copyright 2005-2013 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
# For more information : contact@centreon.com
# Authors : Quentin Garnier <qgarnier@merethis.com>
#
####################################################################################

package hardware::server::hpproliant::mode::hardware;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %cpustatus = (
    1 => ['unknown', 'UNKNOWN'], 
    2 => ['ok', 'OK'], 
    3 => ['degraded', 'WARNING'], 
    4 => ['failed', 'CRITICAL'],
    5 => ['disabled', 'OK']
);
my %conditions = (
    1 => ['other', 'CRITICAL'], 
    2 => ['ok', 'OK'], 
    3 => ['degraded', 'WARNING'], 
    4 => ['failed', 'CRITICAL']
);
my %present_map = (
    1 => 'other',
    2 => 'absent',
    3 => 'present',
);
my %redundant_map = (
    1 => 'other',
    2 => 'not redundant',
    3 => 'redundant',
);
my %psustatus = (
    1  => 'noError',
    2  => 'generalFailure',
    3  => 'bistFailure',
    4  => 'fanFailure',
    5  => 'tempFailure',
    6  => 'interlockOpen',
    7  => 'epromFailed',
    8  => 'vrefFailed',
    9  => 'dacFailed',
    10 => 'ramTestFailed',
    11 => 'voltageChannelFailed',
    12 => 'orringdiodeFailed',
    13 => 'brownOut',
    14 => 'giveupOnStartup',
    15 => 'nvramInvalid',
    16 => 'calibrationTableInvalid',
);
my %fan_location = (
    1 => "other",
    2 => "unknown",
    3 => "system",
    4 => "systemBoard",
    5 => "ioBoard",
    6 => "cpu",
    7 => "memory",
    8 => "storage",
    9 => "removableMedia",
    10 => "powerSupply", 
    11 => "ambient",
    12 => "chassis",
    13 => "bridgeCard",
);
my %fanspeed = (
    1 => "other",
    2 => "normal",
    3 => "high",
);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "exclude"        => { name => 'exclude' },
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

    $self->{components_cpu} = 0;
    $self->{components_psu} = 0;
    $self->{components_pc} = 0;
    $self->{components_fan} = 0;
    
    # In 'CPQSINFO-MIB'
    # my $cpqSiSysSerialNum = "1.3.6.1.4.1.232.2.2.2.1.0";
    # my $cpqSiProductName = "1.3.6.1.4.1.232.2.2.4.2.0";
    # my $cpqSeSysRomVer = "1.3.6.1.4.1.232.1.2.6.1.0";
    
    $self->check_cpu();
    $self->check_psu();
    $self->check_pc();
    $self->check_fan();
    
#    $self->{output}->output_add(severity => 'OK',
#                                short_msg => sprintf("All %d components [%d fans, %d blades, %d network connectors, %d psu, %d temperatures, %d fuses] are ok.", 
#                                ($self->{components_fans} + $self->{components_blades} + $self->{components_nc} + $self->{components_psu} + $self->{components_temperatures} + $self->{components_fuse}), 
#                                $self->{components_fans}, $self->{components_blades}, $self->{components_nc}, $self->{components_psu}, $self->{components_temperatures}, $self->{components_fuse}));
    
    $self->{output}->display();
    $self->{output}->exit();
}

sub check_cpu {
    my ($self) = @_;
    # In MIB 'CPQSTDEQ-MIB.mib'
    
    $self->{output}->output_add(long_msg => "Checking cpu");
    return if ($self->check_exclude('cpu'));
    
    my $oid_cpqSeCpuUnitIndex = '.1.3.6.1.4.1.232.1.2.2.1.1.1';
    my $oid_cpqSeCpuSlot = '.1.3.6.1.4.1.232.1.2.2.1.1.2';
    my $oid_cpqSeCpuName = '.1.3.6.1.4.1.232.1.2.2.1.1.3';
    my $oid_cpqSeCpuStatus = '.1.3.6.1.4.1.232.1.2.2.1.1.6';
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqSeCpuUnitIndex);
    return if (scalar(keys %$result) <= 0);
    
    $self->{snmp}->load(oids => [$oid_cpqSeCpuSlot, $oid_cpqSeCpuName,
                                $oid_cpqSeCpuStatus],
                        instances => [keys %$result]);
    my $result2 = $self->{snmp}->get_leef();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        $key =~ /(\d+)$/;
        my $instance = $1;
    
        my $cpu_slot = $result2->{$oid_cpqSeCpuSlot . '.' . $instance};
        my $cpu_name = $result2->{$oid_cpqSeCpuName . '.' . $instance};
        my $cpu_status = $result2->{$oid_cpqSeCpuStatus . '.' . $instance};
        
        $self->{components_cpu}++;
        $self->{output}->output_add(long_msg => sprintf("cpu [slot: %s, unit: %s, name: %s] status is %s.", 
                                    $cpu_slot, $result->{$key}, $cpu_name,
                                    ${$cpustatus{$cpu_status}}[0]));
        if (${$cpustatus{$cpu_status}}[1] ne 'OK') {
            $self->{output}->output_add(severity => ${$cpustatus{$cpu_status}}[1],
                                        short_msg => sprintf("cpu %d is %s", 
                                            $result->{$key}, ${$cpustatus{$cpu_status}}[0]));
        }
    }    
}

sub check_psu {
    my ($self) = @_;

    # In MIB 'CPQHLTH-MIB.mib'
    $self->{output}->output_add(long_msg => "Checking power supplies");
    return if ($self->check_exclude('psu'));
    
    my $oid_cpqHeFltTolPowerSupplyPresent = '.1.3.6.1.4.1.232.6.2.9.3.1.3';
    my $oid_cpqHeFltTolPowerSupplyChassis = '.1.3.6.1.4.1.232.6.2.9.3.1.1';
    my $oid_cpqHeFltTolPowerSupplyBay = '.1.3.6.1.4.1.232.6.2.9.3.1.2';
    my $oid_cpqHeFltTolPowerSupplyCondition = '.1.3.6.1.4.1.232.6.2.9.3.1.4';
    my $oid_cpqHeFltTolPowerSupplyStatus = '.1.3.6.1.4.1.232.6.2.9.3.1.5';
    my $oid_cpqHeFltTolPowerSupplyRedundant = '.1.3.6.1.4.1.232.6.2.9.3.1.9';
    my $oid_cpqHeFltTolPowerSupplyCapacityUsed = '.1.3.6.1.4.1.232.6.2.9.3.1.7'; # Watts
    my $oid_cpqHeFltTolPowerSupplyCapacityMaximum = '.1.3.6.1.4.1.232.6.2.9.3.1.8';
    my $oid_cpqHeFltTolPowerSupplyMainVoltage = '.1.3.6.1.4.1.232.6.2.9.3.1.6'; # Volts
    my $oid_cpqHeFltTolPowerSupplyRedundantPartner = '.1.3.6.1.4.1.232.6.2.9.3.1.17';
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqHeFltTolPowerSupplyPresent);
    return if (scalar(keys %$result) <= 0);
    my @get_oids = ();
    my @oids_end = ();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($present_map{$result->{$key}} ne 'present');
        $key =~ /\.([0-9]+)$/;
        my $oid_end = $1;
        
        push @oids_end, $oid_end;
        push @get_oids, $oid_cpqHeFltTolPowerSupplyChassis . "." . $oid_end, $oid_cpqHeFltTolPowerSupplyBay . "." . $oid_end,
                $oid_cpqHeFltTolPowerSupplyCondition . "." . $oid_end, $oid_cpqHeFltTolPowerSupplyStatus . "." . $oid_end,
                $oid_cpqHeFltTolPowerSupplyRedundant . "." . $oid_end, $oid_cpqHeFltTolPowerSupplyCapacityUsed . "." . $oid_end,
                $oid_cpqHeFltTolPowerSupplyCapacityMaximum . "." . $oid_end, $oid_cpqHeFltTolPowerSupplyMainVoltage . "." . $oid_end,
                $oid_cpqHeFltTolPowerSupplyRedundantPartner . "." . $oid_end;
    }
    $result = $self->{snmp}->get_leef(oids => \@get_oids);
    my $total_watts = 0;
    foreach (@oids_end) {
        my $psu_chassis = $result->{$oid_cpqHeFltTolPowerSupplyChassis . '.' . $_};
        my $psu_bay = $result->{$oid_cpqHeFltTolPowerSupplyBay . '.' . $_};
        my $psu_condition = $result->{$oid_cpqHeFltTolPowerSupplyCondition . '.' . $_};
        my $psu_status = $result->{$oid_cpqHeFltTolPowerSupplyStatus . '.' . $_};
        my $psu_redundant = $result->{$oid_cpqHeFltTolPowerSupplyRedundant . '.' . $_};
        my $psu_redundantpartner = $result->{$oid_cpqHeFltTolPowerSupplyRedundantPartner . '.' . $_};
        my $psu_capacityused = $result->{$oid_cpqHeFltTolPowerSupplyCapacityUsed . '.' . $_};
        my $psu_capacitymaximum = $result->{$oid_cpqHeFltTolPowerSupplyCapacityMaximum . '.' . $_};
        my $psu_voltage = $result->{$oid_cpqHeFltTolPowerSupplyMainVoltage . '.' . $_};

        $self->{components_psu}++;
        $self->{output}->output_add(long_msg => sprintf("powersupply %d status is %s [chassis: %s, redundance: %s, redundant partner: %s] (status %s).",
                                    $psu_bay, ${$conditions{$psu_condition}}[0],
                                    $psu_chassis, $redundant_map{$psu_redundant}, $psu_redundantpartner,
                                    $psustatus{$psu_status}
                                    ));
        if ($psu_condition != 2) {
            $self->{output}->output_add(severity =>  ${$conditions{$psu_condition}}[1],
                                        short_msg => sprintf("powersupply %d status is %s",
                                           $psu_bay, ${$conditions{$psu_condition}}[0]));
        }
        
        $self->{output}->perfdata_add(label => "psu_" . $psu_bay . "_power", unit => 'W',
                                      value => $psu_capacityused,
                                      critical => $psu_capacitymaximum);
        $self->{output}->perfdata_add(label => "psu_" . $psu_bay . "_voltage", unit => 'V',
                                      value => $psu_voltage);
    }
}

sub check_pc {
    my ($self) = @_;

    # In MIB 'CPQHLTH-MIB.mib'
    $self->{output}->output_add(long_msg => "Checking power converters");
    return if ($self->check_exclude('pc'));
    
    my $oid_cpqHePwrConvPresent = '.1.3.6.1.4.1.232.6.2.13.3.1.3';
    my $oid_cpqHePwrConvIndex = '.1.3.6.1.4.1.232.6.2.13.3.1.2';
    my $oid_cpqHePwrConvChassis = '.1.3.6.1.4.1.232.6.2.13.3.1.1';
    my $oid_cpqHePwrConvCondition = '.1.3.6.1.4.1.232.6.2.13.3.1.8';
    my $oid_cpqHePwrConvRedundant = '.1.3.6.1.4.1.232.6.2.13.3.1.6';
    my $oid_cpqHePwrConvRedundantGroupId = '.1.3.6.1.4.1.232.6.2.13.3.1.7';
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqHePwrConvPresent);
    return if (scalar(keys %$result) <= 0);
    my @get_oids = ();
    my @oids_end = ();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($present_map{$result->{$key}} ne 'present');
        $key =~ /\.([0-9]+)$/;
        my $oid_end = $1;
        
        push @oids_end, $oid_end;
        push @get_oids, $oid_cpqHePwrConvIndex . "." . $oid_end, $oid_cpqHePwrConvChassis . "." . $oid_end,
                $oid_cpqHePwrConvCondition . "." . $oid_end, $oid_cpqHePwrConvRedundant . "." . $oid_end,
                $oid_cpqHePwrConvRedundantGroupId . "." . $oid_end;
    }
    $result = $self->{snmp}->get_leef(oids => \@get_oids);
    foreach (@oids_end) {
        my $pc_chassis = $result->{$oid_cpqHePwrConvChassis . '.' . $_};
        my $pc_index = $result->{$oid_cpqHePwrConvIndex . '.' . $_};
        my $pc_condition = $result->{$oid_cpqHePwrConvIndex . '.' . $_};
        my $pc_redundant = $result->{$oid_cpqHePwrConvRedundant . '.' . $_};
        my $pc_redundantgroup = $result->{$oid_cpqHePwrConvRedundantGroupId . '.' . $_};

        $self->{components_psu}++;
        $self->{output}->output_add(long_msg => sprintf("powerconverter %d status is %s [chassis: %s, redundance: %s, redundant group: %s].",
                                    $pc_index, ${$conditions{$pc_condition}}[0],
                                    $pc_chassis, $redundant_map{$pc_redundant}, $pc_redundantgroup
                                    ));
        if ($pc_condition != 2) {
            $self->{output}->output_add(severity =>  ${$conditions{$pc_condition}}[1],
                                        short_msg => sprintf("powerconverter %d status is %s",
                                           $pc_index, ${$conditions{$pc_condition}}[0]));
        }
    }
}

sub check_fan {
    my ($self) = @_;

    # In MIB 'CPQHLTH-MIB.mib'
    $self->{output}->output_add(long_msg => "Checking fans");
    return if ($self->check_exclude('fan'));
    
    my $oid_cpqHeFltTolFanPresent = '.1.3.6.1.4.1.232.6.2.6.7.1.4';
    my $oid_cpqHeFltTolFanChassis = '.1.3.6.1.4.1.232.6.2.6.7.1.1';
    my $oid_cpqHeFltTolFanIndex = '.1.3.6.1.4.1.232.6.2.6.7.1.2';
    my $oid_cpqHeFltTolFanLocale = '.1.3.6.1.4.1.232.6.2.6.7.1.3';
    my $oid_cpqHeFltTolFanCondition = '.1.3.6.1.4.1.232.6.2.6.7.1.9';
    my $oid_cpqHeFltTolFanSpeed = '.1.3.6.1.4.1.232.6.2.6.7.1.6';
    my $oid_cpqHeFltTolFanCurrentSpeed = '.1.3.6.1.4.1.232.6.2.6.7.1.12';
    my $oid_cpqHeFltTolFanRedundant = '.1.3.6.1.4.1.232.6.2.6.7.1.7';
    my $oid_cpqHeFltTolFanRedundantPartner = '.1.3.6.1.4.1.232.6.2.6.7.1.8';

    my $result = $self->{snmp}->get_table(oid => $oid_cpqHeFltTolFanPresent);
    return if (scalar(keys %$result) <= 0);
    my @get_oids = ();
    my @oids_end = ();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($present_map{$result->{$key}} ne 'present');
        $key =~ /\.([0-9]+)$/;
        my $oid_end = $1;
        
        push @oids_end, $oid_end;
        push @get_oids, $oid_cpqHeFltTolFanChassis . "." . $oid_end, $oid_cpqHeFltTolFanIndex . "." . $oid_end,
                $oid_cpqHeFltTolFanLocale . "." . $oid_end, $oid_cpqHeFltTolFanCondition . "." . $oid_end,
                $oid_cpqHeFltTolFanCurrentSpeed . "." . $oid_end, $oid_cpqHeFltTolFanSpeed . "." . $oid_end, 
                $oid_cpqHeFltTolFanRedundant . "." . $oid_end, $oid_cpqHeFltTolFanRedundantPartner . "." . $oid_end;
    }
    $result = $self->{snmp}->get_leef(oids => \@get_oids);
    my $total_watts = 0;
    foreach (@oids_end) {
        my $fan_chassis = $result->{$oid_cpqHeTemperatureCelsius . '.' . $_};
        my $fan_index = $result->{$oid_cpqHeFltTolFanIndex . '.' . $_};
        my $fan_locale = $result->{$oid_cpqHeFltTolFanLocale . '.' . $_};
        my $fan_condition = $result->{$oid_cpqHeFltTolFanCondition . '.' . $_};
        my $fan_speed = $result->{$oid_cpqHeFltTolFanSpeed . '.' . $_};
        my $fan_currentspeed = $result->{$oid_cpqHeFltTolFanCurrentSpeed . '.' . $_};
        my $fan_redundant = $result->{$oid_cpqHeFltTolFanRedundant . '.' . $_};
        my $fan_redundantpartner = $result->{$oid_cpqHeFltTolFanRedundantPartner . '.' . $_};

        $self->{components_fan}++;
        $self->{output}->output_add(long_msg => sprintf("fan %d status is %s, speed is %s [chassis: %s, location: %s, redundance: %s, redundant partner: %s].",
                                    $fan_index, ${$conditions{$fan_condition}}[0], $fanspeed{$fan_speed},
                                    $fan_chassis, $fan_location{$fan_locale},
                                    $redundant_map{$fan_redundant}, $fan_redundantpartner
                                    ));
        if ($fan_condition != 2) {
            $self->{output}->output_add(severity =>  ${$conditions{$fan_condition}}[1],
                                        short_msg => sprintf("fan %d status is %s",
                                           $fan_index, ${$conditions{$fan_condition}}[0]));
        }

        $self->{output}->perfdata_add(label => "fan_" . $fan_index . "_speed", unit => 'rpm',
                                      value => $fan_currentspeed);
    }
}

sub check_exclude {
    my ($self, $section) = @_;

    if (defined($self->{option_results}->{exclude}) && $self->{option_results}->{exclude} =~ /(^|\s|,)$section(\s|,|$)/) {
        $self->{output}->output_add(long_msg => sprintf("Skipping $section section."));
        return 1;
    }
    return 0;
}

1;

__END__

=head1 MODE

Check Hardware (CPUs, Power Supplies, Power converters, Fans).

=over 8

=item B<--exclude>

Exclude some parts (comma seperated list) (Example: --exclude=psu,pc).

=back

=cut
    