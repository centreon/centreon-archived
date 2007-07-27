#! /usr/bin/perl -w
###################################################################
# Oreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

my $installedPath = "@OREON_PATH@";
#my $installedPath = "/srv/oreon";
use vars qw($mysql_user $mysql_passwd $mysql_host $mysql_database_oreon $mysql_database_ods);

require $installedPath."/ODS/etc/conf.pm";

# Init MySQL Connexion
my $dbh = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 1});
my $dbh_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 1});

# Get conf Data
my $sth = $dbh->prepare("SELECT status_file FROM cfg_nagios WHERE nagios_activate = '1'");
if (!$sth->execute) {die "Error:" . $sth->errstr . "\n";}
my $data = $sth->fetchrow_hashref();

my $STATUS_FILE = $data->{'status_file'};
my $DEST_FILE = $STATUS_FILE."_light"; 

my $sth_ods = $dbh_ods->prepare("SELECT fast_parsing FROM config");
if (!$sth_ods->execute) {die "Error:" . $sth_ods->errstr . "\n";}
my $data_ods = $sth_ods->fetchrow_hashref();

# Check if fast_parsing is enable
if ($data_ods->{'fast_parsing'} ne 1){exit();}
undef($data_ods);
undef($sth_ods);

# Not ';' because some perfparse use ';' in some outputs 
my $SEPARATOR = "#"; 
my $line = "";

open(FILE, "$STATUS_FILE"); open(DEST, "> $DEST_FILE");
while (<FILE>){
    if ($_ =~ m/^(host)/ || $_ =~ m/^(service)/ || $_ =~ m/^(program)/) {
        $line = $1 . $SEPARATOR;
        $line =~ s/host/h/g;
        $line =~ s/service/s/g;
		$line =~ s/program/p/g;
        while (<FILE>) {
            if ($_ =~ m/^\s*}/) {last;}
            $_ =~ s/^\s[a-z\_]+=//g;
            chomp $_;
            $line .= $_ . $SEPARATOR;
        }
        print DEST $line . "\n";
    }
}
close(FILE);
close(DEST);