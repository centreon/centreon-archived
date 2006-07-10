#! /usr/bin/perl -w
#
# $Id: check_graph_uptime.pl,v 1.2 2005/07/27 22:21:49 wistof Exp $
#
# This plugin is developped under GPL Licence:
# http://www.fsf.org/licenses/gpl.txt

# Developped by Linagora SA: http://www.linagora.com

# Modified for Oreon Project by : Mathieu Chateau - Christophe Coraboeuf
# Modified For Oreon compatibility by Julien Mathis For Merethis
#
# The Software is provided to you AS IS and WITH ALL FAULTS.
# LINAGORA makes no representation and gives no warranty whatsoever,
# whether express or implied, and without limitation, with regard to the quality,
# safety, contents, performance, merchantability, non-infringement or suitability for
# any particular or intended purpose of the Software found on the LINAGORA web site.
# In no event will LINAGORA be liable for any direct, indirect, punitive, special,
# incidental or consequential damages however they may arise and even if LINAGORA has
# been previously advised of the possibility of such damages.

# based on "graph plugins" developped by Oreon Team. See http://www.oreon.org.
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
use vars qw($opt_h $opt_V $opt_g $opt_D $opt_S $opt_H $opt_C $opt_v $opt_d $day $opt_step);
my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};

$PROGNAME = $0;
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
     "d"   => \$opt_d, "day"     => \$opt_d,
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

($opt_S) || ($opt_S = shift) || ($opt_S = "1_1");
my $ServiceId = is_valid_serviceid($opt_S);

($opt_C) || ($opt_C = shift) || ($opt_C = "public");

my $rrd = $pathtorrdbase.$ServiceId.".rrd";

my $start=time;
my $name = $0;
$name =~ s/\.pl.*//g;
my $day = 0;

##
## RRDTools create rrd
##
if ($opt_g) {
    if (! -e $rrd) {
         create_rrd($rrd,1,$start,300,"U","U","GAUGE");
    }
}

##
## Plugin snmp requests
##

my $OID_OBJECTID =$oreon{MIB2}{OBJECTID};
my $OID_UPTIME_WINDOWS =$oreon{MIB2}{UPTIME_WINDOWS};
my $OID_UPTIME_OTHER =$oreon{MIB2}{UPTIME_OTHER};

# create a SNMP session
my ( $session, $error ) = Net::SNMP->session(-hostname  => $opt_H,-community => $opt_C, -version  => $snmp);
if ( !defined($session) ) {
    print("CRITICAL: $error");
    exit $ERRORS{'CRITICAL'};
}

my $result = $session->get_request(
                                -varbindlist => [$OID_OBJECTID]
                                   );
if (!defined($result)) {
    printf("UNKNOWN: %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}

my $return_result =  $result->{$OID_OBJECTID};
my $OID = "";
if ($return_result =~ /.*Windows.*/i ) {
    $OID = $OID_UPTIME_WINDOWS;
} else {
    $OID = $OID_UPTIME_OTHER;
}

$result = $session->get_request(
                   -varbindlist => [$OID]
                   );
if (!defined($result)) {
    printf("UNKNOWN: %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}

my $un = 0;

$return_result =  $result->{$OID};
if ( $return_result =~ m/(\d*) day[s]?,\s*(\d*):(\d*):(\d*).(\d*)/ ) {
 $un = $5 + $4 * 100 + $3 * 100 * 60 + $2 * 100 * 60 * 60 + $1 * 100 * 60 * 60 * 24;
 $day = $1;
}

if ( $return_result =~ m/(\d*) hour.*(\d*):(\d*).(\d*)/ ) {
 $un = $4 + $3 * 100 + $3 * 100 * 60 + $1 * 100 * 60 * 60 ;
}

if ($opt_d) {
    $un = $day;
}

#print "un : $un\n";

##
## RRDtools update
##
if ($opt_g) {
    $start=time;
    update_rrd($rrd,$start,$un);
}

##
## Plugin return code
##

if ($un || ( $un == 0) ){
    if ($opt_d) {
        print "OK - Uptime (in day): $un|uptime=".$un."hs\n";
    } else {
        print "OK - Uptime (in hundredths of a second): $un|uptime=".$un."hs\n";
    }
    exit $ERRORS{'OK'};
}
else{
   print "CRITICAL Host unavailable\n";
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
    print "   -g (--rrdgraph)   create a rrd base and add datas into this one\n";
    print "   -D (--directory)  Path to rrdatabase (or create the .rrd in this directory)\n";
    print "                     by default: ".$pathtorrdbase."\n";
    print "                     (The path is valid with spaces '/my\ path/...')\n";
    print "   -S (--ServiceId)  Oreon Service Id\n";
    print "   -d (--day)        Uptime in day\n";
    print "   -V (--version)    Plugin version\n";
    print "   -h (--help)       usage help\n";

}

sub print_help () {
    print "Copyright (c) 2005 Linagora\n";
    print "Modified by Merethis \n";
    print "Bugs to http://www.linagora.com/\n";
    print "\n";
    print_usage();
    print "\n";
}
