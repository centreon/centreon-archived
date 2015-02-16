################################################################################
# Copyright 2005-2013 CENTREON
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
# As a special exception, the copyright holders of this program give CENTREON 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of CENTREON choice, provided that 
# CENTREON also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::script::eventReportBuilder;

use warnings;
use strict;
use POSIX;
use Time::Local;

use centreon::reporting::CentreonHost;
use centreon::reporting::CentreonService;
use centreon::reporting::CentreonLog;
use centreon::reporting::CentreonServiceStateEvents;
use centreon::reporting::CentreonHostStateEvents;
use centreon::reporting::CentreonProcessStateEvents;
use centreon::reporting::CentreonDownTime;
use centreon::reporting::CentreonAck;
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("eventReportBuilder",
        centreon_db_conn => 1,
        centstorage_db_conn => 1
    );

    bless $self, $class;
    $self->add_options(
        "r" => \$self->{opt_rebuild}, "rebuild" => \$self->{opt_rebuild},
    );
    $self->{centstatusdb} = undef;
    $self->{processEvents} = undef;
    $self->{serviceEvents} = undef;
    $self->{hostEvents} = undef;
    $self->{nagiosLog} = undef;
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

# get db layer
sub getDbLayer() {
    my $self = shift;

    my ($status, $res) = $self->{cdb}->query("SELECT `value` FROM `options` WHERE `key` = 'broker'");
    if (my $row = $res->fetchrow_hashref()) { 
        return $row->{'value'};
    }
    return "ndo";
}

# Initialize objects for program
sub initVars {
    my $self = shift;
    my $centstatus;
    
    # Getting centstatus database name
    $self->{dbLayer} = $self->getDbLayer();
    if ($self->{dbLayer} eq "ndo") {
        my ($status, $sth) = $self->{cdb}->query("SELECT db_name, db_host, db_port, db_user, db_pass FROM cfg_ndo2db WHERE activate = '1' LIMIT 1");
        if (my $row = $sth->fetchrow_hashref()) {
            #connecting to censtatus
            $centstatus = centreon::common::db->new(db => $row->{"db_name"},
                                                     host => $row->{'db_host'},
                                                     port => $row->{'db_port'},
                                                     user => $row->{'db_user'},
                                                     password => $row->{'db_pass'},
                                                     force => 0,
                                                     logger => $self->{logger});
        }
    } elsif ($self->{dbLayer} eq "broker") {
        $centstatus = $self->{csdb};
    } else {
        $self->{logger}->writeLogError("Unsupported database layer: " . $self->{dbLayer});
        $self->exit_pgr();
    }
    
    # classes to query database tables 
    $self->{host} = centreon::reporting::CentreonHost->new($self->{logger}, $self->{cdb});
    $self->{service} = centreon::reporting::CentreonService->new($self->{logger}, $self->{cdb});
    $self->{nagiosLog} = centreon::reporting::CentreonLog->new($self->{logger}, $self->{csdb}, $self->{dbLayer});
    my $centreonDownTime = centreon::reporting::CentreonDownTime->new($self->{logger}, $centstatus, $self->{dbLayer});
    my $centreonAck = centreon::reporting::CentreonAck->new($self->{logger}, $centstatus, $self->{dbLayer});
    $self->{serviceEvents} = centreon::reporting::CentreonServiceStateEvents->new($self->{logger}, $self->{csdb}, $centreonAck, $centreonDownTime);
    $self->{hostEvents} = centreon::reporting::CentreonHostStateEvents->new($self->{logger}, $self->{csdb}, $centreonAck, $centreonDownTime);
    
    # Class that builds events
    $self->{processEvents} = centreon::reporting::CentreonProcessStateEvents->new($self->{logger}, $self->{host}, $self->{service}, $self->{nagiosLog}, $self->{hostEvents}, $self->{serviceEvents}, $centreonDownTime, $self->{dbLayer});
}

# For a given period returns in a table each    
sub getDaysFromPeriod {
    my $self = shift;
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
    my $self = shift;
    my $time_period = shift;

    # Empty tables
    $self->{serviceEvents}->truncateStateEvents();
    $self->{hostEvents}->truncateStateEvents();
    # Getting first log and last log times
    my ($start, $end) = $self->{nagiosLog}->getFirstLastLogTime();
       my $periods = $self->getDaysFromPeriod($start, $end);
    # archiving logs for each days
    foreach(@$periods) {
        $self->{logger}->writeLogInfo("Processing period: ".localtime($_->{"day_start"})." => ".localtime($_->{"day_end"}));
        $self->{processEvents}->parseHostLog($_->{"day_start"}, $_->{"day_end"});
        $self->{processEvents}->parseServiceLog($_->{"day_start"}, $_->{"day_end"});
    }
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->{logger}->redirect_output();
    $self->initVars();
    
    $self->{logger}->writeLogInfo("Starting program...");
    
    if (defined($self->{opt_rebuild})) {
        $self->rebuildIncidents();
    } else {
        my $currentTime = time;
        my ($day,$month,$year) = (localtime($currentTime))[3,4,5];
        my $end = mktime(0,0,0,$day,$month,$year,0,0,-1);
        my $start = mktime(0,0,0,$day-1,$month,$year,0,0,-1);
        $self->{logger}->writeLogInfo("Processing period: ".localtime($start)." => ".localtime($end));
        $self->{processEvents}->parseHostLog($start, $end);
        $self->{processEvents}->parseServiceLog($start, $end);
    }

    $self->exit_pgr();
}

1;
