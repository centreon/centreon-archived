#! /usr/bin/perl -w
#
# $Id: check_graph_load_average.pl,v 1.2 2005/07/27 22:21:49 wistof Exp $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Julien Mathis - Romain Le Merlus
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
#use lib "@NAGIOS_PLUGINS@";
use lib "/usr/local/nagios/libexec/";
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
use vars qw($opt_V $opt_h $opt_g $opt_v $opt_C $opt_H $opt_D $opt_S $opt_step $step $snmp $opt_f);


##
## Plugin var init
##
my($return_code);

my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};

$PROGNAME = "check_graph_load_average";
sub print_help ();
sub print_usage ();

Getopt::Long::Configure('bundling');
GetOptions
    ("h"   => \$opt_h, "help"         => \$opt_h,
     "V"   => \$opt_V, "version"      => \$opt_V,
     "g"   => \$opt_g, "rrdgraph"     => \$opt_g,
     "rrd_step=s" => \$opt_step,
     "v=s" => \$opt_v, "snmp=s"       => \$opt_v,
     "C=s" => \$opt_C, "community=s"  => \$opt_C,
     "S=s" => \$opt_S, "ServiceId=s"  => \$opt_S,
     "H=s" => \$opt_H, "hostname=s"   => \$opt_H, 
     "f" => \$opt_f);

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

($opt_S) || ($opt_S = shift) || ($opt_S = "1_1");
my $ServiceId = is_valid_serviceid($opt_S);

($opt_v) || ($opt_v = shift) || ($opt_v = "2");
$snmp = $1 if ($opt_v =~ /(\d)/);

($opt_C) || ($opt_C = shift) || ($opt_C = "public");

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
          	create_rrd ($rrd,3,$start,$step,"U","U","GAUGE");
    }
}

##
## Plugin snmp requests
##
$return_code = 0;

my $OID_CPULOAD_1 =$oreon{UNIX}{CPU_LOAD_1M};
my $OID_CPULOAD_5 =$oreon{UNIX}{CPU_LOAD_5M};
my $OID_CPULOAD_15 =$oreon{UNIX}{CPU_LOAD_15M};

my ( $session, $error ) = Net::SNMP->session(-hostname  => $opt_H,-community => $opt_C, -version  => $snmp);
if ( !defined($session) ) {
    print("UNKNOWN: $error");
    exit $ERRORS{'UNKNOWN'};
}

my $result = $session->get_request(
                                -varbindlist => [$OID_CPULOAD_1, $OID_CPULOAD_5, $OID_CPULOAD_15 ]
                                   );
if (!defined($result)) {
    printf("UNKNOWN: %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}

my $un =  $result->{$OID_CPULOAD_1};
my $cinq  =  $result->{$OID_CPULOAD_5};
my $quinze  =  $result->{$OID_CPULOAD_15};

##
## RRDtools update
##
if ($opt_g && ( $return_code == 0) ) {
    $start=time;
    update_rrd ($rrd,$start,$un,$cinq,$quinze);
}

##
## Plugin return code
##

my $PERFPARSE = "";

if ($return_code == 0){
    if ($opt_f){
		$PERFPARSE = "|load1=".$un."%;;;0;100 load5=".$cinq."%;;;0;100 load15=".$quinze."%;;;0;100";
    }
    print "load average: $un, $cinq, $quinze".$PERFPARSE."\n";
    exit $ERRORS{'OK'};
} else {
    print "Load Average CRITICAL\n";
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
    print "   -g (--rrdgraph)   create  à rrd base and add datas into this one\n";
    print "   --rrd_step	    Specifies the base interval in seconds with which data will be fed into the RRD (300 by default)\n";
    print "   -D (--directory)  Path to rrdatabase (or create the .rrd in this directory)\n";
    print "                     by default: ".$pathtorrdbase."\n";
    print "                     (The path is valid with spaces '/my\ path/...')\n";
    print "   -S (--ServiceId)  Oreon Service Id\n";
    print "   -V (--version)    Plugin version\n";
    print "   -h (--help)       usage help\n";
    print "   -f                Perfparse Compatible\n";
}

sub print_help () {
    print "Copyright (c) 2004-2005 OREON\n";
    print "Bugs to http://bugs.oreon-project.org/\n";
    print "\n";
    print_usage();
    print "\n";
}
