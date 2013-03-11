package centreon::script::centstorage_purge;

use strict;
use warnings;
use centreon::script;
use centreon::lock;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centstorage_purge",
        centreon_db_conn => 1,
        centstorage_db_conn => 1
    );

    bless $self, $class;
    $self->{broker} = "ndo";
    my ($status, $sth) = $self->{csdb}->query(<<"EOQ");
SELECT len_storage_mysql,archive_retention,reporting_retention
FROM config
EOQ
    die "Failed to retrieve configuration from database" if $status == -1;
    $self->{config} = $sth->fetchrow_hashref();

    ($status, $sth) = $self->{cdb}->query(<<"EOQ");
SELECT `value` FROM `options` WHERE `key` = 'broker'
EOQ
    die "Failed to retrieve the broker type from database" if $status == -1;
    $self->{broker} = $sth->fetchrow_hashref()->{value};
    return $self;
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    if (defined $self->{config}->{len_storage_mysql} && 
        $self->{config}->{len_storage_mysql} != 0) {
        my $delete_limit = time() - 60 * 60 * 24 * $self->{config}->{len_storage_mysql};

        $self->{logger}->writeLogInfo("Purging centstorage.data_bin table...");
        $self->{csdb}->do("DELETE FROM data_bin WHERE ctime < '$delete_limit'");
        $self->{logger}->writeLogInfo("Done");
    }

    if (defined($self->{config}->{archive_retention}) 
        && $self->{config}->{archive_retention} != 0) {
        my $last_log = time() - ($self->{config}->{archive_retention} * 24 * 60 * 60);
        my $table = ($self->{broker} eq "broker") ? "logs" : "log";

        $self->{logger}->writeLogInfo("Purging centstorage.$table table...");
        eval {
            my $lock = undef;
            if ($self->{broker} eq "ndo") {
                $lock = centreon::lock::sql("logAnalyser", dbc => $self->{cdb});
                $lock->set();
            }
            $self->{csdb}->do("DELETE FROM `$table` WHERE `ctime` < '$last_log'");
        };
        if ($@) {
            $self->{logger}->writeLogError("Failed: $@");
        } else {
            $self->{logger}->writeLogInfo("Done");
        }
    }

    if (defined($self->{config}->{reporting_retention}) 
        && $self->{config}->{reporting_retention} != 0) {
        my $last_log = time() - ($self->{config}->{reporting_retention} * 24 * 60 * 60);

        $self->{logger}->writeLogInfo("Purging log archive tables...");
        $self->{csdb}->do("DELETE FROM `log_archive_host` WHERE `date_end` < '$last_log'");
        $self->{csdb}->do("DELETE FROM `log_archive_service` WHERE `date_end` < '$last_log'");
        $self->{logger}->writeLogInfo("Done");
    }
}

1;

__END__

=head1 NAME

centstorage_purge - purge centstorage database

=head1 SYNOPSIS

centstorage_purge [options]

=head1 OPTIONS

=over 8

=item B<-help>

Print a brief help message and exits.

=back

=head1 DESCRIPTION

B<This program> will read the given input file(s) and do something
useful with the contents thereof.

=cut
