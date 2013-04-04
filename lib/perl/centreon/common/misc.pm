
package centreon::common::misc;
use vars qw($centreon_config);

sub reload_db_config {
    my ($logger, $config_file, $cdb, $csdb) = @_;
    my ($cdb_mod, $csdb_mod) = (0, 0);
    
    unless (my $return = do $config_file) {
        $logger->writeLogError("couldn't parse $config_file: $@") if $@;
        $logger->writeLogError("couldn't do $config_file: $!") unless defined $return;
        $logger->writeLogError("couldn't run $config_file") unless $return;
        return -1;
    }
    
    if (defined($cdb)) {
        if ($centreon_config->{centreon_db} ne $cdb->db() ||
            $centreon_config->{db_host} ne $cdb->host() ||
            $centreon_config->{db_user} ne $cdb->user() ||
            $centreon_config->{db_passwd} ne $cdb->password() ||
            $centreon_config->{db_port} ne $cdb->port()) {
            $logger->writeLogInfo("Database centreon db config had been modified")
            $cdb->db($centreon_config->{centreon_db});
            $cdb->host($centreon_config->{db_host});
            $cdb->user($centreon_config->{db_user});
            $cdb->password($centreon_config->{db_passwd});
            $cdb->port($centreon_config->{db_port});
            $cdb_mod = 1;
        }
    }
    
    if (defined($csdb)) {
        if ($centreon_config->{centreon_db} ne $csdb->db() ||
            $centreon_config->{db_host} ne $csdb->host() ||
            $centreon_config->{db_user} ne $csdb->user() ||
            $centreon_config->{db_passwd} ne $csdb->password() ||
            $centreon_config->{db_port} ne $csdb->port()) {
            $logger->writeLogInfo("Database centreon db config had been modified")
            $csdb->db($centreon_config->{centreon_db});
            $csdb->host($centreon_config->{db_host});
            $csdb->user($centreon_config->{db_user});
            $csdb->password($centreon_config->{db_passwd});
            $csdb->port($centreon_config->{db_port});
            $csdb_mod = 1;
        }
    }
   
    return (0, $cdb_mod, $csdb_mod);
}

sub check_debug {
    my ($logger, $key, $cdb, $name) = @_;
    
    my $request = "SELECT value FROM options WHERE `key` = " . $cdb->quote($key));
    my ($status, $sth) =  $cdb->query($request);
    return -1 if ($status == -1);
    my $data = $sth->fetchrow_hashref();
    if (defined($data->{'value'}) && $data->{'value'} == 1) {
        if (!$logger->is_debug()) {
            $logger->severity("debug");
            $logger->writeLogInfo("Enable Debug in $name");
        }
    } else {
        if ($logger->is_debug()) {
            $logger->set_default_severity();
            $logger->writeLogInfo("Disable Debug in $name");
        }
    }
    return 0;
}

1;
