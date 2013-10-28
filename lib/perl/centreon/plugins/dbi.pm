
package centreon::plugins::dbi;

use strict;
use warnings;
use DBI;

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
        $options{output}->add_option_msg(short_msg => "Class DBI: Need to specify 'options' argument.");
        $options{output}->option_exit();
    }
    $options{options}->add_options(arguments => 
                { "datasource:s"             => { name => 'data_source' },
                  "username:s"               => { name => 'username' },
                  "password:s"               => { name => 'password' },
                  "sql-errors-exit:s"        => { name => 'sql-errors-exit', default => 'unknown' },
    });
    $options{options}->add_help(package => __PACKAGE__, sections => 'DBI OPTIONS');

    $self->{output} = $options{output};
    $self->{mode} = $options{mode};
    $self->{instance} = undef;
    $self->{statement_handle} = undef;
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

    if (!defined($self->{option_results}->{data_source}) || $self->{option_results}->{data_source} eq '') {
        $self->{output}->add_option_msg(short_msg => "Need to specify database arguments.");
        $self->{output}->option_exit(exit_litteral => $self->{option_results}->{sql_errors_exit});
    }
}

sub quote {
    my $self = shift;

    if (defined($self->{instance})) {
        return $self->{instance}->quote($_[0]);
    }
    return undef;
}

# Connection initializer
sub connect {
    my ($self, %options) = @_;
    my $dontquit = (defined($options{dontquit}) && $options{dontquit} == 1) ? 1 : 0;

    $self->{instance} = DBI->connect(
        "DBI:". $self->{option_results}->{data_source},
        $self->{option_results}->{username},
        $self->{option_results}->{password},
        { "RaiseError" => 0, "PrintError" => 0, "AutoCommit" => 1 }
    );

    if (!defined($self->{instance})) {
        if ($dontquit == 0) {
            $self->{output}->add_option_msg(short_msg => "Cannot connect: " . $DBI::errstr);
            $self->{output}->option_exit(exit_litteral => $self->{option_results}->{sql_errors_exit});
        }
        return (-1, "Cannot connect: " . $DBI::errstr);
    }
    
    $self->{version} = $self->{instance}->get_info(18); # SQL_DBMS_VER
    return 0;
}

sub fetchall_arrayref {
    my ($self, %options) = @_;
    
    return $self->{statement_handle}->fetchall_arrayref();
}

sub query {
    my ($self, %options) = @_;
    
    $self->{statement_handle} = $self->{instance}->prepare($options{query});
    if (!defined($self->{statement_handle})) {
        $self->{output}->add_option_msg(short_msg => "Cannot execute query: " . $self->{instance}->errstr);
        $self->{output}->option_exit(exit_litteral => $self->{option_results}->{sql_errors_exit});
    }

    my $rv = $self->{statement_handle}->execute;
    if (!$rv) {
        $self->{output}->add_option_msg(short_msg => "Cannot execute query: " . $self->{statement_handle}->errstr);
        $self->{output}->option_exit(exit_litteral => $self->{option_results}->{sql_errors_exit});
    }    
}

1;

__END__

=head1 NAME

DBI global

=head1 SYNOPSIS

dbi class

=head1 DBI OPTIONS

=over 8

=item B<--datasource>

Hostname to query (required).

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
