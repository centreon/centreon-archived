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

package centreon::script::centreontrapdforward;

use strict;
use warnings;
use Time::HiRes qw(gettimeofday);
use centreon::script;

use base qw(centreon::script);
use vars qw(%centreontrapd_config);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreontrapdforward",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noconfig => 1
    );
    bless $self, $class;
    $self->add_options(
        "config-extra:s" => \$self->{opt_extra},
    );
    %{$self->{centreontrapd_default_config}} =
      (
       spool_directory => "/var/spool/centreontrapd/"
    );
    return $self;
}

sub init {
    my $self = shift;
    $self->SUPER::init();

    if (!defined($self->{opt_extra})) {
        $self->{opt_extra} = "/etc/centreon/centreontrapd.pm";
    }
    if (-f $self->{opt_extra}) {
        require $self->{opt_extra};
    } else {
        $self->{logger}->writeLogInfo("Can't find extra config file $self->{opt_extra}");
    }

    $self->{centreontrapd_config} = {%{$self->{centreontrapd_default_config}}, %centreontrapd_config};
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
