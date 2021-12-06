################################################################################
# Copyright 2005-2013 Centreon
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
# As a special exception, the copyright holders of this program give Centreon 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of Centreon choice, provided that 
# Centreon also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::script::centreon_trap_send;

use strict;
use warnings;
use Net::SNMP;
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreon_trap_send",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noconfig => 1
    );

    bless $self, $class;
    $self->{opt_d} = 'localhost';
    $self->{opt_snmpcommunity} = 'public';
    $self->{opt_snmpversion} = 'snmpv2c';
    $self->{opt_snmpport} = 162;
    $self->{opt_oidtrap} = '.1.3.6.1.4.1.12182.1';
    @{$self->{opt_args}} = ();
    $self->add_options(
        "d=s" => \$self->{opt_d}, "destination=s" => \$self->{opt_d},
        "c=s" => \$self->{opt_snmpcommunity}, "community=s" => \$self->{opt_snmpcommunity},
        "o=s" => \$self->{opt_oidtrap}, "oid=s" => \$self->{opt_oidtrap},
        "snmpport=i" => \$self->{opt_snmpport},
        "snmpversion=s" => \$self->{opt_snmpversion},
        "a=s" => \@{$self->{opt_args}}, "arg=s" => \@{$self->{opt_args}}
    );
    $self->{timestamp} = time();
    return $self;
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    my ($snmp_session, $error) = Net::SNMP->session(-hostname    => $self->{opt_d},
                                                    -community    => $self->{opt_snmpcommunity},
                                                    -port        => $self->{opt_snmpport},
                                                    -version   => $self->{opt_snmpversion});
    if (!defined($snmp_session)) {
        $self->{logger}->writeLogError("UNKNOWN: SNMP Session : $error");
        die("Quit");
    }

    my @oid_value = ();
    foreach (@{$self->{opt_args}}) {
        my ($oid, $type, $value) = split /:/, $_;
        my $result;
        my $ltmp = "\$result = Net::SNMP::$type;";
        eval $ltmp;
        push @oid_value,($oid, $result, $value);
    }
    unshift @oid_value,('1.3.6.1.6.3.1.1.4.1.0', OBJECT_IDENTIFIER, $self->{opt_oidtrap});
    unshift @oid_value,('1.3.6.1.2.1.1.3.0', TIMETICKS, $self->{timestamp});
    $snmp_session->snmpv2_trap(-varbindlist => \@oid_value);
    $snmp_session->close();
}

1;

__END__

=head1 NAME

    sample - Using GetOpt::Long and Pod::Usage

=cut
