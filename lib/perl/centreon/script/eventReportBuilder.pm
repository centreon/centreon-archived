
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
    
	$self->{logger}->writeLogInfo("INFO", "Exiting program...");
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
	$self->{host} = CentreonHost->new($self->{logger}, $self->{cdb});
	$self->{service} = CentreonService->new($self->{logger}, $self->{cdb});
	$self->{nagiosLog} = CentreonLog->new($self->{logger}, $self->{csdb}, $self->{dbLayer});
	my $centreonDownTime = CentreonDownTime->new($self->{logger}, $centstatus, $self->{dbLayer});
	my $centreonAck = CentreonAck->new($self->{logger}, $centstatus, $self->{dbLayer});
	$self->{serviceEvents} = CentreonServiceStateEvents->new($self->{logger}, $self->{csdb}, $centreonAck, $centreonDownTime);
	$self->{hostEvents} = CentreonHostStateEvents->new($self->{logger}, $self->{csdb}, $centreonAck, $centreonDownTime);
	
	# Class that builds events
	$self->{processEvents} = CentreonProcessStateEvents->new($self->{logger}, $self->{host}, $self->{service}, $self->{nagiosLog}, $self->{hostEvents}, $self->{serviceEvents}, $centreonDownTime, $self->{dbLayer});
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
    }else {
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
