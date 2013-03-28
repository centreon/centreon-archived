package centreon::script::centreon_check_perfdata;

use strict;
use warnings;
use centreon::common::db;
use centreon::centstorage::CentstoragePool;
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreon_check_perfdata",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noconfig => 0
    );

    bless $self, $class;
    return $self;
}

sub run {
    my $self = shift;
    my ($status, $sth, $row);

    $self->SUPER::run();
    my $centreon_db_centreon = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                                         host => $self->{centreon_config}->{db_host},
                                                         port => $self->{centreon_config}->{db_port},
                                                         user => $self->{centreon_config}->{db_user},
                                                         password => $self->{centreon_config}->{db_passwd},
                                                         force => 0,
                                                         logger => $self->{logger});
    $status = $centreon_db_centreon->connect();
    die("Quit. Can't connect") if ($status == 1);
    ($status, $sth, $row) = $centreon_db_centreon->query("SELECT db_name, db_host, db_port, db_user, db_pass FROM cfg_ndo2db WHERE activate = '1' LIMIT 1");
    die("Quit") if ($status == -1);
    if (!($row = $sth->fetchrow_hashref())) {
        $self->{logger}->writeLogError("Can't get ndo connection information (maybe you are using centreon-broker)");
        die("Quit");
    }
    my $centstatus = centreon::common::db->new(db => $row->{db_name},
                                               host => $row->{db_host},
                                               port => $row->{db_port},
                                               user => $row->{db_user},
                                               password => $row->{db_pass},
                                               force => 0,
                                               logger => $self->{logger});
    $status = $centstatus->connect();
    die("Quit. Can't connect to centreon_status database") if ($status == 1);
    ($status, $sth, $row) = $centstatus->query("SELECT no.name1, no.name2, ns.perfdata FROM nagios_objects no, nagios_servicestatus ns WHERE no.is_active = 1 AND no.name2 IS NOT NULL AND no.object_id = ns.service_object_id");
    die("Quit") if ($status == -1);
    my $centstorage_pool = centreon::centstorage::CentstoragePool->new($self->{logger});
    $centstorage_pool->{'perfdata_parser_stop'} = 1;
    while (($row = $sth->fetchrow_hashref())) {
        $centstorage_pool->{'service_perfdata'} = $row->{'perfdata'};
        $centstorage_pool->init_perfdata();
        my $space_in_name = 0;    

        while (($status = $centstorage_pool->get_perfdata()) > 0) {
            $space_in_name = 1 if ($centstorage_pool->{"metric_name"} =~ /\s/);
        }
        if ($status == -1) {
            $self->{logger}->writeLogError("Check service " . $row->{name1} . "/" . $row->{name2});
        }
        if ($space_in_name == 1) {
            $self->{logger}->writeLogInfo("You have metric name with space: " . $row->{name1} . "/" . $row->{name2} . ". You have to delete character quote in 'Illegal Macro Output Characters' attribute.");
        }
    } 
}

1;

__END__

=head1 NAME

    sample - Using GetOpt::Long and Pod::Usage

=cut
