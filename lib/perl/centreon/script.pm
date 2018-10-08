################################################################################
# Copyright 2005-2013 Centreon
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
# As a special exception, the copyright holders of this program give Centreon 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of Centreon choice, provided that 
# Centreon also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::script;

use strict;
use warnings;
use FindBin;
use Getopt::Long;
use Pod::Usage;
use centreon::common::logger;
use centreon::common::db;
use centreon::common::lock;

use vars qw($centreon_config);
use vars qw($mysql_user $mysql_passwd $mysql_host $mysql_database_oreon $mysql_database_ods $mysql_database_ndo $instance_mode);

$SIG{__DIE__} = sub {
    return unless defined $^S and $^S == 0; # Ignore errors in eval
    my $error = shift;
    print "Error: $error";
    exit 1;
};

sub new {
    my ($class, $name, %options) = @_;
    my %defaults = 
      (
       config_file => "/etc/centreon/conf.pm",
       log_file => undef,
       centreon_db_conn => 0,
       centstorage_db_conn => 0,
       severity => "info",
       noconfig => 0,
       noroot => 0,
       instance_mode => "central"
      );
    my $self = {%defaults, %options};

    bless $self, $class;
    $self->{name} = $name;
    $self->{logger} = centreon::common::logger->new();
    $self->{options} = {
        "config=s" => \$self->{config_file},
        "logfile=s" => \$self->{log_file},
        "severity=s" => \$self->{severity},
        "help|?" => \$self->{help}
    };
    return $self;
}

sub init {
    my $self = shift;

    if (defined $self->{log_file}) {
        $self->{logger}->file_mode($self->{log_file});
    }
    $self->{logger}->severity($self->{severity});

    if ($self->{noroot} == 1) {
        # Stop exec if root
        if ($< == 0) {
            $self->{logger}->writeLogError("Can't execute script as root.");
            die("Quit");
        }
    }
    
    if ($self->{centreon_db_conn}) {
        $self->{cdb} = centreon::common::db->new
          (db => $self->{centreon_config}->{centreon_db},
           host => $self->{centreon_config}->{db_host},
           port => $self->{centreon_config}->{db_port},
           user => $self->{centreon_config}->{db_user},
           password => $self->{centreon_config}->{db_passwd},
           logger => $self->{logger});
        $self->{lock} = centreon::common::lock::sql->new($self->{name}, dbc => $self->{cdb});
        $self->{lock}->set();
    }
    if ($self->{centstorage_db_conn}) {
        $self->{csdb} = centreon::common::db->new
          (db => $self->{centreon_config}->{centstorage_db},
           host => $self->{centreon_config}->{db_host},
           port => $self->{centreon_config}->{db_port},
           user => $self->{centreon_config}->{db_user},
           password => $self->{centreon_config}->{db_passwd},
           logger => $self->{logger});
    }
    $self->{instance_mode} = $instance_mode;
}

sub DESTROY {
    my $self = shift;

    if (defined $self->{cdb}) {
        $self->{cdb}->disconnect();
    }
    if (defined $self->{csdb}) {
        $self->{csdb}->disconnect();
    }
}

sub add_options {
    my ($self, %options) = @_;

    $self->{options} = {%{$self->{options}}, %options};
}

sub parse_options {
    my $self = shift;

    Getopt::Long::Configure('bundling');
    die "Command line error" if !GetOptions(%{$self->{options}});
    pod2usage(-exitval => 1, -input => $FindBin::Bin . "/" . $FindBin::Script) if $self->{help};
    if ($self->{noconfig} == 0) {
        require $self->{config_file};
        $self->{centreon_config} = $centreon_config;
    }
}

sub run {
    my $self = shift;

    $self->parse_options();
    $self->init();
}

1;
