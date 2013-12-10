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

package network::cisco::asa::mode::failover;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %map_failover = (
    1 => 'other',
    2 => 'up', # for '.4' index
    3 => 'down', # can be
    4 => 'error', # maybe
    5 => 'overTemp',
    6 => 'busy',
    7 => 'noMedia',
    8 => 'backup',
    9 => 'active', # can be
    10 => 'standby' # can be
);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                {
                                  "dont-warn-notstandby"       => { name => 'dont_warn_notstandby' },
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

    my $active_units = 0;
    my $exit = 'ok';
    # primary is '.6' index and secondary is '.7' index (it's like that. '.4' is the global interface)
    my $oid_cfwHardwareStatusValue_primary = '.1.3.6.1.4.1.9.9.147.1.2.1.1.1.3.6';
    my $oid_cfwHardwareStatusValue_secondary = '.1.3.6.1.4.1.9.9.147.1.2.1.1.1.3.7';
    my $oid_cfwHardwareStatusDetail_primary = '.1.3.6.1.4.1.9.9.147.1.2.1.1.1.4.6';
    my $oid_cfwHardwareStatusDetail_secondary = '.1.3.6.1.4.1.9.9.147.1.2.1.1.1.4.7';
    my $result = $self->{snmp}->get_leef(oids => [$oid_cfwHardwareStatusValue_primary, $oid_cfwHardwareStatusValue_secondary, 
                                                  $oid_cfwHardwareStatusDetail_primary, $oid_cfwHardwareStatusDetail_secondary], nothing_quit => 1);
    
    if ($result->{$oid_cfwHardwareStatusValue_primary} == 9 || $result->{$oid_cfwHardwareStatusValue_primary} == 10 ) {
        $active_units++;
    }
    if ($result->{$oid_cfwHardwareStatusValue_secondary} == 9 || $result->{$oid_cfwHardwareStatusValue_secondary} == 10 ) {
        $active_units++;
    }
    if ($active_units == 0) {
        $exit = 'critical';
    } elsif ($active_units == 1 && !defined($self->{option_results}->{dont_warn_notstandby})) {
        # No redundant interface
        $exit = 'warning';
    }
    
    $self->{output}->output_add(severity => $exit,
                                short_msg => sprintf("Primary unit is '%s' [details: '%s'], Secondary unit is '%s' [details : '%s']",
                                                     $map_failover{$result->{$oid_cfwHardwareStatusValue_primary}}, $result->{$oid_cfwHardwareStatusDetail_primary},
                                                     $map_failover{$result->{$oid_cfwHardwareStatusValue_secondary}}, $result->{$oid_cfwHardwareStatusDetail_secondary}));                                 
                                                     
    $self->{output}->perfdata_add(label => "active_units",
                                  value => $active_units,
                                  min => 0, max => 2);
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check current/average connections on Cisco ASA (CISCO-UNIFIED-FIREWALL-MIB).

=over 8

=item B<--dont-warn-notstandby>

Don't return warning if a unit is active and the other unit is not in standby status.

=back

=cut
    