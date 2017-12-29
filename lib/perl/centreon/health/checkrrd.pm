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

    $self->{output}->{$self->{rrd_metrics}}{size} = centreon::health::misc::format_bytes(bytes_value => $size_metrics);
    $self->{output}->{$self->{rrd_status}}{size} = centreon::health::misc::format_bytes(bytes_value => $size_status);
    $self->{output}->{rrd_written_last_5m} = $count_last_written;
    $self->{output}->{$self->{rrd_metrics}}{count} = $count_metrics;
    $self->{output}->{$self->{rrd_status}}{count} = $count_status;
}

sub run {
    my $self = shift;
    my ($centstorage_db) = @_;

    $self->get_rrd_path(csdb => $centstorage_db);
    $self->get_rrd_infos();

    return $self->{output}
}

1;
