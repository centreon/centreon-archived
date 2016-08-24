#!/usr/bin/perl
################################################################################
# Copyright 2005-2016 Centreon
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
use vars qw($BACKUP_DATABASE_CENTREON $BACKUP_DATABASE_CENTREON_STORAGE $BACKUP_DATABASE_TYPE $BACKUP_DATABASE_LEVEL $BACKUP_RETENTION);
use vars qw($BACKUP_CONFIGURATION_FILES $MYSQL_CONF $ZEND_CONF);
use vars qw($TEMP_DB_DIR $TEMP_CENTRAL_DIR $TEMP_CENTRAL_ETC_DIR $TEMP_CENTRAL_INIT_DIR $TEMP_CENTRAL_CRON_DIR $TEMP_CENTRAL_LOG_DIR $TEMP_CENTRAL_BIN_DIR $TEMP_CENTRAL_LIC_DIR $CENTREON_MODULES_PATH $TEMP_POLLERS $DISTANT_POLLER_BACKUP_DIR);
use vars qw($BIN_GZIP $BIN_TAR);
use vars qw($scp_enabled $scp_user $scp_host $scp_directory);

sub print_help();
sub print_usage();
sub trim($);

my $CENTREON_ETC = '@CENTREON_ETC@';
my @licfiles;

# Require DB configuration files
if (-e $CENTREON_ETC.'/conf.pm'){
	require $CENTREON_ETC.'/conf.pm';
}elsif (-e $CENTREON_ETC.'/centreon-config.pm'){
	require $CENTREON_ETC.'/centreon-config.pm';
}

if (defined($mysql_host)) {
    if ($mysql_host =~ /\:/) {
        my @tab = split(/\:/, $mysql_host);
        if (defined($tab[0])) {
            $mysql_host = $tab[0];
        }
        if (defined($tab[1])) {
            $mysql_port = $tab[1];
        }  else {
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

##########################################
# Get backup configuration from database #
##########################################

my $dbh = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host.";port=".$mysql_port, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0});
if (!$dbh) {
    print "Couldn't connect: " . $DBI::errstr . "\n";
}

my $sth = $dbh->prepare("SELECT * FROM options WHERE options.key LIKE 'backup_%';");
if (!$sth || !$sth->execute()) {
    print "Error: " . $dbh->errstr . "\n";
}

my $backupOptions = $sth->fetchall_hashref('key');

$BACKUP_ENABLED = $backupOptions->{'backup_enabled'}->{'value'};
if ($BACKUP_ENABLED != '1') {
    exit 0;
}

$CENTREON_MODULES_PATH = "www/modules";

$BACKUP_DIR = $backupOptions->{'backup_backup_directory'}->{'value'};
$TEMP_DIR = $backupOptions->{'backup_tmp_directory'}->{'value'};
$TEMP_DB_DIR = $TEMP_DIR."/db";
$TEMP_CENTRAL_DIR = $TEMP_DIR."/central";
$TEMP_CENTRAL_ETC_DIR = $TEMP_CENTRAL_DIR."/etc";
$TEMP_CENTRAL_INIT_DIR = $TEMP_CENTRAL_DIR."/init";
$TEMP_CENTRAL_CRON_DIR = $TEMP_CENTRAL_DIR."/cron";
$TEMP_CENTRAL_LOG_DIR = $TEMP_CENTRAL_DIR."/log";
$TEMP_CENTRAL_BIN_DIR = $TEMP_CENTRAL_DIR."/bin";
$TEMP_CENTRAL_LIC_DIR = $TEMP_CENTRAL_DIR."/lic";

$BACKUP_DATABASE_CENTREON = $backupOptions->{'backup_database_centreon'}->{'value'};
$BACKUP_DATABASE_CENTREON_STORAGE = $backupOptions->{'backup_database_centreon_storage'}->{'value'};
$BACKUP_DATABASE_TYPE = $backupOptions->{'backup_database_type'}->{'value'};
$BACKUP_DATABASE_LEVEL = $backupOptions->{'backup_database_level'}->{'value'};
$BACKUP_RETENTION = $backupOptions->{'backup_retention'}->{'value'};

$BACKUP_CONFIGURATION_FILES = $backupOptions->{'backup_configuration_files'}->{'value'};
$MYSQL_CONF = $backupOptions->{'backup_mysql_conf'}->{'value'};
$ZEND_CONF = $backupOptions->{'backup_zend_conf'}->{'value'};

$BIN_GZIP = "";
$BIN_TAR = "";

if ( -e $BACKUP_DIR) {
    if (! -w $BACKUP_DIR) {
        print "Backup directory \"$BACKUP_DIR\" is not writable.\n";
        exit 1;
    }
}
else {
    print "Backup directory \"$BACKUP_DIR\" does not exist.\n";
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

sub print_usage () {
    		print "Usage: ";
    		print $PROGNAME."\n";
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

sub print_help () {
		print "##############################################\n";
		print "#    Copyright (c) 2005-2014 Centreon        #\n";
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

	if ( $BIN_GZIP =~ /no .* in/ ) {
		print "Unable to get gzip binary\n";
	}

	$BIN_TAR = `which tar`;
	$BIN_TAR = trim($BIN_TAR);

	if ( $BIN_TAR =~ /no .* in/ ) {
		print "Unable to get tar binary\n";
	}
}

sub exportBackup() {
    if ($scp_enabled == '1' && $scp_host ne '' && $scp_directory ne '' && $scp_user ne '') {
		if ($BACKUP_DATABASE_CENTREON == '1' || $BACKUP_DATABASE_CENTREON_STORAGE == '1') {
			chdir($TEMP_DB_DIR);
			`scp *.gz $scp_user\@$scp_host:$scp_directory/`;
            if ($? ne 0) {
                print "Error when trying to export files of " . $TEMP_DB_DIR . "\n";
            } else {
                print "All files were copied with success using SCP on ".$scp_user."@".$scp_host.":".$scp_directory."\n";
            }
		} elsif ($BACKUP_CONFIGURATION_FILES == '1') {
			chdir($TEMP_CENTRAL_DIR);
			`scp *.gz $scp_user\@$scp_host:$scp_directory/`;
            if ($? ne 0) {
                print "Error when trying to export files of " . $TEMP_CENTRAL_DIR . "\n";
            } else {
                print "All files were copied with success using SCP on ".$scp_user."@".$scp_host.":".$scp_directory."\n";
            }
		}
	}
}

sub cleanOldBackup() {
	my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time-($BACKUP_RETENTION*3600*24));
	my $max_backup_age = sprintf('%04d-%02d-%02d',(1900+$year),($mon+1),$mday);

	my $dir = IO::Dir->new($BACKUP_DIR);

	if(!defined($dir)) {
		print "Unable to get list of backup files from: ".$BACKUP_DIR."\n";
	} else {
		chdir($BACKUP_DIR);
		while(defined($_ = $dir->read)) {
			if ($_ =~ m/^(\d{4}\-\d{2}\-\d{2}).*/) {
				if ($1 le $max_backup_age)
				{
					print "Delete file: ".$_."\n";
					unlink $_;
				}
			}
		}
		undef($dir);
		#print_log "End of backup ".$_."\n";
	}
}

###############################################
# Functions to get location of specifiec file #
###############################################

sub getApacheDirectory() {
	if ( -d '/etc/httpd/conf.d' ) {
		return '/etc/httpd/conf.d';
	} else {
		#print_log('Central Backup', 'Unable to get Apache conf directory', 'CRITICAL');
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
		print "Unable to get Mysql configuration\n";
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
	my $result = `php -r 'echo php_ini_loaded_file();'`;
	push(@tab_php_ini, trim($result));

	# Apache
	if ( -e '/etc/php.ini') {
		push(@tab_php_ini, '/etc/php.ini');
	} else {
        print "Unable to get PHP configuration\n";
    }

	return @tab_php_ini;
}

sub getZendConfFile() {
	if ( -e '/etc/php.d/zendoptimizer.ini' ) {
		return '/etc/php.d/zendoptimizer.ini';
	} elsif ( -e '/usr/local/Zend/etc/php.ini' ) {
		return '/usr/local/Zend/etc/php.ini';
	}
}

sub getNagiosPluginsdir($$$) {
	my $localhost = shift;
	my $poller_ip = shift;
	my $ssh_port = shift;

	if ($localhost =~ /^1$/) {
		if (-d '/usr/lib/nagios/plugins' ) {
			return '/usr/lib/nagios/plugins/' ;
		}
	} else {
        my $result = `ssh centreon\@$poller_ip -p $ssh_port -C "ls -lah '/usr/lib/nagios/plugins'" | grep -i 'total' | wc -l | bc`;
        if ($result == 1) {
            return '/usr/lib/nagios/plugins';
        }
	}

	return '';
}

# Replace this function by another one
sub getNagiosHomeDir($$$) {
	my $localhost = shift;
	my $poller_ip = shift;
	my $ssh_port = shift;

    if ($localhost =~ /^1$/) {
        my $nagios_home = `cat /etc/passwd | grep centreon-engine | cut -d":" -f6`;

        if ($nagios_home ne "") {
            return $nagios_home;
        } elsif ( -d "/home/centreon-engine" ) {
            return "/home/centreon-engine";
        } else {
            return "";
        }
    } else {
        my $nagios_home = `ssh centreon\@$poller_ip -p $ssh_port -C "cat /etc/passwd | grep centreon-engine | cut -d":" -f6`;

        if ($nagios_home ne "") {
            return $nagios_home;
        } else {
            my $result = `ssh centreon\@$poller_ip -p $ssh_port -C "ls -lah '/home/centreon-engine'" | grep -i 'total' | wc -l | bc`;
            if ($result == 1) {
                return "/home/centreon-engine";
            }
        }
    }
}

############################
# Functions to make backup #
############################

sub databasesBackup() {
	my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
	my $today = sprintf("%d-%02d-%02d",(1900+$year),($mon+1),$mday);

	print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900+$year), ($mon+1), $mday, $hour, $min, $sec) . "] Start database backup processus\n";

	# Create path
	mkpath($TEMP_DB_DIR, {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Database BACKUP: Unable to create temporary directories because: ".$message."\n";
			} else {
				print "Database BACKUP: Problem with file  " . $file . ": " . $message . "\n";
			}
		}
	}

    # Do LVM snapshot backup or fall into degraded mode with mysqldump
    my $partial_opt = '';
    if ($BACKUP_DATABASE_LEVEL == '0') {
        $partial_opt = '-p';
    }

    if ($BACKUP_DATABASE_TYPE == '1') {
        # Do LVM snapshot backup or fall into degraded mode with mysqldump
        my $partial_opt = '';
        if ($BACKUP_DATABASE_LEVEL == '0') {
            $partial_opt = '-p';
        }
        print "Dumping Db with LVM snapshot\n";
        `$centreon_config->{CentreonDir}bin/centreon-backup-mysql.sh -b $TEMP_DB_DIR -d $today $partial_opt`;
        if ($? ne 0) {
            print "Cannot backup with LVM snpashot. Maybe you can try with mysqldump\n";
        }
    } else {
        my $mysql_database_ndo;
        my $dbh = DBI->connect("DBI:mysql:database=" . $mysql_database_oreon . ";host=".$mysql_host.";port=".$mysql_port, $mysql_user, $mysql_passwd,{'RaiseError' => 0, 'PrintError' => 0});
        if (!$dbh) {
            print sprintf("Couldn't connect: %s", $DBI::errstr) . "\n";
        }

        my $file = "";

    	# Make archives from databases dump
    	if ($BACKUP_DATABASE_CENTREON == '1') {
    	    $file = $TEMP_DB_DIR."/".$today."-centreon.sql.gz";
    	    `mysqldump -u $mysql_user -h $mysql_host -p$mysql_passwd $mysql_database_oreon | $BIN_GZIP  > $file`;
    	    if ($? ne 0) {
    	        print "Unable to dump database: " . $mysql_database_oreon . "\n";
    	    } else {
    		    print "Get mysqldump of \"" . $mysql_database_oreon . "\" database\n";
    	    }
    	}


    	# Make centreon_storage dump only if backup type is full
    	if ($BACKUP_DATABASE_CENTREON_STORAGE == '1') {

            # Check if process already exist
            my $process_number = `ps aux | grep -v grep |grep "centstorage" | wc -l | bc`;

            if ($process_number == 0) {
                $file = $TEMP_DB_DIR."/".$today."-centreon_storage.sql.gz";
                `mysqldump -u $mysql_user -h $mysql_host -p$mysql_passwd $mysql_database_ods | $BIN_GZIP  > $file`;
                if ($? ne 0) {
                    print "Unable to dump database: " . $mysql_database_ods . "\n";
                } else {
                    print "Get mysqldump of \"".$mysql_database_ods."\" database\n";
                }
            }
        }
        $dbh->disconnect;
    }
    # End of Db dump

	# Copy archives to local dir
	mkpath($BACKUP_DIR, {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to create backup directory because: " . $message . "\n";
			} else {
				print "Problem with " . $file . ": " . $message . "\n";
			}
		}
	}

	# Export archives
	exportBackup();
    if (-r $TEMP_DB_DIR."/".$today."-mysql.tar.gz") {
        move($TEMP_DB_DIR."/".$today."-mysql.tar.gz", $BACKUP_DIR."/".$today."-mysql.tar.gz");
    } else {
    	move($TEMP_DB_DIR."/".$today."-centreon.sql.gz", $BACKUP_DIR."/".$today."-centreon.sql.gz");
    	move($TEMP_DB_DIR."/".$today."-centreon_storage.sql.gz", $BACKUP_DIR."/".$today."-centreon_storage.sql.gz");
    }

	# Delete temporary directoriess
	chdir;
	rmtree($TEMP_DB_DIR, {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				#print_log("Database BACKUP", "Unable to remove temporary directories because: ".$message, "CRITICAL");
			} else {
				#print_log("Database BACKUP", "Problem unlinking ".$file.": ".$message, "CRITICAL");
			}
		}
	}

	my ($tsec,$tmin,$thour,$tmday,$tmon,$tyear,$twday,$tyday,$tisdst) = localtime(time);
	print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900+$tyear), ($tmon+1), $tmday, $thour, $tmin, $tsec) . "] Finish database backup processus\n";
}

sub centralBackup() {
	my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
	my $today = sprintf("%d-%02d-%02d",(1900+$year),($mon+1),$mday);
	print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900+$year), ($mon+1), $mday, $hour, $min, $sec) . "] Start central backup processus\n";

	###################################
	# Get configuration program files #
	###################################

	# Create path
	mkpath($TEMP_CENTRAL_ETC_DIR, {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to create temporary directories because: " . $message . "\n";
			} else {
				print "Problem with file  " . $file . ": " . $message . "\n";
			}
		}
	}

	# Apache or httpd
	my $ApacheConfdir = getApacheDirectory();
	mkpath($TEMP_CENTRAL_ETC_DIR."/apache", {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to create temporary directories because: " . $message . "\n";
			} else {
				print "Problem with file  " . $file . ": " . $message . "\n";
			}
		}
	}
	`cp -pr $ApacheConfdir* $TEMP_CENTRAL_ETC_DIR/apache/`;
	if ($? ne 0) {
		print "Unable to copy Apache configuration files\n";
	}

	# Centreon etc
	mkpath($TEMP_CENTRAL_ETC_DIR."/centreon", {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to create temporary directories because: ".$message . "\n";
			} else {
				print "Problem with file  ".$file.": ".$message. "\n";
			}
		}
	}
	`cp -pr $CENTREON_ETC/* $TEMP_CENTRAL_ETC_DIR/centreon/`;
	if ($? ne 0) {
		print "Unable to copy Centreon configuration files\n";
	}

	# Centreon Broker etc
	my $cb_path = "/etc/centreon-broker";
	mkpath($TEMP_CENTRAL_ETC_DIR."/centreon-broker", {mode => 0755, error => \my $err_list});
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                 print "Unable to create temporary directories because: ".$message . "\n";
            } else {
                print "Problem with file  ".$file.": ".$message . "\n";
            }
        }
    }
	`cp -r $cb_path $TEMP_CENTRAL_ETC_DIR"/centreon-broker/"`;

	# SNMP configuration
	mkpath($TEMP_CENTRAL_ETC_DIR."/snmp", {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to create temporary directories because: ".$message . "\n";
			} else {
				print "Problem with file  ".$file.": ".$message . "\n";
			}
		}
	}
	`cp -pr /etc/snmp/* $TEMP_CENTRAL_ETC_DIR/snmp/`;
	if ($? ne 0) {
		print "Unable to copy SNMP configuration files\n";
	}

	# MySQL configuration
	mkpath($TEMP_CENTRAL_ETC_DIR."/mysql", {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to create temporary directories because: ".$message . "\n";
			} else {
				print "Problem with file  ".$file.": ".$message . "\n";
			}
		}
	}
	$MYSQL_CONF = getMySQLConfFile();
	`cp -pr $MYSQL_CONF $TEMP_CENTRAL_ETC_DIR/mysql/`;
	if ($? ne 0) {
		print "Unable to copy MySQL configuration file\n";
	}

	# PHP.ini
	mkpath($TEMP_CENTRAL_ETC_DIR."/php", {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to create temporary directories because: " . $message . "\n";
			} else {
				print "Problem with file  ".$file.": ".$message . "\n";
			}
		}
	}
	my @tab_php_ini = getPHPConfFile();
	foreach my $file (@tab_php_ini) {
		my $file_dest = $file;
		$file_dest  =~ s/\//_/g;
		`cp -p $file $TEMP_CENTRAL_ETC_DIR/php/$file_dest`;
		if ($? ne 0) {
			print "Unable to copy PHP configuration file\n";
		}
	}

	##########################################
	# Get Zend binary and configuration file #
	##########################################
	# Zend ini
	mkpath($TEMP_CENTRAL_ETC_DIR."/zend", {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to create temporary directories because: " . $message . "\n";
			} else {
				print "Problem with file  " . $file . ": " . $message . "\n";
			}
		}
	}
	if (!defined($ZEND_CONF)){
		$ZEND_CONF = getZendConfFile();
	}
	`cp -pr $ZEND_CONF $TEMP_CENTRAL_ETC_DIR/zend/`;
	if ($? ne 0) {
		print "Unable to copy Zend configuration file\n";
	}

	# zend binary
	mkpath($TEMP_CENTRAL_ETC_DIR."/zend_bin", {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to create temporary directories because: " . $message . "\n";
			} else {
				print "Problem with file  " . $file . ": " . $message . "\n";
			}
		}
	}

	#####################
	# Get Centreon logs #
	#####################
    # This backup is crazy ! We backup system logs, it's not a good choice to backup them like that.
	mkpath($TEMP_CENTRAL_LOG_DIR, {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				 print "Unable to create temporary directories because: " . $message . "\n";
			} else {
				 print "Problem with file  " . $file . ": " . $message . "\n";
			}
		}
	}

	# Try to Centreon logs directory
	my $centreon_log_path = "";
	my $dbh = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host.";port=".$mysql_port, $mysql_user, $mysql_passwd,{'RaiseError' => 0, 'PrintError' => 0});
	if (!$dbh) {
		print sprintf("Couldn't connect: %s", $DBI::errstr) . "\n";
	}

	my $sth = $dbh->prepare("SELECT value FROM options WHERE `key` LIKE 'debug_path';");
	if (!$sth) {
		print "Error: " . $dbh->errstr . "\n";
	}

	if (!$sth->execute()) {
		$sth = $dbh->prepare("SELECT debug_path FROM general_opt;");
		if (!$sth) {
			print "Error: " . $dbh->errstr . "\n";
		}
		if (!$sth->execute()) {
			print "Error: " . $dbh->errstr . "\n";
		} else {
			$centreon_log_path = $sth->fetchrow_array();
		}
	} else {
		$centreon_log_path = $sth->fetchrow_array();
	}
	$sth->finish();

	if ($centreon_log_path =~/^$/) {
		print "Unable to get Centreon logs directory from database\n";
	} else {
		$centreon_log_path =~ s/\/$//;
		`cp -pr $centreon_log_path/ $TEMP_CENTRAL_LOG_DIR/`;
		if ($? ne 0) {
			print "Unable to copy Centreon logs files\n";
		}
	}

	################
	# Licences     #
	################
	# Centreon licences
    mkpath($TEMP_CENTRAL_LIC_DIR, {mode => 0755, error => \my $err_list});
    if (@$err_list) {
        for my $diag (@$err_list) {
            my ($file, $message) = %$diag;
            if ($file eq '') {
                print "Unable to create temporary directories because: " . $message . "\n";
            } else {
                print "Problem with file  " . $file . ": " . $message . "\n";
            }
        }
    }

    print "Getting licence files\n";
	find(\&getLicFile, $centreon_config->{CentreonDir}.$CENTREON_MODULES_PATH);

    foreach my $licfile ( @licfiles ) {
        print "Getting licence file: " . $licfile . "\n";
        my $origFile = $licfile;
        my $path = $centreon_config->{CentreonDir}.$CENTREON_MODULES_PATH;
        $path =~ s/\//\\\//g;
        $licfile =~ s/$path//;
        my $tempLicDir = $TEMP_CENTRAL_LIC_DIR.dirname($licfile);
        mkpath($tempLicDir, {mode => 0755, error => \my $err_list});
        if (@$err_list) {
            for my $diag (@$err_list) {
                my ($file, $message) = %$diag;
                if ($file eq '') {
                    print "Unable to create temporary directories because: " . $message . "\n";
                } else {
                    print "Problem with file  " . $file  .": " . $message . "\n";
                }
            }
        }
        `cp -pr $origFile $tempLicDir`;
        if ($? ne 0) {
            print "Unable to copy Centreon configuration files\n";
        }
    }

	################
	# Make archive #
	################
       `cd $TEMP_DIR && cd .. && tar -czf $BACKUP_DIR/$today-central.tar.gz backup`;
	if ($? ne 0) {
		print "Unable to make tar of backup\n";
	}

	# Export archives
	exportBackup();
	move ($TEMP_DIR."/".$today."-central.tar.gz", $BACKUP_DIR."/".$today."-central.tar.gz");

	# Remove all temp directory
	chdir;
	rmtree($TEMP_CENTRAL_DIR, {mode => 0755, error => \my $err_list});
	if (@$err_list) {
		for my $diag (@$err_list) {
			my ($file, $message) = %$diag;
			if ($file eq '') {
				print "Unable to remove temporary directories because: " . $message . "\n";
			} else {
				print "Problem unlinking " . $file . ": " . $message . "\n";
			}
		}
	}

	my ($tsec,$tmin,$thour,$tmday,$tmon,$tyear,$twday,$tyday,$tisdst) = localtime(time);
	print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900+$tyear), ($tmon+1), $tmday, $thour, $tmin, $tsec) . "] Finish central backup processus\n";
}

sub monitoringengineBackup() {
	my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
	my $today = sprintf("%d-%02d-%02d",(1900+$year),($mon+1),$mday);
	print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900+$year), ($mon+1), $mday, $hour, $min, $sec) . "] Start monitoring engine backup processus\n";

	my $query = "SELECT * FROM cfg_nagios WHERE nagios_activate = '1'";

	my $dbh = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host.";port=".$mysql_port, $mysql_user, $mysql_passwd,{'RaiseError' => 0, 'PrintError' => 0});
	if (!$dbh) {
		print sprintf("Couldn't connect: %s", $DBI::errstr) . "\n";
	}

	my $sth = $dbh->prepare($query);
	if (!$sth) {
		print "Error: " . $dbh->errstr . "\n";
	}

	if (!$sth->execute()) {
		print "Error: " . $dbh->errstr . "\n";
	}

	if ($sth->rows == 0) {
		print "Unable to get informations about poller form ".$mysql_database_oreon." database" . "\n";
	} else {
		# Create path
		mkpath($TEMP_POLLERS, {mode => 0755, error => \my $err_list});
		if (@$err_list) {
			for my $diag (@$err_list) {
				my ($file, $message) = %$diag;
				if ($file eq '') {
					print "Unable to create temporary directories because: " . $message . "\n";
				} else {
					print "Problem with file  ".$file.": " . $message . "\n";
				}
			}
		}

		while (my $poller = $sth->fetchrow_hashref) {
			my $poller_name = $poller->{nagios_name};
			print "Start backup for poller: " . $poller_name . "\n";

			#########################################################
			# Get information about poller from nagios_server table #
			#########################################################

			my $sth2 = $dbh->prepare("SELECT * FROM nagios_server WHERE id = '".$poller->{nagios_server_id}."';");
			if (!$sth2->execute()) {
				print "Error: " . $dbh->errstr . "\n";
			}

			my $nagios_server;
			if ($sth2->rows == 0) {
				print "Unable to get informations about poller form " . $mysql_database_oreon . " database\n";
			} else {
				$nagios_server =$sth2->fetchrow_hashref;
				$sth2->finish();
			}

			# Remove space
			my $poller_name_dir = $poller_name;
			$poller_name_dir =~ s/ /_/g;

			# Create path for specific poller
			my $ACTUAL_POLLER_BCK_DIR = $TEMP_POLLERS."/".$poller_name_dir;
			mkpath($ACTUAL_POLLER_BCK_DIR, {mode => 0755, error => \my $err_list});
			if (@$err_list) {
				for my $diag (@$err_list) {
					my ($file, $message) = %$diag;
					if ($file eq '') {
						print "Unable to create temporary directories because: " . $message . "\n";
					} else {
						print "Problem with file " . $file.": " . $message . "\n";
					}
				}
			}

			if (!defined($nagios_server->{ssh_port}) || $nagios_server->{ssh_port} == "") {
				$nagios_server->{ssh_port} = 22;
			}

			# If localhost
			if ($nagios_server->{localhost} == 1) {

				###########
				# Plugins #
				###########
				mkpath($ACTUAL_POLLER_BCK_DIR."/plugins", {mode => 0755, error => \my $err_list});
				if (@$err_list) {
					for my $diag (@$err_list) {
						my ($file, $message) = %$diag;
						if ($file eq '') {
							print "Unable to create temporary directories because: ".$message . "\n";
						} else {
							print "Problem with file  ".$file.": ".$message . "\n";
						}
					}
				}
				my $plugins_dir = getNagiosPluginsdir(1, $nagios_server->{ns_ip_address}, $nagios_server->{ssh_port});
				if ($plugins_dir ne "") {
					`cp -pr $plugins_dir/* $ACTUAL_POLLER_BCK_DIR/plugins/`;
					if ($? != 0) {
						print "Unable to copy plugins\n";
					}
				}
				########
				# Logs #
				########
				mkpath($ACTUAL_POLLER_BCK_DIR."/logs", {mode => 0755, error => \my $err_list});
				if (@$err_list) {
					for my $diag (@$err_list) {
						my ($file, $message) = %$diag;
						if ($file eq '') {
							print "Unable to create temporary directories because: ".$message . "\n";
						} else {
							print "Problem with file  ".$file.": ".$message . "\n";
						}
					}
				}

				copy($poller->{log_file}, ($ACTUAL_POLLER_BCK_DIR."/logs/centengine.log"));

				my $logs_archive_directory = substr($poller->{log_archive_path}, 0, rindex($poller->{log_archive_path}, "/"));

				mkpath($ACTUAL_POLLER_BCK_DIR."/logs/archives", {mode => 0755, error => \my $err_list});
				if (@$err_list) {
					for my $diag (@$err_list) {
						my ($file, $message) = %$diag;
						if ($file eq '') {
							print "Unable to create temporary directories because: ".$message . "\n";
						} else {
							print "Problem with file  ".$file.": ".$message . "\n";
						}
					}
				}
				`cp -p $logs_archive_directory/* $ACTUAL_POLLER_BCK_DIR/logs/archives/`;
				if ($? != 0) {
					print "Unable to copy monitoring engine logs archives\n";
				}

				#################
				# Configuration #
				#################
				mkpath($ACTUAL_POLLER_BCK_DIR."/etc", {mode => 0755, error => \my $err_list});
				if (@$err_list) {
					for my $diag (@$err_list) {
						my ($file, $message) = %$diag;
						if ($file eq '') {
							print "Unable to create temporary directories because: ".$message . "\n";
						} else {
							print "Problem with file  ".$file.": ".$message."\n";
						}
					}
				}
				`cp -pr $poller->{cfg_dir }/* $ACTUAL_POLLER_BCK_DIR/etc/`;
				if ($? != 0) {
					print "Unable to copy Monitoring Engine configuration files\n";
				}

				#########################
				# Script initialisation #
				#########################
				copy($nagios_server->{init_script}, ($ACTUAL_POLLER_BCK_DIR."/init_d_centengine"));

				###############
				# Sudo rights #
				###############
				copy("/etc/sudoers", ($ACTUAL_POLLER_BCK_DIR."/etc_sudoers"));

				############
				# SSH keys #
				############
				mkpath($ACTUAL_POLLER_BCK_DIR."/ssh", {mode => 0755, error => \my $err_list});
				if (@$err_list) {
					for my $diag (@$err_list) {
						my ($file, $message) = %$diag;
						if ($file eq '') {
							print "Unable to create temporary directories because: " . $message . "\n";
						} else {
							print "Problem with file  ".$file.": ".$message . "\n";
						}
					}
				}
                my $centreon_home = "/var/spool/centreon";
                if (-d "$centreon_home/.ssh" ) {
                    `cp -pr $centreon_home/.ssh/* $ACTUAL_POLLER_BCK_DIR/ssh`;
                } else {
                    print "No SSH keys for Centreon\n";
                }

                mkpath($ACTUAL_POLLER_BCK_DIR."/ssh-centreon-engine", {mode => 0755, error => \my $err_list});
                if (@$err_list) {
                    for my $diag (@$err_list) {
                        my ($file, $message) = %$diag;
                        if ($file eq '') {
                            print "Unable to create temporary directories because: " . $message . "\n";
                        } else {
                            print "Problem with file  " . $file . ": " . $message . "\n";
                        }
                    }
                }

                my $centreonengine_home = "/var/lib/centreon-engine/";
                if (-d "$centreonengine_home/.ssh") {
                    `cp -pr $centreonengine_home/.ssh/* $ACTUAL_POLLER_BCK_DIR/ssh-centreon-engine/`;
                } else {
                    print "No ssh keys for Monitoring Engine\n";
                }

				#################
				# Make archives #
				#################
				`cd $TEMP_DIR && cd .. && tar -czf $BACKUP_DIR/$today-Monitoring-Engine.tar.gz backup`;
                if ($? ne 0) {
                    print "Unable to make tar of backup\n";
                }

                ###################
                # Export archives #
                ###################
                exportBackup();
                move ($TEMP_POLLERS."/".$today."-Monitoring-Engine-".$nagios_server->{name}.".tar.gz", $BACKUP_DIR."/".$today."-Monitoring-Engine-".$nagios_server->{name}.".tar.gz");
			} else {
				#########################
				# Get archives
				#########################
				mkpath($TEMP_POLLERS."/", 0);
                `su -l centreon -c 'scp -P $nagios_server->{ssh_port} $nagios_server->{ns_ip_address}:$DISTANT_POLLER_BACKUP_DIR/$today.tar.gz $BACKUP_DIR/$today-Monitoring-Engine-$nagios_server->{name}.tar.gz'`;
                if ($? != 0) {
                    print "Unable to get poller backup archive for poller " . $nagios_server->{name} . "\n";
				}
		    }

			# Remove all temp directory
			chdir;
			rmtree($TEMP_POLLERS, {mode => 0755, error => \my $err_list});
			if (@$err_list) {
				for my $diag (@$err_list) {
					my ($file, $message) = %$diag;
					if ($file eq '') {
						print "Unable to remove temporary directories because: " . $message . "\n";
					} else {
						print "Problem unlinking " . $file . ": " . $message . "\n";
					}
				}
			}
			print "finish backup for poller: ".$poller_name."\n";
		}
	}

	$sth->finish();
	$dbh->disconnect;

	my ($tsec,$tmin,$thour,$tmday,$tmon,$tyear,$twday,$tyday,$tisdst) = localtime(time);
	print "[" . sprintf("%4d-%02d-%02d %02d:%02d:%02d", (1900+$tyear), ($tmon+1), $tmday, $thour, $tmin, $tsec) . "] Finish monitoring engine backup processus\n";
}

################
# Main program #
################

getbinaries();

#if (defined($OPTION{'preexec'})) {
#	my $preexec_command = $OPTION{'preexec'};
#	print "PREEXEC " . $preexec_command . "\n";
#	`$preexec_command`;
#}

if ($BACKUP_DATABASE_CENTREON == '1' || $BACKUP_DATABASE_CENTREON_STORAGE == '1') {
    databasesBackup();
}

if ($BACKUP_CONFIGURATION_FILES == '1') {
    centralBackup();
    monitoringengineBackup();
}

#if (defined($OPTION{'postexec'})) {
#	my $postexec_command = $OPTION{'postexec'};
#	print "POSTEXEC " . $postexec_command . "\n";
#        `$postexec_command`;
#}

cleanOldBackup();
