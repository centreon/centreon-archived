
package database::mysql::mysqlcmd;

use strict;
use warnings;
use centreon::plugins::misc;
use Digest::MD5 qw(md5_hex);

sub new {
    my ($class, %options) = @_;
    my $self  = {};
    bless $self, $class;
    # $options{options} = options object
    # $options{output} = output object
    # $options{exit_value} = integer
    # $options{noptions} = integer
    
    if (!defined($options{output})) {
        print "Class mysqlcmd: Need to specify 'output' argument.\n";
        exit 3;
    }
    if (!defined($options{options})) {
        $options{output}->add_option_msg(short_msg => "Class Mysqlcmd: Need to specify 'options' argument.");
        $options{output}->option_exit();
    }
    if (!defined($options{noptions})) {
        $options{options}->add_options(arguments => 
                    { "mysql-cmd:s"              => { name => 'mysql_cmd', default => '/usr/bin/mysql' },
                      "host:s@"                  => { name => 'host' },
                      "port:s@"                  => { name => 'port' },
                      "username:s@"              => { name => 'username' },
                      "password:s@"              => { name => 'password' },
                      "sql-errors-exit:s"        => { name => 'sql_errors_exit', default => 'unknown' },
        });
    }
    $options{options}->add_help(package => __PACKAGE__, sections => 'MYSQLCMD OPTIONS', once => 1);

    $self->{output} = $options{output};
    $self->{mode} = $options{mode};
    $self->{args} = undef;
    $self->{stdout} = undef;
    $self->{version} = undef;
    
    $self->{host} = undef;
    $self->{port} = undef;
    $self->{username} = undef;
    $self->{password} = undef;
    
    return $self;
}

# Method to manage multiples
sub set_options {
    my ($self, %options) = @_;
    # options{options_result}

    $self->{option_results} = $options{option_results};
}

# Method to manage multiples
sub set_defaults {
    my ($self, %options) = @_;
    # options{default}
    
    # Manage default value
    foreach (keys %{$options{default}}) {
        if ($_ eq $self->{mode}) {
            for (my $i = 0; $i < scalar(@{$options{default}->{$_}}); $i++) {
                foreach my $opt (keys %{$options{default}->{$_}[$i]}) {
                    if (!defined($self->{option_results}->{$opt}[$i])) {
                        $self->{option_results}->{$opt}[$i] = $options{default}->{$_}[$i]->{$opt};
                    }
                }
            }
        }
    }
}

sub check_options {
    my ($self, %options) = @_;
    # return 1 = ok still data_source
    # return 0 = no data_source left
    
    $self->{host} = (defined($self->{option_results}->{host})) ? shift(@{$self->{option_results}->{host}}) : undef;
    $self->{port} = (defined($self->{option_results}->{port})) ? shift(@{$self->{option_results}->{port}}) : undef;
    $self->{username} = (defined($self->{option_results}->{username})) ? shift(@{$self->{option_results}->{username}}) : undef;
    $self->{password} = (defined($self->{option_results}->{password})) ? shift(@{$self->{option_results}->{password}}) : undef;
    $self->{sql_errors_exit} = $self->{option_results}->{sql_errors_exit};
    $self->{mysql_cmd} = $self->{option_results}->{mysql_cmd};
 
    if (!defined($self->{host}) || $self->{host} eq '') {
        $self->{output}->add_option_msg(short_msg => "Need to specify host argument.");
        $self->{output}->option_exit(exit_litteral => $self->{sql_errors_exit});
    }
    
    $self->{args} = ['--batch', '--raw', '--skip-column-names', '--host', $self->{host}];
    if (defined($self->{port})) {
        push @{$self->{args}}, "--port", $self->{port};
    }
    if (defined($self->{username})) {
        push @{$self->{args}}, "--user", $self->{username};
    }
    if (defined($self->{password}) && $self->{password} ne '') {
        push @{$self->{args}}, "-p" . $self->{password};
    }

    if (scalar(@{$self->{option_results}->{host}}) == 0) {
        return 0;
    }
    return 1;
}

sub is_version_minimum {
    my ($self, %options) = @_;
    # $options{version} = string version to check
    
    my @version_src = split /\./, $self->{version};
    my @versions = split /\./, $options{version};
    for (my $i = 0; $i < scalar(@versions); $i++) {
        return 1 if ($versions[$i] eq 'x');
        return 1 if (!defined($version_src[$i]));
        $version_src[$i] =~ /^([0-9]*)/;
        next if ($versions[$i] == int($1));
        return 0 if ($versions[$i] > int($1));
        return 1 if ($versions[$i] < int($1));
    }
    
    return 1;
}

sub get_unique_id4save {
    my ($self, %options) = @_;

    my $msg = $self->{host};
    if (defined($self->{port})) {
        $msg .= ":" . $self->{port};
    }
    return md5_hex($msg);
}

sub quote {
    my $self = shift;

    return undef;
}

sub command_execution {
    my ($self, %options) = @_;
    
    my ($lerror, $stdout, $exit_code) = centreon::plugins::misc::backtick(
                                                 command => $self->{mysql_cmd},
                                                 arguments =>  [@{$self->{args}}, '-e', $options{request}],
                                                 timeout => 30,
                                                 wait_exit => 1,
                                                 redirect_stderr => 1
                                                 );
    if ($exit_code <= -1000) {
        if ($exit_code == -1000) {
            $self->{output}->output_add(severity => 'UNKNOWN', 
                                        short_msg => $stdout);
        }
        $self->{output}->display();
        $self->{output}->exit();
    }
    
    return ($exit_code, $stdout); 
}

# Connection initializer
sub connect {
    my ($self, %options) = @_;
    my $dontquit = (defined($options{dontquit}) && $options{dontquit} == 1) ? 1 : 0;

    my ($exit_code, $stdout) = $self->command_execution(request => "SHOW VARIABLES LIKE 'version'");
    if ($exit_code != 0) {
        if ($dontquit == 0) {
            $self->{output}->add_option_msg(short_msg => "Cannot connect: " . $stdout);
            $self->{output}->option_exit(exit_litteral => $self->{sql_errors_exit});
        }
        return (-1, "Cannot connect: " . $stdout);
    }
    
    (my $name, $self->{version}) = split(/\t/, $stdout);

    return 0;
}

sub fetchall_arrayref {
    my ($self, %options) = @_;
    my $array_ref = [];
    
    foreach (split /\n/, $self->{stdout}) {
        push @$array_ref, [map({ s/\\n/\x{0a}/g; s/\\t/\x{09}/g; s/\\/\x{5c}/g; $_; } split(/\t/, $_))];
    }
    
    return $array_ref;
}

sub fetchrow_array {
    my ($self, %options) = @_;
    my @array_result = ();
    
    if (($self->{stdout} =~ s/^(.*?)(\n|$)//)) {
        push @array_result, map({ s/\\n/\x{0a}/g; s/\\t/\x{09}/g; s/\\/\x{5c}/g; $_; } split(/\t/, $1));
    }
    
    return @array_result;
}

sub query {
    my ($self, %options) = @_;
    
    (my $exit_code, $self->{stdout}) = $self->command_execution(request => $options{query});
    
    if ($exit_code != 0) {
        $self->{output}->add_option_msg(short_msg => "Cannot execute query: " . $self->{stdout});
        $self->{output}->option_exit(exit_litteral => $self->{sql_errors_exit});
    }

}

1;

__END__

=head1 NAME

mysqlcmd global

=head1 SYNOPSIS

mysqlcmd class

=head1 MYSQLCMD OPTIONS

=over 8

=item B<--mysql-cmd>

mysql command (Default: '/usr/bin/mysql').

=item B<--host>

Database hostname.

=item B<--port>

Database port.

=item B<--username>

Database username.

=item B<--password>

Database password.

=item B<--sql-errors-exit>

Exit code for DB Errors (default: unknown)

=back

=head1 DESCRIPTION

B<snmp>.

=cut
