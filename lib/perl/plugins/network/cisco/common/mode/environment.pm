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

package network::cisco::common::mode::environment;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %map_type_mon = (
    1 => 'oldAgs',
    2 => 'ags',
    3 => 'c7000',
    4 => 'ci',
    6 => 'cAccessMon',
    7 => 'cat6000',
    8 => 'ubr7200',
    9 => 'cat4000',
    10 => 'c10000',
    11 => 'osr7600',
    12 => 'c7600',
    13 => 'c37xx',
    14 => 'other'
);
my %states = (
    1 => ['normal', 'OK'], 
    2 => ['warning', 'WARNING'], 
    3 => ['critical', 'CRITICAL'], 
    4 => ['shutdown', 'OK'],
    5 => ['not present', 'OK'],
    6 => ['not functioning', 'WARNING'],
);
my %map_psu_source = (
    1 => 'unknown',
    2 => 'ac',
    3 => 'dc',
    4 => 'externalPowerSupply',
    5 => 'internalRedundant'
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

    $self->{components_fans} = 0;
    $self->{components_psus} = 0;
    $self->{components_temperatures} = 0;
    $self->{components_voltages} = 0;
    
    $self->get_type();
    $self->check_fans();
    $self->check_psus();
    $self->check_temperatures();
    $self->check_voltages();
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => sprintf("All %d components [%d fans, %d power supplies, %d temperatures, %d voltages] are ok, Environment type: %s", 
                                ($self->{components_fans} + $self->{components_psus} + $self->{components_temperatures} + $self->{components_voltages}), 
                                $self->{components_fans}, $self->{components_psus}, $self->{components_temperatures}, $self->{components_voltages}, $self->{env_type}));
    
    $self->{output}->display();
    $self->{output}->exit();
}

sub get_type {
    my ($self) = @_;

    my $oid_ciscoEnvMonPresent = ".1.3.6.1.4.1.9.9.13.1.1.0";
    
    my $result = $self->{snmp}->get_leef(oids => [$oid_ciscoEnvMonPresent]);
    
    $self->{env_type} = defined($result->{$oid_ciscoEnvMonPresent}) ? $map_type_mon{$result->{$oid_ciscoEnvMonPresent}} : 'unknown';
}

sub check_fans {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking fans");
    return if ($self->check_exclude('fans'));
    
    my $oid_ciscoEnvMonFanStatusEntry = '.1.3.6.1.4.1.9.9.13.1.4.1';
    my $oid_ciscoEnvMonFanStatusDescr = '.1.3.6.1.4.1.9.9.13.1.4.1.2';
    my $oid_ciscoEnvMonFanState = '.1.3.6.1.4.1.9.9.13.1.4.1.3';
    
    my $result = $self->{snmp}->get_table(oid => $oid_ciscoEnvMonFanStatusEntry);
    return if (scalar(keys %$result) <= 0);

    foreach my $oid (keys %$result) {
        next if ($oid !~ /^$oid_ciscoEnvMonFanStatusDescr/);
        $oid =~ /\.([0-9]+)$/;
        my $instance = $1;
    
        my $fan_descr = $result->{$oid};
        my $fan_state = $result->{$oid_ciscoEnvMonFanState . '.' . $instance};
        
        $self->{components_fans}++;
        $self->{output}->output_add(long_msg => sprintf("Fan '%s' state is %s.", 
                                    $fan_descr, ${$states{$fan_state}}[0]));
        if (${$states{$fan_state}}[1] ne 'OK') {
            $self->{output}->output_add(severity =>  ${$states{$fan_state}}[1],
                                        short_msg => sprintf("Fan '%s' state is %s.", $fan_descr, ${$states{$fan_state}}[0]));
        }
    }
}

sub check_psus {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking power supplies");
    return if ($self->check_exclude('psu'));
    
    my $oid_ciscoEnvMonSupplyStatusEntry = '.1.3.6.1.4.1.9.9.13.1.5.1';
    my $oid_ciscoEnvMonSupplyStatusDescr = '.1.3.6.1.4.1.9.9.13.1.5.1.2';
    my $oid_ciscoEnvMonSupplyState = '.1.3.6.1.4.1.9.9.13.1.5.1.3';
    my $oid_ciscoEnvMonSupplySource = '.1.3.6.1.4.1.9.9.13.1.5.1.4';
    
    my $result = $self->{snmp}->get_table(oid => $oid_ciscoEnvMonSupplyStatusEntry);
    return if (scalar(keys %$result) <= 0);

    foreach my $oid (keys %$result) {
        next if ($oid !~ /^$oid_ciscoEnvMonSupplyStatusDescr/);
        $oid =~ /\.([0-9]+)$/;
        my $instance = $1;
    
        my $psu_descr = $result->{$oid};
        my $psu_state = $result->{$oid_ciscoEnvMonSupplyState . '.' . $instance};
        my $psu_source = $result->{$oid_ciscoEnvMonSupplySource . '.' . $instance};
        
        $self->{components_psus}++;
        $self->{output}->output_add(long_msg => sprintf("Power Supply '%s' [type: %s] state is %s.", 
                                    $psu_descr, $map_psu_source{$psu_source}, ${$states{$psu_state}}[0]));
        if (${$states{$psu_state}}[1] ne 'OK') {
            $self->{output}->output_add(severity =>  ${$states{$psu_state}}[1],
                                        short_msg => sprintf("Power Supply '%s' state is %s.", $psu_descr, ${$states{$psu_state}}[0]));
        }
    }
}

sub check_voltages {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking voltages");
    return if ($self->check_exclude('voltages'));
    
    my $oid_ciscoEnvMonVoltageStatusEntry = '.1.3.6.1.4.1.9.9.13.1.2.1';
    my $oid_ciscoEnvMonVoltageStatusDescr = '.1.3.6.1.4.1.9.9.13.1.2.1.2';
    my $oid_ciscoEnvMonVoltageStatusValue = '.1.3.6.1.4.1.9.9.13.1.2.1.3';
    my $oid_ciscoEnvMonVoltageThresholdLow = '.1.3.6.1.4.1.9.9.13.1.2.1.4';
    my $oid_ciscoEnvMonVoltageThresholdHigh = '.1.3.6.1.4.1.9.9.13.1.2.1.5';
    my $oid_ciscoEnvMonVoltageState = '.1.3.6.1.4.1.9.9.13.1.2.1.7';
    
    my $result = $self->{snmp}->get_table(oid => $oid_ciscoEnvMonVoltageStatusEntry);
    return if (scalar(keys %$result) <= 0);

    foreach my $oid (keys %$result) {
        next if ($oid !~ /^$oid_ciscoEnvMonVoltageStatusDescr/);
        $oid =~ /\.([0-9]+)$/;
        my $instance = $1;
    
        my $voltage_descr = $result->{$oid};
        my $voltage_state = $result->{$oid_ciscoEnvMonVoltageState . '.' . $instance};
        my $voltage_value = $result->{$oid_ciscoEnvMonVoltageStatusValue . '.' . $instance};
        my $voltage_low = $result->{$oid_ciscoEnvMonVoltageThresholdLow . '.' . $instance};
        my $voltage_high = $result->{$oid_ciscoEnvMonVoltageThresholdHigh . '.' . $instance};
        
        $self->{components_voltages}++;
        $self->{output}->output_add(long_msg => sprintf("Voltage '%s' state is %s.", 
                                    $voltage_descr, ${$states{$voltage_state}}[0]));
        if (${$states{$voltage_state}}[1] ne 'OK') {
            $self->{output}->output_add(severity =>  ${$states{$voltage_state}}[1],
                                        short_msg => sprintf("Power Supply '%s' state is %s.", $voltage_descr, ${$states{$voltage_state}}[0]));
        }
        
        $self->{output}->perfdata_add(label => 'voltage_' . $voltage_descr, unit => 'V',
                                      value => $voltage_value,
                                      critical => $voltage_low . ":" . $voltage_high);
    }
}

sub check_temperatures {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking temperatures");
    return if ($self->check_exclude('temperatures'));
    
    my $oid_ciscoEnvMonTemperatureStatusEntry = '.1.3.6.1.4.1.9.9.13.1.3.1';
    my $oid_ciscoEnvMonTemperatureStatusDescr = '.1.3.6.1.4.1.9.9.13.1.3.1.2';
    my $oid_ciscoEnvMonTemperatureStatusValue = '.1.3.6.1.4.1.9.9.13.1.3.1.3';
    my $oid_ciscoEnvMonTemperatureThreshold = '.1.3.6.1.4.1.9.9.13.1.3.1.4';
    my $oid_ciscoEnvMonTemperatureState = '.1.3.6.1.4.1.9.9.13.1.3.1.6';
    
    my $result = $self->{snmp}->get_table(oid => $oid_ciscoEnvMonTemperatureStatusEntry);
    return if (scalar(keys %$result) <= 0);

    foreach my $oid (keys %$result) {
        next if ($oid !~ /^$oid_ciscoEnvMonTemperatureStatusDescr/);
        $oid =~ /\.([0-9]+)$/;
        my $instance = $1;
    
        my $temp_descr = $result->{$oid};
        my $temp_state = $result->{$oid_ciscoEnvMonTemperatureState . '.' . $instance};
        my $temp_value = $result->{$oid_ciscoEnvMonTemperatureStatusValue . '.' . $instance};
        my $temp_threshold = $result->{$oid_ciscoEnvMonTemperatureThreshold . '.' . $instance};
        
        $self->{components_temperatures}++;
        $self->{output}->output_add(long_msg => sprintf("Temperature '%s' state is %s.", 
                                    $temp_descr, ${$states{$temp_state}}[0]));
        if (${$states{$temp_state}}[1] ne 'OK') {
            $self->{output}->output_add(severity =>  ${$states{$temp_state}}[1],
                                        short_msg => sprintf("Temperature '%s' state is %s.", $temp_descr, ${$states{$temp_state}}[0]));
        }
        
        $self->{output}->perfdata_add(label => 'temp_' . $temp_descr, unit => 'C',
                                      value => $temp_value,
                                      critical => "~:" . $temp_threshold);
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

Check Environment monitor (CISCO-ENVMON-MIB) (Fans, Power Supplies, Temperatures, Voltages).

=over 8

=item B<--exclude>

Exclude some parts (comma seperated list) (Example: --exclude=temperatures,psu).

=back

=cut
    