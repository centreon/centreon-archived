#! /usr/bin/perl -w

use strict;
use DBI;
use POSIX;
use Getopt::Long;
use Time::Local;

# variables from external packages
use vars qw ($mysql_database_oreon $mysql_database_ods $mysql_database_status $mysql_host $mysql_user $mysql_passwd);
require "/home/msugumaran/centreon/conf/conf.pm";

use vars qw ($PROGNAME $VERSION $varLibCentreon $lock_file %options %serviceStates %hostStates %servicStateIds %hostStateIds);
require "perl-modules/variables.pm";

# Packages used as classes
require "perl-modules/CentreonLogger.pm";
require "perl-modules/CentreonDB.pm";
require "perl-modules/CentreonHost.pm";
require "perl-modules/CentreonService.pm";
require "perl-modules/CentreonLog.pm";
require "perl-modules/CentreonServiceStateEvents.pm";
require "perl-modules/CentreonHostStateEvents.pm";
require "perl-modules/CentreonProcessStateEvents.pm";
require "perl-modules/CentreonDownTime.pm";

# Variables
my $pid= getpgrp(0);
$PROGNAME = "$0";
$VERSION = "1.0";

my ($centreon, $centstorage, $centstatus, $logger, $processEvents, $serviceEvents, $hostEvents, $nagiosLog, $service, $host);

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
	$centstatus = CentreonDB->new($logger, $mysql_database_status, $mysql_host, $mysql_user, $mysql_passwd);
	
	# classes to query database tables 
	$host = CentreonHost->new($logger, $centreon);
	$service = CentreonService->new($logger, $centreon);
	$nagiosLog = CentreonLog->new($logger, $centstorage);
	my $centreonDownTime = CentreonDownTime->new($logger, $centstatus);
	$serviceEvents = CentreonServiceStateEvents->new($logger, $centstorage, $centreonDownTime);
	$hostEvents = CentreonHostStateEvents->new($logger, $centstorage, $centreonDownTime);
	
	# Class that builds events
	$processEvents = CentreonProcessStateEvents->new($logger, $host, $service, $nagiosLog, $hostEvents, $serviceEvents, $centreonDownTime);
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
    
	exit_pgr;
}

main;
