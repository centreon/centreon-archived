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

package centreon::script::centreonSyncPlugins;

use strict;
use warnings;
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreonSyncPlugins",
        centreon_db_conn => 0,
        centstorage_db_conn => 0
    );
    bless $self, $class;
    $self->{rsync} = "rsync";
    $self->{ssh} = "ssh";
    return $self;
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    my $cdb = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                        host => $self->{centreon_config}->{db_host},
                                        port => $self->{centreon_config}->{db_port},
                                        user => $self->{centreon_config}->{db_user},
                                        password => $self->{centreon_config}->{db_passwd},
                                        force => 0,
                                        logger => $self->{logger});
    my ($status, $sth) = $cdb->query("SELECT `value` FROM `options` WHERE `key` LIKE 'nagios_path_plugins' LIMIT 1");
    die("Error SQL Quit") if ($status == -1);
    my $data = $sth->fetchrow_hashref();
    if (!defined($data->{value}) || $data->{value} eq '') {
        $self->{logger}->writeLogError("Plugin path is not set.");
        die("Quit");
    }
    my $path_plugins = $data->{value};
    
    ($status, $sth) = $cdb->query("SELECT `id`, `ns_ip_address`, `ssh_port` FROM `nagios_server` WHERE `ns_activate` = '1' AND `localhost` = '0'");
    die("Error SQL Quit") if ($status == -1);
    while ((my $data = $sth->fetchrow_hashref())) {
		my $ls = `$self->{ssh} -q -p $data->{'ssh_port'} $data->{'ns_ip_address'} ls -l $path_plugins/ 2>> /dev/null | wc -l`;
        if ($ls > 1) {
            `$self->{rsync} -e "ssh -o port=$data->{'ssh_port'}" -prc $path_plugins/* $data->{'ns_ip_address'}:$path_plugins/`;
        } else {
            $self->{logger}->writeLogError("Directory not present on remote server : " . $data->{'ns_ip_address'});
        }
	}
}

1;

__END__

=head1 NAME

    sample - Using GetOpt::Long and Pod::Usage

=cut
