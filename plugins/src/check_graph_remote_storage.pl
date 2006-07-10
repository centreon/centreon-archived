#! /usr/bin/perl -w
#
# $Id: check_graph_remote_storage.pl,v 1.2 2005/07/27 22:21:49 wistof Exp $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Jean Baptiste Gouret - Julien Mathis - Mathieu Mettre - Romain Le Merlus - Yohann Lecarpentier
# Under control of Flavien Astraud, Jerome Landrieu for Epitech.
# Oreon's plugins are developped in partnership with Linagora company.
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
use Net::SNMP qw(:snmp);
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
use vars qw($opt_V $opt_h $opt_g $opt_v $opt_C $opt_d $opt_w $opt_c $opt_H $opt_S $opt_D $opt_s $opt_step $step @test $opt_f);
my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};
##
## Plugin var init
##
my ($hrStorageDescr, $hrStorageAllocationUnits, $hrStorageSize, $hrStorageUsed);
my ($AllocationUnits, $Size, $Used);
my ($tot, $used, $pourcent, $return_code);

$PROGNAME = "check_graph_remote_storage";
sub print_help ();
sub print_usage ();
Getopt::Long::Configure('bundling');
GetOptions
    ("h"   => \$opt_h, "help"         => \$opt_h,
     "V"   => \$opt_V, "version"      => \$opt_V,
     "s"   => \$opt_s, "show"         => \$opt_s,
     "g"   => \$opt_g, "rrdgraph"     => \$opt_g,
     "rrd_step=s" => \$opt_step, "f"  => \$opt_f,
     "v=s" => \$opt_v, "snmp=s"       => \$opt_v,
     "C=s" => \$opt_C, "community=s"  => \$opt_C,
     "d=s" => \$opt_d, "disk=s"       => \$opt_d,
     "w=s" => \$opt_w, "warning=s"    => \$opt_w,
     "c=s" => \$opt_c, "critical=s"   => \$opt_c,
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


($opt_v) || ($opt_v = shift) || ($opt_v = "2");
my $snmp = $1 if ($opt_v =~ /(\d)/);

($opt_C) || ($opt_C = shift) || ($opt_C = "public");

($opt_d) || ($opt_d = shift) || ($opt_d = 2);

my $partition;
if ($opt_d =~ /([0-9]+)/){
    $partition = $1;
}
else{
    print "Unknown -d number expected... or it doesn't exist, try another disk - number\n";
    exit $ERRORS{'UNKNOWN'};
}

($opt_S) || ($opt_S = shift) || ($opt_S = "1_1");
my $ServiceId = is_valid_serviceid($opt_S);

($opt_c) || ($opt_c = shift) || ($opt_c = 95);
my $critical = $1 if ($opt_c =~ /([0-9]+)/);

($opt_w) || ($opt_w = shift) || ($opt_w = 80);
my $warning = $1 if ($opt_w =~ /([0-9]+)/);
if ($critical <= $warning){
    print "(--crit) must be superior to (--warn)";
    print_usage();
    exit $ERRORS{'OK'};
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
if ($opt_g) {
    if (! -e $rrd) {
    	create_rrd ($rrd,2,$start,$step,"U","U","GAUGE");
    }
}

##
## Plugin snmp requests
##

my $OID_hrStorageDescr =$oreon{MIB2}{HR_STORAGE_DESCR};
my $OID_hrStorageAllocationUnits =$oreon{MIB2}{HR_STORAGE_ALLOCATION_UNITS};
my $OID_hrStorageSize =$oreon{MIB2}{HR_STORAGE_SIZE};
my $OID_hrStorageUsed =$oreon{MIB2}{HR_STORAGE_USED};

# create a SNMP session
my ( $session, $error ) = Net::SNMP->session(-hostname  => $opt_H,-community => $opt_C, -version  => $snmp);
if ( !defined($session) ) {
    print("CRITICAL: SNMP Session : $error");
    exit $ERRORS{'CRITICAL'};
}

if ($opt_s) {
    # Get description table
    my $result = $session->get_table(
        Baseoid => $OID_hrStorageDescr
    );

    if (!defined($result)) {
        printf("ERROR: hrStorageDescr Table : %s.\n", $session->error);
        $session->close;
        exit $ERRORS{'UNKNOWN'};
    }

    foreach my $key ( oid_lex_sort(keys %$result)) {
        my @oid_list = split (/\./,$key);
        my $index = pop (@oid_list) ;
        print "hrStorage $index :: $$result{$key}\n";
    }
exit $ERRORS{'OK'};
}

my $result = $session->get_request(
                                   -varbindlist => [$OID_hrStorageDescr.".".$partition  ,
                                                    $OID_hrStorageAllocationUnits.".".$partition  ,
                                                    $OID_hrStorageSize.".".$partition,
                                                    $OID_hrStorageUsed.".".$partition
                                                    ]
                                   );
if (!defined($result)) {
    printf("ERROR:  %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}
$hrStorageDescr  =  $result->{$OID_hrStorageDescr.".".$partition };
$AllocationUnits  =  $result->{$OID_hrStorageAllocationUnits.".".$partition };
$Size  =  $result->{$OID_hrStorageSize.".".$partition };
$Used  =  $result->{$OID_hrStorageUsed.".".$partition };


##
## Plugins var treatment
##
if (!$Size){
    print "Disk CRITICAL - no output (-p number expected... it doesn't exist, try another disk - number\n";
    exit $ERRORS{'CRITICAL'};
}
if (($Size =~  /([0-9]+)/) && ($AllocationUnits =~ /([0-9]+)/)){
    if (!$Size){
        print "The number of the option -p is not a hard drive\n";
        exit $ERRORS{'CRITICAL'};
    }
    $tot = 1;
    $tot = $Size * $AllocationUnits;
    if (!$tot){$tot = 1;}
    $used = $Used * $AllocationUnits;
    $pourcent = ($used * 100) / $tot;

    if (length($pourcent) > 2){
        @test = split (/\./, $pourcent);
        $pourcent = $test[0];
    }
    $tot = $tot / 1073741824;
    #$tot = $tot / 1000000000;
    $Used = ($Used * $AllocationUnits) / 1073741824;
    #$Used = ($Used * $AllocationUnits) / 1000000000;

    ##
    ## RRDtools update
    ##
    if ($opt_g) {
        $start=time;
        my $totrrd;
        $totrrd = $tot * 1073741824;
       #$totrrd = $tot * 1000000000;
		update_rrd ($rrd,$start,$totrrd,$used);
    }

    ##
    ## Plugin return code
    ##
        if ($pourcent >= $critical){
            print "Disk CRITICAL - ";
            $return_code = 2;
        }
        elsif ($pourcent >= $warning){
            print "Disk WARNING - ";
            $return_code = 1;
        }
        else {
            print "Disk OK - ";
            $return_code = 0;
        }

        if ($hrStorageDescr){
            print $hrStorageDescr . " TOTAL: ";
            printf("%.3f", $tot);
            print " Go USED: " . $pourcent . "% : ";
            printf("%.3f", $Used);
            print " Go\n";
            exit $return_code;
        }
        else {
            print "TOTAL: ";
            printf("%.3f", $tot);
            print " Go USED: " . $pourcent . "% : ";
            printf("%.3f", $Used);
            print " Go\n";
            exit $return_code;
        }
}
else {
    print "Disk CRITICAL - no output (-d number expected... it doesn't exist, try another disk - number\n";
    exit $ERRORS{'CRITICAL'};
}

sub print_usage () {
    print "\nUsage:\n";
    print "$PROGNAME\n";
    print "   -H (--hostname)   Hostname to query - (required)\n";
    print "   -C (--community)  SNMP read community (defaults to public,\n";
    print "                     used with SNMP v1 and v2c\n";
    print "   -v (--snmp_version)  1 for SNMP v1 (default)\n";
    print "                        2 for SNMP v2c\n";
    print "   -d (--disk)       Set the disk (number expected) ex: 1, 2,... (defaults to 2 )\n";
    print "   -s (--show)       Describes all disk (debug mode)\n";
    print "   -g (--rrdgraph)   create a rrd base and add datas into this one\n";
    print "   --rrd_step	    Specifies the base interval in seconds with which data will be fed into the RRD (300 by default)\n";
    print "   -D (--directory)  Path to rrdatabase (or create the .rrd in this directory)\n";
    print "                     by default: ".$pathtorrdbase."\n";
    print "                     (The path is valid with spaces '/my\ path/...')\n";
    print "   -w (--warn)       Signal strength at which a warning message will be generated\n";
    print "                     (default 80)\n";
    print "   -c (--crit)       Signal strength at which a critical message will be generated\n";
    print "                     (default 95)\n";
    print "   -S (--ServiceId)  Oreon Service Id\n";
    print "   -V (--version)    Plugin version\n";
    print "   -h (--help)       usage help\n";

}

sub print_help () {
    print "Copyright (c) 2004 OREON\n";
    print "Bugs to http://www.oreon.org/\n";
    print "\n";
    print_usage();
    print "\n";
}
