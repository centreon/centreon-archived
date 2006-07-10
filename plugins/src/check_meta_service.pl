#! /usr/bin/perl -w
#
# $Id: check_meta_service.pl,v 1.2 2005/07/27 22:21:49 Julio $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Julien Mathis - Romain Le Merlus
#
# Developped by Julien Mathis for Merethis SARL 
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
use vars qw($PROGNAME);
use Getopt::Long;
use vars qw($opt_V $opt_H $opt_h $opt_i);
use lib "@NAGIOS_PLUGINS@";
use utils qw($TIMEOUT %ERRORS &print_revision &support);

## For Debug mode = 1
my $debug = 0;

sub print_help ();
sub print_usage ();

Getopt::Long::Configure('bundling');
GetOptions
    ("h" => \$opt_h, 
     "help" => \$opt_h,
     "V" => \$opt_V, 
     "H" => \$opt_H,
     "i=s" => \$opt_i);


my $dbh = DBI->connect("DBI:mysql:database=oreon;host=localhost",
                         "root", "",
                         {'RaiseError' => 1});

my $str1 = "SELECT * FROM `cfg_perfparse`";
if ($debug) {print $str1 . "\n";}
my $sth1 = $dbh->prepare($str1);
if (!$sth1->execute) {
    die "Error:" . $sth1->errstr . "\n";
}
my $ref1 = $sth1->fetchrow_hashref();

my $dbh2 = DBI->connect("DBI:".$ref1->{'Storage_Modules_Load'}.":database=".$ref1->{'DB_Name'}.";host=".$ref1->{'DB_Host'},
                         $ref1->{'DB_User'}, $ref1->{'DB_Pass'},
                         {'RaiseError' => 1});

if ($opt_V) {
    print_revision($PROGNAME,'$Revision: 0.1 $');
    exit $ERRORS{'OK'};
}

if ($opt_h) {
    print_help();
    exit $ERRORS{'OK'};
}

my $result;
my $warning;
my $critical;
my $metric_id;

sub return_value($$$){
    
    #print "warning : ".$warning."-Critical : ".$critical.":$result\b";
    
    if ($warning ne $critical){
	if ($warning < $critical){ # Bon sens
	    if ($result < $warning){
		print "OK result : " . $result . "|OMS=" . $result . ";".$warning.";".$critical."\n";
		exit $ERRORS{'OK'};
	    } elsif (($result >= $warning) && ($result < $critical)){
		print "WARNING result : " . $result . "|OMS=" . $result . ";".$warning.";".$critical."\n";
		exit $ERRORS{'WARNING'};
	    } elsif ($result >= $critical){
		print "CRITICAL result : " . $result . "|OMS=" . $result . ";".$warning.";".$critical."\n";
		exit $ERRORS{'CRITICAL'};
	    }
	} else { # sens inverse
	    if ($result < $critical){
	    print "CRITICAL result : " . $result . "|OMS=" . $result . ";".$warning.";".$critical."\n";	    
		exit $ERRORS{'CRITICAL'};
	    } elsif ($result >= $critical && $result < $warning){
		print "WARNING result : " . $result . "|OMS=" . $result . ";".$warning.";".$critical."\n";
		exit $ERRORS{'WARNING'};
	    } elsif ($result >= $warning){
		print "OK result : " . $result . "|OMS=" . $result . ";".$warning.";".$critical."\n";
		exit $ERRORS{'OK'};
	    } else{
		print "OK result : " . $result . "|OMS=" . $result . ";".$warning.";".$critical."\n";
		exit $ERRORS{'OK'};
	    }
	}
    } else {
	print "ERROR : warnig level = critical level";
	exit $ERRORS{'CRITICAL'};
    }
}

my $ref;
my $svc_id;
my $metric;
my $host_id;

## Get Value by metric Option

sub get_value_in_database_metric_id($$$$){
    ## Get last entry in perfparse database for this service
    my $str = "SELECT value FROM perfdata_service_metric,perfdata_service_bin WHERE perfdata_service_metric.metric_id = '".$metric_id."' AND perfdata_service_metric.last_perfdata_bin = perfdata_service_bin.id";
    if ($debug) {print $str . "\n";}
    my $sth_deb2 = $dbh2->prepare($str);
    if (!$sth_deb2->execute) {die "Error:" . $sth_deb2->errstr . "\n";}
    my $sth_deb2_data = $sth_deb2->fetchrow_hashref();
    return $sth_deb2_data->{'value'};
}

## Get value For Regexp Options

sub get_value_in_database($$$$$){

    my $str;
    ## Get hostname and service description for perfparse request
    
    $str = "SELECT host_name,service_description FROM host,host_service_relation,service WHERE host.host_id = host_service_relation.host_host_id AND 
service.service_id = host_service_relation.service_service_id AND service.service_id = '".$svc_id."'";
    
    if ($debug) {print $str . "\n";}
    my $host_data = $dbh->prepare($str);
    if (!$host_data->execute) {die "Error:" . $host_data->errstr . "\n";}
    my $data = $host_data->fetchrow_hashref();
    
    ## Get last entry in perfparse database for this service
    my $sth_deb2 = $dbh2->prepare("SELECT value FROM perfdata_service_metric,perfdata_service_bin WHERE perfdata_service_metric.host_name = '".$data->{'host_name'}."' AND perfdata_service_metric.service_description = '".$data->{'service_description'}."' AND perfdata_service_metric.last_perfdata_bin = perfdata_service_bin.id AND perfdata_service_metric.metric = '".$metric."'");
    if (!$sth_deb2->execute) {die "Error:" . $sth_deb2->errstr . "\n";}
    my $sth_deb2_data = $sth_deb2->fetchrow_hashref();
    return $sth_deb2_data->{'value'};
}

sub get_value_in_database_by_host($$$$$){

    my $str;
    
    ## Get hostname and service description for perfparse request
    
    $str = "SELECT host_name,service_description FROM host,host_service_relation,service WHERE host.host_id = host_service_relation.host_host_id AND service.service_id = host_service_relation.service_service_id AND service.service_id = '".$svc_id."'";
    if ($debug) {print $str . "\n";}
    my $host_data = $dbh->prepare($str);
    if (!$host_data->execute) {die "Error:" . $host_data->errstr . "\n";}
    my $data = $host_data->fetchrow_hashref();

    ## Get last entry in perfparse database for this service

    my $sth_deb2 = $dbh2->prepare("SELECT value FROM perfdata_service_metric,perfdata_service_bin WHERE perfdata_service_metric.host_name = '".$data->{'host_name'}."' AND perfdata_service_metric.service_description = '".$data->{'service_description'}."' AND perfdata_service_metric.last_perfdata_bin = perfdata_service_bin.id AND perfdata_service_metric.metric = '".$metric."'");
    if (!$sth_deb2->execute) {die "Error:" . $sth_deb2->errstr . "\n";}
    my $sth_deb2_data = $sth_deb2->fetchrow_hashref();
    return $sth_deb2_data->{'value'};
}

sub get_value_in_database_by_hg($$$$$$){

    my $str;
   
    ## Get hostname 
    
    $str = "SELECT host_name FROM host WHERE host.host_id = '".$host_id."'";
    if ($debug) {print $str . "\n";}
    my $hd = $dbh->prepare($str);
    if (!$hd->execute) {die "Error:" . $hd->errstr . "\n";}
    my $host_data = $hd->fetchrow_hashref();
   
    ## Get service description
    
    $str = "SELECT service_description FROM service WHERE service.service_id = '".$svc_id."'";
    if ($debug) {print $str . "\n";}
    my $sd = $dbh->prepare($str);
    if (!$sd->execute) {die "Error:" . $sd->errstr . "\n";}
    my $service_data = $sd->fetchrow_hashref();
    
    ## Get last entry in perfparse database for this service
    
    my $sth_deb2 = $dbh2->prepare("SELECT value FROM perfdata_service_metric,perfdata_service_bin WHERE perfdata_service_metric.host_name = '".$host_data->{'host_name'}."' AND perfdata_service_metric.service_description = '".$service_data->{'service_description'}."' AND perfdata_service_metric.last_perfdata_bin = perfdata_service_bin.id AND perfdata_service_metric.metric = '".$metric."'");
    if (!$sth_deb2->execute) {die "Error:" . $sth_deb2->errstr . "\n";}
    my $sth_deb2_data = $sth_deb2->fetchrow_hashref();
    return $sth_deb2_data->{'value'};
}

my $cpt = 0;
my $total = 0;
my $max = 0;
my $min = 999999999;
my $svc;
my $value = 0;

if ($opt_i){
    
    my $str;
    # get osl info
    my $sth = $dbh->prepare("SELECT calcul_type,regexp_str,warning,critical,metric, meta_select_mode FROM meta_service WHERE meta_id = '".$opt_i."'");
    if (!$sth->execute) {die "Error:" . $sth->errstr . "\n";}
    $ref = $sth->fetchrow_hashref();
    if (!defined($ref->{'calcul_type'})){
	print "Unvalidate Meta Service\n";
	exit $ERRORS{'CRITICAL'};
    }
    
    $warning = $ref->{'warning'};
    $critical = $ref->{'critical'};
    
    # Get Service List by regexp
    
    if ($ref->{'meta_select_mode'} eq '2'){
	
	###############################################
	
	$str = "SELECT service_id FROM service WHERE `service_description` LIKE '".$ref->{'regexp_str'}."' AND service_activate = '1' AND service_register = '1'";
	if ($debug) {print $str . "\n";}
	$sth = $dbh->prepare($str);
	if (!$sth->execute) {die "Error:" . $sth->errstr . "\n";}
	while ($svc = $sth->fetchrow_hashref()){
	    my $sth2 = $dbh->prepare("SELECT * FROM host_service_relation WHERE service_service_id = '".$svc->{'service_id'}."'");
	    if (!$sth2->execute) {die "Error:" . $sth2->errstr . "\n";}
	    my $svc_relation = $sth2->fetchrow_hashref();
	    if (defined($svc_relation->{'host_host_id'})){
    		
    		#### Par Host
    		
		if (defined($svc->{'service_id'})){$svc_id = $svc->{'service_id'};} else {$svc_id = $svc->{'svc_id'};}
		if (defined($ref->{'regexp_str'})){$metric = $ref->{'metric'};} else {$metric = $svc->{'metric'};}
		$value = get_value_in_database_by_host($dbh,$dbh2,$svc_id,$metric,$debug);			    
		if ($ref->{'calcul_type'} =~ "AVE"){			
		    if (defined($value) && $value){$total += $value;}
		    if ($debug) {print "total = " . $total . "  value = ".$value."\n";} 
		    $cpt++;
		    $result = $total / $cpt;
		} elsif ($ref->{'calcul_type'} =~ "SOM"){
		    if ($value){$total += $value;}
		    if ($debug){print "total = " . $total . "  value = ".$value."\n";} 
		    $result = $total;
		} elsif ($ref->{'calcul_type'} =~ "MIN"){
		    if ($debug){print " min : " . $min . "  value = ".$value."\n";} 
		    if ($value && $value <= $min){$min = $value;}
		    $result = $min;
		} elsif ($ref->{'calcul_type'} =~ "MAX"){
		    if ($debug){print "max = " . $max . "  value = ".$value."\n";}
		    if ($value && $value >= $max){$max = $value;}
		    $result = $max;
		}
	    } else {
 
		### Par Hostgroup

		my $sth3 = $dbh->prepare("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$svc_relation->{'hostgroup_hg_id'}."'");
	    	if (!$sth3->execute) {die "Error:" . $sth3->errstr . "\n";}
	    	while ($svc_relation = $sth3->fetchrow_hashref()){
		    if (defined($svc->{'service_id'})){$svc_id = $svc->{'service_id'};} else {$svc_id = $svc->{'svc_id'};}
		    if (defined($ref->{'regexp_str'})){$metric = $ref->{'metric'};} else {$metric = $svc->{'metric'};}
		    $host_id = $svc_relation->{'host_host_id'};
		    $value = get_value_in_database_by_hg($dbh,$dbh2,$svc_id, $host_id, $metric, $debug);			    
		    if ($ref->{'calcul_type'} =~ "AVE"){			
			if (defined($value) && $value){$total += $value;}
			if ($debug) {print "total = " . $total . "  value = ".$value."\n";} 
			$cpt++;
		    } elsif ($ref->{'calcul_type'} =~ "SOM"){
			if ($value){$total += $value;}
			if ($debug){print "total = " . $total . "  value = ".$value."\n";} 
		    } elsif ($ref->{'calcul_type'} =~ "MIN"){
			if ($debug){print " min : " . $min . "  value = ".$value."\n";} 
			if ($value && $value <= $min){$min = $value;}
		    } elsif ($ref->{'calcul_type'} =~ "MAX"){
			if ($debug){print "max = " . $max . "  value = ".$value."\n";}
			if ($value && $value >= $max){$max = $value;}
		    }
		}
		if ($ref->{'calcul_type'} =~ "AVE"){
		    $result = $total / $cpt;
		} elsif ($ref->{'calcul_type'} =~ "SOM"){
		    $result = $total;
		} elsif ($ref->{'calcul_type'} =~ "MIN"){
		    $result = $min;
		} elsif ($ref->{'calcul_type'} =~ "MAX"){
		    $result = $max;
		}
	    }
	}
	return_value($result, $warning, $critical);
	###############################################
    } else {
	$sth = $dbh->prepare("SELECT metric_id FROM `meta_service_relation` WHERE meta_id = '".$opt_i."'");
	if (!$sth->execute) {die "Error:" . $sth->errstr . "\n";}
	if ($ref->{'calcul_type'} =~ "AVE"){
	    while ($svc = $sth->fetchrow_hashref()){
		if (defined($svc->{'metric_id'})){$metric_id = $svc->{'metric_id'};}
		$value = get_value_in_database_metric_id($dbh,$dbh2,$metric_id,$debug);
		if (defined($value) && $value){$total += $value;}
		$cpt++;
	    }
	    $result = $total / $cpt;
	} elsif ($ref->{'calcul_type'} =~ "SOM"){
	    while ($svc = $sth->fetchrow_hashref()){
		if (defined($svc->{'metric_id'})){$metric_id = $svc->{'metric_id'};}
		$value = get_value_in_database_metric_id($dbh,$dbh2,$metric_id,$debug);
		if ($value){$total += $value;}
		if ($debug){print "total = " . $total . "  value = ".$value."\n";} 
	    }
	    $result = $total;
	} elsif ($ref->{'calcul_type'} =~ "MIN"){
	    while ($svc = $sth->fetchrow_hashref()){
		if (defined($svc->{'metric_id'})){$metric_id = $svc->{'metric_id'};}
		$value = get_value_in_database_metric_id($dbh,$dbh2,$metric_id,$debug);
		if ($debug){print " min : " . $min . "  value = ".$value."\n";} 
		if ($value && $value <= $min){$min = $value;}
	    }
	    $result = $min;
	} elsif ($ref->{'calcul_type'} =~ "MAX"){
	    while ($svc = $sth->fetchrow_hashref()){
		if (defined($svc->{'metric_id'})){$metric_id = $svc->{'metric_id'};}
		$value = get_value_in_database_metric_id($dbh,$dbh2,$metric_id,$debug);
		if ($debug){print "max = " . $max . "  value = ".$value."\n";}
		if ($value && $value >= $max){$max = $value;}
	    }
	    $result = $max;
	}     
	return_value($result, $warning, $critical);
    }
}


sub print_usage () {
    print "Usage:\n";
    print " check_osl.pl\n";
    print "   -H		Hostname to query (Required)\n";
    print "   -i		OSL id\n";
    print "   -V (--version)    Plugin version\n";
    print "   -h (--help)       usage help\n";
}

sub print_help () 
{
    print "###########################################\n";
    print "#                                         #\n";
    print "#  Copyright (c) 2004-2006 Merethis       #\n";
    print "#  Bugs to http://www.oreon-services.com  #\n";
    print "#                                         #\n";
    print "###########################################\n";
    print_usage();
    print "\n";
}
