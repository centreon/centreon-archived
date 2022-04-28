#!/usr/bin/perl
################################################################################
# Copyright 2005-2020 Centreon
# Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
####################################################################################
#
# Script init
#

use strict;
use DBI;
use Getopt::Long;
use File::Path;
use File::Copy;
use File::Find;
use File::Basename;
use IO::Dir;

use vars qw($mysql_user $mysql_passwd $mysql_host $mysql_port $mysql_database_oreon $mysql_database_ods $centreon_config);
use vars qw($BACKUP_ENABLED $BACKUP_DIR $TEMP_DIR);
use vars qw($BACKUP_DATABASE_CENTREON $BACKUP_DATABASE_CENTREON_STORAGE $BACKUP_DATABASE_TYPE $BACKUP_DATABASE_FULL $BACKUP_DATABASE_PARTIAL $BACKUP_RETENTION);
use vars qw($BACKUP_CONFIGURATION_FILES $MYSQL_CONF);
use vars qw($TEMP_DB_DIR $TEMP_CENTRAL_DIR $TEMP_CENTRAL_ETC_DIR $TEMP_CENTRAL_INIT_DIR $TEMP_CENTRAL_CRON_DIR $TEMP_CENTRAL_LOG_DIR $TEMP_CENTRAL_BIN_DIR $TEMP_CENTRAL_LIC_DIR $CENTREON_MODULES_PATH $TEMP_POLLERS $DISTANT_POLLER_BACKUP_DIR);
use vars qw($BIN_GZIP $BIN_TAR);
use vars qw($scp_enabled $scp_user $scp_host $scp_directory);
use vars qw($centreon_config);

sub print_help();
sub print_usage();
sub trim($);

my $CENTREON_ETC = '@CENTREON_ETC@';
my @licfiles;

# Require DB configuration files
if (-e $CENTREON_ETC . '/conf.pm') {
    require $CENTREON_ETC . '/conf.pm';
} elsif (-e $CENTREON_ETC . '/centreon-config.pm') {
    require $CENTREON_ETC . '/centreon-config.pm';
}

## Convert new configuration to old
if (!defined($mysql_host)) {
    $mysql_host = $centreon_config->{db_host};
}
if (!defined($mysql_user)) {
    $mysql_user = $centreon_config->{db_user};
}
if (!defined($mysql_passwd)) {
    $mysql_passwd = $centreon_config->{db_passwd};
}
if (!defined($mysql_database_oreon)) {
    $mysql_database_oreon = $centreon_config->{centreon_db};
}
if (!defined($mysql_database_ods)) {
    $mysql_database_ods = $centreon_config->{centstorage_db};
}

if (defined($mysql_host)) {
    if ($mysql_host =~ /\:/) {
        my @tab = split(/\:/, $mysql_host);
        if (defined($tab[0])) {
            $mysql_host = $tab[0];
        }
        if (defined($tab[1])) {
            $mysql_port = $tab[1];
        } else {
            $mysql_port = 3306;
        }
    }
}

if (!defined($mysql_port)) {
    $mysql_port = 3306;
}

#######################
# Defined global vars #
#######################
my $PROGNAME = $0;
my $VERSION = "1.0";
my $CENTREONDIR = "@INSTALL_DIR_CENTREON@";

##########################################
# Get backup configuration from database #
##########################################

my $dbh = DBI->connect("DBI:mysql:database=" . $mysql_database_oreon . ";host=" . $mysql_host . ";port=" . $mysql_port, $mysql_user, $mysql_passwd, { 'RaiseError' => 0, 'PrintError' => 0 });
if (!$dbh) {
    print STDERR "Couldn't connect: " . $DBI::errstr . "\n";
    exit 1;
}

my $sth = $dbh->prepare("SELECT * FROM options WHERE options.key LIKE 'backup_%';");
if (!$sth || !$sth->execute()) {
    print STDERR "Error: " . $dbh->errstr . "\n";
    exit 1;
}

my $backupOptions = $sth->fetchall_hashref('key');

$BACKUP_ENABLED = $backupOptions->{'backup_enabled'}->{'value'};
if ($BACKUP_ENABLED != '1') {
    exit 0;
}

$CENTREON_MODULES_PATH = "www/modules";

$BACKUP_DIR = $backupOptions->{'backup_backup_directory'}->{'value'};
$TEMP_DIR = $backupOptions->{'backup_tmp_directory'}->{'value'};
$TEMP_DB_DIR = $TEMP_DIR . "/db";
$TEMP_CENTRAL_DIR = $TEMP_DIR . "/central";
$TEMP_CENTRAL_ETC_DIR = $TEMP_CENTRAL_DIR . "/etc";
$TEMP_CENTRAL_INIT_DIR = $TEMP_CENTRAL_DIR . "/init";
$TEMP_CENTRAL_CRON_DIR = $TEMP_CENTRAL_DIR . "/cron";
$TEMP_CENTRAL_LOG_DIR = $TEMP_CENTRAL_DIR . "/log";
$TEMP_CENTRAL_BIN_DIR = $TEMP_CENTRAL_DIR . "/bin";
$TEMP_CENTRAL_LIC_DIR = $TEMP_CENTRAL_DIR . "/lic";

$BACKUP_DATABASE_CENTREON = $backupOptions->{'backup_database_centreon'}->{'value'};
$BACKUP_DATABASE_CENTREON_STORAGE = $backupOptions->{'backup_database_centreon_storage'}->{'value'};
$BACKUP_DATABASE_TYPE = $backupOptions->{'backup_database_type'}->{'value'};
$BACKUP_DATABASE_FULL = $backupOptions->{'backup_database_full'}->{'value'};
$BACKUP_DATABASE_PARTIAL = $backupOptions->{'backup_database_partial'}->{'value'};
$BACKUP_RETENTION = $backupOptions->{'backup_retention'}->{'value'};

$BACKUP_CONFIGURATION_FILES = $backupOptions->{'backup_configuration_files'}->{'value'};
$MYSQL_CONF = $backupOptions->{'backup_mysql_conf'}->{'value'};

$BIN_GZIP = "";
$BIN_TAR = "";

if ( -e $BACKUP_DIR) {
    if (! -w $BACKUP_DIR) {
        print STDERR "Backup directory \"$BACKUP_DIR\" is not writable.\n";
        exit 1;
    }
} else {
    print STDERR "Backup directory \"$BACKUP_DIR\" does not exist.\n";
    exit 1;
}

# Parameters for SCP export
$scp_enabled = $backupOptions->{'backup_export_scp_enabled'}->{'value'};
$scp_user = $backupOptions->{'backup_export_scp_user'}->{'value'};
$scp_host = $backupOptions->{'backup_export_scp_host'}->{'value'};
$scp_directory = $backupOptions->{'backup_export_scp_directory'}->{'value'};

#############
# Functions #
#############

sub print_usage() {
    print "Usage: ";
    print $PROGNAME . "\n";
    print "\t-V | --version\t\tShow plugin version\n";
    print "\t-h | --help\t\tUsage help\n";
    print "\t-d | --debug\t\tPdisplay debug information\n";
    print "\n";
    print "Obligatory options:\n";
    print "\t-T | --backup-type <value> Type of backup in (central, databases, poller).\n";
    print "\t\tIf you use option \"poller\" please use option \"-P | --poller <value>\"\n";
    print "\t\t\"<value>\" is the name of Monitoring Engine poller in Centreon configuration.\n";
    print "\t\tIf \"<value>\" is \"all\", all Monitoring Engine poller will be saved.\n";
    print "\n";
    print "\t--preexec <value> Execute command before backup.\n";
    print "\n";
    print "\t--postexec <value> Execute command after backup.\n";
}

sub print_help() {
    print "##############################################\n";
    print "#    Copyright (c) 2005-2020 Centreon        #\n";
    print "#    Bugs to http://forge.centreon.com       #\n";
    print "##############################################\n";
    print "\n";
    print_usage();
    print "\n";
}

sub trim($) {
    my $string = shift;
    $string =~ s/^\s+//;
    $string =~ s/\s+$//;
    return $string;
}

sub getbinaries() {
    $BIN_GZIP = `which gzip`;
    $BIN_GZIP = trim($BIN_GZIP);

    if ($BIN_GZIP =~ /no .* in/) {
        print STDERR "Unable to get gzip binary\n";
    }

    $BIN_TAR = `which tar`;
    $BIN_TAR = trim($BIN_TAR);

    if ($BIN_TAR =~ /no .* in/) {
        print STDERR "Unable to get tar binary\n";
    }
}

sub exportBackup($) {
    my $export_type = shift; # 0 : database, 1 : configuration
    if ($scp_enabled == '1' &&
        (!defined($scp_host) || $scp_host ne '') &&
        (!defined($scp_directory) || $scp_directory ne '') &&
        (!defined($scp_user) || $scp_user ne '')
    ) {

        # Export database backups
        if ($export_type == 0 && ($BACKUP_DATABASE_CENTREON == '1' || $BACKUP_DATABASE_CENTREON_STORAGE == '1')) {
            chdir($TEMP_DB_DIR);
            `scp *.gz $scp_user\@$scp_host:$scp_directory/`;
            if ($? ne 0) {
                print STDERR "Error when trying to export files of " . $TEMP_DB_DIR . "\n";
            } else {
                print "All files were copied with success using SCP on " . $scp_user . "@" . $scp_host . ":" . $scp_directory . "\n";
            }
        }

        # Export configuration files backup
        if ($export_type == 1 && $BACKUP_CONFIGURATION_FILES == '1') {
            chdir($TEMP_CENTRAL_DIR);
            `scp *.gz $scp_user\@$scp_host:$scp_directory/`;
            if ($? ne 0) {
                print STDERR "Error when trying to export files of " . $TEMP_CENTRAL_DIR . "\n";
            } else {
                print "All files were copied with success using SCP on " . $scp_user . "@" . $scp_host . ":" . $scp_directory . "\n";
            }
        }
    } elsif ($scp_enabled == '1') {
        print STDERR "The export by SCP is enabled but a configuration is missing\n";
    }
}

sub cleanOldBackup() {
    my ($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst) = localtime(time - ($BACKUP_RETENTION * 3600 * 24));
    my $max_backup_age = sprintf('%04d-%02d-%02d', (1900 + $year), ($mon + 1), $mday);

    my $dir = IO::Dir->new($BACKUP_DIR);

    if (!defined($dir)) {
        print STDERR "Unable to get list of backup files from: " . $BACKUP_DIR . "\n";
    } else {
        chdir($BACKUP_DIR);
        while (defined($_ = $dir->read)) {
            if ($_ =~ m/^(\d{4}\-\d{2}\-\d{2}).*/) {
                if ($1 le $max_backup_age) {
                    print "Delete file: " . $_ . "\n";
                    unlink $_;
                }
            }
        }
        undef($dir);
    }
}

###############################################
# Functions to get location of specifiec file #
###############################################

sub getApacheDirectory() {
    if ( -d '/opt/rh/httpd24/root/etc/httpd/conf.d' ) {
        return '/opt/rh/httpd24/root/etc/httpd/conf.d';
    } elsif ( -d '/etc/httpd/conf.d' ) {
        return '/etc/httpd/conf.d';
    } else {
        print STDERR "Unable to get Apache conf directory\n";
    }
}

sub getMySQLConfFile() {
    if (defined($MYSQL_CONF)) {
        if ( -e $MYSQL_CONF) {
            return $MYSQL_CONF;
        }
    } elsif ( -e '/etc/my.cnf' ) {
        return '/etc/my.cnf';
    } else {
        print STDERR "Unable to get Mysql configuration\n";
    }
}

sub getLicFile() {
    if ($_ =~ /^merethis_lic.zl$/) {
        push(@licfiles, $File::Find::name);
    }
}

sub getPHPConfFile() {
    my @tab_php_ini;

    # PHP CLI
    my $result = `@PHP_BIN@ -r 'echo php_ini_loaded_file();'`;
    push(@tab_php_ini, trim($result));

    # PHP configuration files
    my @ini = `@PHP_BIN@ -r "echo php_ini_scanned_files();"`;
    for (@ini) {
        chomp;
        s/,$//;
        push(@tab_php_ini, $_);
    }

    return @tab_php_ini;
}

############################
# Functions to make backup #
############################

sub databasesBackup() {
    my ($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst) = localtime(time);
    my $today = sprintf("%d-%02d-%02d", (1900 + $year), ($mon + 1), $mday);

    print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900 + $year), ($mon + 1), $mday, $hour, $min, $sec) . "] Start database backup process\n";

    # Create path
    mkpath($TEMP_DB_DIR, { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Database BACKUP: Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Database BACKUP: Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }

    my @localtime = localtime(time);
    my $dayOfWeek = @localtime[6];
    my @fullBackupDays = split(/,/, $BACKUP_DATABASE_FULL);
    if ($BACKUP_DATABASE_TYPE == '1') {
        # Do LVM snapshot backup or fall into degraded mode with mysqldump

        if (grep $_ == $dayOfWeek, @fullBackupDays) {
            print "Dumping Db with LVM snapshot (full)\n";
            `$CENTREONDIR/cron/centreon-backup-mysql.sh -b $TEMP_DB_DIR -d $today`;
            if ($? ne 0) {
                print STDERR "Cannot backup with LVM snapshot. Maybe you can try with mysqldump\n";
            }
        }

        my @partialBackupDays = split(/,/, $BACKUP_DATABASE_PARTIAL);
        if (grep $_ == $dayOfWeek, @partialBackupDays) {
            print "Dumping Db with LVM snapshot (partial)\n";
            `$CENTREONDIR/cron/centreon-backup-mysql.sh -b $TEMP_DB_DIR -d $today -p`;
            if ($? ne 0) {
                print STDERR "Cannot backup with LVM snapshot. Maybe you can try with mysqldump\n";
            }
        }
    } elsif (grep $_ == $dayOfWeek, @fullBackupDays) {
        my $mysql_database_ndo;
        my $dbh = DBI->connect("DBI:mysql:database=" . $mysql_database_oreon . ";host=" . $mysql_host . ";port=" . $mysql_port, $mysql_user, $mysql_passwd, { 'RaiseError' => 0, 'PrintError' => 0 });
        if (!$dbh) {
            print STDERR sprintf("Couldn't connect: %s", $DBI::errstr) . "\n";
        }

        my $file = "";

        # Make archives from databases dump
        if ($BACKUP_DATABASE_CENTREON == '1') {
            $file = $TEMP_DB_DIR . "/" . $today . "-centreon.sql.gz";
            `mysqldump -u $mysql_user -h $mysql_host -p'$mysql_passwd' $mysql_database_oreon | $BIN_GZIP  > $file`;
            if ($? ne 0) {
                print STDERR "Unable to dump database: " . $mysql_database_oreon . "\n";
            } else {
                print "Get mysqldump of \"" . $mysql_database_oreon . "\" database\n";
            }
        }


        # Make centreon_storage dump only if backup type is full
        if ($BACKUP_DATABASE_CENTREON_STORAGE == '1') {

            # Check if process already exist
            my $process_number = `ps aux | grep -v grep |grep "centstorage" | wc -l | bc`;

            if ($process_number == 0) {
                $file = $TEMP_DB_DIR . "/" . $today . "-centreon_storage.sql.gz";
                `mysqldump -u $mysql_user -h $mysql_host -p'$mysql_passwd' $mysql_database_ods | $BIN_GZIP  > $file`;
                if ($? ne 0) {
                    print STDERR "Unable to dump database: " . $mysql_database_ods . "\n";
                } else {
                    print "Get mysqldump of \"" . $mysql_database_ods . "\" database\n";
                }
            }
        }
        $dbh->disconnect;
    }
    # End of Db dump

    # Copy archives to local dir
    mkpath($BACKUP_DIR, { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create backup directory because: " . $message . "\n";
            } else {
                print STDERR "Problem with " . $file . ": " . $message . "\n";
            }
        }
    }

    # Export archives
    exportBackup(0);
    if (-r $TEMP_DB_DIR . "/" . $today . "-mysql-full.tar.gz") {
        move($TEMP_DB_DIR . "/" . $today . "-mysql-full.tar.gz", $BACKUP_DIR . "/" . $today . "-mysql-full.tar.gz");
    }
    if (-r $TEMP_DB_DIR . "/" . $today . "-mysql-partial.tar.gz") {
        move($TEMP_DB_DIR . "/" . $today . "-mysql-partial.tar.gz", $BACKUP_DIR . "/" . $today . "-mysql-partial.tar.gz");
    }
    if (-r $TEMP_DB_DIR . "/" . $today . "-centreon.sql.gz") {
        move($TEMP_DB_DIR . "/" . $today . "-centreon.sql.gz", $BACKUP_DIR . "/" . $today . "-centreon.sql.gz");
    }
    if (-r $TEMP_DB_DIR . "/" . $today . "-centreon_storage.sql.gz") {
        move($TEMP_DB_DIR . "/" . $today . "-centreon_storage.sql.gz", $BACKUP_DIR . "/" . $today . "-centreon_storage.sql.gz");
    }

    # Delete temporary directories
    chdir;
    rmtree($TEMP_DB_DIR, { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to remove temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem unlinking " . $file . ": " . $message, . "\n";
            }
        }
    }

    my ($tsec, $tmin, $thour, $tmday, $tmon, $tyear, $twday, $tyday, $tisdst) = localtime(time);
    print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900 + $tyear), ($tmon + 1), $tmday, $thour, $tmin, $tsec) . "] Finish database backup process\n";
}

sub centralBackup() {
    my ($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst) = localtime(time);
    my $today = sprintf("%d-%02d-%02d", (1900 + $year), ($mon + 1), $mday);
    print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900 + $year), ($mon + 1), $mday, $hour, $min, $sec) . "] Start central backup process\n";

    ###################################
    # Get configuration program files #
    ###################################

    # Create path
    mkpath($TEMP_CENTRAL_ETC_DIR, { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }

    # Apache or httpd
    my $ApacheConfdir = getApacheDirectory();
    mkpath($TEMP_CENTRAL_ETC_DIR . "/apache", { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }
    `cp -pr $ApacheConfdir* $TEMP_CENTRAL_ETC_DIR/apache/`;
    if ($? ne 0) {
        print STDERR "Unable to copy Apache configuration files\n";
    }

    # Centreon etc
    mkpath($TEMP_CENTRAL_ETC_DIR . "/centreon", { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }
    `cp -pr $CENTREON_ETC/* $TEMP_CENTRAL_ETC_DIR/centreon/`;
    if ($? ne 0) {
        print STDERR "Unable to copy Centreon configuration files\n";
    }

    # Centreon Broker etc
    my $cb_path = "/etc/centreon-broker";
    mkpath($TEMP_CENTRAL_ETC_DIR . "/centreon-broker", { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }
    `cp -r $cb_path $TEMP_CENTRAL_ETC_DIR"/centreon-broker/"`;

    # SNMP configuration
    mkpath($TEMP_CENTRAL_ETC_DIR . "/snmp", { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }
    `cp -pr /etc/snmp/* $TEMP_CENTRAL_ETC_DIR/snmp/`;
    if ($? ne 0) {
        print STDERR "Unable to copy SNMP configuration files\n";
    }

    # MySQL configuration
    mkpath($TEMP_CENTRAL_ETC_DIR . "/mysql", { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }
    $MYSQL_CONF = getMySQLConfFile();
    `cp -pr $MYSQL_CONF $TEMP_CENTRAL_ETC_DIR/mysql/`;
    if ($? ne 0) {
        print STDERR "Unable to copy MySQL configuration file\n";
    }

    # PHP.ini
    mkpath($TEMP_CENTRAL_ETC_DIR . "/php", { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }
    my @tab_php_ini = getPHPConfFile();
    foreach my $file (@tab_php_ini) {
        my $file_dest = $file;
        $file_dest =~ s/\//_/g;
        `cp -p $file $TEMP_CENTRAL_ETC_DIR/php/$file_dest`;
        if ($? ne 0) {
            print STDERR "Unable to copy PHP configuration file\n";
        }
    }

    #####################
    # Get Centreon logs #
    #####################
    # This backup is crazy ! We backup system logs, it's not a good choice to backup them like that.
    mkpath($TEMP_CENTRAL_LOG_DIR, { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }

    # Try to Centreon logs directory
    my $centreon_log_path = "";
    my $dbh = DBI->connect("DBI:mysql:database=" . $mysql_database_oreon . ";host=" . $mysql_host . ";port=" . $mysql_port, $mysql_user, $mysql_passwd, { 'RaiseError' => 0, 'PrintError' => 0 });
    if (!$dbh) {
        print STDERR sprintf("Couldn't connect: %s", $DBI::errstr) . "\n";
    }

    my $sth = $dbh->prepare("SELECT value FROM options WHERE `key` LIKE 'debug_path';");
    if (!$sth) {
        print STDERR "Error: " . $dbh->errstr . "\n";
    }

    if (!$sth->execute()) {
        $sth = $dbh->prepare("SELECT debug_path FROM general_opt;");
        if (!$sth) {
            print STDERR "Error: " . $dbh->errstr . "\n";
        }
        if (!$sth->execute()) {
            print STDERR "Error: " . $dbh->errstr . "\n";
        } else {
            $centreon_log_path = $sth->fetchrow_array();
        }
    } else {
        $centreon_log_path = $sth->fetchrow_array();
    }
    $sth->finish();

    if ($centreon_log_path =~ /^$/) {
        print STDERR "Unable to get Centreon logs directory from database\n";
    } else {
        $centreon_log_path =~ s/\/$//;
        `cp -pr $centreon_log_path/ $TEMP_CENTRAL_LOG_DIR/`;
        if ($? ne 0) {
            print STDERR "Unable to copy Centreon logs files\n";
        }
    }

    ################
    # Licences     #
    ################
    # Centreon licences
    mkpath($TEMP_CENTRAL_LIC_DIR, { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }

    find(\&getLicFile, $CENTREONDIR . "/" . $CENTREON_MODULES_PATH);

    foreach my $licfile ( @licfiles ) {
        my $origFile = $licfile;
        my $path = $CENTREONDIR . "/" . $CENTREON_MODULES_PATH;
        $path =~ s/\//\\\//g;
        $licfile =~ s/$path//;
        my $tempLicDir = $TEMP_CENTRAL_LIC_DIR . dirname($licfile);
        mkpath($tempLicDir, { mode => 0755, error => \my $err_list });
        if (@$err_list) {
            for my $diag (@$err_list) {
                my ($file, $message) = %$diag;
                if ($file eq '') {
                    print STDERR "Unable to create temporary directories because: " . $message . "\n";
                } else {
                    print STDERR "Problem with file  " . $file  .": " . $message . "\n";
                }
            }
        }
        `cp -pr $origFile $tempLicDir`;
        if ($? ne 0) {
            print STDERR "Unable to copy Centreon configuration files\n";
        }
    }

    ################
    # Make archive #
    ################
    chdir($TEMP_DIR);
    `$BIN_TAR -czf $today-central.tar.gz central`;
    move("$today-central.tar.gz", "central/$today-central.tar.gz");
    if ($? ne 0) {
        print STDERR "Unable to make tar of backup\n";
    }

    # Export archives
    exportBackup(1);
    move($TEMP_CENTRAL_DIR . "/" . $today . "-central.tar.gz", $BACKUP_DIR . "/" . $today . "-central.tar.gz");

    # Remove all temp directory
    chdir;
    rmtree($TEMP_CENTRAL_DIR, { mode => 0755, error => \my $err_list });
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to remove temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem unlinking " . $file . ": " . $message . "\n";
            }
        }
    }

    my ($tsec, $tmin, $thour, $tmday, $tmon, $tyear, $twday, $tyday, $tisdst) = localtime(time);
    print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900 + $tyear), ($tmon + 1), $tmday, $thour, $tmin, $tsec) . "] Finish central backup process\n";
}

sub monitoringengineBackup() {
    my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
    my $today = sprintf("%d-%02d-%02d",(1900+$year),($mon+1),$mday);
    print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900+$year), ($mon+1), $mday, $hour, $min, $sec) . "] Start monitoring engine backup process\n";

    # create path
    mkpath($TEMP_CENTRAL_DIR, {mode => 0755, error => \my $err_list});

    my $sth2 = $dbh->prepare("SELECT n.nagios_name, n.cfg_dir, n.log_file, ns.* FROM nagios_server ns, cfg_nagios n WHERE ns.id = n.nagios_server_id AND n.nagios_activate = '1' AND ns.localhost = '1';");
    if (!$sth2->execute()) {
        print STDERR "Error: " . $dbh->errstr . "\n";
        return 1;
    }

    my $nagios_server;
    my $poller_name;
    if ($sth2->rows == 0) {
        print STDERR "Unable to get informations about poller form " . $mysql_database_oreon . " database\n";
        return 1;
    } else {
        $nagios_server = $sth2->fetchrow_hashref;
        $nagios_server->{log_archive_path} = $nagios_server->{log_file};
        $nagios_server->{log_archive_path} =~ s/(.*)\/.*/$1\/archives\//;
        $poller_name = $nagios_server->{nagios_name};
        $sth2->finish();
    }

    ###########
    # Plugins #
    ###########
    mkpath($TEMP_CENTRAL_DIR."/plugins", {mode => 0755, error => \my $err_list});
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }
    my $plugins_dir = "/usr/lib64/nagios/plugins";
    if ($plugins_dir ne "") {
        `cp -pr $plugins_dir/* $TEMP_CENTRAL_DIR/plugins/`;
        if ($? != 0) {
            print STDERR "Unable to copy plugins\n";
        }
    }

    ########
    # Logs #
    ########
    mkpath($TEMP_CENTRAL_DIR."/logs", {mode => 0755, error => \my $err_list});
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }

    copy($nagios_server->{log_file}, ($TEMP_CENTRAL_DIR."/logs/centengine.log"));
    my $logs_archive_directory = substr($nagios_server->{log_archive_path}, 0, rindex($nagios_server->{log_archive_path}, "/"));
    mkpath($TEMP_CENTRAL_DIR."/logs/archives", {mode => 0755, error => \my $err_list});
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }
    `cp -p $logs_archive_directory/* $TEMP_CENTRAL_DIR/logs/archives/`;
    if ($? != 0) {
        print STDERR "Unable to copy monitoring engine logs archives\n";
    }

    #################
    # Configuration #
    #################
    mkpath($TEMP_CENTRAL_DIR."/etc/centreon-engine", {mode => 0755, error => \my $err_list});
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: ". $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message."\n";
            }
        }
    }
    `cp -pr $nagios_server->{cfg_dir}/* $TEMP_CENTRAL_DIR/etc/centreon-engine`;
    if ($? != 0) {
        print STDERR "Unable to copy Monitoring Engine configuration files\n";
    }

    #########################
    # Script initialisation #
    #########################
    if (defined($nagios_server->{init_script}) && $nagios_server->{init_script} ne '') {
        copy($nagios_server->{init_script}, ($TEMP_CENTRAL_DIR . "/init_d_centengine"));
    }

    ###############
    # Sudo rights #
    ###############
    copy("/etc/sudoers", ($TEMP_CENTRAL_DIR . "/etc_sudoers"));

    ############
    # SSH keys #
    ############
    mkpath($TEMP_CENTRAL_DIR."/ssh", {mode => 0755, error => \my $err_list});
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }
    my $centreon_home = "/var/spool/centreon";
    if (-d "$centreon_home/.ssh" ) {
        `cp -pr $centreon_home/.ssh/* $TEMP_CENTRAL_DIR/ssh`;
    } else {
        print STDERR "No SSH keys for Centreon\n";
    }

    mkpath($TEMP_CENTRAL_DIR."/ssh-centreon-engine", {mode => 0755, error => \my $err_list});
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to create temporary directories because: " . $message . "\n";
            } else {
                    print STDERR "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }

    my $centreonengine_home = "/var/lib/centreon-engine/";
    if (-d "$centreonengine_home/.ssh") {
        `cp -pr $centreonengine_home/.ssh/* $TEMP_CENTRAL_DIR/ssh-centreon-engine/`;
    } else {
        print STDERR "No ssh keys for Centreon Engine\n";
    }

    ##################
    # Make archives #
    #################
    chdir($TEMP_DIR);
    `$BIN_TAR -czf $today-centreon-engine.tar.gz central`;
    move("$today-centreon-engine.tar.gz", "central/$today-centreon-engine.tar.gz");
    if ($? ne 0) {
        print STDERR "Unable to make tar of backup\n";
    }

    ###################
    # Export archives #
    ###################
    exportBackup(1);
    move ($TEMP_CENTRAL_DIR . "/" . $today . "-centreon-engine.tar.gz", $BACKUP_DIR . "/" . $today . "-centreon-engine.tar.gz");

    # Remove all temp directory
    chdir;
    rmtree($TEMP_DIR, {mode => 0755, error => \my $err_list});
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print STDERR "Unable to remove temporary directories because: " . $message . "\n";
            } else {
                print STDERR "Problem unlinking " . $file . ": " . $message . "\n";
            }
        }
    }

    $sth->finish();
    $dbh->disconnect;

    my ($tsec,$tmin,$thour,$tmday,$tmon,$tyear,$twday,$tyday,$tisdst) = localtime(time);
    print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900+$tyear), ($tmon+1), $tmday, $thour, $tmin, $tsec) . "] Finish monitoring engine backup process\n";
}

################
# Main program #
################

getbinaries();

if ($BACKUP_CONFIGURATION_FILES == '1') {
    centralBackup();
    monitoringengineBackup();
}

if ($BACKUP_DATABASE_CENTREON == '1' || $BACKUP_DATABASE_CENTREON_STORAGE == '1') {
    databasesBackup();
}

cleanOldBackup();
