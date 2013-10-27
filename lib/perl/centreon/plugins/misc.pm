package centreon::plugins::misc;

use strict;
use warnings;

sub backtick {
    my %arg = (
        command => undef,
        arguments => [],
        timeout => 30,
        wait_exit => 0,
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

        if (scalar(@{$arg{arguments}}) <= 0) {
            exec($arg{command});
        } else {
            exec($arg{command}, @{$arg{arguments}});
        }
        exit(0);
    }

    return (0, join("\n", @output), $return_code);
}

1;

__END__

