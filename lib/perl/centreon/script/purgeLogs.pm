
package centreon::script::purgeLogs;

use strict;
use warnings;
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("purgeLogs",
        centreon_db_conn => 1,
        centstorage_db_conn => 1
    );
    bless $self, $class;
    
    return $self;
}

sub run {
    my $self = shift;

    $self->SUPER::run();

    # Get conf Data
    my $logRetention;
    my $reportingRetention;
    my ($status, $sth_config) = $self->{csdb}->query("SELECT `archive_retention`, `reporting_retention` FROM `config` LIMIT 1");
    if ($status != -1) {
        my $data = $sth_config->fetchrow_hashref();
        $logRetention = $data->{'archive_retention'};
        $reportingRetention = $data->{'reporting_retention'};
    }

    ################################
    # Get broker
    my $broker = "ndo";
    ($status, $sth_config) = $self->{csdb}->query("SELECT `value` FROM `options` WHERE `key` = 'broker' LIMIT 1");
    if ($status != -1) {
        my $data = $sth_config->fetchrow_hashref();
        $broker = $data->{'value'};
    }

    ####################################################
    # Logs Data purge
    if (defined($logRetention) && $logRetention ne 0){
        my $last_log = time() - ($logRetention * 24 * 60 * 60);

        # Purge Log Database
        if ($broker eq "ndo") {
            $self->{logger}->writeLogInfo("Begin centstorage.log purge");
            $self->{csdb}->query("DELETE FROM `log` WHERE `ctime` < '$last_log'");
            $self->{logger}->writeLogInfo("End centstorage.log purge");
        } else {
            $self->{logger}->writeLogInfo("Begin centstorage.logs purge");
            $self->{csdb}->query("DELETE FROM `logs` WHERE `ctime` < '$last_log'");
            $self->{logger}->writeLogInfo("End centstorage.logs purge");
        }   
    }

    ####################################################
    # Reporting Data purge
    if (defined($reportingRetention) && $reportingRetention ne 0){
        my $last_log = time() - ($reportingRetention * 24 * 60 * 60);

        $self->{logger}->writeLogInfo("Begin log_archive table purge");
        $self->{csdb}->query("DELETE FROM `log_archive_host` WHERE `date_end` < '$last_log'");
        $self->{csdb}->query("DELETE FROM `log_archive_service` WHERE `date_end` < '$last_log'");
        $self->{logger}->writeLogInfo("End log_archive table purge");
    }
    
    exit(0);
}

1;

__END__

=head1 NAME

    sample - Using GetOpt::Long and Pod::Usage

=cut
