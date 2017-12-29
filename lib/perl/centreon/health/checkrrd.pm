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

package centreon::health::checkrrd;

use strict;
use warnings;
use centreon::common::misc;
use centreon::health::misc;

sub new {
    my $class = shift;
    my $self = {};
    $self->{rrd_metrics} = undef;
    $self->{rrd_status} = undef;
    $self->{output} = {};

    bless $self, $class;
    return $self;
}

sub get_rrd_path {
    my ($self, %options) = @_;
    my ($sth, $status);

    $sth = $options{csdb}->query("SELECT RRDdatabase_path, RRDdatabase_status_path FROM config");

    while (my $row = $sth->fetchrow_hashref()) {
        $self->{rrd_metrics} = $row->{RRDdatabase_path};
        $self->{rrd_status} = $row->{RRDdatabase_status_path};
    }
    
}
     
sub get_rrd_infos {
    my $self = shift;

    my ($lerror_m, $size_metrics) = centreon::common::misc::backtick(command => "du -sb " . $self->{rrd_metrics});
    my ($lerror_s, $size_status) = centreon::common::misc::backtick(command => "du -sb " . $self->{rrd_status});
    my ($lerror_cm, $count_metrics) = centreon::common::misc::backtick(command => "ls -l " . $self->{rrd_metrics} . " | wc -l"); 
    my ($lerror_cs, $count_status) = centreon::common::misc::backtick(command => "ls -l " . $self->{rrd_status} . " | wc -l");
    my ($lerror_lw, $count_last_written) = centreon::common::misc::backtick(command => "find " . $self->{rrd_metrics} . " -type f -mmin 5 | wc -l");
    my ($lerror_od, $count_outdated_rrd) = centreon::common::misc::backtick(command => "find " . $self->{rrd_metrics} . " -type f -mmin +288000 | wc -l"); 

    $self->{output}->{$self->{rrd_metrics}}{size} = centreon::health::misc::format_bytes(bytes_value => $size_metrics);
    $self->{output}->{$self->{rrd_status}}{size} = centreon::health::misc::format_bytes(bytes_value => $size_status);
    $self->{output}->{rrd_written_last_5m} = $count_last_written;
    $self->{output}->{rrd_not_updated_since_180d} = $count_outdated_rrd;
    $self->{output}->{$self->{rrd_metrics}}{count} = $count_metrics;
    $self->{output}->{$self->{rrd_status}}{count} = $count_status;
}

sub run {
    my $self = shift;
    my ($centstorage_db, $flag, $logger) = @_;
 
    if ($flag == 1) {
	$logger->writeLogDebug("[INFO] Skipping RRD checks");
	return 0
    }
    
    $logger->writeLogDebug("[INFO] Gathering RRD informations");   
    $self->get_rrd_path(csdb => $centstorage_db);
    $self->get_rrd_infos();

    return $self->{output}
}

1;
