
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
    
    ($status, $sth) = $cdb->query("SELECT `id`, `ns_ip_address` FROM `nagios_server` WHERE `ns_activate` = '1' AND `localhost` = '0'");
    die("Error SQL Quit") if ($status == -1);
    while ((my $data = $sth->fetchrow_hashref())) {
		my $ls = `$self->{ssh} -q $data->{'ns_ip_address'} ls -l $path_plugins/ 2>> /dev/null | wc -l`;
        if ($ls > 1) {
            `$self->{rsync} -prc $path_plugins/* $data->{'ns_ip_address'}:$path_plugins/`;
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