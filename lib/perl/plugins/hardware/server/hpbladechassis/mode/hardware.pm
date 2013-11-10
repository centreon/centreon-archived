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

package hardware::server::hpbladechassis::mode::hardware;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %conditions = (1 => ['other', 'CRITICAL'], 
                  2 => ['ok', 'OK'], 
                  3 => ['degraded', 'WARNING'], 
                  4 => ['failed', 'CRITICAL']);
my %present_map = (1 => 'other',
                   2 => 'absent',
                   3 => 'present',
                   4 => 'Weird!!!', # for blades it can return 4, which is NOT spesified in MIB
);
my %device_type = (1 => 'noconnect', 
                   2 => 'network',
                   3 => 'fibrechannel',
                   4 => 'sas',
                   5 => 'inifiband',
                   6 => 'pciexpress'
);
my %psu_status = (1  => 'noError',
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
my %inputline_status = (1 => 'noError',
                        2 => 'lineOverVoltage',
                        3 => 'lineUnderVoltage',
                        4 => 'lineHit',
                        5 => 'brownOut',
                        6 => 'linePowerLoss',
);
my %map_role = (1 => 'Standby',
                2 => 'Active',
);
my %map_has = (1 => 'false',
               2 => 'true',
);
my %map_temp_type = (1 => 'other',
                     5 => 'blowout',
                     9 => 'caution',
                     15 => 'critical',
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
    $self->{components_blades} = 0;
    $self->{components_nc} = 0;
    $self->{components_psu} = 0;
    $self->{components_temperatures} = 0;
    $self->{components_fuse} = 0;
    
    $self->check_enclosure_status();
    $self->check_managers();
    $self->check_fans();
    $self->check_blades();
    $self->check_iom();
    $self->check_psu();
    $self->check_temperatures();
    $self->check_fuse();
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => sprintf("All %d components [%d fans, %d blades, %d network connectors, %d psu, %d temperatures, %d fuses] are ok.", 
                                ($self->{components_fans} + $self->{components_blades} + $self->{components_nc} + $self->{components_psu} + $self->{components_temperatures} + $self->{components_fuse}), 
                                $self->{components_fans}, $self->{components_blades}, $self->{components_nc}, $self->{components_psu}, $self->{components_temperatures}, $self->{components_fuse}));
    
    $self->{output}->display();
    $self->{output}->exit();
}

sub check_enclosure_status {
    my ($self) = @_;

    my $oid_cpqRackCommonEnclosurePartNumber = '.1.3.6.1.4.1.232.22.2.3.1.1.1.5.1';
    my $oid_cpqRackCommonEnclosureSparePartNumber = '.1.3.6.1.4.1.232.22.2.3.1.1.1.6.1';
    my $oid_cpqRackCommonEnclosureSerialNum = '.1.3.6.1.4.1.232.22.2.3.1.1.1.7.1';
    my $oid_cpqRackCommonEnclosureFWRev = '.1.3.6.1.4.1.232.22.2.3.1.1.1.8.1';
    my $oid_cpqRackCommonEnclosureCondition = '.1.3.6.1.4.1.232.22.2.3.1.1.1.16.1';
    my $oid_cpqRackCommonEnclosureHasServerBlades = '.1.3.6.1.4.1.232.22.2.3.1.1.1.17.1';
    my $oid_cpqRackCommonEnclosureHasPowerSupplies = '.1.3.6.1.4.1.232.22.2.3.1.1.1.18.1';
    my $oid_cpqRackCommonEnclosureHasNetConnectors = '.1.3.6.1.4.1.232.22.2.3.1.1.1.19.1';
    my $oid_cpqRackCommonEnclosureHasTempSensors = '.1.3.6.1.4.1.232.22.2.3.1.1.1.20.1';
    my $oid_cpqRackCommonEnclosureHasFans = '.1.3.6.1.4.1.232.22.2.3.1.1.1.21.1';
    my $oid_cpqRackCommonEnclosureHasFuses = '.1.3.6.1.4.1.232.22.2.3.1.1.1.22.1';
    #my $oid_cpqRackServerBladeHasManagementDevice = '.1.3.6.1.4.1.232.22.2.4.1.1.1.29.1';
    
    $self->{global_results} = $self->{snmp}->get_leef(oids => [$oid_cpqRackCommonEnclosurePartNumber, $oid_cpqRackCommonEnclosureSparePartNumber, 
                                                    $oid_cpqRackCommonEnclosureSerialNum, $oid_cpqRackCommonEnclosureFWRev,
                                                    $oid_cpqRackCommonEnclosureCondition, $oid_cpqRackCommonEnclosureHasServerBlades,
                                                    $oid_cpqRackCommonEnclosureHasPowerSupplies, $oid_cpqRackCommonEnclosureHasNetConnectors,
                                                    $oid_cpqRackCommonEnclosureHasTempSensors, $oid_cpqRackCommonEnclosureHasFans, 
                                                    $oid_cpqRackCommonEnclosureHasFuses], nothing_quit => 1);

    $self->{output}->output_add(long_msg => sprintf("Enclosure overall health condition is %s [part: %s, spare: %s, sn: %s, fw: %s].", 
                                ${$conditions{$self->{global_results}->{$oid_cpqRackCommonEnclosureCondition}}}[0],
                                $self->{global_results}->{$oid_cpqRackCommonEnclosurePartNumber},
                                $self->{global_results}->{$oid_cpqRackCommonEnclosureSparePartNumber},
                                $self->{global_results}->{$oid_cpqRackCommonEnclosureSerialNum},
                                $self->{global_results}->{$oid_cpqRackCommonEnclosureFWRev}));
    if ($self->{global_results}->{$oid_cpqRackCommonEnclosureCondition} != 2) {
        $self->{output}->output_add(severity =>  ${$conditions{$self->{global_results}->{$oid_cpqRackCommonEnclosureCondition}}}[1],
                                    short_msg => sprintf("Enclosure overall health condition is %s", ${$conditions{$self->{global_results}->{$oid_cpqRackCommonEnclosureCondition}}}[0]));
    }
}

sub check_managers {
    my ($self) = @_;

    #my $oid_cpqRackServerBladeHasManagementDevice = '.1.3.6.1.4.1.232.22.2.4.1.1.1.29.1';
    #if (defined($map_has{$global_results->{$oid_cpqRackServerBladeHasManagementDevice}}) && 
    #   $map_has{$global_results->{$oid_cpqRackServerBladeHasManagementDevice}} =~ /^false$/i) {
    #   output_add(long_msg => sprintf("Skipping Managers: enclosure doesnt contain managers."));
    #   return ;
    #}
    
    return if ($self->check_exclude('managers'));

    # No check if OK
    if ($self->{output}->is_status(compare => 'ok', litteral => 1)) {
        return ;
    }
    $self->{output}->output_add(long_msg => "Checking managers");
    
    my $oid_cpqRackCommonEnclosureManagerIndex = '.1.3.6.1.4.1.232.22.2.3.1.6.1.3';
    my $oid_cpqRackCommonEnclosureManagerPartNumber = '.1.3.6.1.4.1.232.22.2.3.1.6.1.6';
    my $oid_cpqRackCommonEnclosureManagerSparePartNumber = '.1.3.6.1.4.1.232.22.2.3.1.6.1.7';
    my $oid_cpqRackCommonEnclosureManagerSerialNum = '.1.3.6.1.4.1.232.22.2.3.1.6.1.8';
    my $oid_cpqRackCommonEnclosureManagerRole = '.1.3.6.1.4.1.232.22.2.3.1.6.1.9';
    my $oid_cpqRackCommonEnclosureManagerCondition = '.1.3.6.1.4.1.232.22.2.3.1.6.1.12';
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqRackCommonEnclosureManagerIndex);
    return if (scalar(keys %$result) <= 0);
    
    $self->{snmp}->load(oids => [$oid_cpqRackCommonEnclosureManagerPartNumber, $oid_cpqRackCommonEnclosureManagerSparePartNumber,
                                $oid_cpqRackCommonEnclosureManagerSerialNum, $oid_cpqRackCommonEnclosureManagerRole,
                                $oid_cpqRackCommonEnclosureManagerCondition],
                        instances => [keys %$result]);
    my $result2 = $self->{snmp}->get_leef();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        $key =~ /(\d+)$/;
        my $instance = $1;
    
        my $man_part = $result2->{$oid_cpqRackCommonEnclosureManagerPartNumber . '.' . $instance};
        my $man_spare = $result2->{$oid_cpqRackCommonEnclosureManagerSparePartNumber . '.' . $instance};
        my $man_serial = $result2->{$oid_cpqRackCommonEnclosureManagerSerialNum . '.' . $instance};
        my $man_role = $result2->{$oid_cpqRackCommonEnclosureManagerRole . '.' . $instance};
        my $man_condition = $result2->{$oid_cpqRackCommonEnclosureManagerCondition . '.' . $instance};
        
        $self->{output}->output_add(long_msg => sprintf("Enclosure management module %d is %s, status is %s [serial: %s, part: %s, spare: %s].", 
                                    $instance, ${$conditions{$man_condition}}[0], $map_role{$man_role},
                                    $man_serial, $man_part, $man_spare));
        if ($man_condition != 2) {
            $self->{output}->output_add(severity =>  ${$conditions{$man_condition}}[1],
                                        short_msg => sprintf("Enclosure management module %d is %s, status is %s", 
                                            $instance, ${$conditions{$man_condition}}[0], $map_role{$man_role}));
        }
    }
}

sub check_fans {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking fans");
    return if ($self->check_exclude('fans'));

    my $oid_cpqRackCommonEnclosureHasFans = '.1.3.6.1.4.1.232.22.2.3.1.1.1.21.1';
    if (defined($map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasFans}}) && 
        $map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasFans}} =~ /^false$/i) {
        $self->{output}->output_add(long_msg => "Skipping Fans: enclosure cannot house fans (??!!).");
        return ;
    }
    
    my $oid_cpqRackCommonEnclosureFanPresent = '.1.3.6.1.4.1.232.22.2.3.1.3.1.8';
    my $oid_cpqRackCommonEnclosureFanIndex = '.1.3.6.1.4.1.232.22.2.3.1.3.1.3';
    my $oid_cpqRackCommonEnclosureFanPartNumber = '.1.3.6.1.4.1.232.22.2.3.1.3.1.6';
    my $oid_cpqRackCommonEnclosureFanSparePartNumber = '.1.3.6.1.4.1.232.22.2.3.1.3.1.7';
    my $oid_cpqRackCommonEnclosureFanCondition = '.1.3.6.1.4.1.232.22.2.3.1.3.1.11';
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqRackCommonEnclosureFanPresent);
    return if (scalar(keys %$result) <= 0);
    my @get_oids = ();
    my @oids_end = ();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($present_map{$result->{$key}} ne 'present');
        $key =~ /\.([0-9]+)$/;
        my $oid_end = $1;
        
        push @oids_end, $oid_end;
        push @get_oids, $oid_cpqRackCommonEnclosureFanIndex . "." . $oid_end, $oid_cpqRackCommonEnclosureFanPartNumber . "." . $oid_end,
                $oid_cpqRackCommonEnclosureFanSparePartNumber . "." . $oid_end, $oid_cpqRackCommonEnclosureFanCondition . "." . $oid_end;
    }
    $result = $self->{snmp}->get_leef(oids => \@get_oids);
    foreach (@oids_end) {
        my $fan_index = $result->{$oid_cpqRackCommonEnclosureFanIndex . '.' . $_};
        my $fan_condition = $result->{$oid_cpqRackCommonEnclosureFanCondition . '.' . $_};
        my $fan_part = $result->{$oid_cpqRackCommonEnclosureFanPartNumber . '.' . $_};
        my $fan_spare = $result->{$oid_cpqRackCommonEnclosureFanSparePartNumber . '.' . $_};
        
        $self->{components_fans}++;
        $self->{output}->output_add(long_msg => sprintf("Fan %d condition is %s [part: %s, spare: %s].", 
                                    $fan_index, ${$conditions{$fan_condition}}[0],
                                    $fan_part, $fan_spare));
        if ($fan_condition != 2) {
            $self->{output}->output_add(severity =>  ${$conditions{$fan_condition}}[1],
                                        short_msg => sprintf("Fan %d condition is %s", $fan_index, ${$conditions{$fan_condition}}[0]));
        }
    }
}

sub check_blades {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking blades");
    return if ($self->check_exclude('blades'));

    my $oid_cpqRackCommonEnclosureHasServerBlades = '.1.3.6.1.4.1.232.22.2.3.1.1.1.17.1';
    if (defined($map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasServerBlades}}) &&
        $map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasServerBlades}} =~ /^false$/i) {
        $self->{output}->output_add(long_msg => "Skipping Blades: enclosure cannot house blades (??!!).");
        return ;
    }
    
    my $oid_cpqRackServerBladePresent = '.1.3.6.1.4.1.232.22.2.4.1.1.1.12';
    my $oid_cpqRackServerBladeIndex = '.1.3.6.1.4.1.232.22.2.4.1.1.1.3';
    my $oid_cpqRackServerBladeName = '.1.3.6.1.4.1.232.22.2.4.1.1.1.4';
    my $oid_cpqRackServerBladePartNumber = '.1.3.6.1.4.1.232.22.2.4.1.1.1.6';
    my $oid_cpqRackServerBladeSparePartNumber = '.1.3.6.1.4.1.232.22.2.4.1.1.1.7';
    my $oid_cpqRackServerBladeProductId = '.1.3.6.1.4.1.232.22.2.4.1.1.1.17';
    my $oid_cpqRackServerBladeStatus = '.1.3.6.1.4.1.232.22.2.4.1.1.1.21'; # v2
    my $oid_cpqRackServerBladeFaultDiagnosticString = '.1.3.6.1.4.1.232.22.2.4.1.1.1.24'; # v2
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqRackServerBladePresent);
    return if (scalar(keys %$result) <= 0);
    my @get_oids = ();
    my @oids_end = ();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($present_map{$result->{$key}} ne 'present');
        $key =~ /\.([0-9]+)$/;
        my $oid_end = $1;
        
        push @oids_end, $oid_end;
        push @get_oids, $oid_cpqRackServerBladeIndex . "." . $oid_end, $oid_cpqRackServerBladeName . "." . $oid_end,
                $oid_cpqRackServerBladePartNumber . "." . $oid_end, $oid_cpqRackServerBladeSparePartNumber . "." . $oid_end,
                $oid_cpqRackServerBladeProductId . "." . $oid_end, 
                $oid_cpqRackServerBladeStatus . "." . $oid_end, $oid_cpqRackServerBladeFaultDiagnosticString . "." . $oid_end;
    }

    $result = $self->{snmp}->get_leef(oids => \@get_oids);
    foreach (@oids_end) {
        my $blade_index = $result->{$oid_cpqRackServerBladeIndex . '.' . $_};
        my $blade_status = defined($result->{$oid_cpqRackServerBladeStatus . '.' . $_}) ? $result->{$oid_cpqRackServerBladeStatus . '.' . $_} : '';
        my $blade_name = $result->{$oid_cpqRackServerBladeName . '.' . $_};
        my $blade_part = $result->{$oid_cpqRackServerBladePartNumber . '.' . $_};
        my $blade_spare = $result->{$oid_cpqRackServerBladeSparePartNumber . '.' . $_};
        my $blade_productid = $result->{$oid_cpqRackServerBladeProductId . '.' . $_};
        my $blade_diago = defined($result->{$oid_cpqRackServerBladeFaultDiagnosticString . '.' . $_}) ? $result->{$oid_cpqRackServerBladeFaultDiagnosticString . '.' . $_} : '';
        
        $self->{components_blades}++;
        if ($blade_status eq '') {
            $self->{output}->output_add(long_msg => sprintf("Skipping Blade %d (%s, %s). Cant get status.",
                                        $blade_index, $blade_name, $blade_productid));
            next;
        }
        $self->{output}->output_add(long_msg => sprintf("Blade %d (%s, %s) status is %s [part: %s, spare: %s]%s.",
                                    $blade_index, $blade_name, $blade_productid,
                                    ${$conditions{$blade_status}}[0],
                                    $blade_part, $blade_spare,
                                    ($blade_diago ne '') ? " (Diagnostic '$blade_diago')" : ''
                                    ));
        if ($blade_status != 2) {
            $self->{output}->output_add(severity =>  ${$conditions{$blade_status}}[1],
                                        short_msg => sprintf("Blade %d (%s, %s) status is %s",
                                            $blade_index, $blade_name, $blade_productid,
                                            ${$conditions{$blade_status}}[0]
                                       ));
        }
    }
}


sub check_iom {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking network connectors");
    return if ($self->check_exclude('network'));

    my $oid_cpqRackCommonEnclosureHasNetConnectors = '.1.3.6.1.4.1.232.22.2.3.1.1.1.19.1';
    if (defined($map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasNetConnectors}}) &&
        $map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasNetConnectors}} =~ /^false$/i) {
        $self->{output}->output_add(long_msg => "Skipping Network Connectors: enclosure cannot house network connectors (??!!).");
        return ;
    }
    
    my $oid_cpqRackNetConnectorPresent = '.1.3.6.1.4.1.232.22.2.6.1.1.1.13';
    my $oid_cpqRackNetConnectorIndex = '.1.3.6.1.4.1.232.22.2.6.1.1.1.3';
    my $oid_cpqRackNetConnectorModel = '.1.3.6.1.4.1.232.22.2.6.1.1.1.6';
    my $oid_cpqRackNetConnectorSerialNum = '.1.3.6.1.4.1.232.22.2.6.1.1.1.7';
    my $oid_cpqRackNetConnectorPartNumber = '.1.3.6.1.4.1.232.22.2.6.1.1.1.8';
    my $oid_cpqRackNetConnectorSparePartNumber = '.1.3.6.1.4.1.232.22.2.6.1.1.1.9';
    my $oid_cpqRackNetConnectorDeviceType = '.1.3.6.1.4.1.232.22.2.6.1.1.1.17';
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqRackNetConnectorPresent);
    return if (scalar(keys %$result) <= 0);
    my @get_oids = ();
    my @oids_end = ();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($present_map{$result->{$key}} ne 'present');
        $key =~ /\.([0-9]+)$/;
        my $oid_end = $1;
        
        push @oids_end, $oid_end;
        push @get_oids, $oid_cpqRackNetConnectorIndex . "." . $oid_end, $oid_cpqRackNetConnectorModel . "." . $oid_end,
                $oid_cpqRackNetConnectorSerialNum . "." . $oid_end, $oid_cpqRackNetConnectorPartNumber . "." . $oid_end,
                $oid_cpqRackNetConnectorSparePartNumber . "." . $oid_end, $oid_cpqRackNetConnectorDeviceType . "." . $oid_end;
    }
    $result = $self->{snmp}->get_leef(oids => \@get_oids);
    foreach (@oids_end) {
        my $nc_index = $result->{$oid_cpqRackNetConnectorIndex . '.' . $_};
        my $nc_model = $result->{$oid_cpqRackNetConnectorModel . '.' . $_};
        my $nc_serial = $result->{$oid_cpqRackNetConnectorSerialNum . '.' . $_};
        my $nc_part = $result->{$oid_cpqRackNetConnectorPartNumber . '.' . $_};
        my $nc_spare = $result->{$oid_cpqRackNetConnectorSparePartNumber . '.' . $_};
        my $nc_device = $result->{$oid_cpqRackNetConnectorDeviceType . '.' . $_};
        
        $self->{components_nc}++;
        $self->{output}->output_add(long_msg => sprintf("Network Connector %d (%s) type '%s' is present [serial: %s, part: %s, spare: %s].",
                                    $nc_index, $nc_model,
                                    $device_type{$nc_device},
                                    $nc_serial, $nc_part, $nc_spare
                                    ));
    }
}

sub check_psu {
    my ($self) = @_;

    # We dont check 'cpqRackPowerEnclosureTable' (the overall power system status)
    # We check 'cpqRackPowerSupplyTable' (unitary)

    $self->{output}->output_add(long_msg => "Checking power supplies");
    return if ($self->check_exclude('psu'));

    my $oid_cpqRackCommonEnclosureHasPowerSupplies = '.1.3.6.1.4.1.232.22.2.3.1.1.1.18.1';
    if (defined($map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasPowerSupplies}}) &&
        $map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasPowerSupplies}} =~ /^false$/i) {
        $self->{output}->output_add(long_msg => "Skipping PSU: enclosure cannot house power supplies (??!!).");
        return ;
    }
    
    my $oid_cpqRackPowerSupplyPresent = '.1.3.6.1.4.1.232.22.2.5.1.1.1.16';
    my $oid_cpqRackPowerSupplyIndex = '.1.3.6.1.4.1.232.22.2.5.1.1.1.3';
    my $oid_cpqRackPowerSupplySerialNum = '.1.3.6.1.4.1.232.22.2.5.1.1.1.5';
    my $oid_cpqRackPowerSupplyPartNumber = '.1.3.6.1.4.1.232.22.2.5.1.1.1.6';
    my $oid_cpqRackPowerSupplySparePartNumber = '.1.3.6.1.4.1.232.22.2.5.1.1.1.7';
    my $oid_cpqRackPowerSupplyStatus = '.1.3.6.1.4.1.232.22.2.5.1.1.1.14';
    my $oid_cpqRackPowerSupplyInputLineStatus = '.1.3.6.1.4.1.232.22.2.5.1.1.1.15';
    my $oid_cpqRackPowerSupplyCondition = '.1.3.6.1.4.1.232.22.2.5.1.1.1.17';
    my $oid_cpqRackPowerSupplyCurPwrOutput = '.1.3.6.1.4.1.232.22.2.5.1.1.1.10'; # Watts
    my $oid_cpqRackPowerSupplyIntakeTemp = '.1.3.6.1.4.1.232.22.2.5.1.1.1.12';
    my $oid_cpqRackPowerSupplyExhaustTemp = '.1.3.6.1.4.1.232.22.2.5.1.1.1.13';
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqRackPowerSupplyPresent);
    return if (scalar(keys %$result) <= 0);
    my @get_oids = ();
    my @oids_end = ();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($present_map{$result->{$key}} ne 'present');
        $key =~ /\.([0-9]+)$/;
        my $oid_end = $1;
        
        push @oids_end, $oid_end;
        push @get_oids, $oid_cpqRackPowerSupplyIndex . "." . $oid_end, $oid_cpqRackPowerSupplySerialNum . "." . $oid_end,
                $oid_cpqRackPowerSupplyPartNumber . "." . $oid_end, $oid_cpqRackPowerSupplySparePartNumber . "." . $oid_end,
                $oid_cpqRackPowerSupplyStatus . "." . $oid_end, $oid_cpqRackPowerSupplyInputLineStatus . "." . $oid_end,
                $oid_cpqRackPowerSupplyCondition . "." . $oid_end, $oid_cpqRackPowerSupplyCurPwrOutput . "." . $oid_end,
                $oid_cpqRackPowerSupplyIntakeTemp . "." . $oid_end, $oid_cpqRackPowerSupplyExhaustTemp . "." . $oid_end;
    }
    $result = $self->{snmp}->get_leef(oids => \@get_oids);
    my $total_watts = 0;
    foreach (@oids_end) {
        my $psu_index = $result->{$oid_cpqRackPowerSupplyIndex . '.' . $_};
        my $psu_status = $result->{$oid_cpqRackPowerSupplyStatus . '.' . $_};
        my $psu_serial = $result->{$oid_cpqRackPowerSupplySerialNum . '.' . $_};
        my $psu_part = $result->{$oid_cpqRackPowerSupplyPartNumber . '.' . $_};
        my $psu_spare = $result->{$oid_cpqRackPowerSupplySparePartNumber . '.' . $_};
        my $psu_inputlinestatus = $result->{$oid_cpqRackPowerSupplyInputLineStatus . '.' . $_};
        my $psu_condition = $result->{$oid_cpqRackPowerSupplyCondition . '.' . $_};
        my $psu_pwrout = $result->{$oid_cpqRackPowerSupplyCurPwrOutput . '.' . $_};
        my $psu_intemp = $result->{$oid_cpqRackPowerSupplyIntakeTemp . '.' . $_};
        my $psu_exhtemp = $result->{$oid_cpqRackPowerSupplyExhaustTemp . '.' . $_};
        
        $total_watts += $psu_pwrout;
        $self->{components_psu}++;
        $self->{output}->output_add(long_msg => sprintf("PSU %d status is %s [serial: %s, part: %s, spare: %s] (input line status %s) (status %s).",
                                    $psu_index, ${$conditions{$psu_condition}}[0],
                                    $psu_serial, $psu_part, $psu_spare,
                                    $inputline_status{$psu_inputlinestatus},
                                    $psu_status{$psu_status}
                                    ));
        if ($psu_condition != 2) {
            $self->{output}->output_add(severity =>  ${$conditions{$psu_condition}}[1],
                                        short_msg => sprintf("PSU %d status is %s",
                                           $psu_index, ${$conditions{$psu_condition}}[0]));
        }
        
        $self->{output}->perfdata_add(label => "psu_" . $psu_index . "_power", unit => 'W',
                                      value => $psu_pwrout);
        if (defined($psu_intemp) && $psu_intemp != -1) {
            $self->{output}->perfdata_add(label => "psu_" . $psu_index . "_temp_intake", unit => 'C',
                                          value => $psu_intemp);
        }
        if (defined($psu_exhtemp) && $psu_exhtemp != -1) {
            $self->{output}->perfdata_add(label => "psu_" . $psu_index . "_temp_exhaust", unit => 'C',
                                          value => $psu_exhtemp);
        }
    }
    
    $self->{output}->perfdata_add(label => "total_power", unit => 'W',
                                  value => $total_watts);
}

sub check_temperatures {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking temperatures");
    return if ($self->check_exclude('temperatures'));

    my $oid_cpqRackCommonEnclosureHasTempSensors = '.1.3.6.1.4.1.232.22.2.3.1.1.1.20.1';
    if (defined($map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasTempSensors}}) &&
        $map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasTempSensors}} =~ /^false$/i) {
        $self->{output}->output_add(long_msg => "Skipping Temperatures: enclosure doesnt contain temperatures sensors.");
        return ;
    }
    
    my $oid_cpqRackCommonEnclosureTempSensorIndex = '.1.3.6.1.4.1.232.22.2.3.1.2.1.3';
    my $oid_cpqRackCommonEnclosureTempSensorEnclosureName = '.1.3.6.1.4.1.232.22.2.3.1.2.1.4';
    my $oid_cpqRackCommonEnclosureTempLocation = '.1.3.6.1.4.1.232.22.2.3.1.2.1.5';
    my $oid_cpqRackCommonEnclosureTempCurrent = '.1.3.6.1.4.1.232.22.2.3.1.2.1.6';
    my $oid_cpqRackCommonEnclosureTempThreshold = '.1.3.6.1.4.1.232.22.2.3.1.2.1.7';
    my $oid_cpqRackCommonEnclosureTempCondition = '.1.3.6.1.4.1.232.22.2.3.1.2.1.8';
    my $oid_cpqRackCommonEnclosureTempType = '.1.3.6.1.4.1.232.22.2.3.1.2.1.9';
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqRackCommonEnclosureTempSensorIndex);
    return if (scalar(keys %$result) <= 0);
    my @get_oids = ();
    my @oids_end = ();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        my $oid_end = $result->{$key};
        
        push @oids_end, $oid_end;
        push @get_oids, $oid_cpqRackCommonEnclosureTempSensorEnclosureName . "." . $oid_end, $oid_cpqRackCommonEnclosureTempLocation . "." . $oid_end,
                $oid_cpqRackCommonEnclosureTempCurrent . "." . $oid_end, $oid_cpqRackCommonEnclosureTempThreshold . "." . $oid_end,
                $oid_cpqRackCommonEnclosureTempCondition . "." . $oid_end, $oid_cpqRackCommonEnclosureTempType . "." . $oid_end;
    }
    $result = $self->{snmp}->get_leef(oids => \@get_oids);
    foreach (@oids_end) {
        my $temp_index = $_;
        my $temp_name = $result->{$oid_cpqRackCommonEnclosureTempSensorEnclosureName . '.' . $_};
        my $temp_location = $result->{$oid_cpqRackCommonEnclosureTempLocation . '.' . $_};
        my $temp_current = $result->{$oid_cpqRackCommonEnclosureTempCurrent . '.' . $_};
        my $temp_threshold = $result->{$oid_cpqRackCommonEnclosureTempThreshold . '.' . $_};
        my $temp_condition = $result->{$oid_cpqRackCommonEnclosureTempCondition . '.' . $_};
        my $temp_type = $result->{$oid_cpqRackCommonEnclosureTempType . '.' . $_};
        
        $self->{components_temperatures}++;
        $self->{output}->output_add(long_msg => sprintf("Temperature %d status is %s [name: %s, location: %s] (value = %s, threshold = %s%s).",
                                    $temp_index, ${$conditions{$temp_condition}}[0],
                                    $temp_name, $temp_location,
                                    $temp_current, $temp_threshold,
                                    defined($map_temp_type{$temp_type}) ? ", status type = " . $map_temp_type{$temp_type} : ''));
        if ($temp_condition != 2) {
            $self->{output}->output_add(severity =>  ${$conditions{$temp_condition}}[1],
                                        short_msg => sprintf("Temperature %d status is %s",
                                          $temp_index, ${$conditions{$temp_condition}}[0]));
        }
        
        $self->{output}->perfdata_add(label => "temp_" . $temp_index, unit => 'C',
                                      value => $temp_current,
                                      warning => $temp_threshold);
    }
}

sub check_fuse {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking fuse");
    return if ($self->check_exclude('fuse'));
    
    my $oid_cpqRackCommonEnclosureHasFuses = '.1.3.6.1.4.1.232.22.2.3.1.1.1.22.1';
    if (defined($map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasFuses}}) &&
        $map_has{$self->{global_results}->{$oid_cpqRackCommonEnclosureHasFuses}} =~ /^false$/i) {
        $self->{output}->output_add(long_msg => "Skipping Fuse: enclosure doesnt contain fuse.");
        return ;
    }
    
    my $oid_cpqRackCommonEnclosureFusePresent = '.1.3.6.1.4.1.232.22.2.3.1.4.1.6';
    my $oid_cpqRackCommonEnclosureFuseIndex = '.1.3.6.1.4.1.232.22.2.3.1.4.1.3';
    my $oid_cpqRackCommonEnclosureFuseEnclosureName = '.1.3.6.1.4.1.232.22.2.3.1.4.1.4';
    my $oid_cpqRackCommonEnclosureFuseLocation = '.1.3.6.1.4.1.232.22.2.3.1.4.1.5';
    my $oid_cpqRackCommonEnclosureFuseCondition = '.1.3.6.1.4.1.232.22.2.3.1.4.1.7';
    
    my $result = $self->{snmp}->get_table(oid => $oid_cpqRackCommonEnclosureFusePresent);
    return if (scalar(keys %$result) <= 0);
    my @get_oids = ();
    my @oids_end = ();
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($present_map{$result->{$key}} ne 'present');
        $key =~ /\.([0-9]+)$/;
        my $oid_end = $1;
        
        push @oids_end, $oid_end;
        push @get_oids, $oid_cpqRackCommonEnclosureFuseIndex . "." . $oid_end, $oid_cpqRackCommonEnclosureFuseEnclosureName . "." . $oid_end,
                $oid_cpqRackCommonEnclosureFuseLocation . "." . $oid_end, $oid_cpqRackCommonEnclosureFuseCondition . "." . $oid_end;
    }
    $result = $self->{snmp}->get_leef(oids => \@get_oids);
    foreach (@oids_end) {
        my $fuse_index = $result->{$oid_cpqRackCommonEnclosureFuseIndex . '.' . $_};
        my $fuse_name = $result->{$oid_cpqRackCommonEnclosureFuseEnclosureName . '.' . $_};
        my $fuse_location = $result->{$oid_cpqRackCommonEnclosureFuseLocation . '.' . $_};
        my $fuse_condition = $result->{$oid_cpqRackCommonEnclosureFuseCondition . '.' . $_};
        
        $self->{components_fuse}++;
        $self->{output}->output_add(long_msg => sprintf("Fuse %d status is %s [name: %s, location: %s].",
                                    $fuse_index, ${$conditions{$fuse_condition}}[0],
                                    $fuse_name, $fuse_location));
        if ($fuse_condition != 2) {
            $self->{output}->output_add(severity =>  ${$conditions{$fuse_condition}}[1],
                                        short_msg => sprintf("Fuse %d status is %s",
                                            $fuse_index, ${$conditions{$fuse_condition}}[0]));
        }
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

Check Hardware (Fans, Power Supplies, Blades, Temperatures, Fuses).

=over 8

=item B<--exclude>

Exclude some parts (comma seperated list) (Example: --exclude=temperatures,psu).

=back

=cut
    