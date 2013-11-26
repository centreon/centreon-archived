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

package storage::netapp::mode::partnerstatus;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %states = (
    1 => ['maybe down', 'WARNING'], 
    2 => ['ok', 'OK'], 
    3 => ['dead', 'CRITICAL'],
);
my %connect_state = (
    1 => ['not present', 'CRITICAL'], 
    2 => ['down', 'CRITICAL'], 
    3 => ['partial failure', 'WARNING'],
    4 => ['up', 'OK'],
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

    my $oid_cfPartnerStatus = '.1.3.6.1.4.1.789.1.2.3.4.0';
    my $oid_cfPartnerName = '.1.3.6.1.4.1.789.1.2.3.6.0';
    my $oid_cfInterconnectStatus = '.1.3.6.1.4.1.789.1.2.3.8.0';
    my $result = $self->{snmp}->get_leef(oids => [$oid_cfPartnerStatus, $oid_cfPartnerName, $oid_cfInterconnectStatus], nothing_quit => 1);
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => sprintf("Partner '%s' status is '%s'", $result->{$oid_cfPartnerName}, 
                                            ${$states{$result->{$oid_cfPartnerStatus}}}[0]));
    if (${$states{$result->{$oid_cfPartnerStatus}}}[1] ne 'OK') {
        $self->{output}->output_add(severity => ${$states{$result->{$oid_cfPartnerStatus}}}[1],
                                    short_msg => sprintf("Partner '%s' status is '%s'", $result->{$oid_cfPartnerName}, 
                                            ${$states{$result->{$oid_cfPartnerStatus}}}[0]));
    }
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => sprintf("Interconnect status is '%s'", 
                                            ${$connect_state{$result->{$oid_cfInterconnectStatus}}}[0]));
    if (${$connect_state{$result->{$oid_cfInterconnectStatus}}}[1] ne 'OK') {
        $self->{output}->output_add(severity => ${$connect_state{$result->{$oid_cfInterconnectStatus}}}[1],
                                    short_msg => sprintf("Interconnect status is '%s'", 
                                            ${$connect_state{$result->{$oid_cfInterconnectStatus}}}[0]));
    }

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check status of clustered failover partner.

=over 8

=back

=cut
    