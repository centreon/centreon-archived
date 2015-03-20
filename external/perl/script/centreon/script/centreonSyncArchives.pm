################################################################################
# Copyright 2005-2013 CENTREON
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
# As a special exception, the copyright holders of this program give CENTREON 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of CENTREON choice, provided that 
# CENTREON also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::script::centreonSyncArchives;

use strict;
use warnings;
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreonSyncArchives",
        centreon_db_conn => 0,
        centstorage_db_conn => 0
    );
    bless $self, $class;
    $self->{rsync} = "rsync";
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
    my ($status, $sth) = $cdb->query("SELECT nagios_server.id, nagios_server.ip_address, cfg_nagios.log_archive_path FROM nagios_server, cfg_nagios 
                                      WHERE nagios_server.activate = '1' AND nagios_server.localhost = '0' AND nagios_server.id = cfg_nagios.nagios_server_id");
    die("Error SQL Quit") if ($status == -1);
    while ((my $data = $sth->fetchrow_hashref())) {
		if (defined($data->{log_archive_path}) && $data->{log_archive_path} ne '') {
			`$self->{rsync} -c $data->{'ip_address'}:$data->{'log_archive_path'}/* $self->{centreon_config}->{VarLib}/log/$data->{'id'}/archives/`;
		} else {
			$self->{logger}->writeLogError("Can't get archive path for service " . $data->{'id'} . " (" . $data->{'ns_address_ip'} . ")");
		}		
	}
}

1;

__END__

=head1 NAME

    sample - Using GetOpt::Long and Pod::Usage

=cut
