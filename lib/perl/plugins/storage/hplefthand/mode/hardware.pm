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

package storage::hplefthand::mode::hardware;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::misc;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "exclude"        => { name => 'exclude' },
                                });

    $self->{product_name} = undef;
    $self->{serial} = undef;
    $self->{romversion} = undef;
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

    $self->{components_fan} = 0;
    $self->{components_rcc} = 0;
    $self->{components_temperature} = 0;
    $self->{components_psu} = 0;
    $self->{components_voltage} = 0;
    $self->{components_device} = 0;
    $self->{components_rc} = 0;
    $self->{components_ro} = 0;

    $self->get_global_information();
    $self->check_fan();
    $self->check_rcc();
    $self->check_temperature();
    $self->check_psu();
    $self->check_voltage();
    $self->check_device();
    $self->check_rc();
    $self->check_ro();
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => sprintf("All %d components [%d fans, %d power supplies, %d temperatures, %d voltages, %d raid controller caches, %d devices, %d raid controllers, %d raid os] are ok.", 
                                ($self->{components_fan} + $self->{components_rcc} + $self->{components_temperature} + $self->{components_psu} + $self->{components_voltage} + $self->{components_device} + $self->{components_rc} + $self->{components_ro}), 
                                $self->{components_fan}, $self->{components_psu}, $self->{components_temperature}, $self->{components_voltage}, $self->{components_rcc}, $self->{components_device}, $self->{components_rc}, $self->{components_ro}));
    
    $self->{output}->display();
    $self->{output}->exit();
}

sub get_global_information {
    my ($self) = @_;
    
    $self->{global_information} = $self->{snmp}->get_leef(oids => [
                 '.1.3.6.1.4.1.9804.3.1.1.2.1.110.0', # fancount
                 '.1.3.6.1.4.1.9804.3.1.1.2.1.90.0', # raid controlle cache count
                 '.1.3.6.1.4.1.9804.3.1.1.2.1.120.0', # temperature sensor
                 '.1.3.6.1.4.1.9804.3.1.1.2.1.130.0', # powersupply
                 '.1.3.6.1.4.1.9804.3.1.1.2.1.140.0', # voltage sensor
                 '.1.3.6.1.4.1.9804.3.1.1.2.4.1.0', # storage device
                 '.1.3.6.1.4.1.9804.3.1.1.2.4.3.0', # raid controller
                 '.1.3.6.1.4.1.9804.3.1.1.2.4.50.0' # raid internal
                ], nothing_quit => 1);
}

sub check_fan {
    my ($self) = @_;
    
    $self->{output}->output_add(long_msg => "Checking fan");
    return if ($self->check_exclude('fan'));
    
    my $fan_count_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.110.0"; # 0 means 'none'
    my $fan_name_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.111.1.2"; # begin .1
    my $fan_speed_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.111.1.3"; # dont have
    my $fan_min_speed_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.111.1.4"; # dont have
    my $fan_state_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.111.1.90"; # string explained
    my $fan_status_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.111.1.91";
    return if ($self->{global_information}->{$fan_count_oid} == 0);
    
    $self->{snmp}->load(oids => [$fan_name_oid, $fan_name_oid,
                                 $fan_min_speed_oid, $fan_state_oid, $fan_status_oid],
                        begin => 1, end => $self->{global_information}->{$fan_count_oid});
    my $result = $self->{snmp}->get_leef();
    return if (scalar(keys %$result) <= 0);
    
    my $number_fans = $self->{global_information}->{$fan_count_oid};
    for (my $i = 1; $i <= $number_fans; $i++) {
        my $fan_name = $result->{$fan_name_oid . "." . $i};
        my $fan_speed = $result->{$fan_speed_oid . "." . $i};
        my $fan_min_speed = $result->{$fan_min_speed_oid . "." . $i};
        my $fan_status = $result->{$fan_status_oid . "." . $i};
        my $fan_state = $result->{$fan_state_oid . "." . $i};
    
        $self->{components_fan}++;
    
        # Check Fan Speed
        if (defined($fan_speed)) {
            my $low_limit = '';
            if (defined($fan_min_speed)) {
                $low_limit = '@:' . $fan_min_speed;
                if ($fan_speed <= $fan_min_speed) {
                    $self->{output}->output_add(severity => 'CRITICAL', 
                                                short_msg => "Fan '" .  $fan_name . "' speed too low");
                }
            }
            $self->{output}->output_add(long_msg => "Fan '" .  $fan_name . "' speed = '" . $fan_speed  . "' (<= $fan_min_speed)");
            $self->{output}->perfdata_add(label => $fan_name, unit => 'rpm',
                                          value => $fan_speed,
                                          critical => $low_limit);            
        }
        
        if ($fan_status != 1) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Fan '" .  $fan_name . "' problem '" . $fan_state . "'");
        }
        $self->{output}->output_add(long_msg => "Fan '" .  $fan_name . "' status = '" . $fan_status  . "', state = '" . $fan_state . "'");
    }
}

sub check_rcc {
    my ($self) = @_;
    
    $self->{output}->output_add(long_msg => "Checking raid controller cache");
    return if ($self->check_exclude('rcc'));
    
    my $rcc_count_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.90.0";
    my $rcc_name_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.91.1.2"; # begin .1
    my $rcc_state_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.91.1.90";
    my $rcc_status_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.91.1.91";
    my $bbu_enabled_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.91.1.50"; # 1 mean 'enabled'
    my $bbu_state_oid = '.1.3.6.1.4.1.9804.3.1.1.2.1.91.1.22';
    my $bbu_status_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.91.1.23"; # 1 mean 'ok'
    return if ($self->{global_information}->{$rcc_count_oid} == 0);
    
    $self->{snmp}->load(oids => [$rcc_name_oid, $rcc_state_oid,
                                 $rcc_status_oid, $bbu_enabled_oid, $bbu_state_oid, $bbu_status_oid],
                        begin => 1, end => $self->{global_information}->{$rcc_count_oid});
    my $result = $self->{snmp}->get_leef();
    return if (scalar(keys %$result) <= 0);
    
    my $number_raid = $self->{global_information}->{$rcc_count_oid};
    for (my $i = 1; $i <= $number_raid; $i++) {
        my $raid_name = $result->{$rcc_name_oid . "." . $i};
        my $raid_state = $result->{$rcc_state_oid . "." . $i};
        my $raid_status = $result->{$rcc_status_oid . "." . $i};
        my $bbu_enabled = $result->{$bbu_enabled_oid . "." . $i};
        my $bbu_state = $result->{$bbu_state_oid . "." . $i};
        my $bbu_status = $result->{$bbu_status_oid . "." . $i};
        
        $self->{components_rcc}++;
        
        if ($raid_status != 1) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Raid Controller Caches '" .  $raid_name . "' problem '" . $raid_state . "'");
        }
        $self->{output}->output_add(long_msg => "Raid Controller Caches '" .  $raid_name . "' status = '" . $raid_status  . "', state = '" . $raid_state . "'");
        if ($bbu_enabled == 1) {
            if ($bbu_status != 1) {
                 $self->{output}->output_add(severity => 'CRITICAL', 
                                             short_msg => "BBU '" .  $raid_name . "' problem '" . $bbu_state . "'");
            }
            $self->{output}->output_add(long_msg => "   BBU status = '" . $bbu_status  . "', state = '" . $bbu_state . "'");
        } else {
            $self->{output}->output_add(long_msg => "   BBU disabled");
        }
    }
}

sub check_temperature {
    my ($self) = @_;
    
    $self->{output}->output_add(long_msg => "Checking temperature sensors");
    return if ($self->check_exclude('temperature'));
    
    my $temperature_sensor_count_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.120.0";
    my $temperature_sensor_name_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.121.1.2";
    my $temperature_sensor_value_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.121.1.3";
    my $temperature_sensor_critical_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.121.1.4";
    my $temperature_sensor_limit_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.121.1.5"; # warning. lower than critical
    my $temperature_sensor_state_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.121.1.90";
    my $temperature_sensor_status_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.121.1.91";
    return if ($self->{global_information}->{$temperature_sensor_count_oid} == 0);
    
    $self->{snmp}->load(oids => [$temperature_sensor_name_oid, $temperature_sensor_value_oid,
                                 $temperature_sensor_critical_oid, $temperature_sensor_limit_oid, $temperature_sensor_state_oid, $temperature_sensor_status_oid],
                        begin => 1, end => $self->{global_information}->{$temperature_sensor_count_oid});
    my $result = $self->{snmp}->get_leef();
    return if (scalar(keys %$result) <= 0);
    
    my $number_temperature = $self->{global_information}->{$temperature_sensor_count_oid};
    for (my $i = 1; $i <= $number_temperature; $i++) {
        my $ts_name = $result->{$temperature_sensor_name_oid . "." . $i};
        my $ts_value = $result->{$temperature_sensor_value_oid . "." . $i};
        my $ts_critical = $result->{$temperature_sensor_critical_oid . "." . $i};
        my $ts_limit = $result->{$temperature_sensor_limit_oid . "." . $i};
        my $ts_state = $result->{$temperature_sensor_state_oid . "." . $i};
        my $ts_status = $result->{$temperature_sensor_status_oid . "." . $i};
        
        $self->{components_temperature}++;
        
        if ($ts_value >= $ts_critical) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Temperature sensor '" .  $ts_name . "' too high");
        } elsif ($ts_value >= $ts_limit) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Temperature sensor '" .  $ts_name . "' over the limit");
        }
        $self->{output}->output_add(long_msg => "Temperature sensor '" .  $ts_name . "' value = '" . $ts_value  . "' (limit >= $ts_limit, critical >= $ts_critical)");
        $self->{output}->perfdata_add(label => $ts_name . "_temp",
                                      value => $ts_value,
                                      warning => $ts_limit, critical => $ts_critical);
 
        if ($ts_status != 1) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Temperature sensor '" .  $ts_name . "' problem '" . $ts_state . "'");
        }
        $self->{output}->output_add(long_msg => "Temperature sensor '" .  $ts_name . "' status = '" . $ts_status  . "', state = '" . $ts_state . "'");
    }
}

sub check_psu {
    my ($self) = @_;
    
    $self->{output}->output_add(long_msg => "Checking power supplies");
    return if ($self->check_exclude('psu'));
    
    my $power_supply_count_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.130.0";
    my $power_supply_name_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.131.1.2";
    my $power_supply_state_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.131.1.90";
    my $power_supply_status_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.131.1.91";
    return if ($self->{global_information}->{$power_supply_count_oid} == 0);
    
    $self->{snmp}->load(oids => [$power_supply_name_oid, $power_supply_name_oid,
                                 $power_supply_status_oid],
                        begin => 1, end => $self->{global_information}->{$power_supply_count_oid});
    my $result = $self->{snmp}->get_leef();
    return if (scalar(keys %$result) <= 0);
    
    my $number_ps = $self->{global_information}->{$power_supply_count_oid};
    for (my $i = 1; $i <= $number_ps; $i++) {
        my $ps_name = $result->{$power_supply_name_oid . "." . $i};
        my $ps_state = $result->{$power_supply_state_oid . "." . $i};
        my $ps_status = $result->{$power_supply_status_oid . "." . $i};
        
        $self->{components_psu}++;
        
        if ($ps_status != 1) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Power Supply '" .  $ps_name . "' problem '" . $ps_state . "'");
        }
        $self->{output}->output_add(long_msg => "Power Supply '" .  $ps_name . "' status = '" . $ps_status  . "', state = '" . $ps_state . "'");
    }
}

sub check_voltage {
    my ($self) = @_;
    
    $self->{output}->output_add(long_msg => "Checking voltage sensors");
    return if ($self->check_exclude('voltage'));
    
    my $vs_count_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.140.0";
    my $vs_name_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.141.1.2";
    my $vs_value_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.141.1.3";
    my $vs_low_limit_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.141.1.4";
    my $vs_high_limit_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.141.1.5";
    my $vs_state_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.141.1.90";
    my $vs_status_oid = ".1.3.6.1.4.1.9804.3.1.1.2.1.141.1.91";
    return if ($self->{global_information}->{$vs_count_oid} == 0);
    
    $self->{snmp}->load(oids => [$vs_name_oid, $vs_value_oid,
                                 $vs_low_limit_oid, $vs_high_limit_oid,
                                 $vs_state_oid, $vs_status_oid],
                        begin => 1, end => $self->{global_information}->{$vs_count_oid});
    my $result = $self->{snmp}->get_leef();
    return if (scalar(keys %$result) <= 0);
    
    my $number_vs = $self->{global_information}->{$vs_count_oid};
    for (my $i = 1; $i <= $number_vs; $i++) {
        my $vs_name = $result->{$vs_name_oid . "." . $i};
        my $vs_value = $result->{$vs_value_oid . "." . $i};
        my $vs_low_limit = $result->{$vs_low_limit_oid . "." . $i};
        my $vs_high_limit = $result->{$vs_high_limit_oid . "." . $i};
        my $vs_state = $result->{$vs_state_oid . "." . $i};
        my $vs_status = $result->{$vs_status_oid . "." . $i};
        
        $self->{components_voltage}++;
        
        # Check Voltage limit
        if (defined($vs_low_limit) && defined($vs_high_limit)) {
            if ($vs_value <= $vs_low_limit) {
                $self->{output}->output_add(severity => 'CRITICAL', 
                                            short_msg => "Voltage sensor '" .  $vs_name . "' too low");
            } elsif ($vs_value >= $vs_high_limit) {
                $self->{output}->output_add(severity => 'CRITICAL', 
                                            short_msg => "Voltage sensor '" .  $vs_name . "' too high");
            }
            $self->{output}->output_add(long_msg => "Voltage sensor '" .  $vs_name . "' value = '" . $vs_value  . "' (<= $vs_low_limit, >= $vs_high_limit)");
            $self->{output}->perfdata_add(label => $vs_name . "_volt",
                                          value => $vs_value,
                                          warning => '@:' . $vs_low_limit, critical => $vs_high_limit);
        }
        
        if ($vs_status != 1) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Voltage sensor '" .  $vs_name . "' problem '" . $vs_state . "'");
        }
        $self->{output}->output_add(long_msg => "Voltage sensor '" .  $vs_name . "' status = '" . $vs_status  . "', state = '" . $vs_state . "'");
    }
}

sub check_device {
    my ($self) = @_;
    
    $self->{output}->output_add(long_msg => "Checking devices");
    return if ($self->check_exclude('device'));
    
    my $device_count_oid = ".1.3.6.1.4.1.9804.3.1.1.2.4.1.0";
    my $device_name_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.14';
    my $device_serie_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.7';
    my $device_present_state_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.90';
    my $device_present_status_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.91';
    my $device_health_state_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.17'; # normal, marginal, faulty
    my $device_health_status_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.18';
    my $device_temperature_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.9';
    my $device_temperature_critical_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.10';
    my $device_temperature_limit_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.11';
    my $device_temperature_status_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.2.1.12';
    return if ($self->{global_information}->{$device_count_oid} == 0);
    
    $self->{snmp}->load(oids => [$device_name_oid, $device_serie_oid,
                                 $device_present_state_oid, $device_present_status_oid,
                                 $device_health_state_oid, $device_health_status_oid,
                                 $device_temperature_oid, $device_temperature_critical_oid,
                                 $device_temperature_limit_oid, $device_temperature_status_oid],
                        begin => 1, end => $self->{global_information}->{$device_count_oid});
    my $result = $self->{snmp}->get_leef();
    return if (scalar(keys %$result) <= 0);
    
    my $number_device = $self->{global_information}->{$device_count_oid};
    for (my $i = 1; $i <= $number_device; $i++) {
        my $device_name = $result->{$device_name_oid . "." . $i};
        my $device_serie = $result->{$device_serie_oid . "." . $i};
        my $device_present_state = $result->{$device_present_state_oid . "." . $i};
        my $device_present_status = $result->{$device_present_status_oid . "." . $i};
        my $device_health_state = $result->{$device_health_state_oid . "." . $i};
        my $device_health_status = $result->{$device_health_status_oid . "." . $i};
        my $device_temperature = $result->{$device_temperature_oid . "." . $i};
        my $device_temperature_critical = $result->{$device_temperature_critical_oid . "." . $i};
        my $device_temperature_limit = $result->{$device_temperature_limit_oid . "." . $i};
        my $device_temperature_status = $result->{$device_temperature_status_oid . "." . $i};
        
        $self->{components_device}++;
        
        $self->{output}->output_add(long_msg => "Storage Device '$device_name' and Serial Number '$device_serie', state = '$device_present_state'");
        # Check if present
        if ($device_present_state =~ /off_and_secured|off_or_removed/i) {
            next;
        }
        
        # Check global health
        if ($device_health_status != 1) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Storage Device '" .  $device_name . "' Smart Health problem '" . $device_health_state . "'");
        }
        $self->{output}->output_add(long_msg => "    Smart Health status = '" . $device_health_status  . "', Smart Health state = '" . $device_health_state . "'");
        
        # Check temperature
        if ($device_temperature >= $device_temperature_critical) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Device Storage '" . $device_name . "' temperature too high");
        } elsif ($device_temperature >= $device_temperature_limit) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Device Storage '" . $device_name . "' over the limit");
        }
        $self->{output}->output_add(long_msg => "    Temperature value = '" . $device_temperature  . "' (limit >= $device_temperature_limit, critical >= $device_temperature_critical)");
        $self->{output}->perfdata_add(label => $device_name . "_temp",
                                      value => $device_temperature,
                                      warning => $device_temperature_limit, critical => $device_temperature_critical);
    }
}

sub check_rc {
    my ($self) = @_;
    
    $self->{output}->output_add(long_msg => "Checking raid controllers");
    return if ($self->check_exclude('rc'));
    
    my $rc_count_oid = ".1.3.6.1.4.1.9804.3.1.1.2.4.3.0";
    my $rc_name_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.4.1.2';
    my $rc_state_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.4.1.90';
    my $rc_status_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.4.1.91';
    return if ($self->{global_information}->{$rc_count_oid} == 0);
    
    $self->{snmp}->load(oids => [$rc_name_oid, $rc_state_oid,
                                 $rc_status_oid],
                        begin => 1, end => $self->{global_information}->{$rc_count_oid});
    my $result = $self->{snmp}->get_leef();
    return if (scalar(keys %$result) <= 0);
    
    my $number_rc = $self->{global_information}->{$rc_count_oid};
    for (my $i = 1; $i <= $number_rc; $i++) {
        my $rc_name = $result->{$rc_name_oid . "." . $i};
        my $rc_state = $result->{$rc_state_oid . "." . $i};
        my $rc_status = $result->{$rc_status_oid . "." . $i};
        
        $self->{components_rc}++;
        
        if ($rc_status != 1) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Raid Device (Controller) '" .  $rc_name . "' problem '" . $rc_state . "'");
        }
        $self->{output}->output_add(long_msg => "Raid Device (Controller) '" .  $rc_name . "' status = '" . $rc_status  . "', state = '" . $rc_state . "'");
    }
}

sub check_ro {
    my ($self) = @_;
    
    $self->{output}->output_add(long_msg => "Checking raid os devices");
    return if ($self->check_exclude('ro'));
    
    my $raid_os_count_oid = ".1.3.6.1.4.1.9804.3.1.1.2.4.50.0";
    my $raid_os_name_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.51.1.2';
    my $raid_os_state_oid = '.1.3.6.1.4.1.9804.3.1.1.2.4.51.1.90'; # != 'normal'
    return if ($self->{global_information}->{$raid_os_count_oid} == 0);
    
    $self->{snmp}->load(oids => [$raid_os_name_oid, $raid_os_state_oid],
                        begin => 1, end => $self->{global_information}->{$raid_os_count_oid});
    my $result = $self->{snmp}->get_leef();
    return if (scalar(keys %$result) <= 0);
    
    my $number_ro = $self->{global_information}->{$raid_os_count_oid};
    for (my $i = 1; $i <= $number_ro; $i++) {
        my $ro_name = $arg_result->{values}->{$raid_os_name_oid . "." . $i};
        my $ro_state = $arg_result->{values}->{$raid_os_state_oid . "." . $i};
        
        $self->{components_ro}++;
        
        if ($ro_state !~ /normal/i) {
            $self->{output}->output_add(severity => 'CRITICAL', 
                                        short_msg => "Raid OS Device '" .  $ro_name . "' problem '" . $ro_state . "'");
        }
        $self->{output}->output_add(long_msg => "Raid OS Device '" .  $ro_name . "' state = '" . $ro_state . "'");
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

Check Hardware (fans, power supplies, temperatures, voltages, raid controller caches, devices, raid controllers, raid os).

=over 8

=item B<--exclude>

Exclude some parts (comma seperated list) (Example: --exclude=psu,rcc).

=back

=cut
    