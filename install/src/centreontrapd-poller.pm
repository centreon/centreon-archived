#############################################
# File Added by Centreon
#
our %centreontrapd_config = (
        # databases credentials
        centreon_db => "dbname=@SNMP_ETC_DIR@/centreontrapd.sdb",
        centstorage_db => "dbname=@SNMP_ETC_DIR@/centreon_traps/centreontrapd.sdb",
        db_type => 'SQLite',
        # server type (0: central, 1: poller)
        mode => 1
);

1;
