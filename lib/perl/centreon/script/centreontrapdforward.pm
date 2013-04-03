
package centreon::script::centreontrapdforward;

use strict;
use warnings;
use Time::HiRes qw(gettimeofday);
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreontrapdforward",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noconfig => 1
    );
    bless $self, $class;
    $self->add_options(
        "config-extra" => \$self->{opt_extra},
    );
    my %centreontrapd_default_config =
      (
       spool_directory => "/var/spool/centreontrapd/"
      )
    );
    return $self;
}

sub init {
    my $self = shift;
    
    if (!defined($self->{opt_extra})) {
        $self->{opt_extra} = "/etc/centreon/centreontrapd.pm";
    }
    if (-f $self->{opt_extra}) {
        require $self->{opt_extra};
    } else {
        $self->{logger}->writeLogInfo("Can't find extra config file $self->{opt_extra}");
    }

    $self->{centreontrapd_config} = {%centreontrapd_default_config, %centreontrapd_config};
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->init();

    # Create file in spool directory based on current time
    my ($s, $usec) = gettimeofday;

    # Pad the numbers with 0's to make sure they are all the same length.  Sometimes the
    # usec is shorter than 6.
    my $s_pad = sprintf("%09d",$s);
    my $usec_pad = sprintf("%06d",$usec);

    # Print out time
    $self->{logger}->writeLogDebug("centreon-trapforward started: " . scalar(localtime));
    $self->{logger}->writeLogDebug("s = $s, usec = $usec");
    $self->{logger}->writeLogDebug("s_pad = $s_pad, usec_pad = $usec_pad");
    $self->{logger}->writeLogDebug("Data received:");

    my $spoolfile = $self->{centreontrapd_config}->{spool_directory} . '#centreon-trap-'.$s_pad.$usec_pad;

    unless (open SPOOL, ">$spoolfile") {
        $self->{logger}->writeLogError("Could not write to file file $spoolfile!  Trap will be lost!");
        exit(1);
    }

    print SPOOL time()."\n";

    while (defined(my $line = <>)) {
        print SPOOL $line;
	
        if ($self->{logger}->is_debug()) {
            # Print out item passed from snmptrapd
            chomp $line;
            $self->{logger}->writeLogDebug($line);
        }
    }

    exit(0);
}

1;