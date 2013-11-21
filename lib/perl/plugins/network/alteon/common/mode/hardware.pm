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

package network::alteon::common::mode::hardware;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %states_temp_cpu = (
    1 => ['normal', 'OK'], 
    2 => ['warning', 'WARNING'],
    3 => ['critical', 'CRITICAL'],     
);
my %states_temp = (
    1 => ['ok', 'OK'], 
    2 => ['exceed', 'WARNING'], 
);
my %states_psu = (
    1 => ['single power supply ok', 'WARNING'], 
    2 => ['first powerSupply failed', 'CRITICAL'],
    3 => ['second power supply failed', 'CRITICAL'],
    4 => ['double power supply ok', 'OK'],
    5 => ['unknown power supply failed', 'UNKNOWN'],
);
my %states_fan = (
    1 => ['ok', 'OK'], 
    2 => ['fail', 'CRITICAL'],
);
my $oid_hwTemperatureStatus = '.1.3.6.1.4.1.1872.2.5.1.3.1.3.0';
my $oid_hwFanStatus = '.1.3.6.1.4.1.1872.2.5.1.3.1.4.0';
my $oid_hwTemperatureThresholdStatusCPU1Get = '.1.3.6.1.4.1.1872.2.5.1.3.1.28.3.0';
my $oid_hwTemperatureThresholdStatusCPU2Get = '.1.3.6.1.4.1.1872.2.5.1.3.1.28.4.0';
my $oid_hwPowerSupplyStatus = '.1.3.6.1.4.1.1872.2.5.1.3.1.29.2.0';

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
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
    
    $self->{global_result} = $self->{snmp}->get_leef(oids => [$oid_hwTemperatureStatus, $oid_hwFanStatus, 
                                                     $oid_hwTemperatureThresholdStatusCPU1Get, $oid_hwTemperatureThresholdStatusCPU2Get,
                                                     $oid_hwPowerSupplyStatus],
                                                     nothing_quit => 1);
    
    $self->check_fans();
    $self->check_psus();
    $self->check_temperatures();
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => sprintf("All %d components [%d fans, %d power supplies, %d temperatures] are ok", 
                                ($self->{components_fans} + $self->{components_psus} + $self->{components_temperatures}), 
                                $self->{components_fans}, $self->{components_psus}, $self->{components_temperatures}));
    
    $self->{output}->display();
    $self->{output}->exit();
}

sub check_fans {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking fans");
    return if (!defined($self->{global_result}->{$oid_hwFanStatus}));
    
    $self->{components_fans}++;
    my $fan_state = $self->{global_result}->{$oid_hwFanStatus};
  
    $self->{output}->output_add(long_msg => sprintf("Fan status is %s.", ${$states_fan{$fan_state}}[0]));
    if (${$states_fan{$fan_state}}[1] ne 'OK') {
        $self->{output}->output_add(severity =>  ${$states_fan{$fan_state}}[1],
                                    short_msg => sprintf("Fan status is %s.", ${$states_fan{$fan_state}}[0]));
    }
}

sub check_psus {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking power supplies");
    return if (!defined($self->{global_result}->{$oid_hwPowerSupplyStatus}));
    
    $self->{components_psus}++;
    my $psu_state = $self->{global_result}->{$oid_hwPowerSupplyStatus};
  
    $self->{output}->output_add(long_msg => sprintf("Power supplies status is %s.", ${$states_psu{$psu_state}}[0]));
    if (${$states_psu{$psu_state}}[1] ne 'OK') {
        $self->{output}->output_add(severity =>  ${$states_psu{$psu_state}}[1],
                                    short_msg => sprintf("Power supplies status is %s.", ${$states_psu{$psu_state}}[0]));
    }
}

sub check_temperatures {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking temperatures global");
    return if (!defined($self->{global_result}->{$oid_hwTemperatureStatus}));
    
    $self->{components_temperatures}++;
    my $temp_state = $self->{global_result}->{$oid_hwTemperatureStatus};
  
    $self->{output}->output_add(long_msg => sprintf("Global temperature sensor status is %s.", ${$states_temp{$temp_state}}[0]));
    if (${$states_temp{$temp_state}}[1] ne 'OK') {
        $self->{output}->output_add(severity =>  ${$states_temp{$temp_state}}[1],
                                    short_msg => sprintf("Global temperature sensor  status is %s.", ${$states_temp{$temp_state}}[0]));
    }
    
    $self->{output}->output_add(long_msg => "Checking temperatures cpus");
    return if (!defined($self->{global_result}->{$oid_hwTemperatureThresholdStatusCPU1Get}) && 
               !defined($self->{global_result}->{$oid_hwTemperatureThresholdStatusCPU2Get}));
    
    $self->{components_temperatures} += 2;
    my $temp_cpu1_state = $self->{global_result}->{$oid_hwTemperatureThresholdStatusCPU1Get};
    my $temp_cpu2_state = $self->{global_result}->{$oid_hwTemperatureThresholdStatusCPU2Get};
  
    $self->{output}->output_add(long_msg => sprintf("Temperature cpu 1 status is %s.", ${$states_temp_cpu{$temp_cpu1_state}}[0]));
    if (${$states_temp_cpu{$temp_cpu1_state}}[1] ne 'OK') {
        $self->{output}->output_add(severity =>  ${$states_temp_cpu{$temp_cpu1_state}}[1],
                                    short_msg => sprintf("Temperature cpu 1 status is %s.", ${$states_temp_cpu{$temp_cpu1_state}}[0]));
    }
    
    $self->{output}->output_add(long_msg => sprintf("Temperature cpu 2 status is %s.", ${$states_temp_cpu{$temp_cpu2_state}}[0]));
    if (${$states_temp_cpu{$temp_cpu2_state}}[1] ne 'OK') {
        $self->{output}->output_add(severity =>  ${$states_temp_cpu{$temp_cpu2_state}}[1],
                                    short_msg => sprintf("Temperature cpu 2 status is %s.", ${$states_temp_cpu{$temp_cpu2_state}}[0]));
    }
}

1;

__END__

=head1 MODE

Check Hardware (ALTEON-CHEETAH-SWITCH-MIB) (Fans, Power Supplies, Temperatures).

=over 8

=back

=cut
    