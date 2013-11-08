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

package centreon::plugins::dbi;

use strict;
use warnings;
use DBI;
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
        print "Class DBI: Need to specify 'output' argument.\n";
        exit 3;
    }
    if (!defined($options{options})) {
        $options{output}->add_option_msg(short_msg => "Class DBI: Need to specify 'options' argument.");
        $options{output}->option_exit();
    }
    
    if (!defined($options{noptions})) {
        $options{options}->add_options(arguments => 
                    { "datasource:s@"      => { name => 'data_source' },
                      "username:s@"        => { name => 'username' },
                      "password:s@"        => { name => 'password' },
                      "sql-errors-exit:s"  => { name => 'sql_errors_exit', default => 'unknown' },
        });
    }
    $options{options}->add_help(package => __PACKAGE__, sections => 'DBI OPTIONS', once => 1);

    $self->{output} = $options{output};
    $self->{mode} = $options{mode};
    $self->{instance} = undef;
    $self->{statement_handle} = undef;
    $self->{version} = undef;
    
    $self->{data_source} = undef;
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
    
    $self->{data_source} = (defined($self->{option_results}->{data_source})) ? shift(@{$self->{option_results}->{data_source}}) : undef;
    $self->{username} = (defined($self->{option_results}->{username})) ? shift(@{$self->{option_results}->{username}}) : undef;
    $self->{password} = (defined($self->{option_results}->{password})) ? shift(@{$self->{option_results}->{password}}) : undef;
    $self->{sql_errors_exit} = $self->{option_results}->{sql_errors_exit};
    
    if (!defined($self->{data_source}) || $self->{data_source} eq '') {
        $self->{output}->add_option_msg(short_msg => "Need to specify database arguments.");
        $self->{output}->option_exit(exit_litteral => $self->{sql_errors_exit});
    }
    
    if (scalar(@{$self->{option_results}->{data_source}}) == 0) {
        return 0;
    }
    return 1;
}

sub quote {
    my $self = shift;

    if (defined($self->{instance})) {
        return $self->{instance}->quote($_[0]);
    }
    return undef;
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
    
sub connect {
    my ($self, %options) = @_;
    my $dontquit = (defined($options{dontquit}) && $options{dontquit} == 1) ? 1 : 0;

    $self->{instance} = DBI->connect(
        "DBI:". $self->{data_source},
        $self->{username},
        $self->{password},
        { "RaiseError" => 0, "PrintError" => 0, "AutoCommit" => 1 }
    );

    if (!defined($self->{instance})) {
        if ($dontquit == 0) {
            $self->{output}->add_option_msg(short_msg => "Cannot connect: " . $DBI::errstr);
            $self->{output}->option_exit(exit_litteral => $self->{sql_errors_exit});
        }
        return (-1, "Cannot connect: " . $DBI::errstr);
    }
    
    $self->{version} = $self->{instance}->get_info(18); # SQL_DBMS_VER
    return 0;
}

sub get_id {
    my ($self, %options) = @_;
    
    return $self->{data_source};
}

sub get_unique_id4save {
    my ($self, %options) = @_;

    return md5_hex($self->{data_source});
}

sub fetchall_arrayref {
    my ($self, %options) = @_;
    
    return $self->{statement_handle}->fetchall_arrayref();
}

sub fetchrow_array {
    my ($self, %options) = @_;
    
    return $self->{statement_handle}->fetchrow_array();
}

sub fetchrow_hashref {
    my ($self, %options) = @_;
    
    return $self->{statement_handle}->fetchrow_hashref();
}

sub query {
    my ($self, %options) = @_;
    
    $self->{statement_handle} = $self->{instance}->prepare($options{query});
    if (!defined($self->{statement_handle})) {
        $self->{output}->add_option_msg(short_msg => "Cannot execute query: " . $self->{instance}->errstr);
        $self->{output}->option_exit(exit_litteral => $self->{sql_errors_exit});
    }

    my $rv = $self->{statement_handle}->execute;
    if (!$rv) {
        $self->{output}->add_option_msg(short_msg => "Cannot execute query: " . $self->{statement_handle}->errstr);
        $self->{output}->option_exit(exit_litteral => $self->{sql_errors_exit});
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
