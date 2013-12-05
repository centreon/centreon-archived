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

package network::juniper::common::mode::hardware;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %map_fru_offline = (
    1 => 'unknown', 2 => 'none', 3 => 'error', 4 => 'noPower', 5 => 'configPowerOff', 6 => 'configHoldInReset', 
    7 => 'cliCommand', 8 => 'buttonPress', 9 => 'cliRestart', 10 => 'overtempShutdown', 11 => 'masterClockDown', 
    12 => 'singleSfmModeChange', 13 => 'packetSchedulingModeChange', 14 => 'physicalRemoval', 15 => 'unresponsiveRestart', 
    16 => 'sonetClockAbsent', 17 => 'rddPowerOff', 18 => 'majorErrors', 19 => 'minorErrors', 20 => 'lccHardRestart', 
    21 => 'lccVersionMismatch', 22 => 'powerCycle', 23 => 'reconnect', 24 => 'overvoltage', 25 => 'pfeVersionMismatch', 
    26 => 'febRddCfgChange', 27 => 'fpcMisconfig', 28 => 'fruReconnectFail', 29 => 'fruFwddReset', 30 => 'fruFebSwitch', 
    31 => 'fruFebOffline', 32 => 'fruInServSoftUpgradeError', 33 => 'fruChasdPowerRatingExceed', 34 => 'fruConfigOffline', 
    35 => 'fruServiceRestartRequest', 36 => 'spuResetRequest', 37 => 'spuFlowdDown', 38 => 'spuSpi4Down', 39 => 'spuWatchdogTimeout', 
    40 => 'spuCoreDump', 41 => 'fpgaSpi4LinkDown', 42 => 'i3Spi4LinkDown', 43 => 'cppDisconnect', 44 => 'cpuNotBoot', 
    45 => 'spuCoreDumpComplete', 46 => 'rstOnSpcSpuFailure', 47 => 'softRstOnSpcSpuFailure', 48 => 'hwAuthenticationFailure', 
    49 => 'reconnectFpcFail', 50 => 'fpcAppFailed', 51 => 'fpcKernelCrash', 52 => 'spuFlowdDownNoCore', 53 => 'spuFlowdCoreDumpIncomplete',
    54 => 'spuFlowdCoreDumpComplete', 55 => 'spuIdpdDownNoCore', 56 => 'spuIdpdCoreDumpIncomplete', 57 => 'spuIdpdCoreDumpComplete', 
    58 => 'spuCoreDumpIncomplete', 59 => 'spuIdpdDown', 60 => 'fruPfeReset', 61 => 'fruReconnectNotReady', 62 => 'fruSfLinkDown', 
    63 => 'fruFabricDown', 64 => 'fruAntiCounterfeitRetry', 65 => 'fruFPCChassisClusterDisable', 66 => 'spuFipsError', 
    67 => 'fruFPCFabricDownOffline', 68 => 'febCfgChange', 69 => 'routeLocalizationRoleChange', 70 => 'fruFpcUnsupported', 
    71 => 'psdVersionMismatch', 72 => 'fruResetThresholdExceeded', 73 => 'picBounce', 74 => 'badVoltage', 75 => 'fruFPCReducedFabricBW', 
    76 => 'fruAutoheal', 77 => 'builtinPicBounce', 78 => 'fruFabricDegraded', 79 => 'fruFPCFabricDegradedOffline', 80 => 'fruUnsupportedSlot', 
    81 => 'fruRouteLocalizationMisCfg', 82 => 'fruTypeConfigMismatch', 83 => 'lccModeChanged', 84 => 'hwFault', 85 => 'fruPICOfflineOnEccErrors',
    86 => 'fruFpcIncompatible', 87 => 'fruFpcFanTrayPEMIncompatible', 88 => 'fruUnsupportedFirmware', 
    89 => 'openflowConfigChange', 90 => 'fruFpcScbIncompatible', 91 => 'fruReUnresponsive' 
);
my %map_fru_type = (
    1 => 'other', 2 => 'clockGenerator', 3 => 'flexiblePicConcentrator', 4 => 'switchingAndForwardingModule', 5 => 'controlBoard', 
    6 => 'routingEngine', 7 => 'powerEntryModule', 8 => 'frontPanelModule', 9 => 'switchInterfaceBoard', 10 => 'processorMezzanineBoardForSIB', 
    11 => 'portInterfaceCard', 12 => 'craftInterfacePanel', 13 => 'fan', 14 => 'lineCardChassis', 15 => 'forwardingEngineBoard', 
    16 => 'protectedSystemDomain', 17 => 'powerDistributionUnit', 18 => 'powerSupplyModule', 19 => 'switchFabricBoard', 20 => 'adapterCard' 
);
my %fru_states = (
    1 => ['unknown', 'UNKNOWN'], 
    2 => ['empty', 'OK'], 
    3 => ['present', 'OK'], 
    4 => ['ready', 'OK'],
    5 => ['announce online', 'OK'],
    6 => ['online', 'OK'],
    7 => ['announce offline', 'WARNING'],
    8 => ['offline', 'CRITICAL'],
    9 => ['diagnostic', 'WARNING'],
    10 => ['standby', 'WARNING'],
);
my %operating_states = (
    1 => ['unknown', 'UNKNOWN'], 
    2 => ['running', 'OK'], 
    3 => ['ready', 'OK'], 
    4 => ['reset', 'WARNING'],
    5 => ['runningAtFullSpeed', 'WARNING'],
    6 => ['down', 'CRITICAL'],
    7 => ['standby', 'OK'],
);

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

    $self->{components_frus} = 0;
    $self->{components_operating} = 0;
    
    $self->get_type();
    $self->check_frus();
    $self->check_operating();
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => sprintf("All %d components [%d frus, %d operating] are ok, Environment type: %s", 
                                ($self->{components_frus} + $self->{components_operating}), 
                                $self->{components_frus}, $self->{components_operating}, $self->{env_type}));
    
    $self->{output}->display();
    $self->{output}->exit();
}

sub get_type {
    my ($self) = @_;

    my $oid_jnxBoxDescr = ".1.3.6.1.4.1.2636.3.1.2.0";
    
    my $result = $self->{snmp}->get_leef(oids => [$oid_jnxBoxDescr]);
    
    $self->{env_type} = defined($result->{$oid_jnxBoxDescr}) ? $result->{$oid_jnxBoxDescr} : 'unknown';
}

sub check_frus {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking frus");
    
    my $oid_jnxFruName = '.1.3.6.1.4.1.2636.3.1.15.1.5';
    my $oid_jnxFruType = '.1.3.6.1.4.1.2636.3.1.15.1.6';
    my $oid_jnxFruState = '.1.3.6.1.4.1.2636.3.1.15.1.8';
    my $oid_jnxFruTemp = '.1.3.6.1.4.1.2636.3.1.15.1.9';
    my $oid_jnxFruOfflineReason = '.1.3.6.1.4.1.2636.3.1.15.1.10';
    
    my $result = $self->{snmp}->get_table(oid => $oid_jnxFruName);
    return if (scalar(keys %$result) <= 0);

    $self->{snmp}->load(oids => [$oid_jnxFruType, $oid_jnxFruState, $oid_jnxFruTemp, $oid_jnxFruOfflineReason],
                        instances => [keys %$result],
                        instance_regexp => "^" . $oid_jnxFruName . '\.(.+)');
    my $result2 = $self->{snmp}->get_leef();
    
    foreach my $oid (keys %$result) {        
        $oid =~ /^$oid_jnxFruName\.(.+)/;
        my $instance = $1;
        
        my $fru_name = $result->{$oid};
        my $fru_type = $result2->{$oid_jnxFruType . "." . $instance};
        my $fru_state = $result2->{$oid_jnxFruState . "." . $instance};
        my $fru_temp = $result2->{$oid_jnxFruTemp . "." . $instance};
        my $fru_offlinereason = $result2->{$oid_jnxFruOfflineReason . "." . $instance};
        
        # Empty. Skip
        if ($fru_state == 2) {
            $self->{output}->output_add(long_msg => sprintf("Skipping fru '%s' [type: %s]: empty.", 
                                           $fru_name, $map_fru_type{$fru_type}));
            next;
        }
        
        $self->{components_frus}++;
        $self->{output}->output_add(long_msg => sprintf("Fru '%s' state is %s [type: %s, offline reason: %s]", 
                                    $fru_name, ${$fru_states{$fru_state}}[0], 
                                    $map_fru_type{$fru_type}, $map_fru_offline{$fru_offlinereason}));
        if (${$fru_states{$fru_state}}[1] ne 'OK') {
            $self->{output}->output_add(severity =>  ${$fru_states{$fru_state}}[1],
                                        short_msg => sprintf("Fru '%s' state is %s [offline reason: %s]", $fru_name, ${$fru_states{$fru_state}}[0],
                                        $map_fru_offline{$fru_offlinereason}));
        }
        
        if ($fru_temp != 0) {
            $self->{output}->perfdata_add(label => 'temp_' . $fru_name, unit => 'C',
                                          value => $fru_temp);
        }
    }
}

sub check_operating {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking operating");
    
    my $oid_jnxOperatingDescr = '.1.3.6.1.4.1.2636.3.1.13.1.5';
    my $oid_jnxOperatingState = '.1.3.6.1.4.1.2636.3.1.13.1.6';
    
    my $result = $self->{snmp}->get_table(oid => $oid_jnxOperatingDescr);
    return if (scalar(keys %$result) <= 0);

    $self->{snmp}->load(oids => [$oid_jnxOperatingState],
                        instances => [keys %$result],
                        instance_regexp => "^" . $oid_jnxOperatingDescr . '\.(.+)');
    my $result2 = $self->{snmp}->get_leef();
    
    foreach my $oid (keys %$result) {        
        $oid =~ /^$oid_jnxOperatingDescr\.(.+)/;
        my $instance = $1;
        
        my $operating_descr = $result->{$oid};
        my $operating_state = $result2->{$oid_jnxOperatingState . "." . $instance};
        
        $self->{components_operating}++;
        $self->{output}->output_add(long_msg => sprintf("Operating '%s' state is %s", 
                                    $operating_descr, ${$operating_states{$operating_state}}[0]));
        if (${$operating_states{$operating_state}}[1] ne 'OK') {
            $self->{output}->output_add(severity => ${$operating_states{$operating_state}}[1],
                                        short_msg => sprintf("Operating '%s' state is %s", 
                                    $operating_descr, ${$operating_states{$operating_state}}[0]));
        }
    }
}

1;

__END__

=head1 MODE

Check Hardware (mib-jnx-chassis) (frus, operating).

=over 8

=back

=cut
    