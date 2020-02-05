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

package centreon::health::checkdb;

use strict;
use warnings;
use POSIX qw(strftime);
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
    my ($centreon_db, $centstorage_db, $centstorage_db_name, $flag, $logger) = @_;
    my $size = 0;
    my ($sth, $status);

    foreach my $db_name ('centreon', $centstorage_db_name) {
        $sth = $centreon_db->query("SELECT table_schema AS db_name, SUM(data_length+index_length) AS db_size 
                                    FROM information_schema.tables
				    WHERE table_schema=".$centreon_db->quote($db_name)."");
        while (my $row = $sth->fetchrow_hashref) {
            $self->{output}->{db_size}->{$row->{db_name}} = centreon::health::misc::format_bytes(bytes_value => $row->{db_size});
        }
        next if $db_name !~ /$centstorage_db_name/;
        foreach my $table ('data_bin', 'logs', 'log_archive_host', 'log_archive_service', 'downtimes') {

            $sth = $centreon_db->query("SELECT table_name, SUM(data_length+index_length) AS table_size
                                        FROM information_schema.tables
                                        WHERE table_schema=".$centreon_db->quote($db_name)."
	    			        AND table_name=".$centreon_db->quote($table)."");

            while (my $row = $sth->fetchrow_hashref()) {
                $self->{output}->{table_size}->{$row->{table_name}} = centreon::health::misc::format_bytes(bytes_value =>$row->{table_size});
            }
	    
	    next if ($table =~ m/downtimes/);
            $sth = $centreon_db->query("SELECT MAX(CONVERT(PARTITION_DESCRIPTION, SIGNED INTEGER)) as lastPart 
					FROM INFORMATION_SCHEMA.PARTITIONS 
					WHERE TABLE_NAME='" . $table . "' 
					AND TABLE_SCHEMA='" . $db_name . "' GROUP BY TABLE_NAME;");

            while (my $row = $sth->fetchrow_hashref()) {
	        $self->{output}->{partitioning_last_part}->{$table} = defined($row->{lastPart}) ? strftime("%m/%d/%Y %H:%M:%S",localtime($row->{lastPart})) : $table . " has no partitioning !";
	    }
	}
    
    }

    my $var_list = { 'innodb_file_per_table' => 0,
		     'open_files_limit' => 0,
		     'read_only' => 0,
                     'key_buffer_size' => 1,
                     'sort_buffer_size' => 1,
                     'join_buffer_size' => 1,
                     'read_buffer_size' => 1,
                     'read_rnd_buffer_size' => 1,
                     'max_allowed_packet' => 1 };

    foreach my $var (keys %{$var_list}) {
	my $sth = $centreon_db->query("SHOW GLOBAL VARIABLES LIKE " . $centreon_db->quote($var));
	my $value = $sth->fetchrow();
	$self->{output}->{interesting_variables}->{$var} = ($var_list->{$var} == 1) ? centreon::health::misc::format_bytes(bytes_value => $value) : $value;
    }

    return $self->{output};
    
}

1;
