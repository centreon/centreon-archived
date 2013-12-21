#!/usr/bin/perl
###################################################################
# Centreon is developped with GPL Licence 2.0
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
#
# Developped by : Julien Mathis - Romain Le Merlus - Sylvestre Ho
#
###################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
#    For information : contact@merethis.com
####################################################################

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
require "@CENTREON_ETC@/centreon-config.pm";

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
my $query  = "SELECT RRDdatabase_path FROM config";
my $sth = $dbh->prepare($query);
die "Error : " . $dbh->errstr . "\n" if (!$sth);
$sth->execute();
my $row = $sth->fetchrow_hashref();
my $metric_path = $row->{RRDdatabase_path};


# Get the list of metrics to convert
$query = "SELECT metric_id, metric_name
  FROM metrics";
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

$dbh->disconnect();
