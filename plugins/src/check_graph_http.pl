#! /usr/bin/perl -w
#
# $Id: check_graph_http.pl,v 1.3 2005/08/01 18:03:52 gollum123 Exp $
#
# This plugin is developped under GPL Licence:
# http://www.fsf.org/licenses/gpl.txt

# Developped by Linagora SA: http://www.linagora.com

# Modified for Oreon Project by : Mathieu Chateau - Christophe Coraboeuf

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

use Getopt::Long;
use vars qw($opt_h $opt_V $opt_g $opt_D $opt_S $opt_H $opt_I $opt_e $opt_s $opt_u $opt_p $opt_P $opt_w $opt_c
            $opt_t $opt_a $opt_L $opt_f $opt_l $opt_r $opt_R $opt_z $opt_C $opt_step $step);
use vars qw($PROGNAME);

##
## Plugin var init
##
my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};
my $pathtolibexechttp = $oreon{GLOBAL}{ NAGIOS_LIBEXEC}."check_http";

$PROGNAME = "$0";
sub print_help ();
sub print_usage ();

Getopt::Long::Configure('bundling');
GetOptions
    ("h"     => \$opt_h, "help"             => \$opt_h,
     "V"     => \$opt_V, "version"          => \$opt_V,
     "g"     => \$opt_g, "rrdgraph"         => \$opt_g,
     "rrd_step=s" => \$opt_step,
     "S=s"   => \$opt_S, "ServiceId=s"      => \$opt_S,
     "H=s"   => \$opt_H, "hostname=s"       => \$opt_H,
     "I=s"   => \$opt_I, "IP-address=s"     => \$opt_I,
     "e=s"   => \$opt_e, "expect=s"         => \$opt_e,
     "s=s"   => \$opt_s, "string=s"         => \$opt_s,
     "u=s"   => \$opt_u, "url=s"            => \$opt_u,
     "p=s"   => \$opt_p, "port=s"           => \$opt_p,
     "P=s"   => \$opt_P, "post=s"           => \$opt_P,
     "w=s"   => \$opt_w, "warning=s"        => \$opt_w,
     "c=s"   => \$opt_c, "critical=s"       => \$opt_c,
     "t=s"   => \$opt_t, "timeout=s"        => \$opt_t,
     "a=s"   => \$opt_a, "authorization=s"  => \$opt_a,
     "L=s"   => \$opt_L, "link=s"           => \$opt_L,
     "f=s"   => \$opt_f, "onredirect=s"     => \$opt_f,
     "l=s"   => \$opt_l, "linespan=s"       => \$opt_l,
     "r=s"   => \$opt_r, "regex=s"          => \$opt_r,
     "R=s"   => \$opt_R, "eregi=s"          => \$opt_R,
     "C=s"   => \$opt_C, "certificate=s"    => \$opt_C,
     "z"   => \$opt_R, "ssl"              => \$opt_z

     );

if ($opt_V) {
    print_revision($PROGNAME,'$Revision: 1.3 $');
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

($opt_step) || ($opt_step = shift) || ($opt_step = "300");
$step = $1 if ($opt_step =~ /(\d+)/);

my $args_check_http = "";
if ( $opt_H ) {
    $args_check_http .= " -H $opt_H";
}
if ( $opt_I ) {
    $args_check_http .= " -I $opt_I";
}
if ( $opt_e ) {
    $args_check_http .= " -e $opt_e";
}
if ( $opt_s ) {
    $args_check_http .= " -s $opt_s";
}
if ( $opt_u ) {
    $args_check_http .= " -u $opt_u";
}
if ( $opt_p ) {
    $args_check_http .= " -p $opt_p";
}
if ( $opt_P ) {
    $args_check_http .= " -P $opt_P";
}
if ( $opt_I ) {
    $args_check_http .= " -I $opt_I";
}
if ( $opt_e ) {
    $args_check_http .= " -e $opt_e";
}
if ( $opt_w ) {
    $args_check_http .= " -w $opt_w";
}
if ( $opt_c ) {
    $args_check_http .= " -c $opt_c";
}
if ( $opt_t ) {
    $args_check_http .= " -t $opt_t";
}
if ( $opt_a ) {
    $args_check_http .= " -a $opt_a";
}
if ( $opt_L ) {
    $args_check_http .= " -L $opt_L";
}
if ( $opt_f ) {
    $args_check_http .= " -f $opt_f";
}
if ( $opt_l ) {
    $args_check_http .= " -l $opt_l";
}
if ( $opt_r ) {
    $args_check_http .= " -r $opt_r";
}
if ( $opt_R ) {
    $args_check_http .= " -R $opt_R";
}
if ( $opt_C ) {
    $args_check_http .= " -C $opt_C";
}
if ( $opt_z ) {
    $args_check_http .= " --ssl";
}


my $rrd = $pathtorrdbase.$ServiceId.".rrd";

my $start=time;
my $name = $0;
$name =~ s/\.pl.*//g;

##
## RRDTools create rrd
##
if ($opt_g) {
	if (! -e $rrd) {
        create_rrd ($rrd,1,$start,$step,"U","U","GAUGE");
	}
}
##
## Plugin requests
##
# print "args: $args_check_http \n";
my $result = `$pathtolibexechttp $args_check_http`;
my $return_code = $? / 256;

$_ = $result;
m/time=\s*(\d*\.\d*)/;
my $time = $1;

##
## RRDtools update
##
if ($opt_g && $time ) {
    $start=time;
    update_rrd ($rrd,$start,$time);
}

print "$result";
exit $return_code;

##
## Plugin return code
##
sub print_usage () {
    my $screen = `$pathtolibexechttp -h`;
    $screen =~ s/check_http/check_graph_http/g;
    $screen =~ s/-S/-Z/;
    print $screen;
}

sub print_help () {
    print "Copyright (c) 2005 LINAGORA SA\n";
    print "Bugs to http://www.linagora.com/\n";
    print "\n";
    print_usage();
    print "\n";
}
