
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
    
    if (!defined($options{output})) {
        print "Class SNMP: Need to specify 'output' argument.\n";
        exit 3;
    }
    if (!defined($options{options})) {
        $options{output}->add_option_msg(short_msg => "Class Mysqlcmd: Need to specify 'options' argument.");
        $options{output}->option_exit();
    }
    $options{options}->add_options(arguments => 
                { "mysql-cmd:s"              => { name => 'mysql_cmd', default => '/usr/bin/mysql' },
                  "host:s"                   => { name => 'host' },
                  "port:s"                   => { name => 'port' },
                  "username:s"               => { name => 'username' },
                  "password:s"               => { name => 'password' },
                  "sql-errors-exit:s"        => { name => 'sql-errors-exit', default => 'unknown' },
    });
    $options{options}->add_help(package => __PACKAGE__, sections => 'MYSQLCMD OPTIONS');

    $self->{output} = $options{output};
    $self->{mode} = $options{mode};
    $self->{args} = undef;
    $self->{stdout} = undef;
    $self->{version} = undef;
    
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    # options{default} = { 'mode_name' => { option_name => opt_value } }

    %{$self->{option_results}} = %{$options{option_results}};
    # Manage default value
    return if (!defined($options{default}));
    foreach (keys %{$options{default}}) {
        if ($_ eq $self->{mode}) {
            foreach my $value (keys %{$options{default}->{$_}}) {
                if (!defined($self->{option_results}->{$value})) {
                    $self->{option_results}->{$value} = $options{default}->{$_}->{$value};
                }
            }
        }
    }

    if (!defined($self->{option_results}->{host}) || $self->{option_results}->{host} eq '') {
        $self->{output}->add_option_msg(short_msg => "Need to specify host argument.");
        $self->{output}->option_exit(exit_litteral => $self->{option_results}->{sql_errors_exit});
    }
    
    $self->{args} = ['--batch', '--raw', '--skip-column-names', '--host', $self->{option_results}->{host}];
    if (defined($self->{option_results}->{port})) {
        push @{$self->{args}}, "--port", $self->{option_results}->{port};
    }
    if (defined($self->{option_results}->{username})) {
        push @{$self->{args}}, "--user", $self->{option_results}->{username};
    }
    if (defined($self->{option_results}->{password}) && $self->{option_results}->{password} ne '') {
        push @{$self->{args}}, "-p" . $self->{option_results}->{password};
    }
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

    my $msg = $self->{option_results}->{host};
    if (defined($self->{option_results}->{port})) {
        $msg .= ":" . $self->{option_results}->{port};
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
                                                 command => $self->{option_results}->{mysql_cmd},
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
            $self->{output}->option_exit(exit_litteral => $self->{option_results}->{sql_errors_exit});
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
        $self->{output}->option_exit(exit_litteral => $self->{option_results}->{sql_errors_exit});
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
