#! /usr/bin/perl -w
#
# $Id: check_graph_dell_temperature.pl,v 1.1 2005/07/27 22:22:48 wistof Exp $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Wistof
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

# based on "graph plugins" developped by Oreon Team. See http://www.oreon.org.
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

use vars qw($PROGNAME $VERSION);
use Getopt::Long;
use vars qw($opt_h $opt_V $opt_g $opt_D $opt_S $opt_H $opt_C $opt_v $opt_s $opt_t $opt_step $step $sensor $OID $OID_DESC);

##
## Plugin var init
##


$VERSION = '$Revision: 1.1 $';
$VERSION =~ s/^\$.*:\W(.*)\W.+?$/$1/;

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
     "s"   => \$opt_s, "show"     => \$opt_s,
     "t=s"   => \$opt_t, "sensor=s"     => \$opt_t,
     "H=s" => \$opt_H, "hostname=s"   => \$opt_H);

if ($opt_V) {
    print_revision($PROGNAME,'$Revision: 1.1 $');
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

($opt_t) || ($opt_t = shift) || ($opt_t = "1");
my $sensor = $1 if ($opt_t =~ /(\d)/);

($opt_S) || ($opt_S = shift) || ($opt_S = "1_1");
my $ServiceId = is_valid_serviceid($opt_S);

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
          	oreon::create_rrd ($rrd,1,$start,$step,"U","U","GAUGE");
    }
}

##
## Plugin snmp requests
##
my $OID = ".1.3.6.1.4.1.674.10892.1.700.20.1.6.1";
my $OID_DESC = ".1.3.6.1.4.1.674.10892.1.700.20.1.8.1";


# create a SNMP session
my ( $session, $error ) = Net::SNMP->session(-hostname  => $opt_H,-community => $opt_C, -version  => $snmp);
if ( !defined($session) ) {
    print("UNKNOWN: $error");
    exit $ERRORS{'UNKNOWN'};
}

if ($opt_s) {
    # Get desctiption table
    my $result = $session->get_table(
        Baseoid => $OID_DESC
    );

    if (!defined($result)) {
        printf("ERROR: Description Table : %s.\n", $session->error);
        $session->close;
        exit $ERRORS{'UNKNOWN'};
    }

    foreach my $key ( oid_lex_sort(keys %$result)) {
        my @oid_list = split (/\./,$key);
        my $index = pop (@oid_list) ;
        print "Temperature Sensor $index :: $$result{$key}\n";
    }
exit $ERRORS{'OK'};
}


my $result = $session->get_request(
                                -varbindlist => [$OID.".".$sensor,
                                                  $OID_DESC.".".$sensor]
                                   );
if (!defined($result)) {
    printf("UNKNOWN: %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}

my $return_result =  $result->{$OID.".".$sensor};
my $un = 0;
if ($return_result =~ /(\d+)/ ) {
    $un = $1;
} else {
    printf("UNKNOWN:  Unable to parse SNMP Output :: %s", $return_result );
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}

$un =  sprintf("%02.2f", $un / 10);

##
## RRDtools update
##
if ($opt_g) {
    $start=time;
    oreon::update_rrd ($rrd,$start,$un);
}

##
## Plugin return code
##
if ($un || ( $un == 0) ){
    print "OK - ". $result->{$OID_DESC.".".$sensor} ." : $un\n";
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
    print "   --rrd_step	    Specifies the base interval in seconds with which data will be fed into the RRD (300 by default)\n";
    print "   -D (--directory)  Path to rrdatabase (or create the .rrd in this directory)\n";
    print "                     by default: ".$pathtorrdbase."\n";
    print "                     (The path is valid with spaces '/my\ path/...')\n";
    print "   -S (--ServiceId)  Oreon Service Id\n";
    print "   -t (--sensor)     Set the sensor number (1 by default)\n";
    print "   -s (--show)       Describes all sensors \n";
    print "   -V (--version)    Plugin version\n";
    print "   -h (--help)       usage help\n";

}

sub print_help () {
    print "Copyright (c) 2005 Oreon\n";
    print "Bugs to http://www.oreon.org/\n";
    print "\n";
    print_usage();
    print "\n";
}
