#! /usr/bin/perl -w
#
# $Id: check_snmp_value.pl,v 1.2 2005/11/17 10:21:49 Julien Mathis $
#
# This plugin is developped under GPL Licence:
# http://www.fsf.org/licenses/gpl.txt
#
# Developped by Merethis SARL : http://www.merethis.com
#
# The Software is provided to you AS IS and WITH ALL FAULTS.
# MERETHIS makes no representation and gives no warranty whatsoever,
# whether express or implied, and without limitation, with regard to the quality,
# safety, contents, performance, merchantability, non-infringement or suitability for
# any particular or intended purpose of the Software found on the LINAGORA web site.
# In no event will MERETHIS be liable for any direct, indirect, punitive, special,
# incidental or consequential damages however they may arise and even if MERETHIS has
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
use vars qw($opt_h $opt_V $opt_g $opt_D $opt_S $opt_H $opt_C $opt_v $opt_o $opt_c $opt_w $opt_f $opt_t);
use vars qw($ServiceId $rrd $snmp $DS_type);
my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};

$PROGNAME = $0;
sub print_help ();
sub print_usage ();

Getopt::Long::Configure('bundling');
GetOptions
    ("h"   => \$opt_h, "help"         => \$opt_h,
     "V"   => \$opt_V, "version"      => \$opt_V,
     "g"   => \$opt_g, "rrdgraph"     => \$opt_g,
     "f"   => \$opt_f,
     "v=s" => \$opt_v, "snmp=s"       => \$opt_v,
     "C=s" => \$opt_C, "community=s"  => \$opt_C,
     "S=s" => \$opt_S, "ServiceId=s"  => \$opt_S,
     "o=s"   => \$opt_o, "oid=s"          => \$opt_o,
     "t=s"   => \$opt_t, "type=s"          => \$opt_t,
     "w=s" => \$opt_w, "warning=s"    => \$opt_w,
     "c=s" => \$opt_c, "critical=s"   => \$opt_c,
     "H=s" => \$opt_H, "hostname=s"   => \$opt_H);

if ($opt_V) {
    print_revision($PROGNAME,'$Revision: 1.0');
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

($opt_t) || ($opt_t = shift) || ($opt_t = "GAUGE");
my $DS_type = $1 if ($opt_t =~ /(GAUGE)/ || $opt_t =~ /(COUNTER)/);

($opt_c) || ($opt_c = shift);
my $critical = $1 if ($opt_c =~ /([0-9]+)/);

($opt_w) || ($opt_w = shift);
my $warning = $1 if ($opt_w =~ /([0-9]+)/);
if ($critical <= $warning){
    print "(--critical) must be superior to (--warning)";
    print_usage();
    exit $ERRORS{'OK'};
}



my $start=time;
my $name = $0;
$name =~ s/\.pl.*//g;
my $day = 0;


#===  RRDTools create rrd  ====


if ($opt_g) {
    if (! -e $rrd) {
		create_rrd($rrd,1,$start,300,"U","U",$DS_type);
    }
}

#===  create a SNMP session ====

my ($session, $error) = Net::SNMP->session(-hostname  => $opt_H,-community => $opt_C, -version  => $snmp);
if (!defined($session)) {
    print("CRITICAL: $error");
    exit $ERRORS{'CRITICAL'};
}

my $result = $session->get_request(-varbindlist => [$opt_o]);
if (!defined($result)) {
    printf("UNKNOWN: %s.\n", $session->error);
    $session->close;
    exit $ERRORS{'UNKNOWN'};
}

my $return_result =  $result->{$opt_o};

#===  RRDtools update  ====

if ($opt_g) {
    $start=time;
    update_rrd($rrd,$start,$return_result);
}

#===  Plugin return code  ====
if (defined($return_result)){
    if ($opt_w && $opt_c && $return_result < $opt_w){
    	print "Ok value : " . $return_result;
	if ($opt_f){ print "|value=".$return_result.";".$opt_w.";".$opt_c.";;";}
	print "\n";
	exit $ERRORS{'OK'};
    } elsif ($opt_w && $opt_c && $return_result >= $opt_w && $return_result < $opt_c){
	print "Warning value : " . $return_result;
	if ($opt_f){ print "|value=$return_result;".$opt_w.";".$opt_c.";;";}
	print "\n";
	exit $ERRORS{'WARNING'};
    } elsif ($opt_w && $opt_c && $return_result >= $opt_c){
    	print "Critical value : " . $return_result;
	if ($opt_f){ print "|value=".$return_result.";".$opt_w.";".$opt_c.";;";}
	print "\n";
	exit $ERRORS{'CRITICAL'};
    }
} else {
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
    print "   -t (--type)       Data Source Type (GAUGE or COUNTER) (GAUGE by default)\n";
    print "   -g (--rrdgraph)   create a rrd base and add datas into this one\n";
    print "   -D (--directory)  Path to rrdatabase (or create the .rrd in this directory)\n";
    print "                     by default: ".$pathtorrdbase."\n";
    print "                     (The path is valid with spaces '/my\ path/...')\n";
    print "   -S (--ServiceId)  Oreon Service Id\n";
    print "   -o (--oid)        OID to check\n";
    print "   -w (--warning)    Warning level\n";
    print "   -c (--critical)   Critical level\n";
    print "   -V (--version)    Plugin version\n";
    print "   -h (--help)       usage help\n";
    print "   -f                Perfparse Compatible\n";
}

sub print_help () {
    print "#=========================================\n";
    print "#  Copyright (c) 2005 Merethis SARL      =\n";
    print "#  Developped by Julien Mathis           =\n";
    print "#  Bugs to http://www.oreon-project.org/ =\n";
    print "#=========================================\n";
    print_usage();
    print "\n";
}
