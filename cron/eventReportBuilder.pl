#! /usr/bin/perl -w
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
# SVN : $URL
# SVN : $Id
#
####################################################################################

use strict;
use DBI;
use POSIX;
use Getopt::Long;
use Time::Local;

# variables from external packages
use vars qw ($mysql_database_oreon $mysql_database_ods $mysql_host $mysql_user $mysql_passwd);
require "@CENTREON_ETC@/conf.pm";

#use vars qw ($PROGNAME $VERSION $varLibCentreon $lock_file %options %serviceStates %hostStates %servicStateIds %hostStateIds);
#require "perl-modules/variables.pm";

# Packages used as classes
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonLogger.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonDB.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonHost.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonService.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonLog.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonServiceStateEvents.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonHostStateEvents.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonProcessStateEvents.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonDownTime.pm";

# Variables
my $pid= getpgrp(0);
my $PROGNAME = "$0";
my $VERSION = "1.0";
my $varLibCentreon="@CENTREON_VARLIB@";

my ($centreon, $centstorage, $centstatus, $logger, $processEvents, $serviceEvents, $hostEvents, $nagiosLog, $service, $host, $dbLayer);

# program exit function
sub exit_pgr() {
	system("rm -f ".$varLibCentreon."/archive-monitoring-incidents.lock");
    if (defined($centreon)) {
		$centreon->disconnect;
    }
    if (defined($centstorage)) {
		$centstorage->disconnect;
    }
    $logger->writeLog("INFO", "Exiting program...(pid=$pid)");
    $logger->close();
    exit;
}

# program usage echo
sub print_usage() {
    print "Usage: $PROGNAME [-h||--help] [-v|--version] [-r|--rebuild] [-l|--lock]\n";
    exit;
}

# get db layer
sub getDbLayer() {
	my $res = $centreon->query("SELECT `value` FROM `options` WHERE `key` = 'broker'");
	if (my $row = $res->fetchrow_hashref()) { 
		return $row->{'value'};
	}
	return "ndo";
}

# function that checks if the log is already built
sub dayAlreadyProcessed($$$$) {
    my ($day, $month, $year) = (shift, shift, shift);
    my $lock = shift;

    if (!defined($lock)) {
		return 0;
    }

    my $tmp_file = $varLibCentreon . "/archive-monitoring-incidents.last";
    my $last;
    my $now;
    my $write_cmd;

    $now = $day.$month.$year;
    if (-e "$tmp_file") {
    	chomp($last = `cat $tmp_file`);
    	$write_cmd = `echo $now > $tmp_file`;
	if ($now == $last) {
	    print "[".time."] Error : day already processed\n";
	    return 1;
	}
	else {
	    return 0;
	}
    }
    $write_cmd = `echo $now > $tmp_file`;
    return 0;
}

# Initialize objects for program
sub initVars {
	# program logger
	$logger = CentreonLogger->new();
	$logger->stderr(1);
	
	# database connectors
	$centreon = CentreonDB->new($logger, $mysql_database_oreon, $mysql_host, $mysql_user, $mysql_passwd);
	$centstorage = CentreonDB->new($logger, $mysql_database_ods, $mysql_host, $mysql_user, $mysql_passwd);
	
	# Getting centstatus database name
	$dbLayer = getDbLayer();
	if ($dbLayer eq "ndo") {
		my $sth = $centreon->query("SELECT db_name, db_host, db_user, db_pass FROM cfg_ndo2db WHERE activate = '1' LIMIT 1");
		if (my $row = $sth->fetchrow_hashref()) {
			#connecting to censtatus
			$centstatus = CentreonDB->new($logger, $row->{"db_name"}, $row->{'db_host'}, $row->{'db_user'}, $row->{'db_pass'});
		}
	} elsif ($dbLayer eq "broker") {
		$centstatus = $centstorage;
	} else {
		$logger->writeLog("ERROR", "Unsupported database layer: " . $dbLayer);
		exit_pgr();
	}
	
	# classes to query database tables 
	$host = CentreonHost->new($logger, $centreon);
	$service = CentreonService->new($logger, $centreon);
	$nagiosLog = CentreonLog->new($logger, $centstorage, $dbLayer);
	my $centreonDownTime = CentreonDownTime->new($logger, $centstatus, $dbLayer);
	$serviceEvents = CentreonServiceStateEvents->new($logger, $centstorage, $centreonDownTime);
	$hostEvents = CentreonHostStateEvents->new($logger, $centstorage, $centreonDownTime);
	
	# Class that builds events
	$processEvents = CentreonProcessStateEvents->new($logger, $host, $service, $nagiosLog, $hostEvents, $serviceEvents, $centreonDownTime, $dbLayer);
}

# For a given period returns in a table each	
sub getDaysFromPeriod {
	my ($start, $end) = (shift, shift);
	
	my @days;
    # Check if $end is > to current time
	my ($day,$month,$year) = (localtime(time))[3,4,5];
    my $today_begin =  mktime(0,0,0,$day,$month,$year,0,0,-1);
    if ($end > $today_begin) {
		$end = $today_begin;
    }
    # get start day as mm/dd/yyyy 00:00
    ($day,$month,$year) = (localtime($start))[3,4,5];
    $start =  mktime(0,0,0,$day,$month,$year,0,0,-1);
    my $previousDay = mktime(0,0,0,$day - 1,$month,$year,0,0,-1);
    
    while ($start < $end) {
		# getting day beginning => 00h 00min
	    # if there is few hour gap (time change : winter/summer), we also readjust it
		if ($start == $previousDay) {
		    $start = mktime(0,0,0, ++$day, $month, $year,0,0,-1);
		}
		# setting day beginning/end hour and minute with the value set in centreon DB
		my $dayEnd =mktime(0, 0, 0, ++$day, $month, $year, 0, 0, -1);
		my %period = ("day_start" => $start, "day_end" => $dayEnd);
		$days[scalar(@days)] = \%period;
		
		$previousDay = $start;
		$start = $dayEnd;
    }
    return \@days;
}

# rebuild all events
sub rebuildIncidents {
    my $time_period = shift;
    # Empty tables
    $serviceEvents->truncateStateEvents();
    $hostEvents->truncateStateEvents();
    # Getting first log and last log times
    my ($start, $end) = $nagiosLog->getFirstLastLogTime();
   	my $periods = getDaysFromPeriod($start, $end);
    # archiving logs for each days
    foreach(@$periods) {
    	$logger->writeLog("INFO", "Processing period: ".localtime($_->{"day_start"})." => ".localtime($_->{"day_end"}));
		$processEvents->parseHostLog($_->{"day_start"}, $_->{"day_end"});
		$processEvents->parseServiceLog($_->{"day_start"}, $_->{"day_end"});
    }
}

# MAIN function
sub main {
	
    initVars();
    my %options;
    Getopt::Long::Configure('bundling');
    GetOptions ("h|help" => \$options{"help"}, 
		"l|use-lock" => \$options{"lock"},
		"r|rebuild" => \$options{"rebuild"},
		"v|version" => \$options{"version"});

    if (defined($options{"help"})) {
		print_usage;
    }
    if ($options{'version'}) {
		print "Program version: $VERSION\n";
		exit;
    }
    
    $logger->writeLog("INFO", "Starting program...(pid=$pid)");
    
    if (defined($options{'rebuild'})) {
		rebuildIncidents();
    }else {
    	my $currentTime = time;
		my ($day,$month,$year) = (localtime($currentTime))[3,4,5];
		my $end = mktime(0,0,0,$day,$month,$year,0,0,-1);
		my $start = $end - (60 * 60 * 24);
		$logger->writeLog("INFO", "Processing period: ".localtime($start)." => ".localtime($end));
		$processEvents->parseHostLog($start, $end);
		$processEvents->parseServiceLog($start, $end);
    }
    
	exit_pgr;
}

main;
