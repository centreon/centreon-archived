#! /usr/bin/perl -w
#
# $Id: purgeDB.pl,v 1.2 2005/07/27 22:21:49 kyo $
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
use vars qw($dbh $opt_V $opt_h $opt_f);
use FileHandle;
use Getopt::Long;

##
## Specify the directory where the binary perfparse-db-purge is located
##

my $NagiosInstallFolder = "/usr/local/nagios/bin/";

##
## Warning : during the suppression make sure that no data will be put into perfparse database otherwise the suppression 
## might be stopped!
##

my $file_lock = "/usr/local/nagios/var/purge.lock";

##
## Configuration init base oreon
##

my $User = "root";
my $Password = "";
my $DataBase = "purge_oreon";
my $Host = "localhost";

##
## Configuration init base perfparse
##

my $Userpp = "root";
my $Passwordpp = "";
my $DataBasepp = "perfparse";
my $Hostpp = "localhost";

######## init connection database oreon and perfparse #######

my $dbh = DBI->connect("DBI:mysql:database=$DataBase;host=$Host",
                         "$User", "$Password",
                         {'RaiseError' => 1});

my $dbpp = DBI->connect("DBI:mysql:database=$DataBasepp;host=$Hostpp",
                         "$Userpp", "$Passwordpp",
                         {'RaiseError' => 1});
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

# retrieve and stock all purge_policy

	# define
my $purge_policy_id = 0;
my $purge_policy_retention = 1;
my $purge_policy_raw = 2;
my $purge_policy_bin = 3;
my $purge_policy_metric = 4;
my $purge_policy_service = 5;
my $purge_policy_host = 6;

my %hashPolicy;
sub	getPolicy()
{
    my $cmd = "SELECT purge_policy_id, purge_policy_retention, purge_policy_raw, purge_policy_bin, ";
    $cmd .= "purge_policy_metric, purge_policy_service, purge_policy_host FROM purge_policy";
    my $policy = $dbh->prepare($cmd);
    if (!$policy) {die "Error:" . $dbh->errstr . "\n";}
    if (!$policy->execute) {die "Error:" . $dbh->errstr . "\n";}

    while (my @arrPolicy = $policy->fetchrow_array)
    {
	$hashPolicy{$arrPolicy[0]} = [@arrPolicy];
    }
}

# retrieve and stock all host_id and hostname

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

my %hashForceService;
sub	getForceService()
{
    my $retrieve_service = "SELECT service_description from service";
    my $r_service = $dbh->prepare($retrieve_service);
    if (!$r_service) {die "Error:" . $dbh->errstr . "\n";}
    if (!$r_service->execute) {die "Error:" . $dbh->errstr . "\n";}
    while (my @arrservice = $r_service->fetchrow_array)
    {
	$hashForceService{$arrservice[0]} = $arrservice[0];
    }
}

# retrieve purge policy for host

sub getMyHostPolicy($)
{
    my $host_id = $_[0];

    while(1)
    {
	my $cmd = "SELECT purge_policy_id, host_template_model_htm_id ";
	$cmd .= "FROM host ";
	$cmd .= "WHERE host_id = '".$host_id."' LIMIT 1";
	my $policy = $dbh->prepare($cmd);
	if (!$policy) {die "Error:" . $dbh->errstr . "\n";}
	if (!$policy->execute) {die "Error:" . $dbh->errstr . "\n";}
	my @row = $policy->fetchrow_array;
	if (defined($row[0]))
	{return $row[0];}
	elsif (defined($row[1]))
	{$host_id = $row[1];}
	else
	{last;}
    }
    return ("");
}

# retrieve purge policy for service

sub getMyServicePolicy($)
{
    my $service_id = $_[0];

    while(1)
    {
	my $cmd = "SELECT purge_policy_id, service_template_model_stm_id ";
	$cmd .= "FROM service ";
	$cmd .= "WHERE service_id = '".$service_id."' LIMIT 1";
	my $policy = $dbh->prepare($cmd);
	if (!$policy) {die "Error:" . $dbh->errstr . "\n";}
	if (!$policy->execute) {die "Error:" . $dbh->errstr . "\n";}
	my @row = $policy->fetchrow_array;
	if (defined($row[0]))
	{return $row[0];}
	elsif (defined($row[1]))
	{$service_id = $row[1];}
	else
	{last;}
    }
    return ("");
}

# retrieve all host for a service

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

########################## elementary delete fonction #########################

sub delete_bin(@)
{
    my $host_name = $_[0];
    my $svc_desc = $_[1];
    my $metric = $_[2];
    my $retain = $_[3];
    my $cmd = "DELETE from perfdata_service_bin where host_name = \'$host_name\'";
    if ($svc_desc ne ""){
	$cmd .= " and service_description = \'$svc_desc\'";}
    if ($metric ne ""){
	$cmd .= " and metric = \'$metric\'";}
    if ($retain ne ""){
	$cmd .= " and ctime <= \'$retain\'";}
    if ($debug) {print $cmd . "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
}

sub delete_raw(@)
{
    my $host_name = $_[0];
    my $svc_desc = $_[1];
    my $retain = $_[2];

    my $cmd = "DELETE from perfdata_service_raw where host_name = \'$host_name\'";
    if ($svc_desc ne ""){
	$cmd .= " and service_description = \'$svc_desc\'";}
    if ($retain ne ""){
	$cmd .= " and ctime <= \'$retain\'";}
    if ($debug) {print $cmd . "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
}

sub delete_metric(@)
{
    my $host_name = $_[0];
    my $svc_desc = $_[1];
    my $metric = $_[2];
    my $cmd = "DELETE from perfdata_service_metric where host_name = \'$host_name\'";
    if ($svc_desc ne ""){
	$cmd .= " and service_description = \'$svc_desc\'";}
    if ($metric ne ""){
	$cmd .= " and metric = \'$metric\'";}
    if ($debug) {print $cmd . "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
}

sub delete_raw_summary($)
{
    my $id_service = $_[0];
    my $cmd = "DELETE from perfdata_raw_summary where service_id = \'$id_service\'";
    if ($debug) {print $cmd . "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
}

sub delete_raw_summary_data($)
{
    my $id_service = $_[0];
    my $cmd = "DELETE from perfdata_raw_summary_data where service_id = \'$id_service\'";
    if ($debug) {print $cmd . "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
}

sub search_raw_summary(@)
{
    my $host_name = $_[0];
    my $svc_desc = $_[1];

    my $cmd = "SELECT service_id from perfdata_service where host_name = \'$host_name\'";
    if ($svc_desc ne ""){
	$cmd .= " and service_description = \'$svc_desc\'";}
    if ($debug) {print $cmd . "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
    while (my @row_ary  = $req->fetchrow_array)
    {
	delete_raw_summary_data($row_ary[0]);
	delete_raw_summary($row_ary[0]);
    }
}

sub delete_service(@)
{
    my $host_name = $_[0];
    my $svc_desc = $_[1];

    #delete raw summary
    search_raw_summary($host_name, $svc_desc);
    #
    my $cmd = "DELETE from perfdata_service where host_name = \'$host_name\'";
    if ($svc_desc ne ""){
	$cmd .= " and service_description = \'$svc_desc\'";}
    if ($debug) {print $cmd . "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
}

sub delete_host($)
{
    my $host_name = $_[0];
    my $cmd = "DELETE from perfdata_host where host_name = \'$host_name\'";
    if ($debug) {print $cmd . "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}

}

################## end of elementary delete fonction #####################
################ delete dust #########################

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
	if (!defined($hashForceHostname{$host_ary[1]}))
	{
	    print "id host : $host_ary[0] \t host_name : $host_ary[1]\n";
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

sub check_data_service()
{
    getForceService();
    my $cmd = "SELECT service_id, service_description from perfdata_service";
    if ($debug) {print $cmd. "\n";}
    my $req = $dbpp->prepare($cmd);
    if (!$req) {die "Error:" . $dbpp->errstr . "\n";}
    if (!$req->execute) {die "Error:" . $dbpp->errstr . "\n";}
    while (my @service_ary = $req->fetchrow_array)
    {
	if (!defined($hashForceService{$service_ary[1]}))
	{
	    print "id host : $service_ary[0] \t host_name : $service_ary[1]\n";
	    is_delete_service($service_ary[0]);
	}
    }
}

sub complete_deletion()
{
    if ($Passwordpp ne "") {
	system($NagiosInstallFolder."perfparse-db-purge -D $DataBasepp -U $Userpp -P $Passwordpp -H $Hostpp");
    }else{
	system($NagiosInstallFolder."perfparse-db-purge -D $DataBasepp -U $Userpp -H $Hostpp");}
}


################ end delete dust #########################

sub delete_by_host(@)
{
    my $hostname = $_[0];
    my $svc_desc = $_[1];
    my $metric = $_[2];
    my $retain = $_[3];
    delete_bin($hostname, $svc_desc, $metric, $retain);
    delete_raw($hostname, $svc_desc, $retain);
    delete_metric($hostname, $svc_desc, $metric);
    delete_service($hostname, $svc_desc);
    delete_host($hostname);
}

sub delete_by_service(@)
{
    my $hostname = $_[0];
    my $svc_desc = $_[1];
    my $metric = $_[2];
    my $retain = $_[3];

    delete_bin($hostname, $svc_desc, $metric, $retain);
    delete_raw($hostname, $svc_desc, $retain);
    delete_metric($hostname, $svc_desc, $metric);
    delete_service($hostname, $svc_desc);
}

sub delete_by_metric(@)
{
    my $hostname = $_[0];
    my $svc_desc = $_[1];
    my $metric = $_[2];
    my $retain = $_[3];

    delete_bin($hostname, $svc_desc, $metric, $retain);
    delete_raw($hostname, $svc_desc, $retain);
    delete_metric($hostname, $svc_desc, $metric);
}

sub	make_date($)
{
    if ($_[0] ne "")
    {
 	my $timestamp_cur = time;
	$timestamp_cur -= $_[0];
	(my $sec,my $min, my $hour,my $mday,my $mon,my $year,my $wday,my $yday,my $isdst) = localtime($timestamp_cur);
	$year+=1900;
	$mon+=1;
	my $result = "$year-$mon-$mday $hour:$min:$sec";
	return ($result);
    }
}

sub	delete_motor(@)
{
    my $id_policy = $_[0];
    my $hostname = $_[1];
    my $svc_desc = $_[2];
    my $retain = $_[3];
    my $flag = 0;
    if ($hashPolicy{$id_policy}[$purge_policy_host] eq "1" && $svc_desc eq "")
    {
	delete_by_host($hostname, $svc_desc, "", "");
	$flag++;
    }
    elsif ($hashPolicy{$id_policy}[$purge_policy_service] eq "1")
    {
	delete_by_service($hostname, $svc_desc, "", "");
	$flag++;
    }
    elsif ($hashPolicy{$id_policy}[$purge_policy_metric] eq "1")
    {
	delete_by_metric($hostname, $svc_desc, "", "");
	$flag++;
    }
    elsif ($flag == 0)
    {
	if ($hashPolicy{$id_policy}[$purge_policy_raw] eq "1")
	{
	    delete_raw($hostname, $svc_desc, $retain);
	}
	if ($hashPolicy{$id_policy}[$purge_policy_bin] eq "1")
	{
	    delete_bin($hostname, $svc_desc, "", $retain);
	}
    }
}

# purge hosts

my $cmd_host = "SELECT host_id, host_name ";
$cmd_host .= "FROM host ";
$cmd_host .= "WHERE host_register = \'1\' ";
my $host = $dbh->prepare($cmd_host);
if (!$host) {die "Error:" . $dbh->errstr . "\n";}
if (!$host->execute) {die "Error:" . $dbh->errstr . "\n";}

sub	purge_host()
{
    while (my @row_ary = $host->fetchrow_array)
    {
	my $id_policy = getMyHostPolicy($row_ary[0]);
	if ($id_policy ne "")
	{
	    my $hostname =  return_eval($row_ary[1]);
	    my $retain = make_date(return_eval($hashPolicy{$id_policy}[1]));
	    delete_motor($id_policy, $hostname, "", $retain);
	}
	print ".";
    }
}

# purge services

my $cmd_service = "SELECT service_id, service_description ";
$cmd_service .= "FROM service ";
$cmd_service .= "WHERE service_register = \'1\' ";
my $svc = $dbh->prepare($cmd_service);
if (!$svc) {die "Error:" . $dbh->errstr . "\n";}
if (!$svc->execute) {die "Error:" . $dbh->errstr . "\n";}

sub	purge_service()
{
    while (my @svc_ary = $svc->fetchrow_array)
    {
	my $id_policy = getMyServicePolicy($svc_ary[0]);
	if ($id_policy ne ""){
	    my %hostslist = getAllMyServiceHosts($svc_ary[0]);
	    my $retain = make_date(return_eval($hashPolicy{$id_policy}[1]));
	    while ((my $clef, my $valeur) = each(%hostslist)) {
		delete_motor($id_policy, $valeur, $svc_ary[1], $retain);
	    }
	}
	print ".";
    }
}

sub	main()
{
    create_lock_file();
    if ($opt_f)
    {
	check_data_host();
	check_data_service();
	complete_deletion();
    }
    autoflush STDOUT 1;
    getPolicy();
    getHostname();
    purge_host();
    purge_service();
    delete_lock_file();
    print "Finish!\n";
}

Getopt::Long::Configure('bundling');
GetOptions
    ("h" => \$opt_h,
     "help" => \$opt_h,
     "version" => \$opt_V,
     "f" => \$opt_f,
     "V" => \$opt_V);

if ($opt_h) {
    print_help();
    exit(0);
}

if ($opt_V) {
    print "Version plugin V1.0.2 (2006/06/30)\n";
    exit(0);
}

main();
