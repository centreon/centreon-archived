#! /usr/bin/perl -w
###################################################################
# Oreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/gpl.txt
#
# Developped by : Julien Mathis - jmathis@merethis.com
#
###################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
#    For information : contact@oreon-project.org
####################################################################

use strict;
use warnings;
use DBI;
use File::stat;

use vars qw($mysql_user $mysql_passwd $mysql_host $mysql_database_oreon $mysql_database_ods);

my $installedPath = "@OREON_PATH@";

require $installedPath."ODS/etc/conf.pm";

## Init Date
my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = gmtime(time);

# Init MySQL Connexion
my $dbh = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 1});

# Get conf Data
my $sth = $dbh->prepare("SELECT archive_retention, nagios_log_file  FROM config");
if (!$sth->execute) {die "Error:" . $sth->errstr . "\n";}
my $data = $sth->fetchrow_hashref();

my $retention = $data->{'archive_retention'};
my $LOG_FILE = $data->{'nagios_log_file'};
if (!(-r $LOG_FILE)) {
    print "Error : cannot open $LOG_FILE\n";
    exit(0);
}

my $cpt = 0;
my $ctime = 0;
my $last_line_read;
my %status 	= ("OK" => 0,"WARNING" => 1,"CRITICAL" => 2,"UNKNOWN" => 3,"DOWN" => 0,"UP" => 1,"UNREACHABLE" => 2);
my %type 	= ("SOFT" => 0, "HARD" => 1);

# Decide if we have to read the nagios.log from the begining 
if ($hour eq 0 && $min eq 0){
    $last_line_read = 0;
    $sth = $dbh->prepare("UPDATE config SET `last_line_read` = '0'");
    if (!$sth->execute) {die "Error:" . $sth->errstr . "\n";}
    $data = $sth->fetchrow_hashref();
}

$sth = $dbh->prepare("SELECT last_line_read FROM config");
if (!$sth->execute) {die "Error:" . $sth->errstr . "\n";}
$data = $sth->fetchrow_hashref();
$last_line_read = $data->{'last_line_read'};
if (!defined($last_line_read)){$last_line_read = 0;}
open(FILE, $LOG_FILE); 

# Parsing nagios.log
while ($cpt < $last_line_read && <FILE>){
    $cpt++;
}
while (<FILE>) {
	if ($_ =~ /^\[([0-9]*)\]\sSERVICE ALERT\:\ ([a-zA-Z0-9\.\-\_\ \%\'\"\(\[\]\)\{\}\,\;\:\/\=\<\>\*\$\^\~\@\+]*)/){
		my @tab = split(/;/, $2);
		$ctime = $1;
		$tab[5] =~ s/\'/\\\'/g; 
		$sth = $dbh->prepare("INSERT INTO `log` (`msg_type`,`ctime`, `host_name` , `service_description`, `status`, `type`, `retry`, `output`) VALUES ('0', '$ctime', '".$tab[0]."', '".$tab[1]."', '".$status{$tab[2]}."', '".$type{$tab[3]}."','".$tab[4]."','".$tab[5]."')");
		if (!$sth->execute) {print "Error:" . $sth->errstr . "\n";}	    	
    } elsif ($_ =~ /^\[([0-9]*)\]\sHOST ALERT\:\ ([a-zA-Z0-9\.\-\_\ \%\'\"\(\[\]\)\{\}\,\;\:\/\=\<\>\*\$\^\~\@\+]*)/){
		my @tab = split(/;/, $2);
		$ctime = $1;
		$tab[4] =~ s/\'/\\\'/g; 
		$sth = $dbh->prepare("INSERT INTO `log` (`msg_type`,`ctime`, `host_name` , `status`,  `type`, `retry`, `output`) VALUES ('1', '$ctime', '".$tab[0]."', '".$status{$tab[1]}."', '".$type{$tab[2]}."','".$tab[3]."','".$tab[4]."')");
		if (!$sth->execute) {print "Error:" . $sth->errstr . "\n";}	    	
    } elsif ($_ =~ /^\[([0-9]*)\]\sSERVICE NOTIFICATION\:\ ([a-zA-Z0-9\.\-\_\ \%\'\"\(\[\]\)\{\}\,\;\:\/\=\<\>\*\$\^\~\@\+]*)/){
		my @tab = split(/;/, $2);
		$ctime = $1;
		$tab[5] =~ s/\'/\\\'/g; 
		$sth = $dbh->prepare("INSERT INTO `log` (`msg_type`,`ctime`, `host_name` , `service_description`, `status`, `notification_cmd`, `notification_contact`, `output`) VALUES ('2', '$ctime', '".$tab[1]."', '".$tab[2]."', '".$status{$tab[3]}."', '".$tab[4]."','".$tab[0]."','".$tab[5]."')");
		if (!$sth->execute) {print "Error:" . $sth->errstr . "\n";}	    	
    } elsif ($_ =~ /^\[([0-9]*)\]\sHOST NOTIFICATION\:\ ([a-zA-Z0-9\.\-\_\ \%\'\"\(\[\]\)\{\}\,\;\:\/\=\<\>\*\$\^\~\@\+]*)/){
		my @tab = split(/;/, $2);
		$ctime = $1;
		$tab[4] =~ s/\'/\\\'/g; 
		$sth = $dbh->prepare("INSERT INTO `log` (`msg_type`,`ctime`, `notification_contact`, `host_name` , `status`, `notification_cmd`,  `output`) VALUES ('3', '$ctime', '".$tab[0]."','".$tab[1]."', '".$status{$tab[2]}."', '".$tab[3]."','".$tab[4]."')");
		if (!$sth->execute) {print "Error:" . $sth->errstr . "\n";}	    	
    } elsif ($_ =~ /^\[([0-9]*)\]\sWarning\:\ ([a-zA-Z0-9\.\-\_\ \%\'\"\(\[\]\)\{\}\,\;\:\/\=\<\>\*\$\^\~\@\+]*)/){
		my $tab = $2;
		$ctime = $1;
		$tab =~ s/\'/\\\'/g; 
		$sth = $dbh->prepare("INSERT INTO `log` (`msg_type`,`ctime`, `output`) VALUES ('4','$ctime', '".$tab."')");
		if (!$sth->execute) {print "Error:" . $sth->errstr . "\n";}	    	
    } elsif ($_ =~ /^\[([0-9]*)\]\ ([a-zA-Z0-9\.\-\_\ \%\'\"\(\[\]\)\{\}\,\;\:\/\=\<\>\*\$\^\~\@\+]*)/) {
		$ctime = $1;
		my $tab = $2;
		$tab =~ s/\'/\\\'/g; 
		$sth = $dbh->prepare("INSERT INTO `log` (`msg_type`,`ctime`, `output`) VALUES ('5','$ctime', '".$tab."')");
		if (!$sth->execute) {print "Error:" . $sth->errstr . "\n";}	    	
    }
    $cpt++;
}

# Purge
if ($retention ne 0){
    my $last_log = time() - ($retention * 24 * 60 * 60);
    my $sth1 = $dbh->prepare("DELETE FROM log WHERE ctime < '$last_log'");
    if (!$sth1->execute) {die "Error:" . $sth1->errstr . "\n";}
}
# Update statistics and flags
my $sth1 = $dbh->prepare("UPDATE `config` SET `last_line_read` = '".$cpt."'");
if (!$sth1->execute) {die "Error:" . $sth1->errstr . "\n";}
close(FILE);
# Good Bye
exit;
