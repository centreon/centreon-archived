#! /usr/bin/perl -w
#
# $Id: check_graph_traffic.pl,v 1.2 2005/07/27 22:21:49 wistof Exp $
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

use vars qw($VERSION %oreon);
use vars qw(%oreon);
$VERSION = '$Revision: 1.2 $';
$VERSION =~ s/^\$.*:\W(.*)\W.+?$/$1/;

use vars qw($PROGNAME);
use Getopt::Long;
use vars qw($opt_V $opt_h $opt_g $opt_v $opt_C $opt_b $opt_H $opt_D $opt_i $opt_w $opt_c $opt_s $opt_S $opt_T $opt_step $step);

my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};
my $pathtotrafficmap = $oreon{GLOBAL}{DIR_OREON}."include/trafficMap/average/";


##
## Plugin var init
##
my($proc, $proc_run, @test, $row, @laste_values, $last_check_time);
my($last_in_bytes, $last_out_bytes, @last_values, $update_time);
my($db_file, $in_traffic, $out_traffic, $in_usage, $out_usage);

$PROGNAME = "check_graph_traffic";
sub print_help ();
sub print_usage ();

Getopt::Long::Configure('bundling');
GetOptions
    ("h"   => \$opt_h, "help"         => \$opt_h,
     "s"   => \$opt_s, "show"         => \$opt_s,
     "V"   => \$opt_V, "version"      => \$opt_V,
     "g"   => \$opt_g, "rrdgraph"     => \$opt_g,
     "rrd_step=s" => \$opt_step,
     "i=s" => \$opt_i, "interface=s"  => \$opt_i,
     "v=s" => \$opt_v, "snmp=s"       => \$opt_v,
     "C=s" => \$opt_C, "community=s"  => \$opt_C,
     "b=s" => \$opt_b, "bps=s"        => \$opt_b,
     "w=s" => \$opt_w, "warning=s"    => \$opt_w,
     "c=s" => \$opt_c, "critical=s"   => \$opt_c,
     "S=s" => \$opt_S, "ServiceId=s"  => \$opt_S,
     "T=s" => \$opt_T, "TrafficMap=s"  => \$opt_T,
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


($opt_C) || ($opt_C = shift) || ($opt_C = "public");

($opt_i) || ($opt_i = shift) || ($opt_i = 2);
my $interface;
if ($opt_i =~ /([0-9]+)/){
    $interface = $1;
}
else{
    print "Unknown -i number expected... or it doesn't exist, try another interface - number\n";
    exit $ERRORS{'UNKNOWN'};
}

($opt_S) || ($opt_S = shift) || ($opt_S = "1_1");
my $ServiceId = is_valid_serviceid($opt_S);

($opt_b) || ($opt_b = shift) || ($opt_b = 95);
my $bps = $1 if ($opt_b =~ /([0-9]+)/);

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

($opt_T) || ($opt_T = shift) || ($opt_T = $pathtotrafficmap);
$pathtotrafficmap = $1 if ($opt_T =~ /([-.\/\_\ A-Za-z0-9]+)/);

##
## RRDTool var init
##
my $rrd = $pathtorrdbase.$ServiceId.".rrd";
my $start=time;
my $name = $0;
$name =~ s/\.pl.*//g;

##
## RRDTool create rrd
##
if ($opt_g) {
    if (! -e $rrd) {
         create_rrd ($rrd,2,$start,$step,"U","U","COUNTER");
    }
}
##
## Plugin snmp requests
##

my $OID_IN =$oreon{MIB2}{IF_IN_OCTET}.".".$interface;
my $OID_OUT = $oreon{MIB2}{IF_OUT_OCTET}.".".$interface;
my $OID_SPEED = $oreon{MIB2}{IF_SPEED}.".".$interface;
my $OID_DESC =$oreon{MIB2}{IF_DESC};

# create a SNMP session
my ( $session, $error ) = Net::SNMP->session(-hostname  => $opt_H,-community => $opt_C, -version  => $snmp);
if ( !defined($session) ) {
    print("UNKNOWN: SNMP Session : $error");
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
        print "Interface $index :: $$result{$key}\n";
    }
exit $ERRORS{'OK'};
}


# get IN bytes
my $result = $session->get_request(
                                   -varbindlist => [$OID_IN]
                                   );
if (!defined($result)) {
    printf("ERROR: IN Bytes :  %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}
my $in_bytes;
$in_bytes =  $result->{$OID_IN};
#print "in_bytes: $in_bytes\n";


# get OUT bytes
my $out_bytes;
$result = $session->get_request(
                                -varbindlist => [$OID_OUT]
                                );
if (!defined($result)) {
    printf("ERROR: Out Bytes : %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}
$out_bytes = $result->{$OID_OUT};
#print "out_bytes: $out_bytes\n";

# Get SPEED of interface
$result = $session->get_request(
                                -varbindlist => [$OID_SPEED]
                                );
if (!defined($result)) {
    printf("ERROR: Interface Speed : %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}
my $speed;
$speed = $result->{$OID_SPEED};
#print "speed: $speed \n";
##
## Plugin return code
##

##
## Status
##
my $last_check_time_status = 0;
my $last_in_bytes_status = 0;
my $last_out_bytes_stastus = 0;
my $flg = 0;
$last_in_bytes = 0;
$last_out_bytes  = 0;

if (-e "/tmp/traffic_if".$interface."_".$opt_H ) {
    open(FILE,"<"."/tmp/traffic_if".$interface."_".$opt_H);
    while($row = <FILE>)
    {
     @last_values = split(":",$row);
     $last_check_time = $last_values[0];
     $last_in_bytes = $last_values[1];
     $last_out_bytes = $last_values[2];
     ##
     ## Status
     ##
     $last_check_time_status = $last_values[3];
     $last_in_bytes_status = $last_values[4];
     $last_out_bytes_stastus = $last_values[5];
     $flg = $last_values[6];
    }
    close(FILE);
}

$update_time = time;

unless (open(FILE,">"."/tmp/traffic"."_if".$interface."_".$opt_H) )
    {
        print "Unknown - /tmp/traffic"."_if".$interface."_".$opt_H. " $!\n";
        exit $ERRORS{"UNKNOWN"};
    }
print FILE "$update_time:$in_bytes:$out_bytes";

##
## TRAFFIC - map depends:Status
##
if (!$flg) { #if new
print FILE ":$update_time:$in_bytes:$out_bytes:17";
}
my $hour;
$hour = `/bin/date +%k 2>/dev/null`;
#$hour = 17;
if ($hour =~ /^17$/){
    if ($flg =~ /^18$/){ # 17H flg=18 (update done)
    $flg = 18;
    print FILE ":$last_check_time_status:$last_in_bytes_status:$last_out_bytes_stastus:$flg";
    }
    if ($flg =~ /^17$/){ # 17H flg=17
    $flg = 18;
    print FILE ":$update_time:$in_bytes:$out_bytes:$flg";
    }
} else {
    if ($flg =~ /^18$/){# =! 17H flg 18
    $flg = 17;
    }
    print FILE ":$last_check_time_status:$last_in_bytes_status:$last_out_bytes_stastus:$flg";

}
close(FILE);

$in_traffic = sprintf("%.2f",($in_bytes-$last_in_bytes_status)/(time-$last_check_time_status));
$out_traffic = sprintf("%.2f",($out_bytes-$last_out_bytes_stastus)/(time-$last_check_time_status));
my $in_traffic2;
my $out_traffic2;
$in_traffic2 = int($in_traffic);
$out_traffic2 = int($out_traffic);

unless (open(HTML,">".$pathtotrafficmap.$opt_H."_".$ServiceId.".html") )
    {
        print "Unknown - $pathtotrafficmap".$opt_H."_".$ServiceId.".html - $!\n";
        exit $ERRORS{"UNKNOWN"};
    }

my $sortie = "<html>\n<head>\n<!-- cuin d $in_traffic2 --> <!-- cuout d $out_traffic2 -->\n</head>\n<body>\n</body>\n</html>";
print HTML $sortie;

close(HTML);


## Bandwith = IN + OUT / Delta(T) = 6 Mb/s
## (100 * Bandwith) / (2(si full duplex) * Ispeed)
if ($in_bytes - $last_in_bytes > 0) {
    $in_traffic = sprintf("%.2f",($in_bytes-$last_in_bytes)/(time-$last_check_time));
}
if ($out_bytes - $last_out_bytes > 0) {
    $out_traffic = sprintf("%.2f",($out_bytes-$last_out_bytes)/(time-$last_check_time));
}

if ( $speed != 0 ) {
    $in_usage = sprintf("%.1f",($in_traffic*100)/$speed);
    $out_usage = sprintf("%.1f",($out_traffic*100)/$speed);


##
## RRDtools update
##
    if ($opt_g) {
        $start=time;
    	update_rrd ($rrd,$start,$in_bytes,$out_bytes);
    }
}


my $in_prefix;
my $out_prefix;
$in_prefix = " ";
$out_prefix = " ";
if($in_traffic > 1024)
{
 $in_traffic = sprintf("%.2f",$in_traffic/1024);
 $in_prefix = "k";
 if($in_traffic > 1024)
 {
  $in_traffic = sprintf("%.2f",$in_traffic/1024);
  $in_prefix = "M";
 }
}

if($out_traffic > 1024)
{
 $out_traffic = sprintf("%.2f",$out_traffic/1024);
 $out_prefix = "k";
 if($out_traffic > 1024)
 {
  $out_traffic = sprintf("%.2f",$out_traffic/1024);
  $out_prefix = "M";
 }
}

$in_bytes = sprintf("%.2f",($in_bytes/1024)/1024);
$out_bytes = sprintf("%.2f",($out_bytes/1024)/1024);

if ( $speed == 0 ) {
    print "<br>CRITICAL: Interface speed equal 0! Interface must be down.\n";
    exit($ERRORS{"CRITICAL"});
}

if(($in_usage > $critical) or ($out_usage > $critical) )
{
 print "<br>CRITICAL: (".$critical."%) bandwidth utilization.\n";
 exit($ERRORS{"CRITICAL"});
}

if(($in_usage > $warning) or ($out_usage > $warning))
{
 print "WARNING: (".$warning."%) bandwidth utilization.\n";
 exit($ERRORS{"WARNING"});
}


print "Traffic: $in_traffic ".$in_prefix."B/s (".$in_usage."%) in, $out_traffic ".$out_prefix."B/s (".$out_usage."%) out - ";
print "Total RX Bytes: $in_bytes MB, Total TX Bytes: $out_bytes MB \n";
exit($ERRORS{"OK"});


sub print_usage () {
    print "\nUsage:\n";
    print "$PROGNAME\n\n";
    print "This plugin directly store data in COUNTER format RRD\n\n";
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
    print "   -T (--TrafficMap) Path to trafficmap (or create the .html in this directory)\n";
    print "                     by default: ".$pathtotrafficmap."\n";
    print "                     (The path is valid with spaces '/my\ path/...')\n";
    print "   -s (--show)       Describes all interfaces number (debug mode)\n";
    print "   -i (--interface)  Set the interface number (2 by default)\n";
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
