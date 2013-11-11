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
# For more information : contact@centreon.com
# Authors : Quentin Garnier <qgarnier@merethis.com>
#
####################################################################################

package centreon::plugins::misc;

use strict;
use warnings;

sub mymodule_load {
    my (%options) = @_;
    my $file;
    ($file = $options{module} . ".pm") =~ s{::}{/}g;
     
    eval {
        local $SIG{__DIE__} = 'IGNORE';
        require $file;
    };
    if ($@) {
        $options{output}->add_option_msg(long_msg => $@);
        $options{output}->add_option_msg(short_msg => $options{error_msg});
        $options{output}->option_exit();
    }
}

sub backtick {
    my %arg = (
        command => undef,
        arguments => [],
        timeout => 30,
        wait_exit => 0,
        redirect_stderr => 0,
        @_,
    );
    my @output;
    my $pid;
    my $return_code;
    
    my $sig_do;
    if ($arg{wait_exit} == 0) {
        $sig_do = 'IGNORE';
        $return_code = undef;
    } else {
        $sig_do = 'DEFAULT';
    }
    local $SIG{CHLD} = $sig_do;

    if (!defined($pid = open( KID, "-|" ))) {
        return (-1001, "Cant fork: $!", -1);
    }

    if ($pid) {
        
        eval {
           local $SIG{ALRM} = sub { die "Timeout by signal ALARM\n"; };
           alarm( $arg{timeout} );
           while (<KID>) {
               chomp;
               push @output, $_;
           }

           alarm(0);
        };

        if ($@) {
            if ($pid != -1) {
                kill -9, $pid;
            }

            alarm(0);
            return (-1000, "Command too long to execute (timeout)...", -1);
        } else {
            if ($arg{wait_exit} == 1) {
                # We're waiting the exit code                
                waitpid($pid, 0);
                $return_code = ($? >> 8);
            }
            close KID;
        }
    } else {
        # child
        # set the child process to be a group leader, so that
        # kill -9 will kill it and all its descendents
        setpgrp( 0, 0 );

        if ($arg{redirect_stderr} == 1) {
            open STDERR, ">&STDOUT";
        }
        open STDERR, ">&STDOUT";
        if (scalar(@{$arg{arguments}}) <= 0) {
            exec($arg{command});
        } else {
            exec($arg{command}, @{$arg{arguments}});
        }
        exit(0);
    }

    return (0, join("\n", @output), $return_code);
}

sub trim {
    # Sometimes there is a null character
    $_[0] =~ s/\x00$//;
    $_[0] =~ s/^[ \t]+//;
    $_[0] =~ s/[ \t]+$//;
    return $_[0];
}

1;

__END__

