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

package centreon::health::checkglobal;

use strict;
use warnings;
use centreon::common::misc;
use centreon::health::misc;

sub new {
    my $class = shift;
    my $self = {};
    $self->{output} = {};

    bless $self, $class;
    return $self;
}

sub query_misc {
    my ($self, %options) = @_;
    my ($sth, $status);

    $sth = $options{cdb}->query($options{query});

    return $sth->fetchrow()
}
     
sub run {
    my $self = shift;
    my ($centreon_db, $centstorage_db, $centreon_version) = @_;

    my $query_misc = {   count_pp => [$centreon_db, $centreon_version eq '2.7' ? "SELECT count(*) FROM mod_pluginpack" : "SELECT count(*) FROM mod_ppm_pluginpack"],
			 count_downtime => [$centreon_db, "SELECT count(*) FROM downtime"],
			 count_modules => [$centreon_db, "SELECT count(*) FROM modules_informations"],
			 centreon_version => [$centreon_db, "SELECT value FROM informations LIMIT 1"],
			 count_metrics => [$centstorage_db, "SELECT count(*) FROM metrics"] };

    foreach my $info (keys $query_misc) { 
        my $result = $self->query_misc(cdb => $query_misc->{$info}[0],
		 		       query => $query_misc->{$info}[1] );
	$self->{output}->{$info} = $result;

    }

    return $self->{output}
}

1;
