package centreon::logger;

=head1 NOM

centreon::logger - Simple logging module

=head1 SYNOPSIS

 #!/usr/bin/perl -w

 use strict;
 use warnings;

 use centreon::polling;

 my $logger = new centreon::logger();

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

# Getter/Setter Log severity
sub severity {
    my $self = shift;
    if (@_) {
        $self->{"severity"} = $_[0];
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
