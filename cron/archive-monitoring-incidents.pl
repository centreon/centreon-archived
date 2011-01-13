#! /usr/bin/perl -w

use strict;
use DBI;
use POSIX;
use Getopt::Long;
use Time::Local;

# Include Centreon DB configuration variables
use vars qw ($mysql_database_oreon $mysql_database_ods $mysql_host $mysql_user $mysql_passwd);
use vars qw ($PROGNAME $VERSION $varLibCentreon $lock_file %options %states %state_ids);
require "perl-modules/variables.pm";
require "/home/msugumaran/centreon/conf/conf.pm";
require "perl-modules/CentreonLogger.pm";
require "perl-modules/CentreonDB.pm";
require "perl-modules/CentreonHost.pm";
require "perl-modules/CentreonService.pm";
require "perl-modules/CentreonStateEvents.pm";
require "perl-modules/CentreonLog.pm";

# Variables
my $pid= getpgrp(0);
$PROGNAME = "$0";
$VERSION = "1.0";

my ($centreon, $centstorage, $logger, $nagiosLog, $service, $events);

# program exit function
sub exit_pgr() {
	system("rm -f ".$lock_file);
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

# Initialize objects for program
sub initVars {
	$logger = CentreonLogger->new();
	$logger->stderr(1);
	$centreon = CentreonDB->new($logger, $mysql_database_oreon, $mysql_host, $mysql_user, $mysql_passwd);
	$centstorage = CentreonDB->new($logger, $mysql_database_ods, $mysql_host, $mysql_user, $mysql_passwd);
	$service = CentreonService->new($logger, $centreon);
	$events = CentreonStateEvents->new($logger, $centreon, $centstorage);
	$nagiosLog = CentreonLog->new($logger, $centreon, $centstorage);
}

# Parse services logs for given period
# Parameters:
# $start: period start
# $end: period end 
sub parseServiceLog {
    my ($start ,$end) = (shift,shift);
    
    my ($serviceIds, $serviceNames) = $service->getAllServices();
	my $currentEvents = $events->getLastStateOfServices($start, $serviceNames);
	my $logs = $nagiosLog->getLogOfServices($start, $end);

    while(my $row = $logs->fetchrow_hashref()) {
		my $id  = $row->{'host_name'}.";;".$row->{'service_description'};
		my $eventInfos; # $eventInfos is a reference to a table containing : incident start time | status | state_event_id. The last one is optionnal
		if (defined($serviceIds->{$id})) {
			if (defined($currentEvents->{$id})) {
				my $eventInfos =  $currentEvents->{$id};
				if ($eventInfos->[1] ne $row->{'status'}) {
						if (defined($eventInfos->[2])) {
							# If eventId of log is defined, update the last day event
							$events->updateServiceEventEndTime($row->{'ctime'}, $eventInfos->[1]);
							$eventInfos->[2] = undef;
						}else {
							my ($host_id, $service_id) = split (";;", $serviceIds->{$id});
							$events->insertServiceEvent($host_id, 
														$service_id,
														$states{$eventInfos->[1]},
														$eventInfos->[0],
														$row->{'ctime'});
						}
				}
			}
			$currentEvents->{$id} = $eventInfos;
		}
	}
	insertLastServiceEvents($end, $currentEvents, $serviceIds);
	
	return ($currentEvents);
}

# Insert in DB last incident of day currently processed
# Parameters:
# $end: period end
# $currentEvents: reference to a hash table that contains last incident details
# $serviceIds: reference to a hash table that returns host/service ids for host/service names
sub insertLastServiceEvents {
	my $end = shift;
	my $currentEvents = shift;
	my $serviceIds = shift;
	
	while(my ($id, $eventInfos) = each (%$currentEvents)) {
		my ($host_name, $service_description) = split ";;", $id;
		if (defined($serviceIds->{$id})) {
			if (defined($eventInfos->[2])) {
				$events->updateServiceEventEndTime($end, $eventInfos->[1]);
				$eventInfos->[2] = undef;
			}else {
				my ($host_id, $service_id) = split (";;", $serviceIds->{$id});
				$events->insertServiceEvent($host_id, $service_id, $states{$eventInfos->[1]}, $eventInfos->[0], $end);
			}
		}
	}
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
    $events->truncateServiceStateEvents();
    # Getting first log and last log times
    my ($start, $end) = $nagiosLog->getFirstLastLogTime();
    print $start." ".$end."\n";
   	my $periods = getDaysFromPeriod($start, $end);
    # archiving logs for each days
    foreach(@$periods) {
		print "rebuilding : ".localtime($_->{"day_start"});
		print " To ".localtime($_->{"day_end"})."\n";
		parseServiceLog($_->{"day_start"}, $_->{"day_end"});
    }
}


# MAIN function
sub main {
	
    initVars();
    
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
    
    if (!defined($options{'rebuild'})) {
		rebuildIncidents();
    }else {
		;
    }
}

main;
exit_pgr;
