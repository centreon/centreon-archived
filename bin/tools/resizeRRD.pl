#! /usr/bin/perl
################################################################################
# Copyright 2005-2011 MERETHIS
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
# For more information : contact@centreon.com
# 
# SVN : $URL$
# SVN : $Id$
#
####################################################################################

use DBI;

use vars qw($mysql_user $mysql_passwd $mysql_host $mysql_database_oreon $mysql_database_ods);
use vars qw($con $conC $debug $status $metrics);

$debug = 0;

require "@CENTREON_ETC@/conf.pm";

# Init Values
$status = 0;
$metrics = 0;

$con  = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
if (!defined($con)) {
    writeLogFile("Error when connecting to database : ".$DBI::errstr."");
    exit();
}

$conC = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
if (!defined($conC)) {
    writeLogFile("Error when connecting to database : ".$DBI::errstr."");
    exit();
}

# Get Config information
my $sth1 = $conC->prepare("SELECT * FROM `config`");
if (!$sth1->execute) {
    writeLogFile("Error:" . $sth1->errstr . "\n");
}
my $config = $sth1->fetchrow_hashref();
$sth1->finish();
undef($sth1);

# Change heartbeat for metrics Databases
my $dh;
my $step = 300;
opendir($dh, $config->{'RRDdatabase_path'})  || die "can't opendir : $!";
while (my $file = readdir($dh)) {
    if ($file != "." && $file != "..") {
	my $RESULT = `rrdtool info $config->{'RRDdatabase_path'}/$file`;
	if ($debug) {
	    print $RESULT;
	}
	my @tab = split("\n", $RESULT);   
	if ($tab[2] =~ m/step\ =\ ([0-9]*)/) {
	    $step = $1;
	}
	if ($tab[5] =~ m/ds\[([a-zA-Z0-9\_\-]*)\]/) {
	    my $hb = $step * 10;
	    my $cmd = "rrdtool tune ".$config->{'RRDdatabase_path'}."/$file --heartbeat \"$1:$hb\"";
	    if ($debug) {
		print $cmd."\n";
	    } else {
		`$cmd`;
		$metrics++;
	    }
	} else {
	    print " --> Doesn't match\n";
	}
    }
}
closedir $dh;

# Change heartbeat for Status Databases
$dh;
$step = 300;
opendir($dh, $config->{'RRDdatabase_status_path'})  || die "can't opendir : $!";
while (my $file = readdir($dh)) {
    if ($file != "." && $file != "..") {
	my $RESULT = `rrdtool info $config->{'RRDdatabase_status_path'}/$file`;
	if ($debug) {
	    print $RESULT;
	}
	my @tab = split("\n", $RESULT);   
	if ($tab[2] =~ m/step\ =\ ([0-9]*)/) {
	    $step = $1;
	}
	if ($tab[5] =~ m/ds\[([a-zA-Z0-9\_\-]*)\]/) {
	    my $hb = $step * 10;
	    my $cmd = "rrdtool tune ".$config->{'RRDdatabase_status_path'}."$file --heartbeat \"$1:$hb\"";
	    if ($debug) {
		print $cmd."\n";
	    } else {
		`$cmd`;
		$status++;
	    }
	} else {
	    print "Regexp problem --> Doesn't match\n";
	}
    }
}
closedir $dh;

print "$metrics metrics RRDTool databases modified\n";
print "$status status RRDTool databases modified\n";
