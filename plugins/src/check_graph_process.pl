#! /usr/bin/perl -w
#
# $Id: check_graph_process.pl,v 1.2 2005/07/27 22:21:49 wistof Exp $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Julien Mathis - Mathieu Mettre - Romain Le Merlus
#
# Modified for Oreon Project by : Mathieu Chateau - Christophe Coraboeuf
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
use Net::SNMP qw(:snmp oid_lex_sort);
use FindBin;
use lib "$FindBin::Bin";
use lib "@NAGIOS_PLUGINS@";
use utils qw($TIMEOUT %ERRORS &print_revision &support);

if (eval "require oreon" ) {
	use oreon qw(get_parameters create_rrd update_rrd &is_valid_serviceid);
	use vars qw($VERSION %oreon);
	%oreon=get_parameters();
} else {
	print "Unable to load oreon perl module\n";
    exit $ERRORS{'UNKNOWN'};
}

use vars qw($PROGNAME);
use Getopt::Long;
use vars qw($opt_V $opt_h $opt_g $opt_v $opt_C $opt_p $opt_H $opt_D $opt_n $opt_S $opt_step $step $result @result %process_list %STATUS $opt_f);
my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};

##
## Plugin var init
##

my($proc, $proc_run);

$PROGNAME = "check_graph_process";
sub print_help ();
sub print_usage ();


%STATUS=(1=>'running',2=>'runnable',3=>'notRunnable',4=>'invalid');

Getopt::Long::Configure('bundling');
GetOptions
    ("h"   => \$opt_h, "help"         => \$opt_h,
     "V"   => \$opt_V, "version"      => \$opt_V,
     "g"   => \$opt_g, "rrdgraph"     => \$opt_g,
     "rrd_step=s" => \$opt_step, "f"  => \$opt_f,
     "n"   => \$opt_n, "number"       => \$opt_n,
     "v=s" => \$opt_v, "snmp=s"       => \$opt_v,
     "C=s" => \$opt_C, "community=s"  => \$opt_C,
     "p=s" => \$opt_p, "process=s"    => \$opt_p,
     "S=s" => \$opt_S, "ServiceId=s"  => \$opt_S,
     "H=s" => \$opt_H, "hostname=s"   => \$opt_H);

if ($opt_V) {
    print_revision($PROGNAME,'$Revision: 1.2 $');
    exit $ERRORS{'OK'};
}

if ($opt_h) {
    print_help();
    exit $ERRORS{'OK'};
}

$opt_H = shift unless ($opt_H);
(print_usage() && exit $ERRORS{'OK'}) unless ($opt_H);

($opt_v) || ($opt_v = shift) || ($opt_v = "1");
my $snmp = $1 if ($opt_v =~ /(\d)/);

($opt_S) || ($opt_S = shift) || ($opt_S = 1);
my $ServiceId = is_valid_serviceid($opt_S);

($opt_C) || ($opt_C = shift) || ($opt_C = "public");

my $process = "";
if ($opt_p){
    $process = $1 if ($opt_p =~ /([-.A-Za-z0-9]+)/);
} 

($opt_step) || ($opt_step = shift) || ($opt_step = "300");
$step = $1 if ($opt_step =~ /(\d+)/);


my $rrd = $pathtorrdbase.$ServiceId.".rrd";
my $start=time;
my $name = $0;
$name =~ s/\.pl.*//g;

##
## RRDTools create rrd
##

if ( $opt_g && $opt_n && (! -e $rrd))   {
    create_rrd ($rrd,1,$start,$step,"U","U","GAUGE");
}

##
## Plugin snmp requests
##
my $OID_SW_RunName = $oreon{MIB2}{SW_RUNNAME};
my $OID_SW_RunIndex =$oreon{MIB2}{SW_RUNINDEX};
my $OID_SW_RunStatus =$oreon{MIB2}{SW_RUNSTATUS};

my ( $session, $error ) = Net::SNMP->session(-hostname  => $opt_H,-community => $opt_C, -version  => $snmp);
if ( !defined($session) ) {
    print("UNKNOWN: $error");
    exit $ERRORS{'UNKNOWN'};
}

$result = $session->get_table(Baseoid => $OID_SW_RunName);
if (!defined($result)) {
    printf("UNKNOWN: %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}

$proc = 0;
foreach my $key (oid_lex_sort(keys %$result)) {
    my @oid_list = split (/\./,$key);
    $process_list{$$result{$key}} =  pop (@oid_list) ;
    if (defined($opt_p) && $opt_p ne ""){
	if ($$result{$key} eq $opt_p){
	    $proc++;
	}
    } else {
	$proc++;
    }
}



if (!($opt_n))
{
    if ($process_list{$process}) {
        $result = $session->get_request(-varbindlist => [$OID_SW_RunStatus . "." . $process_list{$process}]);
        if (!defined($result)) {
            printf("UNKNOWN: %s.\n", $session->error);
            $session->close;
            exit $ERRORS{'UNKNOWN'};
        }
	$proc_run =  $result->{$OID_SW_RunStatus . "." . $process_list{$process} };
	print $proc_run;
    }
}

##
## RRDtools update
##

if ( $opt_g && $opt_n) {
    $start=time;
    my $totrrd;
    if ($opt_n){$totrrd = $proc;}
    else{
        if ( ($proc_run == "3") ||  ($proc_run == "4") ){$totrrd = 0;}
        else{$totrrd = 1;}
    }
    update_rrd ($rrd,$start,$totrrd);
}

##
## Plugin return code
##

my $PERFPARSE = "";

if ($opt_n){
    if ($opt_f){
	$PERFPARSE = "|nbproc=$proc";
    }
    print "Processes OK - Number of current processes: $proc".$PERFPARSE."\n";
    exit $ERRORS{'OK'};
} else {
    if ($proc_run){
	if ($opt_f){
	    $PERFPARSE = "|procstatus=$proc_run";
	}
        print "Process OK - $process: $STATUS{$proc_run}".$PERFPARSE."\n";
        exit $ERRORS{'OK'};
    } else {
        print "Process CRITICAL - $process not in 'running' state\n";
        exit $ERRORS{'CRITICAL'};
    }
}

sub print_usage () {
    print "\nUsage:\n";
    print "$PROGNAME\n";
    print "   -H (--hostname)   Hostname to query - (required)\n";
    print "   -C (--community)  SNMP read community (defaults to public,\n";
    print "                     used with SNMP v1 and v2c\n";
    print "   -v (--snmp_version)  1 for SNMP v1 (default)\n";
    print "                        2 for SNMP v2c\n";
    print "   -g (--rrdgraph)   create  à rrd base and add datas into this one\n";
    print "   --rrd_step	    Specifies the base interval in seconds with which data will be fed into the RRD (300 by default)\n";
    print "   -D (--directory)  Path to rrdatabase (or create the .rrd in this directory)\n";
    print "                     by default: ".$pathtorrdbase."\n";
    print "                     (The path is valid with spaces '/my\ path/...')\n";
    print "   -n (--number)     Return the number of current running processes. \n";
    print "   -p (--process)    Set the process name ex: by default smbd\n";
    print "   -S (--ServiceId)  Oreon Service Id\n";
    print "   -V (--version)    Plugin version\n";
    print "   -h (--help)       usage help\n";
    print "   -f       Perfparse Compatible\n";
}


sub print_help () {
    print "##########################################\n";
    print "#  Copyright (c) 2004-2006 Oreon         #\n";
    print "#  Bugs to http://www.oreon-project.org/ #\n";
    print "##########################################\n";
    print_usage();
    print "\n";
}

