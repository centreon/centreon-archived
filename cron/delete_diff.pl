#! /usr/bin/perl -w
#
# $Id: delete_diff.pl,v 1.0.1 2006/10/19 22:21:49 kyo $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Julien Mathis - Romain Le Merlus
#
# Modified by Merethis SARL for perfparse compatibility
#
# The Software is provided to you AS IS and WITH ALL FAULTS.
# OREON makes no representation and gives no warranty whatsoever,
# whether express or implied, and without limitation, with regard to the quality,
# safety, contents, performance, merchantability, non-infringement or suitability for
# any particular or intended purpose of the Software found on the OREON web site.
# In no event will OREON be liable for any direct, indirect, punitive, special,
# incidental or consequential damages however they may arise and even if OREON has
# been previously advised of the possibility of such damages.

##
## Plugin init
##

use strict;
use DBI;
use vars qw($dbh $opt_V $opt_h);
use FileHandle;
use Getopt::Long;

#
## in $PerfparseInstallFolder you must specify perfparse install directory
## This script use : perfparse-db-purge binary
## Moreover  perfparse-db-purge binary require perfparse.cfg which must be in perfparse install directory in etc
## $PerfparseInstallFolde/etc/perfparse.cfg you may found this file in $NAGIOS_PATH/etc/perfparse.cfg

my $PerfparseInstallFolder = "/srv/perfparse/bin/";

##
## Warning : during the suppression make sure that no data will be put into perfparse database otherwise the suppression
## might be stopped!
##

my $file_lock = "/var/lock/purge.lock";

##
## Specify the path of oreon.conf.php should be $PATH_OREON/www/oreon.conf.php
##

my $oreon_conf = "/srv/oreon/www/oreon.conf.php";

##
## Set this variable to 1 in order to display debug display
##

my $debug=0;

##
##
##
# other function

sub print_usage () {
    print "Usage:\n";
    print " ./deleteDB.pl\n";
    print "   -V (--version)    Plugin version\n";
    print "   -h (--help)       usage help\n";
}

sub print_help ()
{
    print "###########################################\n";
    print "#                                         #\n";
    print "#  Copyright (c) 2006 Merethis            #\n";
    print "#  Bugs to http://www.oreon-services.com  #\n";
    print "#                                         #\n";
    print "###########################################\n";
    print_usage();
    print "\n";
}

sub create_lock_file(){

    system("touch  $file_lock") == 0
	 or die "system touch $file_lock failed: $?";
}

sub delete_lock_file(){
    system("rm -rf  $file_lock") == 0
	or die "system rm $file_lock failed: $?";
}

sub return_eval($)
{
     if (defined($_[0])){
	 return $_[0];
      }
      else
      {
	  return "";
      }
}

my $Userpp;
my $Passwordpp;
my $DataBasepp;
my $Hostpp;
my $dbh;
my $dbpp;

sub	connect_db()
{
    my $User;
    my $Password;
    my $DataBase;
    my $Host;
    open(OREON_FD,"$oreon_conf");
    while (<OREON_FD>)
    {
	chomp($_);
	if ($_ =~ /=/)
	{
	    my @access = split(/\"/, $_);
	    my @field = split(/\'/, $_);
	    if ($field[1] eq "host"){
	        $Host = return_eval($access[1]);
	    }
	    elsif ($field[1] eq "user"){
		$User = return_eval($access[1]);
	    }
	    elsif ($field[1] eq "password"){
		$Password = return_eval($access[1]);
	    }
	    elsif ($field[1] eq "db"){
		$DataBase = return_eval($access[1]);
	    }
	}
    }
    close(OREON_FD);
#    if ($debug) {print "connecting : Database : $DataBase \t Host : $Host \t User : $User \t Pass : $Password\n";}
    $dbh = DBI->connect("DBI:mysql:database=$DataBase;host=$Host",
			"$User", "$Password",
			{'RaiseError' => 1});

    my $cmd_access = "SELECT DB_user, DB_pass, DB_name, DB_host from cfg_perfparse";
    my $p_access = $dbh->prepare($cmd_access);
    if (!$p_access) {die "Error:" . $dbh->errstr . "\n";}
    if (!$p_access->execute) {die "Error:" . $dbh->errstr . "\n";}
    my @arr_p_access = $p_access->fetchrow_array;

    $Userpp = return_eval($arr_p_access[0]);
    $Passwordpp = return_eval($arr_p_access[1]);
    $DataBasepp = return_eval($arr_p_access[2]);
    $Hostpp = return_eval($arr_p_access[3]);
#    if ($debug) {print "connecting : Database : $DataBasepp \t Host : $Hostpp \t User : $Userpp \t Pass : $Passwordpp\n";}
    $dbpp = DBI->connect("DBI:mysql:database=$DataBasepp;host=$Hostpp",
                         "$Userpp", "$Passwordpp",
                         {'RaiseError' => 1});
}

my %hashForceHostname;
sub	getForceHostname()
{
    my $retrieve_host = "SELECT host_name from host";
    my $r_host = $dbh->prepare($retrieve_host);
    if (!$r_host) {die "Error:" . $dbh->errstr . "\n";}
    if (!$r_host->execute) {die "Error:" . $dbh->errstr . "\n";}
    while (my @arrhost = $r_host->fetchrow_array)
    {
	$hashForceHostname{$arrhost[0]} = $arrhost[0];
    }
}

my %hashHostname;
sub	getHostname()
{
    my $retrieve_host = "SELECT host_id, host_name from host";
    my $r_host = $dbh->prepare($retrieve_host);
    if (!$r_host) {die "Error:" . $dbh->errstr . "\n";}
    if (!$r_host->execute) {die "Error:" . $dbh->errstr . "\n";}
    while (my @arrhost = $r_host->fetchrow_array)
    {
	$hashHostname{$arrhost[0]} = $arrhost[1];
    }
}

sub getAllMyServiceHosts($)
{
    my %hostslist;
    my $service_id = $_[0];
    my $cmd = "SELECT host_host_id, hostgroup_hg_id ";
    $cmd .= "FROM host_service_relation hsr ";
    $cmd .= "WHERE hsr.service_service_id = '".$service_id."'";
    my $host = $dbh->prepare($cmd);
    if (!$host) {die "Error:" . $dbh->errstr . "\n";}
    if (!$host->execute) {die "Error:" . $dbh->errstr . "\n";}

    my $cmd2 = "";
    while (my @elem = $host->fetchrow_array)
    {
	if ($elem[0])
	{
	    $hostslist{$elem[0]} = $hashHostname{$elem[0]};
	}
	elsif ($elem[1])
	{
	    $cmd2 = "SELECT host_host_id ";
	    $cmd2 .= "FROM hostgroup_relation hgr ";
	    $cmd2 .= "WHERE hgr.hostgroup_hg_id = '".$elem[1]."'";
	    my $hostgroup = $dbh->prepare($cmd2);
	    if (!$hostgroup) {die "Error:" . $dbh->errstr . "\n";}
	    if (!$hostgroup->execute) {die "Error:" . $dbh->errstr . "\n";}
	    while (my @elem2 = $hostgroup->fetchrow_array)
	    {
		$hostslist{$elem2[0]} = $hashHostname{$elem2[0]};
	    }

	}
    }
    return %hostslist;
}

################ set is_deleted in perfparse database #########################

sub is_delete_host($)
{
    my $host_id_pp = $_[0];
    my $cmd_del = "UPDATE perfdata_host SET is_deleted=1 where host_id = '".$host_id_pp."'";
    if ($debug) {print $cmd_del. "\n";}
    my $req = $dbpp->prepare($cmd_del);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
}

sub check_data_host()
{
    getForceHostname();
    my $cmd = "SELECT host_id, host_name from perfdata_host";
    if ($debug) {print $cmd. "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}

    while (my @host_ary = $req->fetchrow_array)
    {
		if (!defined($hashForceHostname{$host_ary[1]}) && $host_ary[1] ne "OSL_Module" && $host_ary[1] ne "Meta_Module")
		{
		    is_delete_host($host_ary[0]);
		}
    }
}

sub is_delete_service($)
{
    my $service_id_pp = $_[0];
    my $cmd_del = "UPDATE perfdata_service SET is_deleted=1 where service_id = '".$service_id_pp."'";
    if ($debug) {print $cmd_del. "\n";}
    my $req = $dbpp->prepare($cmd_del);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
}

sub check_host_service(@)
{
    my $service_desc = $_[0];
    my $service_host = $_[1];
    my $cmd_svc = "SELECT service_id from service where service_description = '".$service_desc."' ";
    if ($debug) {print $cmd_svc. "\n";}
    my $req = $dbh->prepare($cmd_svc);
    if (!$req) {die "Error:" . $dbh->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbh->errstr . "\n";}

    while (my @service_ary = $req->fetchrow_array)
    {
		my $service_id = return_eval($service_ary[0]);
		if (defined($service_id)){
		    my %hostslist = getAllMyServiceHosts($service_id);
		    while ((my $clef, my $valeur) = each(%hostslist)) {
			if ($service_host eq $valeur)
			{
			    return (0);
			}
		    }
		}
    }
    return (1);
}

sub check_data_service()
{
    my $cmd = "SELECT service_id, service_description, host_name from perfdata_service";
    if ($debug) {print $cmd. "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
   
    while (my @service_ary = $req->fetchrow_array)
    {
		if ($service_ary[2] ne "OSL_Module" && $service_ary[2] ne "Meta_Module")
		{
		    my $return_val = check_host_service($service_ary[1], $service_ary[2]);
		    if ($return_val == 1){
			is_delete_service($service_ary[0]);
		    }
		}
    }
}

########################### launch perfparse-db-purge binary ######################################################

sub complete_deletion()
{
    if ($Passwordpp ne "") {
	system($PerfparseInstallFolder."perfparse-db-purge -D $DataBasepp -U $Userpp -P $Passwordpp -H $Hostpp");
    }else{
	system($PerfparseInstallFolder."perfparse-db-purge -D $DataBasepp -U $Userpp -H $Hostpp");}
}

################################################ Start Script #######################################################

sub	main()
{
    create_lock_file();
    connect_db();
    getHostname();
    check_data_host();
    check_data_service();
    complete_deletion();
    delete_lock_file();
}

Getopt::Long::Configure('bundling');
GetOptions
    ("h" => \$opt_h,
     "help" => \$opt_h,
     "version" => \$opt_V,
     "V" => \$opt_V);

if ($opt_h) {
    print_help();
    exit(0);
}

if ($opt_V) {
    print "Version plugin V1.0.1 (2006/10/19)\n";
    exit(0);
}

main();
