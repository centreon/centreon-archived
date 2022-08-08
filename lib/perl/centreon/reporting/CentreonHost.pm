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

package centreon::reporting::CentreonHost;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
# $centreon: Instance of centreonDB class for connection to Centreon database
# $centstorage: (optionnal) Instance of centreonDB class for connection to Centstorage database
sub new {
    my $class = shift;
    my $self  = {};
    $self->{logger} = shift;
    $self->{centreon} = shift;
    if (@_) {
        $self->{centstorage}  = shift;
    }
    bless $self, $class;
    return $self;
}

# returns two references to two hash tables => hosts indexed by id and hosts indexed by id
sub getAllHostIds {
    my $self = shift;
    my $centreon = $self->{centreon};
    my $activated = 1;
    if (@_) {
        $activated  = 0;
    }
    my %hostIds;

    my $query = "SELECT `host_id`" .
                " FROM `host`".
                " WHERE `host_register` = '1'";
    if ($activated == 1) {
        $query .= " AND `host_activate` = '1'";
    }
    my ($status, $sth) = $centreon->query($query);
    while (my $row = $sth->fetchrow_hashref()) {
        $hostIds{$row->{host_id}} = 1;
    }
    $sth->finish();
    return \%hostIds;
}

1;
