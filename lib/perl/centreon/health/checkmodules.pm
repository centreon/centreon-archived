#
# Copyright 2017 Centreon (http://www.centreon.com/)
#
# Centreon is a full-fledged industry-strength solution that meets
# the needs in IT infrastructure and application monitoring for
# service performance.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#

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
    my ($centreon_db, $logger) = @_;
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
