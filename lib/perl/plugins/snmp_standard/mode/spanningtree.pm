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

package snmp_standard::mode::spanningtree;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %states = (
    1 => ['disabled', 'OK'], 
    2 => ['blocking', 'CRITICAL'], 
    3 => ['listening', 'OK'], 
    4 => ['learning', 'OK'],
    5 => ['forwarding', 'OK'],
    6 => ['broken', 'CRITICAL'],
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

    my $oid_dot1dStpPortEnable = '.1.3.6.1.2.1.17.2.15.1.4';
    my $oid_dot1dStpPortState = '.1.3.6.1.2.1.17.2.15.1.3';
    my $oid_dot1dBasePortIfIndex = '.1.3.6.1.2.1.17.1.4.1.2';
    my $oid_ifDesc = '.1.3.6.1.2.1.2.2.1.2';
    my $result = $self->{snmp}->get_table(oid => $oid_dot1dStpPortEnable, nothing_quit => 1);
    
    foreach my $oid (keys %$result) {
        $oid =~ /\.([0-9]+)$/;
        my $instance = $1;

        # '2' => disabled, we skip
        if ($result->{$oid} == 2) {
            $self->{output}->output_add(long_msg => sprintf("Skipping interface '%d': Stp port disabled", $instance));
            next;
        }
        
        $self->{snmp}->load(oids => [$oid_dot1dStpPortState . "." . $instance, $oid_dot1dBasePortIfIndex . "." . $instance]);
    }
    
    $result = $self->{snmp}->get_leef(nothing_quit => 1);
    $self->{output}->output_add(severity => 'OK',
                                short_msg => 'Spanning Tree is ok on all interfaces');
    # Get description
    foreach my $oid (keys %$result) {
        next if ($oid !~ /^$oid_dot1dBasePortIfIndex/);
        $self->{snmp}->load(oids => [$oid_ifDesc . "." . $result->{$oid}]);
    }
    my $result_desc = $self->{snmp}->get_leef();
    
    # Parsing ports
    foreach my $oid (keys %$result) {
        next if ($oid !~ /^$oid_dot1dStpPortState/);
        $oid =~ /\.([0-9]+)$/;
        my $instance = $1;

        my $stp_state = $result->{$oid};
        my $descr = $result_desc->{$oid_ifDesc . '.' . $result->{$oid_dot1dBasePortIfIndex . '.' . $instance}};
        
        $self->{output}->output_add(long_msg => sprintf("Spanning Tree interface '%s' state is %s", $descr,
                                            ${$states{$stp_state}}[0]));
        if (${$states{$stp_state}}[1] ne 'OK') {
             $self->{output}->output_add(severity => ${$states{$stp_state}}[1],
                                        short_msg => sprintf("Spanning Tree interface '%s' state is %s", $descr,
                                                             ${$states{$stp_state}}[0]));
        }
    }
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check Spanning-Tree current state of ports (BRIDGE-MIB).

=over 8

=back

=cut
    