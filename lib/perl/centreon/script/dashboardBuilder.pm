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

package centreon::script::dashboardBuilder;

use warnings;
use strict;
use POSIX;
use Time::Local;

use centreon::reporting::CentreonHost;
use centreon::reporting::CentreonService;
use centreon::reporting::CentreonServiceStateEvents;
use centreon::reporting::CentreonHostStateEvents;
use centreon::reporting::CentreonDashboard;
use centreon::script;

use base qw(centreon::script);

my @weekDays = ("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("dashboardBuilder",
        centreon_db_conn => 1,
        centstorage_db_conn => 1
    );

    bless $self, $class;
    $self->add_options(
        "r" => \$self->{opt_rebuild}, "rebuild" => \$self->{opt_rebuild},
        "s=s" => \$self->{opt_startperiod}, "start-period=s" => \$self->{opt_startperiod},
        "e=s" => \$self->{opt_endperiod}, "end-period=s" => \$self->{opt_endperiod},
        "host-only" => \$self->{opt_hostonly},
        "service-only" => \$self->{opt_serviceonly},
    );
    $self->{serviceEvents} = undef;
    $self->{hostEvents} = undef;
    $self->{dashboard} = undef;
    $self->{liveService} = undef;
    $self->{service} = undef;
    $self->{host} = undef;
    $self->{dbLayer} = undef;
    return $self;
}

# program exit function
sub exit_pgr() {
    my $self = shift;
    
    $self->{logger}->writeLogInfo("Exiting program...");
    exit (0);
}

# get informations about an  hidden feature
# reporting can be calculated on a specific time range for each day of the week
sub getLiveService {
    my $self = shift;
    my %result = ();

    my ($status, $sth) = $self->{cdb}->query("SELECT * FROM `contact_param` WHERE `cp_contact_id` is null");
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
    my $self = shift;
    
    # classes to query database tables 
    $self->{host} = centreon::reporting::CentreonHost->new($self->{logger}, $self->{cdb});
    $self->{service} = centreon::reporting::CentreonService->new($self->{logger}, $self->{cdb});
    $self->{serviceEvents} = centreon::reporting::CentreonServiceStateEvents->new($self->{logger}, $self->{csdb});
    $self->{hostEvents} = centreon::reporting::CentreonHostStateEvents->new($self->{logger}, $self->{csdb});
    
    # Class that builds events
    $self->{dashboard} = centreon::reporting::CentreonDashboard->new($self->{logger}, $self->{csdb});
    $self->{liveService} = $self->getLiveService();
}

# For a given period returns in a table each
sub getDaysFromPeriod {
    my $self = shift;
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
        if (defined($self->{liveService}->{"report_".$weekDays[$currentDayOfWeek]}) && $self->{liveService}->{"report_".$weekDays[$currentDayOfWeek]} == 1) {
            # setting reporting date and time ranges
            my $dayStart = mktime(0,$self->{liveService}->{"report_minute_start"},$self->{liveService}->{"report_hour_start"},$day,$month,$year,0,0,-1);
            my $dayEnd = mktime(0,$self->{liveService}->{"report_minute_end"},$self->{liveService}->{"report_hour_end"},$day,$month,$year,0,0,-1);
            my %period = ("day_start" => $dayStart, "day_end" => $dayEnd);
            $days[scalar(@days)] = \%period;
        } else {
            $day++;
        }
        
        $previousDay = $start;
        $start = mktime(0,0,0, $day, $month, $year,0,0,-1);
    }
    return \@days;
}

# rebuild all events
sub rebuildIncidents {
    my $self = shift;
    my ($start, $end, $purgeType, $hostOnly, $serviceOnly) = (shift, shift, shift, shift, shift);

    if (!defined($start) || !defined($end)) {
        $self->{logger}->writeLogError("Cannot determine reporting rebuild period");
        $self->{logger}->writeLogError("Please use -s and -e option to defined the rebuild period.");
    } else {
	
        # Purge tables in order to rebuild statistics
        my $periods = $self->getDaysFromPeriod($start, $end);
        if (!scalar(@$periods)) {
            $self->{logger}->writeLogInfo("Incorrect rebuild period");
        }
        if ($purgeType eq "truncate") {
            $self->{dashboard}->truncateServiceStats();
            $self->{dashboard}->truncateHostStats();
        } else {
            $self->{dashboard}->deleteServiceStats($start, $end);
            $self->{dashboard}->deleteHostStats($start, $end);
        }
	
        if (defined($start) && defined($end) && !$serviceOnly) {
            my ($allIds, $allNames) = $self->{host}->getAllHosts(0);
	    
            # archiving logs for each days
            foreach(@$periods) {
                $self->{logger}->writeLogInfo("[HOST] Processing period: ".localtime($_->{"day_start"})." => ".localtime($_->{"day_end"}));
                my $hostStateDurations = $self->{hostEvents}->getStateEventDurations($_->{"day_start"}, $_->{"day_end"});
                $self->{dashboard}->insertHostStats($allNames, $hostStateDurations, $_->{"day_start"}, $_->{"day_end"});
            }
        }
        if (defined($start) && defined($end) && !$hostOnly) {
            my ($allIds, $allNames) = $self->{service}->getAllServices(0);
	    
            # archiving logs for each days
            foreach(@$periods) {
                $self->{logger}->writeLogInfo("[SERVICE] Processing period: ".localtime($_->{"day_start"})." => ".localtime($_->{"day_end"}));
                my $serviceStateDurations = $self->{serviceEvents}->getStateEventDurations($_->{"day_start"}, $_->{"day_end"});
                $self->{dashboard}->insertServiceStats($allNames, $serviceStateDurations, $_->{"day_start"}, $_->{"day_end"});
            }
        }
    }
}

# returns the reporting rebuild period that could be retrieved form DB or given in parameter
# returns also reporting tables purge type truncate or delete for a specific period
sub getRebuildOptions {
    my $self = shift;
    my ($paramStartDate, $paramEndDate, $hostOnly, $serviceOnly) = (shift, shift, shift, shift);
    
    if (!defined($hostOnly)) {
        $hostOnly = 0;
    } else {
        $hostOnly = 1;
    }

    if (!defined($serviceOnly)) {
        $serviceOnly = 0;
    } else {
        $serviceOnly = 1;
    }

    my ($start, $end);
    my $purgeType = "truncate";
    if (defined($paramStartDate)){  
        if ($paramStartDate =~ /^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/) {
            $start = mktime(0,0,0,$3,$2 - 1,$1 - 1900,0,0,-1);
            $purgeType = "delete";
        } else {
            $self->{logger}->writeLogError("Bad paramater syntax for option [-s|--period-start]. Syntax example: 2011-11-09");
        }
    }
    if (defined($paramEndDate)){
        if ($paramEndDate =~ /^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/) {
            $end = mktime(0,0,0,$3,$2 - 1,$1 - 1900,0,0,-1);
            $purgeType = "delete";
        }else {
            $self->{logger}->writeLogError("Bad paramater syntax for option [-e|--period-end]. Syntax example: 2011-11-09");
        }
    }
    
    my ($dbStart, $dbEnd) = $self->{hostEvents}->getFirstLastIncidentTimes();
    if (!defined($start)) {
        $start = $dbStart;
    }
    if (!defined($end)) {
        $end = $dbEnd;
    }
    return ($start, $end, $purgeType, $hostOnly, $serviceOnly);
}


sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->{logger}->redirect_output();
    $self->initVars();

    $self->{logger}->writeLogInfo("Starting program...");
    
    if (defined($self->{opt_rebuild})) {
        $self->rebuildIncidents($self->getRebuildOptions($self->{opt_startperiod}, $self->{opt_endperiod}, $self->{opt_hostonly}, $self->{opt_serviceonly}));
    } else {
        my $currentTime = time;
        my ($day,$month,$year, $dayOfWeek) = (localtime($currentTime))[3,4,5,6];
      
	# getting day of week of date to process 
        if ($dayOfWeek == 0) {
            $dayOfWeek = 6;
        } else {
            $dayOfWeek--;
        }
	
        # If in the configuration, this day of week is not selected, the reporting is not calculated
        if (defined($self->{liveService}->{"report_".$weekDays[$dayOfWeek]}) && $self->{liveService}->{"report_".$weekDays[$dayOfWeek]} != 1) {
            $self->{logger}->writeLogInfo("Reporting must not be calculated for this day, check your configuration");
            $self->exit_pgr();
        }

        # setting reporting date and time ranges
        my $end = mktime(0,$self->{liveService}->{"report_minute_end"},$self->{liveService}->{"report_hour_end"},$day - 1,$month,$year,0,0,-1);
        my $start = mktime(0,$self->{liveService}->{"report_minute_start"},$self->{liveService}->{"report_hour_start"},$day - 1,$month,$year,0,0,-1);
        
        my ($serviceIds, $serviceNames) = $self->{service}->getAllServices(0);
        my ($hostIds, $hostNames) = $self->{host}->getAllHosts(0);
        $self->{logger}->writeLogInfo("Processing period: ".localtime($start)." => ".localtime($end));
        my $hostStateDurations = $self->{hostEvents}->getStateEventDurations($start, $end);
        $self->{dashboard}->insertHostStats($hostNames, $hostStateDurations, $start, $end);
        my $serviceStateDurations = $self->{serviceEvents}->getStateEventDurations($start, $end);
        $self->{dashboard}->insertServiceStats($serviceNames, $serviceStateDurations, $start, $end);
    }
    $self->exit_pgr();
}

1;
