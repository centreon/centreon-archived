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

package storage::netapp::mode::shelf;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::misc;

my %com_states = (
    1 => ['initializing', 'WARNING'], 
    2 => ['transitioning', 'WARNING'], 
    3 => ['active', 'OK'], 
    4 => ['inactive', 'CRITICAL'],
    5 => ['reconfiguring', 'WARNING'],
    6 => ['nonexistent', 'CRITICAL'],
);

my $oid_enclNumber = '.1.3.6.1.4.1.789.1.21.1.1.0';
my $oid_enclContactState = '.1.3.6.1.4.1.789.1.21.1.2.1.2';
my $oid_enclChannelShelfAddr = '.1.3.6.1.4.1.789.1.21.1.2.1.3';
my $oid_enclPowerSuppliesPresent = '.1.3.6.1.4.1.789.1.21.1.2.1.13';
my $oid_enclPowerSuppliesFailed = '.1.3.6.1.4.1.789.1.21.1.2.1.15';
my $oid_enclFansPresent = '.1.3.6.1.4.1.789.1.21.1.2.1.17';
my $oid_enclFansFailed = '.1.3.6.1.4.1.789.1.21.1.2.1.18';
my $oid_enclTempSensorsPresent = '.1.3.6.1.4.1.789.1.21.1.2.1.20';
my $oid_enclTempSensorsOverTempFail = '.1.3.6.1.4.1.789.1.21.1.2.1.21';
my $oid_enclTempSensorsOverTempWarn = '.1.3.6.1.4.1.789.1.21.1.2.1.22';
my $oid_enclTempSensorsUnderTempFail = '.1.3.6.1.4.1.789.1.21.1.2.1.23';
my $oid_enclTempSensorsUnderTempWarn = '.1.3.6.1.4.1.789.1.21.1.2.1.24';
my $oid_enclTempSensorsCurrentTemp = '.1.3.6.1.4.1.789.1.21.1.2.1.25';
my $oid_enclTempSensorsOverTempFailThr = '.1.3.6.1.4.1.789.1.21.1.2.1.26';
my $oid_enclTempSensorsOverTempWarnThr = '.1.3.6.1.4.1.789.1.21.1.2.1.27';
my $oid_enclTempSensorsUnderTempFailThr = '.1.3.6.1.4.1.789.1.21.1.2.1.28';
my $oid_enclTempSensorsUnderTempWarnThr = '.1.3.6.1.4.1.789.1.21.1.2.1.29';
my $oid_enclElectronicsPresent = '.1.3.6.1.4.1.789.1.21.1.2.1.31';
my $oid_enclElectronicsFailed = '.1.3.6.1.4.1.789.1.21.1.2.1.33';
my $oid_enclVoltSensorsPresent = '.1.3.6.1.4.1.789.1.21.1.2.1.35';
my $oid_enclVoltSensorsOverVoltFail = '.1.3.6.1.4.1.789.1.21.1.2.1.36';
my $oid_enclVoltSensorsOverVoltWarn = '.1.3.6.1.4.1.789.1.21.1.2.1.37';
my $oid_enclVoltSensorsUnderVoltFail = '.1.3.6.1.4.1.789.1.21.1.2.1.38';
my $oid_enclVoltSensorsUnderVoltWarn = '.1.3.6.1.4.1.789.1.21.1.2.1.39';
my $oid_enclVoltSensorsCurrentVolt = '.1.3.6.1.4.1.789.1.21.1.2.1.40';
my $oid_enclVoltSensorsOverVoltFailThr = '.1.3.6.1.4.1.789.1.21.1.2.1.41';
my $oid_enclVoltSensorsOverVoltWarnThr = '.1.3.6.1.4.1.789.1.21.1.2.1.42';
my $oid_enclVoltSensorsUnderVoltFailThr = '.1.3.6.1.4.1.789.1.21.1.2.1.43';
my $oid_enclVoltSensorsUnderVoltWarnThr = '.1.3.6.1.4.1.789.1.21.1.2.1.44';

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
    
    my $result = $self->{snmp}->get_leef(oids => [$oid_enclNumber], nothing_quit => 1);
    $self->{snmp}->load(oids => [$oid_enclContactState, $oid_enclChannelShelfAddr, $oid_enclPowerSuppliesPresent, $oid_enclPowerSuppliesFailed,
                                 $oid_enclFansPresent, $oid_enclFansFailed, $oid_enclTempSensorsPresent, $oid_enclTempSensorsOverTempFail, $oid_enclTempSensorsOverTempWarn,
                                 $oid_enclTempSensorsUnderTempFail, $oid_enclTempSensorsUnderTempWarn, $oid_enclTempSensorsCurrentTemp,
                                 $oid_enclTempSensorsOverTempFailThr, $oid_enclTempSensorsOverTempWarnThr, $oid_enclTempSensorsUnderTempFailThr, 
                                 $oid_enclTempSensorsUnderTempWarnThr, $oid_enclElectronicsPresent, $oid_enclElectronicsFailed, $oid_enclVoltSensorsPresent,
                                 $oid_enclVoltSensorsOverVoltFail, $oid_enclVoltSensorsOverVoltWarn, $oid_enclVoltSensorsUnderVoltFail, $oid_enclVoltSensorsUnderVoltWarn,
                                 $oid_enclVoltSensorsCurrentVolt, $oid_enclVoltSensorsOverVoltFailThr, $oid_enclVoltSensorsOverVoltWarnThr, $oid_enclVoltSensorsUnderVoltFailThr,
                                 $oid_enclVoltSensorsUnderVoltWarnThr], 
                        begin => 1, end => $result->{$oid_enclNumber});
    $self->{result} = $self->{snmp}->get_leef();
    
    $self->{number_shelf} = $result->{$oid_enclNumber};
    $self->{components_fans} = 0;
    $self->{components_psus} = 0;
    $self->{components_temperatures} = 0;
    $self->{components_electronics} = 0;
    $self->{components_voltages} = 0;
    
    $self->check_communication();
    $self->check_fans();
    $self->check_psus();
    $self->check_temperatures();
    $self->check_electronics();
    $self->check_voltages();

    $self->{output}->output_add(severity => 'OK',
                                short_msg => sprintf("All %d shelves [%d fans, %d power supplies, %d temperatures, %d voltages, %d electronics] are ok", 
                                $self->{number_shelf}, 
                                $self->{components_fans}, $self->{components_psus}, $self->{components_temperatures}, $self->{components_voltages}, $self->{components_electronics}));
    
    $self->{output}->display();
    $self->{output}->exit();
}

sub check_communication {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking communications");

    for (my $i = 1; $i <= $self->{number_shelf}; $i++) {
        my $shelf_addr = $self->{result}->{$oid_enclChannelShelfAddr . '.' . $i};
        my $com_state = $self->{result}->{$oid_enclContactState . '.' . $i};
     
        $self->{output}->output_add(long_msg => sprintf("Shelve '%s' communication state is '%s'", 
                                                          $shelf_addr, ${$com_states{$com_state}}[0]));
        if (${$com_states{$com_state}}[1] ne 'OK') {
            $self->{output}->output_add(severity => ${$com_states{$com_state}}[0],
                                        short_msg => sprintf("Shelve '%s' communication state is '%s'", 
                                                            $shelf_addr, ${$com_states{$com_state}}[0]));
        }
    }
}

sub check_fans {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking fans");

    for (my $i = 1; $i <= $self->{number_shelf}; $i++) {
        my $shelf_addr = $self->{result}->{$oid_enclChannelShelfAddr . '.' . $i};
        my $present = $self->{result}->{$oid_enclFansPresent . '.' . $i};
        my $failed = $self->{result}->{$oid_enclFansFailed . '.' . $i};
        
        foreach my $num (split /,/, $present) {
            $num = centreon::plugins::misc::trim($num);
            next if ($num !~ /[0-9]/);
            
            $self->{components_fans}++;
            if ($failed =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'CRITICAL', 
                                            long_msg => sprintf("Shelve '%s' Fan '%s' is failed", 
                                                                $shelf_addr, $num));
            } else {
                $self->{output}->output_add(long_msg => sprintf("Shelve '%s' Fan '%s' is ok", 
                                                                $shelf_addr, $num));
            }
        }
    }
}

sub check_psus {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking power supplies");

    for (my $i = 1; $i <= $self->{number_shelf}; $i++) {
        my $shelf_addr = $self->{result}->{$oid_enclChannelShelfAddr . '.' . $i};
        my $present = $self->{result}->{$oid_enclPowerSuppliesPresent . '.' . $i};
        my $failed = $self->{result}->{$oid_enclPowerSuppliesFailed . '.' . $i};
        
        foreach my $num (split /,/, $present) {
            $num = centreon::plugins::misc::trim($num);
            next if ($num !~ /[0-9]/);
            
            $self->{components_psus}++;
            if ($failed =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'CRITICAL', 
                                            long_msg => sprintf("Shelve '%s' PSU '%s' is failed", 
                                                                $shelf_addr, $num));
            } else {
                $self->{output}->output_add(long_msg => sprintf("Shelve '%s' PSU '%s' is ok", 
                                                                $shelf_addr, $num));
            }
        }
    }
}

sub check_electronics {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking electronics");

    for (my $i = 1; $i <= $self->{number_shelf}; $i++) {
        my $shelf_addr = $self->{result}->{$oid_enclChannelShelfAddr . '.' . $i};
        my $present = $self->{result}->{$oid_enclElectronicsPresent . '.' . $i};
        my $failed = $self->{result}->{$oid_enclElectronicsFailed . '.' . $i};
        
        foreach my $num (split /,/, $present) {
            $num = centreon::plugins::misc::trim($num);
            next if ($num !~ /[0-9]/);
            
            $self->{components_electronics}++;
            if ($failed =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'CRITICAL', 
                                            long_msg => sprintf("Shelve '%s' electronics '%s' is failed", 
                                                                $shelf_addr, $num));
            } else {
                $self->{output}->output_add(long_msg => sprintf("Shelve '%s' electronics '%s' is ok", 
                                                                $shelf_addr, $num));
            }
        }
    }
}

sub check_voltages {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking voltages");
    for (my $i = 1; $i <= $self->{number_shelf}; $i++) {
        my $shelf_addr = $self->{result}->{$oid_enclChannelShelfAddr . '.' . $i};
        my $present = $self->{result}->{$oid_enclVoltSensorsPresent . '.' . $i};
        my @current_volt = split /,/, $self->{result}->{$oid_enclVoltSensorsCurrentVolt . '.' . $i};
        
        my $warn_under = $self->{result}->{$oid_enclVoltSensorsUnderVoltWarn . '.' . $i};
        my $crit_under = $self->{result}->{$oid_enclVoltSensorsUnderVoltFail . '.' . $i};
        my $warn_over = $self->{result}->{$oid_enclVoltSensorsOverVoltWarn . '.' . $i};
        my $crit_over = $self->{result}->{$oid_enclVoltSensorsOverVoltFail . '.' . $i};

        my @warn_under_thr = split /,/, $self->{result}->{$oid_enclVoltSensorsUnderVoltWarnThr . '.' . $i};
        my @crit_under_thr = split /,/, $self->{result}->{$oid_enclVoltSensorsUnderVoltFailThr . '.' . $i};
        my @warn_over_thr = split /,/, $self->{result}->{$oid_enclVoltSensorsOverVoltWarnThr . '.' . $i};
        my @crit_over_thr = split /,/, $self->{result}->{$oid_enclVoltSensorsOverVoltFailThr . '.' . $i};

        foreach my $num (split /,/, $present) {
            $num = centreon::plugins::misc::trim($num);
            next if ($num !~ /[0-9]/);
            
            my $wu_thr = (defined($warn_under_thr[$i - 1]) && $warn_under_thr[$i - 1] =~ /(^|\s)(-*[0-9]+)/) ? $2 : '';
            my $cu_thr = (defined($crit_under_thr[$i - 1]) && $crit_under_thr[$i - 1] =~ /(^|\s)(-*[0-9]+)/) ? $2 : '';
            my $wo_thr = (defined($warn_over_thr[$i - 1]) && $warn_over_thr[$i - 1] =~ /(^|\s)(-*[0-9]+)/) ? $2 : '';
            my $co_thr = (defined($crit_over_thr[$i - 1]) && $crit_over_thr[$i - 1] =~ /(^|\s)(-*[0-9]+)/) ? $2 : '';
            my $current_value = ($current_volt[$i - 1] =~ /(^|\s)(-*[0-9]+)/) ? $2 : '';
            
            $self->{components_voltages}++;
            if ($crit_under =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'CRITICAL', 
                                            long_msg => sprintf("Shelve '%s' voltage sensor '%s' is under critical threshold [current = %s < %s]", 
                                                                $shelf_addr, $num, $current_value, $cu_thr));
            } elsif ($warn_under =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'WARNING', 
                                            long_msg => sprintf("Shelve '%s' voltage sensor '%s' is under warning threshold [current = %s < %s]", 
                                                                $shelf_addr, $num, $current_value, $wu_thr));
            } elsif ($crit_over =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'CRITICAL', 
                                            long_msg => sprintf("Shelve '%s' voltage sensor '%s' is over critical threshold [current = %s > %s]", 
                                                                $shelf_addr, $num, $current_value, $co_thr));
            } elsif ($warn_over =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'WARNING', 
                                            long_msg => sprintf("Shelve '%s' voltage sensor '%s' is over warning threshold [current = %s > %s]", 
                                                                $shelf_addr, $num, $current_value, $wo_thr));
            } else {
                $self->{output}->output_add(long_msg => sprintf("Shelve '%s' voltage sensor '%s' is ok [current = %s]", 
                                                                $shelf_addr, $num, $current_value));
            }
            
            $self->{output}->perfdata_add(label => "volt_" . $i . "_" . $num, unit => 'mV',
                                          value => $current_value,
                                          warning => ($wu_thr ne '' || $wo_thr ne '') ? ($wu_thr . ':' . $wo_thr) : '',
                                          critical => ($cu_thr ne '' || $co_thr ne '') ? ($cu_thr . ':' . $co_thr) : '');
        }
    }
}

sub check_temperatures {
    my ($self) = @_;

    $self->{output}->output_add(long_msg => "Checking temperatures");

    for (my $i = 1; $i <= $self->{number_shelf}; $i++) {
        my $shelf_addr = $self->{result}->{$oid_enclChannelShelfAddr . '.' . $i};
        my $present = $self->{result}->{$oid_enclTempSensorsPresent . '.' . $i};
        my @current_temp = split /,/, $self->{result}->{$oid_enclTempSensorsCurrentTemp . '.' . $i};
        
        my $warn_under = $self->{result}->{$oid_enclTempSensorsUnderTempWarn . '.' . $i};
        my $crit_under = $self->{result}->{$oid_enclTempSensorsUnderTempFail . '.' . $i};
        my $warn_over = $self->{result}->{$oid_enclTempSensorsOverTempWarn . '.' . $i};
        my $crit_over = $self->{result}->{$oid_enclTempSensorsOverTempFail . '.' . $i};
        
        my @warn_under_thr = split /,/, $self->{result}->{$oid_enclTempSensorsUnderTempWarnThr . '.' . $i};
        my @crit_under_thr = split /,/, $self->{result}->{$oid_enclTempSensorsUnderTempFailThr . '.' . $i};
        my @warn_over_thr = split /,/, $self->{result}->{$oid_enclTempSensorsOverTempWarnThr . '.' . $i};
        my @crit_over_thr = split /,/, $self->{result}->{$oid_enclTempSensorsOverTempFailThr . '.' . $i};

        foreach my $num (split /,/, $present) {
            $num = centreon::plugins::misc::trim($num);
            next if ($num !~ /[0-9]/);
            
            $warn_under_thr[$i - 1] =~ /(-*[0-9]+)C/;
            my $wu_thr = $1;
            $crit_under_thr[$i - 1] =~ /(-*[0-9]+)C/;
            my $cu_thr = $1;
            $warn_over_thr[$i - 1] =~ /(-*[0-9]+)C/;
            my $wo_thr = $1;
            $crit_over_thr[$i - 1] =~ /(-*[0-9]+)C/;
            my $co_thr = $1;
            $current_temp[$i - 1] =~ /(-*[0-9]+)C/;
            my $current_value = $1;
            
            $self->{components_temperatures}++;
            if ($crit_under =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'CRITICAL', 
                                            long_msg => sprintf("Shelve '%s' temperature sensor '%s' is under critical threshold [current = %s < %s]", 
                                                                $shelf_addr, $num, $current_value, $cu_thr));
            } elsif ($warn_under =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'WARNING', 
                                            long_msg => sprintf("Shelve '%s' temperature sensor '%s' is under warning threshold [current = %s < %s]", 
                                                                $shelf_addr, $num, $current_value, $wu_thr));
            } elsif ($crit_over =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'CRITICAL', 
                                            long_msg => sprintf("Shelve '%s' temperature sensor '%s' is over critical threshold [current = %s > %s]", 
                                                                $shelf_addr, $num, $current_value, $co_thr));
            } elsif ($warn_over =~ /(^|,|\s)$num(,|\s|$)/) {
                $self->{output}->output_add(severity => 'WARNING', 
                                            long_msg => sprintf("Shelve '%s' temperature sensor '%s' is over warning threshold [current = %s > %s]", 
                                                                $shelf_addr, $num, $current_value, $wo_thr));
            } else {
                $self->{output}->output_add(long_msg => sprintf("Shelve '%s' temperature sensor '%s' is ok [current = %s]", 
                                                                $shelf_addr, $num, $current_value));
            }
            
            $self->{output}->perfdata_add(label => "temp_" . $i . "_" . $num, unit => 'C',
                                          value => $current_value,
                                          warning => $wu_thr . ':' . $wo_thr,
                                          critical => $cu_thr . ':' . $co_thr);
        }
    }
}

1;

__END__

=head1 MODE

Check Shelves hardware (temperatures, voltages, electronics, fan, power supplies).

=over 8

=back

=cut
    