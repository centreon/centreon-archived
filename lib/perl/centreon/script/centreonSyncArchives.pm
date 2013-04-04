
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
    my ($status, $sth) = $cdb->query("SELECT nagios_server.id, nagios_server.ns_ip_address, cfg_nagios.log_archive_path FROM nagios_server, cfg_nagios 
                                      WHERE nagios_server.ns_activate = '1' AND nagios_server.localhost = '0' AND nagios_server.id = cfg_nagios.nagios_server_id");
    die("Error SQL Quit") if ($status == -1);
    while ((my $data = $sth->fetchrow_hashref())) {
		if (defined($data->{log_archive_path}) && $data->{log_archive_path} ne '') {
			`$self->{rsync} -c $data->{'ns_ip_address'}:$data->{'log_archive_path'}/* $self->{centreon_config}->{VarLib}/log/$data->{'id'}/archives/`;
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
