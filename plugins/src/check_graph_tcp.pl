#! /usr/bin/perl -w
# $Id: check_graph_tcp.pl,v 1.2 2005/08/01 17:50:50 gollum123 Exp $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Julien Mathis - Romain Le Merlus
#
# Modified for Oreon Project by : Mathieu Chateau - Christophe Coraboeuf
# Modified By Julien Mathis For Merethis Company
#
# The Software is provided to you AS IS and WITH ALL FAULTS.
# OREON makes no representation and gives no warranty whatsoever,
# whether express or implied, and without limitation, with regard to the quality,
# safety, contents, performance, merchantability, non-infringement or suitability for
# any particular or intended purpose of the Software found on the OREON web site.
# In no event will OREON be liable for any direct, indirect, punitive, special,
# incidental or consequential damages however they may arise and even if OREON has
# been previously advised of the possibility of such damages.

use strict;
use FindBin;
use lib "$FindBin::Bin";
#use lib "@NAGIOS_PLUGINS@";
use lib "/usr/lib/nagios/plugins";
use utils qw($TIMEOUT %ERRORS &print_revision &support);

if (eval "require oreon" ) {
	use oreon qw(get_parameters create_rrd update_rrd &is_valid_serviceid);
	use vars qw($VERSION %oreon);
	%oreon=get_parameters();
} else {
	print "Unable to load oreon perl module\n";
    exit $ERRORS{'UNKNOWN'};
}

use Getopt::Long;
use vars qw($opt_V $opt_h $opt_p $opt_c $opt_w $opt_H $opt_S $opt_g $opt_D $opt_step);
use vars qw($PROGNAME);

my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};
my $pathtolibexectcp = $oreon{GLOBAL}{NAGIOS_LIBEXEC}."check_tcp";

sub print_help ();
sub print_usage ();
$PROGNAME = "check_graph_tcp";

#
# get options
#

Getopt::Long::Configure('bundling');
GetOptions
    ("h|help"   		=> \$opt_h,
     "V|version"   		=> \$opt_V,
     "H|hostname=s" 	=> \$opt_H,
     "p|port=s" 		=> \$opt_p,
     "w|warning=s" 		=> \$opt_w,
     "c|critical=s" 	=> \$opt_c,
     "S|ServiceId=s" 	=> \$opt_S,
     "rrd_step=s" 		=> \$opt_step,
     "g|rrdgraph"   	=> \$opt_g);

if (defined($opt_h)) {
	print_help();
    exit $ERRORS{'OK'};
}

$opt_H = shift unless ($opt_H);
(print_usage() && exit $ERRORS{'OK'}) unless ($opt_H);

($opt_S) || ($opt_S = shift) || ($opt_S = "1_1");
my $ServiceId = is_valid_serviceid($opt_S);

($opt_step) || ($opt_step = shift) || ($opt_step = "300");
my $step = $1 if ($opt_step =~ /(\d+)/);

##################################################
#### Create Command line 
#

my $args_check_tcp = "-H $opt_H -p $opt_p";
$args_check_tcp .= " -w $opt_w -c $opt_c" if ($opt_w && $opt_c);

my $start=time;


##
## RRDTools create rrd
##
if ($opt_g) {
	create_rrd ($pathtorrdbase.$ServiceId.".rrd",1,$start,$step,"U","U","GAUGE") if (! -e $pathtorrdbase.$ServiceId.".rrd");
}

my $result = `$pathtolibexectcp $args_check_tcp`;
my $return_code = $? / 256;
$_ = $result;
m/(\d*\.\d*) second/;
my $time = $1;

#
# RRDtools update
#

update_rrd ($pathtorrdbase.$ServiceId.".rrd",$start,$time) if ($opt_g && $time );

print "$result";
exit $return_code;

sub print_usage () {
    print "\nUsage:\n";
    print $0."\n";
    print "\t-H (--hostname)\t\tHostname to query (required)\n";
    print "\t-p, --port\t\tPort number (required)\n";
    print "\t-w, --warning\t\tResponse time to result in warning status (seconds) - optional\n";
    print "\t-c, --critical\t\tResponse time to result in critical status (seconds) - optional\n";
    print "\t--rrd_step\t\tSpecifies the base interval in seconds with which data will be fed into the RRD (300 by default)\n";
    print "\t-V (--version)\t\tVieuw plugin version\n";
    print "\t-h (--help)\t\tusage help\n";

}

sub print_help () {
    print "################################################\n";
    print "#    Bugs to http://bugs.oreon-project.org/    #\n";
    print "################################################\n";
    print "\n";
    print_usage();
    print "\n";
}






