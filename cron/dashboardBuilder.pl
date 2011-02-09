#! /usr/bin/perl -w
################################################################################
# Copyright 2005-2010 MERETHIS
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
use vars qw ($mysql_database_oreon $mysql_database_ods $mysql_database_status $mysql_host $mysql_user $mysql_passwd);
require "@CENTREON_ETC@/conf.pm";

# Packages used as classes
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonLogger.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonDB.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonHost.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonService.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonServiceStateEvents.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonHostStateEvents.pm";
require "@INSTALL_DIR_CENTREON@/cron/perl-modules/CentreonDashboard.pm";

# Variables
my $pid= getpgrp(0);
my $PROGNAME = "$0";
my $VERSION = "1.0";
my $varLibCentreon="@CENTREON_VARLIB@";

my ($centreon, $centstorage, $centstatus, $logger, $serviceEvents, $hostEvents, $dashboard, $service, $host);

# program exit function
sub exit_pgr() {
	system("rm -f ".$varLibCentreon."/centreon-dashboard-engine.lock");
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
sub print_usage () {
    print "Usage: $PROGNAME [-h||--help] [-v|--version] [-r|--rebuild] [-l|--lock]\n";
    exit;
}

# function that checks if the log is already built
sub dayAlreadyProcessed($$$$) {
    my ($day, $month, $year) = (shift, shift, shift);
    my $lock = shift;

    if (!defined($lock)) {
		return 0;
    }

    my $tmp_file = $varLibCentreon . "/centreon-dashboard-engine.last";
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
	
	# classes to query database tables 
	$host = CentreonHost->new($logger, $centreon);
	$service = CentreonService->new($logger, $centreon);
	$serviceEvents = CentreonServiceStateEvents->new($logger, $centstorage);
	$hostEvents = CentreonHostStateEvents->new($logger, $centstorage);
	
	# Class that builds events
	$dashboard = CentreonDashboard->new($logger, $centstorage);
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
    $dashboard->truncateServiceStats();
    $dashboard->truncateHostStats();
    # Getting first log and last log times
    my ($start, $end) = $hostEvents->getFirstLastIncidentTimes();
    if (defined($start) && defined($end)) {
   		my $periods = getDaysFromPeriod($start, $end);
   		my ($allIds, $allNames) = $host->getAllHosts(0);
    	# archiving logs for each days
    	foreach(@$periods) {
    		$logger->writeLog("INFO", "[HOST] Processing period: ".localtime($_->{"day_start"})." => ".localtime($_->{"day_end"}));
    		my $hostStateDurations = $hostEvents->getStateEventDurations($_->{"day_start"}, $_->{"day_end"});
			$dashboard->insertHostStats($allNames, $hostStateDurations, $_->{"day_start"}, $_->{"day_end"});
    	}
    }
 	($start, $end) = $serviceEvents->getFirstLastIncidentTimes();
   	if (defined($start) && defined($end)) {
   		my $periods = getDaysFromPeriod($start, $end);
   		my ($allIds, $allNames) = $service->getAllServices(0);
    	# archiving logs for each days
    	foreach(@$periods) {
    		$logger->writeLog("INFO", "[SERVICE] Processing period: ".localtime($_->{"day_start"})." => ".localtime($_->{"day_end"}));
			my $serviceStateDurations = $serviceEvents->getStateEventDurations($_->{"day_start"}, $_->{"day_end"});
			$dashboard->insertServiceStats($allNames, $serviceStateDurations, $_->{"day_start"}, $_->{"day_end"});
    	}
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
		
		my ($serviceIds, $serviceNames) = $service->getAllServices(0);
		my ($hostIds, $hostNames) = $host->getAllHosts(0);
		$logger->writeLog("INFO", "Processing period: ".localtime($start)." => ".localtime($end));
		my $hostStateDurations = $hostEvents->getStateEventDurations($start, $end);
		$dashboard->insertHostStats($hostNames, $hostStateDurations, $start, $end);
		my $serviceStateDurations = $serviceEvents->getStateEventDurations($start, $end);
		$dashboard->insertServiceStats($serviceNames, $serviceStateDurations, $start, $end);
    }
    
	exit_pgr;
}

main;
