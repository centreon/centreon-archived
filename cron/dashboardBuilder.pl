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
my $VERSION = "1.1";
my $varLibCentreon="@CENTREON_VARLIB@";

my ($centreon, $centstorage, $centstatus, $logger, $serviceEvents, $hostEvents, $dashboard, $service, $host, $liveService);
my @weekDays = ("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

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
    print "Usage: $PROGNAME \n";
    print "      [-h||--help]        Prints program help\n";
    print "      [-l|--lock]         Verify if the program was already run for the current day\n";
    print "      [-r|--rebuild]      Rebuild completely the reporting\n";
    print "      [-s|--start-period] (optionnal) The reporting rebuild period starts at the indicated date. Example: 2011-09-11\n";
    print "      [-e|--end-period]   (optionnal) The reporting rebuild period ends at the indicated date. Example: 2011-09-12\n";
    print "      [--host-only]       (optionnal) Rebuild only reporting for hosts\n";
    print "      [--service-only]    (optionnal) Rebuild only reporting for services\n";
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

# get informations about an  hidden feature
# reporting can be calculated on a specific time range for each day of the week
sub getLiveService {
	my %result = ();;
	my $sth = $centreon->query("SELECT * FROM `contact_param` WHERE `cp_contact_id` is null");
    while (my $row = $sth->fetchrow_hashref()) {
		$result{$row->{"cp_key"}} = $row->{"cp_value"};
    }
    $sth->finish;
    # verifying if all variables are set
    if (!defined($result{"report_hour_start"})) {
		   	$result{"report_hour_start"} = 0;
	}
	if (!defined($result{"report_minute_start"})) {
		   	$result{"report_minute_start"} = 0;
	}
	if (!defined($result{"report_hour_end"})) {
		   	$result{"report_hour_end"} = 24;
	}
	if (!defined($result{"report_minute_end"})) {
		   	$result{"report_minute_end"} = 0;
	}
	foreach(@weekDays) {
		my $day = $_;
		if (!defined($result{"report_".$day})) {
		   	$result{"report_".$day} = 1;
		}
	}
    return(\%result);
}

# Initialize objects for program
sub initVars {
	# program logger
	$logger = CentreonLogger->new();
	$logger->stderr(1);
	
	# database connectors
	$centreon = CentreonDB->new($logger, $mysql_database_oreon, $mysql_host, $mysql_user, $mysql_passwd, 3306);
	$centstorage = CentreonDB->new($logger, $mysql_database_ods, $mysql_host, $mysql_user, $mysql_passwd, 3306);
	
	# classes to query database tables 
	$host = CentreonHost->new($logger, $centreon);
	$service = CentreonService->new($logger, $centreon);
	$serviceEvents = CentreonServiceStateEvents->new($logger, $centstorage);
	$hostEvents = CentreonHostStateEvents->new($logger, $centstorage);
	
	# Class that builds events
	$dashboard = CentreonDashboard->new($logger, $centstorage);
	$liveService = getLiveService;
}

# For a given period returns in a table each
sub getDaysFromPeriod {
	my ($start, $end) = (shift, shift);
	
	my @days;
    # Check if $end is > to current time
	my ($day,$month,$year) = (localtime(time))[3,4,5];
    my $todayStart =  mktime(0,0,0,$day,$month,$year,0,0,-1);
    if ($end > $todayStart) {
		$end = $todayStart;
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
		my $currentDayOfWeek = (localtime($start))[6];
		 # If in the configuration, this day of week is not selected, the reporting is not calculated
		if (defined($liveService->{"report_".$weekDays[$currentDayOfWeek]}) && $liveService->{"report_".$weekDays[$currentDayOfWeek]} == 1) {
			# setting reporting date and time ranges
			my $dayStart = mktime(0,$liveService->{"report_minute_start"},$liveService->{"report_hour_start"},$day,$month,$year,0,0,-1);
			my $dayEnd = mktime(0,$liveService->{"report_minute_end"},$liveService->{"report_hour_end"},$day,$month,$year,0,0,-1);
			my %period = ("day_start" => $dayStart, "day_end" => $dayEnd);
			$days[scalar(@days)] = \%period;
		}else {
			$day++;
		}
		
		$previousDay = $start;
		$start = mktime(0,0,0, $day, $month, $year,0,0,-1);
    }
    return \@days;
}

# rebuild all events
sub rebuildIncidents {
	my ($start, $end, $purgeType, $hostOnly, $serviceOnly) = (shift, shift, shift, shift, shift);

	if (!defined($start) || !defined($end)) {
		$logger->writeLog("ERROR", "Cannot determine reporting rebuild period");
	}
    # purge tables in order to rebuild statistics
    my $periods = getDaysFromPeriod($start, $end);
    if (!scalar(@$periods)) {
    	$logger->writeLog("INFO", "Incorrect rebuild period");
    }
	if ($purgeType eq "truncate") {
    	$dashboard->truncateServiceStats();
    	$dashboard->truncateHostStats();
	}else {
		$dashboard->deleteServiceStats($start, $end);
		$dashboard->deleteHostStats($start, $end);
	}
	if (defined($start) && defined($end) && !$serviceOnly) {
		my ($allIds, $allNames) = $host->getAllHosts(0);
	  	# archiving logs for each days
	   	foreach(@$periods) {
	   		$logger->writeLog("INFO", "[HOST] Processing period: ".localtime($_->{"day_start"})." => ".localtime($_->{"day_end"}));
	   		my $hostStateDurations = $hostEvents->getStateEventDurations($_->{"day_start"}, $_->{"day_end"});
			$dashboard->insertHostStats($allNames, $hostStateDurations, $_->{"day_start"}, $_->{"day_end"});
	   	}
    }
   	if (defined($start) && defined($end) && !$hostOnly) {
   		my ($allIds, $allNames) = $service->getAllServices(0);
    	# archiving logs for each days
    	foreach(@$periods) {
    		$logger->writeLog("INFO", "[SERVICE] Processing period: ".localtime($_->{"day_start"})." => ".localtime($_->{"day_end"}));
			my $serviceStateDurations = $serviceEvents->getStateEventDurations($_->{"day_start"}, $_->{"day_end"});
			$dashboard->insertServiceStats($allNames, $serviceStateDurations, $_->{"day_start"}, $_->{"day_end"});
    	}
    }
}

# returns the reporting rebuild period that could be retrieved form DB or given in parameter
# returns also reporting tables purge type truncate or delete for a specific period
sub getRebuildOptions {
	my ($paramStartDate, $paramEndDate, $hostOnly, $serviceOnly) = (shift, shift, shift, shift);
	
	if (!defined($hostOnly)) {
		$hostOnly = 0;
	}else {
		$hostOnly = 1;
	}
	if (!defined($serviceOnly)) {
		$serviceOnly = 0;
	}else {
		$serviceOnly = 1;
	}
	my ($start, $end);
	my $purgeType = "truncate";
	if (defined($paramStartDate)){
		
		if ($paramStartDate =~ /^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/) {
			$start = mktime(0,0,0,$3,$2 - 1,$1 - 1900,0,0,-1);
			$purgeType = "delete";
		}else {
			$logger->writeLog("ERROR", "Bad paramater syntax for option [-s|--period-start]. Syntax example: 2011-11-09");
		}
	}
	if (defined($paramEndDate)){
		if ($paramEndDate =~ /^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/) {
			$end = mktime(0,0,0,$3,$2 - 1,$1 - 1900,0,0,-1);
			$purgeType = "delete";
		}else {
			$logger->writeLog("ERROR", "Bad paramater syntax for option [-e|--period-end]. Syntax example: 2011-11-09");
		}
	}
	
    my ($dbStart, $dbEnd) = $hostEvents->getFirstLastIncidentTimes();
    if (!defined($start)) {
    	$start = $dbStart;
    }
    if (!defined($end)) {
    	$end = $dbEnd;
    }
    return ($start, $end, $purgeType, $hostOnly, $serviceOnly);
}

# MAIN function
sub main {
	
    initVars();
    my %options;
    Getopt::Long::Configure('bundling');
    GetOptions ("h|help" => \$options{"help"}, 
		"l|use-lock" => \$options{"lock"},
		"r|rebuild" => \$options{"rebuild"},
		"e|period-end=s" => \$options{"end"},
		"s|period-start=s" => \$options{"start"},
		"host-only" => \$options{"host-only"},
		"service-only" => \$options{"service-only"},
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
		rebuildIncidents(getRebuildOptions($options{"start"}, $options{"end"},$options{"host-only"}, $options{"service-only"}));
    }else {
    	my $currentTime = time;
		my ($day,$month,$year, $dayOfWeek) = (localtime($currentTime))[3,4,5,6];
		# getting day of week of date to process 
		if ($dayOfWeek == 0) {
			$dayOfWeek = 6;
		}else {
			$dayOfWeek--;
		}
		# If in the configuration, this day of week is not selected, the reporting is not calculated
		if (defined($liveService->{"report_".$weekDays[$dayOfWeek]}) && $liveService->{"report_".$weekDays[$dayOfWeek]} != 1) {
			$logger->writeLog("INFO", " Reporting must not be calculated for this day, check your configuration\n");
			exit_pgr;
		}
		# setting reporting date and time ranges
		my $end = mktime(0,$liveService->{"report_minute_end"},$liveService->{"report_hour_end"},$day - 1,$month,$year,0,0,-1);
		my $start = mktime(0,$liveService->{"report_minute_start"},$liveService->{"report_hour_start"},$day - 1,$month,$year,0,0,-1);
		
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
