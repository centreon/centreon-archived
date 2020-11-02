################################################################################
# Copyright 2005-2020 Centreon
# Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

use strict;
use warnings;

package centreon::reporting::CentreonAck;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
# $centreon: Instance of centreonDB class for connection to Centreon database
# $centstorage: (optionnal) Instance of centreonDB class for connection to Centstorage database
sub new {
    my $class = shift;
    my $self  = {};
    $self->{logger} = shift;
    $self->{centstatus} = shift;
    if (@_) {
        $self->{centstorage}  = shift;
    }
    bless $self, $class;
    return $self;
}

# returns first ack time for a service or a host event
sub getServiceAckTime {
    my $self = shift;
    my $centreon = $self->{centstatus};
    my $start = shift;
    my $end = shift;
    my $hostId = shift;
    my $serviceId = shift;
    my $query;

    $query = "SELECT `entry_time` as ack_time, sticky ".
    " FROM `acknowledgements`" .
    " WHERE `host_id` = " . $hostId .
    " AND `service_id` = ". $serviceId .
    " AND `type` = 1" .
    " AND `entry_time` >= " . $start .
    " AND `entry_time` <= " . $end .
    " ORDER BY `entry_time` ASC";

    my ($status, $sth) = $centreon->query($query);
    my $ackTime = "NULL";
    my $sticky = 0;
    if (my $row = $sth->fetchrow_hashref()) {
        $ackTime = $row->{ack_time};
        $sticky = $row->{sticky};
    }
    $sth->finish();
    return ($ackTime, $sticky);
}

# returns first ack time for a service or a host event
sub getHostAckTime {
    my $self = shift;
    my $centreon = $self->{centstatus};
    my $start = shift;
    my $end = shift;
    my $hostId = shift;
    my $query;

    $query = "SELECT entry_time as ack_time, sticky ".
        " FROM `acknowledgements`".
        " WHERE `type` = 0".
        " AND `entry_time` >= " . $start .
        " AND `entry_time` <= " . $end .
        " AND `host_id` = " . $hostId .
        " ORDER BY `entry_time` ASC";

    my ($status, $sth) = $centreon->query($query);
    my $ackTime = "NULL";
    my $sticky = 0;
    if (my $row = $sth->fetchrow_hashref()) {
        $ackTime = $row->{ack_time};
        $sticky = $row->{sticky};
    }
    $sth->finish();
    return ($ackTime, $sticky);
}

1;
