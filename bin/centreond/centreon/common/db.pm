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

package centreon::common::db;

use strict;
use warnings;
use DBI;

sub new {
    my ($class, %options) = @_;
    my %defaults =
      (
       logger => undef,
       db => undef,
       host => "localhost",
       user => undef,
       password => undef,
       port => 3306,
       force => 0,
       type => "mysql"
      );
    my $self = {%defaults, %options};
    $self->{type} = 'mysql' if (!defined($self->{type}));

    $self->{instance} = undef;
    $self->{args} = [];
    bless $self, $class;
    return $self;
}

# Getter/Setter DB name
sub type {
    my $self = shift;
    if (@_) {
        $self->{type} = shift;
    }
    return $self->{type};
}

# Getter/Setter DB name
sub db {
    my $self = shift;
    if (@_) {
        $self->{db} = shift;
    }
    return $self->{db};
}

# Getter/Setter DB host
sub host {
    my $self = shift;
    if (@_) {
        $self->{host} = shift;
    }
    return $self->{host};
}

# Getter/Setter DB port
sub port {
    my $self = shift;
    if (@_) {
        $self->{port} = shift;
    }
    return $self->{port};
}

# Getter/Setter DB user
sub user {
    my $self = shift;
    if (@_) {
        $self->{user} = shift;
    }
    return $self->{user};
}

# Getter/Setter DB force
sub force {
    my $self = shift;
    if (@_) {
        $self->{force} = shift;
    }
    return $self->{force};
}

# Getter/Setter DB password
sub password {
    my $self = shift;
    if (@_) {
        $self->{password} = shift;
    }
    return $self->{password};
}

sub last_insert_id {
    my $self = shift;
    return $self->{instance}->last_insert_id(undef, undef, undef, undef);
}

sub quote {
    my $self = shift;

    if (defined($self->{instance})) {
        return $self->{instance}->quote($_[0]);
    }
    my $num = scalar(@{$self->{args}});
    push @{$self->{args}}, $_[0];
    return "##__ARG__$num##";
}

sub set_inactive_destroy {
    my $self = shift;

    if (defined($self->{instance})) {
        $self->{instance}->{InactiveDestroy} = 1;
    }
}

sub transaction_mode {
    my ($self, $status) = @_;

    if ($status) {
        $self->{instance}->begin_work;
    } else {
        $self->{instance}->{AutoCommit} = 1;
    }
}

sub commit { shift->{instance}->commit; }

sub rollback { shift->{instance}->rollback; }

sub kill {
    my $self = shift;

    if (defined($self->{instance})) {
        $self->{logger}->writeLogInfo("KILL QUERY\n");
        my $rv = $self->{instance}->do("KILL QUERY " . $self->{instance}->{'mysql_thread_id'});
        if (!$rv) {
            my ($package, $filename, $line) = caller;
            $self->{logger}->writeLogError("MySQL error : " . $self->{instance}->errstr . " (caller: $package:$filename:$line)");
        }
    }
}

# Connection initializer
sub connect() {
    my $self = shift;
    my ($status, $count) = (0, 0);

    while (1) {
        $self->{port} = 3306 if (!defined($self->{port}) && $self->{type} eq 'mysql');
        if ($self->{type} =~ /SQLite/i) {
            $self->{instance} = DBI->connect(
                "DBI:".$self->{type} 
                    .":".$self->{db},
                $self->{user},
                $self->{password},
                { RaiseError => 0, PrintError => 0, AutoCommit => 1 }
            );
        } else {
            $self->{instance} = DBI->connect(
                "DBI:".$self->{type} 
                    .":".$self->{db}
                    .":".$self->{host}
                    .":".$self->{port},
                $self->{user},
                $self->{password},
                { RaiseError => 0, PrintError => 0, AutoCommit => 1 }
            );
        }
        if (defined($self->{instance})) {
            last;
        }

        my ($package, $filename, $line) = caller;
        $self->{logger}->writeLogError("MySQL error : cannot connect to database " . $self->{db} . ": " . $DBI::errstr . " (caller: $package:$filename:$line) (try: $count)");
        if ($self->{force} == 0 || ($self->{force} == 2 && $count == 1)) {
            $status = -1;
            last;
        }
        sleep(1);
        $count++;
    }
    return $status;
}

# Destroy connection
sub disconnect {
    my $self = shift;
    my $instance = $self->{instance};
    if (defined($instance)) {
        $instance->disconnect;
        $self->{instance} = undef;
    }
}

sub do {
    my ($self, $query) = @_;

    if (!defined $self->{instance}) {
        if ($self->connect() == -1) {
            $self->{logger}->writeLogError("Can't connect to the database");
            return -1;
        }
    }
    my $numrows = $self->{instance}->do($query);
    die $self->{instance}->errstr if !defined $numrows;
    return $numrows;
}

sub error {
    my ($self, $error, $query) = @_;
    my ($package, $filename, $line) = caller 1;

    chomp($query);
    $self->{logger}->writeLogError(<<"EOE");
MySQL error: $error (caller: $package:$filename:$line)
Query: $query
EOE
    $self->disconnect();
    $self->{instance} = undef;
}

sub query {
    my $self = shift;
    my $query = shift;
    my ($status, $count) = (0, -1);
    my $statement_handle;

    while (1) {
        if (!defined($self->{instance})) {
            $status = $self->connect();
            if ($status != -1) {
                for (my $i = 0; $i < scalar(@{$self->{args}}); $i++) {
                    my $str_quoted = $self->quote(${$self->{args}}[0]);
                    $query =~ s/##__ARG__$i##/$str_quoted/;
                }
                $self->{args} = [];
            }
            if ($status == -1 && $self->{force} != 1) {
                $self->{args} = [];
                last;
            }
        }

        $count++;
        $statement_handle = $self->{instance}->prepare($query);
        if (!defined $statement_handle) {
            $self->error($self->{instance}->errstr, $query);
            $status = -1;
            last if ($self->{force} == 0 || ($self->{force} == 2 && $count == 1));
            next;
        }

        my $rv = $statement_handle->execute;
        if (!$rv) {
            $self->error($statement_handle->errstr, $query);
            $status = -1;
            last if ($self->{force} == 0 || ($self->{force} == 2 && $count == 1));
            next;
        }
        last;
    }
    return ($status, $statement_handle);
}

1;
