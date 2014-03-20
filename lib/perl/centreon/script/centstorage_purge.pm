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
#
####################################################################################

package centreon::script::centstorage_purge;

use strict;
use warnings;
use centreon::script;
use centreon::common::lock;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centstorage_purge",
        centreon_db_conn => 1,
        centstorage_db_conn => 1
    );

    bless $self, $class;
    $self->{broker} = "ndo";
    return $self;
}

sub read_config {
    my $self = shift;
    my ($status, $sth) = $self->{csdb}->query(<<"EOQ");
SELECT len_storage_mysql,archive_retention,reporting_retention
FROM config
EOQ
    die "Failed to retrieve configuration from database" if $status == -1;
    $self->{config} = $sth->fetchrow_hashref();

    ($status, $sth) = $self->{cdb}->query(<<"EOQ");
SELECT `value` FROM `options` WHERE `key` = 'broker'
EOQ
    die "Failed to retrieve the broker type from database" if $status == -1;
    $self->{broker} = $sth->fetchrow_hashref()->{value};
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->read_config();

    if (defined $self->{config}->{len_storage_mysql} && 
        $self->{config}->{len_storage_mysql} != 0) {
        my $delete_limit = time() - 60 * 60 * 24 * $self->{config}->{len_storage_mysql};

        $self->{logger}->writeLogInfo("Purging centstorage.data_bin table...");
        $self->{csdb}->do("DELETE FROM data_bin WHERE ctime < '$delete_limit'");
        $self->{logger}->writeLogInfo("Done");
    }

    if (defined($self->{config}->{archive_retention}) 
        && $self->{config}->{archive_retention} != 0) {
        my $last_log = time() - ($self->{config}->{archive_retention} * 24 * 60 * 60);
        my $table = ($self->{broker} eq "broker") ? "logs" : "log";

        $self->{logger}->writeLogInfo("Purging centstorage.$table table...");
        eval {
            my $lock = undef;
            if ($self->{broker} eq "ndo") {
                $lock = centreon::common::lock::sql->new("logAnalyser", dbc => $self->{cdb});
                $lock->set();
            }
            $self->{csdb}->do("DELETE FROM `$table` WHERE `ctime` < '$last_log'");
        };
        if ($@) {
            $self->{logger}->writeLogError("Failed: $@");
        } else {
            $self->{logger}->writeLogInfo("Done");
        }
    }

    if (defined($self->{config}->{reporting_retention}) 
        && $self->{config}->{reporting_retention} != 0) {
        my $last_log = time() - ($self->{config}->{reporting_retention} * 24 * 60 * 60);

        $self->{logger}->writeLogInfo("Purging log archive tables...");
        $self->{csdb}->do("DELETE FROM `log_archive_host` WHERE `date_end` < '$last_log'");
        $self->{csdb}->do("DELETE FROM `log_archive_service` WHERE `date_end` < '$last_log'");
        $self->{logger}->writeLogInfo("Done");
    }
}

1;

