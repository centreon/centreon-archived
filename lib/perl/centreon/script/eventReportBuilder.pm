
use warnings;
use strict;
use POSIX;
use Time::Local;

use reporting::CentreonHost;
use reporting::CentreonService;
use reporting::CentreonLog;
use reporting::CentreonServiceStateEvents;
use reporting::CentreonHostStateEvents;
use reporting::CentreonProcessStateEvents;
use reporting::CentreonDownTime;
use reporting::CentreonAck;
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

	my $res = $self->{cdb}->query("SELECT `value` FROM `options` WHERE `key` = 'broker'");
	if (my $row = $res->fetchrow_hashref()) { 
		return $row->{'value'};
	}
	return "ndo";
}

# function that checks if the log is already built
sub dayAlreadyProcessed($$$$) {
    my $self = shift;
    my ($day, $month, $year) = (shift, shift, shift);

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
	my $self = shift;
	
	# Getting centstatus database name
	$self->{dbLayer} = getDbLayer();
	if ($self->{dbLayer} eq "ndo") {
		my $sth = $centreon->query("SELECT db_name, db_host, db_port, db_user, db_pass FROM cfg_ndo2db WHERE activate = '1' LIMIT 1");
		if (my $row = $sth->fetchrow_hashref()) {
			#connecting to censtatus
			$centstatus = CentreonDB->new($logger, $row->{"db_name"}, $row->{'db_host'}, $row->{'db_user'}, $row->{'db_pass'}, $row->{'db_port'});
		}
	} elsif ($self->{dbLayer} eq "broker") {
		$centstatus = $centstorage;
	} else {
		$self->{logger}->writeLogError("Unsupported database layer: " . $dbLayer);
		$self->exit_pgr();
	}
	
	# classes to query database tables 
	$host = CentreonHost->new($logger, $centreon);
	$service = CentreonService->new($logger, $centreon);
	$nagiosLog = CentreonLog->new($logger, $centstorage, $dbLayer);
	my $centreonDownTime = CentreonDownTime->new($logger, $centstatus, $dbLayer);
	my $centreonAck = CentreonAck->new($logger, $centstatus, $dbLayer);
	$serviceEvents = CentreonServiceStateEvents->new($logger, $centstorage, $centreonAck, $centreonDownTime);
	$hostEvents = CentreonHostStateEvents->new($logger, $centstorage, $centreonAck, $centreonDownTime);
	
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
    	$self->{logger}->writeLogInfo("Processing period: ".localtime($_->{"day_start"})." => ".localtime($_->{"day_end"}));
		$processEvents->parseHostLog($_->{"day_start"}, $_->{"day_end"});
		$processEvents->parseServiceLog($_->{"day_start"}, $_->{"day_end"});
    }
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->{logger}->redirect_output();
    $self->initVars();
    
    $self->{logger}->writeLogInfo("Starting program...");
    
    if (defined($options{'rebuild'})) {
		$self->rebuildIncidents();
    }else {
    	my $currentTime = time;
		my ($day,$month,$year) = (localtime($currentTime))[3,4,5];
		my $end = mktime(0,0,0,$day,$month,$year,0,0,-1);
		my $start = mktime(0,0,0,$day-1,$month,$year,0,0,-1);
		$self->{logger}->writeLogInfo("Processing period: ".localtime($start)." => ".localtime($end));
		$processEvents->parseHostLog($start, $end);
		$processEvents->parseServiceLog($start, $end);
    }

	$self->exit_pgr();
}

1;
