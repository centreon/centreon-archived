#!/usr/bin/perl
################################################################################
# Copyright 2005-2015 Centreon
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
####################################################################################

use strict;

####################################################################
# Required libs
####################################################################
use DBI;
use RRDs;

####################################################################
# Global Variables
####################################################################

# Include Centreon DB Configuration Variables
use vars qw($centreon_config);
require "@CENTREON_ETC@/conf.pm";

sub get_ds_name {
    my $ds_name = shift;
    
    $ds_name =~ s/\//slash\_/g;
    $ds_name =~ s/\\/bslash\_/g;
    $ds_name =~ s/\%/pct\_/g;
    $ds_name =~ s/\#S\#/slash\_/g;
    $ds_name =~ s/\#BS\#/bslash\_/g;
    $ds_name =~ s/\#P\#/pct\_/g;
    $ds_name =~ s/[^0-9_\-a-zA-Z]/-/g;
    return $ds_name;
}

my $dbh = DBI->connect(
    "DBI:mysql:database=" . $centreon_config->{centstorage_db} . ";host=" . $centreon_config->{db_host},
    $centreon_config->{db_user},
    $centreon_config->{db_passwd},
    { 'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1}
    );

# Get path to metrics file
my $query  = "SELECT RRDdatabase_path, RRDdatabase_status_path FROM config";
my $sth = $dbh->prepare($query);
die "Error : " . $dbh->errstr . "\n" if (!$sth);
$sth->execute();
my $row = $sth->fetchrow_hashref();
my $metric_path = $row->{RRDdatabase_path};
my $status_path = $row->{RRDdatabase_status_path};


# Get the list of metrics to convert
$query = "SELECT metric_id, metric_name FROM metrics";
$sth = $dbh->prepare($query);
die "Error : " . $dbh->errstr . "\n" if (!$sth);
$sth->execute();
die "Error : " . $dbh->errstr . "\n" if (!$sth);

while ($row = $sth->fetchrow_hashref()) {
    my $filename = $metric_path . '/' . $row->{metric_id} . '.rrd';
    my $metric_name = get_ds_name($row->{metric_name});
    $metric_name = substr($metric_name, 0, 19);
    if (-w $filename) {
        RRDs::tune($filename, "-r", $metric_name . ":value");
        my $rrdError = RRDs::error;
        if ($rrdError) {
            print "Error in metric " . $row->{metric_id} . " : " . $rrdError . "\n";
        }
    }
}

# Get the list of index_data id to convert
$query = "SELECT id FROM index_data";
$sth = $dbh->prepare($query);
die "Error : " . $dbh->errstr . "\n" if (!$sth);
$sth->execute();
die "Error : " . $dbh->errstr . "\n" if (!$sth);

while ($row = $sth->fetchrow_hashref()) {
    my $filename = $status_path . '/' . $row->{id} . '.rrd';
    if (-w $filename) {
        RRDs::tune($filename, "-r", "status:value");
        my $rrdError = RRDs::error;
        if ($rrdError) {
            print "Error in index " . $row->{id} . " : " . $rrdError . "\n";
        }
    }
}

$dbh->disconnect();
