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

package centreon::common::logger;

=head1 NOM

centreon::common::logger - Simple logging module

=head1 SYNOPSIS

 #!/usr/bin/perl -w

 use strict;
 use warnings;

 use centreon::polling;

 my $logger = new centreon::common::logger();

 $logger->writeLogInfo("information");

=head1 DESCRIPTION

This module offers a simple interface to write log messages to various output:

* standard output
* file
* syslog

=cut

use strict;
use warnings;
use Sys::Syslog qw(:standard :macros);
use IO::Handle;

my %severities = (1 => LOG_INFO,
                  2 => LOG_ERR,
                  4 => LOG_DEBUG);

sub new {
    my $class = shift;

    my $self = bless
      {
       file => 0,
       filehandler => undef,
       # 0 = nothing, 1 = critical, 3 = info, 7 = debug
       severity => 3,
       old_severity => 3,
       # 0 = stdout, 1 = file, 2 = syslog
       log_mode => 0,
       # syslog
       log_facility => undef,
       log_option => LOG_PID,
      }, $class;
    return $self;
}

sub file_mode($$) {
    my ($self, $file) = @_;

    if (defined($self->{filehandler})) {
        $self->{filehandler}->close();
    }
    if (open($self->{filehandler}, ">>", $file)){
        $self->{log_mode} = 1;
        $self->{filehandler}->autoflush(1);
        $self->{file_name} = $file;
        return 1;
    }
    $self->{filehandler} = undef;
    print STDERR "Cannot open file $file: $!\n";
    return 0;
}

sub is_file_mode {
    my $self = shift;
    
    if ($self->{log_mode} == 1) {
        return 1;
    }
    return 0;
}

sub is_debug {
    my $self = shift;
    
    if (($self->{severity} & 4) == 0) {
        return 0;
    }
    return 1;
}

sub syslog_mode($$$) {
    my ($self, $logopt, $facility) = @_;

    $self->{log_mode} = 2;
    openlog($0, $logopt, $facility);
    return 1;
}

# For daemons
sub redirect_output {
    my $self = shift;

    if ($self->is_file_mode()) {
        open my $lfh, '>>', $self->{file_name};
        open STDOUT, '>&', $lfh;
        open STDERR, '>&', $lfh;
    }
}

sub set_default_severity {
    my $self = shift;

    $self->{"severity"} = $self->{"old_severity"};
}

# Getter/Setter Log severity
sub severity {
    my $self = shift;
    if (@_) {
        my $save_severity = $self->{"severity"};
        if ($_[0] =~ /^[012347]$/) {
            $self->{"severity"} = $_[0];
        } elsif ($_[0] eq "none") {
            $self->{"severity"} = 0;
        } elsif ($_[0] eq "error") {
            $self->{"severity"} = 1;
        } elsif ($_[0] eq "info") {
            $self->{"severity"} = 3;
        } elsif ($_[0] eq "debug") {
            $self->{"severity"} = 7;
        } else {
            $self->writeLogError("Wrong severity value set.");
            return -1;
        }
        $self->{"old_severity"} = $save_severity;
    }
    return $self->{"severity"};
}

sub get_date {
    my $self = shift;
    my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time());
    return sprintf("%04d-%02d-%02d %02d:%02d:%02d", 
                   $year+1900, $mon+1, $mday, $hour, $min, $sec);
}

sub writeLog($$$%) {
    my ($self, $severity, $msg, %options) = @_;
    my $withdate = (defined $options{withdate}) ? $options{withdate} : 1;
    my $newmsg = ($withdate) 
      ? $self->get_date . " - $msg" : $msg;

    if (($self->{severity} & $severity) == 0) {
        return;
    }
    if ($self->{log_mode} == 0) {
        print "$newmsg\n";
    } elsif ($self->{log_mode} == 1) {
        if (defined $self->{filehandler}) {
            print { $self->{filehandler} } "$newmsg\n";
        }
    } elsif ($self->{log_mode} == 2) {
        syslog($severities{$severity}, $msg);
    }
}

sub writeLogDebug {
    shift->writeLog(4, @_);
}

sub writeLogInfo {
    shift->writeLog(2, @_);
}

sub writeLogError {
    shift->writeLog(1, @_);
}

sub DESTROY {
    my $self = shift;

    if (defined $self->{filehandler}) {
        $self->{filehandler}->close();
    }
}

1;
