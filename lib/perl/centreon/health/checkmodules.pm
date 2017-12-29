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

package centreon::health::checkmodules;

use strict;
use warnings;
use centreon::health::misc;

sub new {
    my $class = shift;
    my $self = {};
    $self->{output} = {};

    bless $self, $class;
    return $self;
}

sub run {
    my $self = shift;
    my ($centreon_db) = @_;
    my $size = 0;
    my ($sth, $status);

    $sth = $centreon_db->query("SELECT name, rname, author, mod_release FROM  modules_informations");
    while (my $row = $sth->fetchrow_hashref) {
        $self->{output}->{$row->{name}}{full_name} = $row->{rname};
        $self->{output}->{$row->{name}}{author} = $row->{author};
        $self->{output}->{$row->{name}}{version} = $row->{mod_release};
    }

    return $self->{output};
    
}

1;
