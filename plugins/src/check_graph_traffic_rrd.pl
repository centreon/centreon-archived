#! /usr/bin/perl -w
#
# $Id: check_graph_traffic,v 1.1 2004/10/21 17:00:00 projectOREON $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Last Developpement by : Jean Baptiste Gouret - Julien Mathis - Mathieu Mettre - Romain Le Merlus - Yohann Lecarpentier
#
# REVISED BY CVF 6/05/05
# Modified for Oreon Project by : Christophe Coraboeuf

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
	use oreon qw(get_parameters create_rrd update_rrd fetch_rrd &is_valid_serviceid);
	use vars qw($VERSION %oreon);
	%oreon=get_parameters();
} else {
	print "Unable to load oreon perl module\n";
    exit $ERRORS{'UNKNOWN'};
}

use vars qw($VERSION %oreon);

use Getopt::Long;
use vars qw($opt_V $opt_h $opt_g $opt_v $opt_C $opt_H $opt_D  $opt_step $opt_i $opt_w $opt_c $opt_s $opt_S $opt_T $opt_Y);
use Data::Dumper;

Getopt::Long::Configure('bundling');
    GetOptions  ("h"   => \$opt_h, "help"         => \$opt_h,
                 "s"   => \$opt_s, "show"         => \$opt_s,
                 "V"   => \$opt_V, "version"      => \$opt_V,
                 "g"   => \$opt_g, "rrdgraph"     => \$opt_g,
                 "D=s" => \$opt_D, "directory=s"  => \$opt_D,
                 "i=s" => \$opt_i, "interface=s"  => \$opt_i,
                 "v=s" => \$opt_v, "snmp=s"       => \$opt_v,
                 "C=s" => \$opt_C, "community=s"  => \$opt_C,
                 "w=s" => \$opt_w, "warning=s"    => \$opt_w,
                 "c=s" => \$opt_c, "critical=s"   => \$opt_c,
                 "S=s" => \$opt_S, "ServiceId=s"  => \$opt_S,
                 "H=s" => \$opt_H, "hostname=s"   => \$opt_H,
                 "T=s" => \$opt_T, "Speed=s"      => \$opt_T,
     			 "rrd_step=s" => \$opt_step,
                 );

my $PROGNAME = "check_graph_traffic_rrd";
my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};


sub print_help () {
    print "\n";
    print_revision($PROGNAME,'Revision: 1.1 ');
    print "\n";
    print_usage();
    print "\n";
    support();
    print "\n";
}

sub main () {

##
## Plugin var init
##
    my ($in_usage, $out_usage, $in_prefix, $out_prefix, $in_traffic, $out_traffic);
    my ($host, $snmp, $community);
    my ($interface, $ServiceId, $critical, $warning, $rrd, $start, $name);
    my $ERROR;
    my $result;
    my ($in_bits, $out_bits, $speed, $ds_names, $step, $data);
    my @valeur;
    my $i = 0;
    my ($line, $update_time, $rrdstep, $rrdheartbeat, $bitcounter);
    my $not_traffic = 1;

    my $OID_IN =$oreon{MIB2}{IF_IN_OCTET};
	my $OID_OUT = $oreon{MIB2}{IF_OUT_OCTET};
	my $OID_SPEED = $oreon{MIB2}{IF_SPEED};
	my $OID_DESC = $oreon{MIB2}{IF_DESC};

    if ($opt_V) { print_revision ($PROGNAME, "Revision: 1.1 "); exit $ERRORS{'OK'}; }
    if ($opt_h) { print_help(); exit $ERRORS{'OK'}; }
    if (defined($opt_H) && $opt_H =~ m/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/) { $host = $1; } else { print_usage(); exit $ERRORS{'UNKNOWN'}; }
    if ($opt_v) { $snmp = $opt_v; } else { $snmp = "2c"; }
    if ($opt_C) { $community = $opt_C; } else { $community = "public"; }
    if ($opt_s || (defined($opt_i) && $opt_i =~ /([0-9]+)/)) { $interface = $1;} else { print "\nUnknown -i number expected... or it doesn't exist, try another interface - number\n"; }
    if ($opt_g) {
    ($opt_S) || ($opt_S = shift) || ($opt_S = "1_1");
		$ServiceId = is_valid_serviceid($opt_S);
    }
    if (defined($opt_step) && $opt_step =~ /([0-9]+)/) { $rrdstep = $1; $rrdheartbeat = $rrdstep * 2; } else { $rrdstep = 300; $rrdheartbeat = $rrdstep * 2; }
    if (defined($opt_c) && $opt_c =~ /([0-9]+)/) { $critical = $1; } else { $critical = 95; }
    if (defined($opt_w) && $opt_w =~ /([0-9]+)/) { $warning = $1; } else { $warning = 80; }
    if (defined($opt_c) && defined($opt_w) && $critical <= $warning){ print "(--crit) must be superior to (--warn)"; print_usage(); exit $ERRORS{'OK'}; }
    if (defined($opt_D) && $opt_D =~ /([-.\/\_\ A-Za-z0-9]+)/) { $pathtorrdbase = $1; }

	($opt_v) || ($opt_v = shift) || ($opt_v = "1");
	$snmp = $1 if ($opt_v =~ /(\d)/);

##
## RRDTool var init
##
    if ($opt_i) {
        $OID_IN .= ".".$interface;
        $OID_OUT .= ".".$interface;
        $OID_SPEED .= ".".$interface;
    }

    if ($opt_g && $opt_S) {
	    $rrd = $pathtorrdbase.$ServiceId.".rrd";
	    $start = time;
	    $name = $0;
	    $name =~ s/\.pl.*//g;

		##
		## RRDTool create rrd
		##

        if (!(-e $rrd)) {
	        $_ = `/usr/bin/snmpwalk  $host -c $community -v $snmp $OID_IN 2>/dev/null`;
	        if ($_ =~ m/Counter(\d+)/) { $bitcounter = $1; } else { $bitcounter = 32; }
	        $bitcounter = 2 ** $bitcounter;
	        create_rrd ($rrd,2,$start,$rrdstep,0,$bitcounter,"COUNTER");
      }
    }


##
## Plugin snmp requests
##

  #  if ($opt_s) { $_ = `/usr/bin/snmpwalk  $host -c $community -v $snmp $OID_DESC 2>/dev/null`; print $_; exit $ERRORS{'OK'}; }


# create a SNMP session #
    my ( $session, $error ) = Net::SNMP->session(-hostname  => $host,-community => $community, -version  => $snmp);
    if ( !defined($session) ) { print("UNKNOWN: $error"); exit $ERRORS{'UNKNOWN'}; }


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

# get IN bits #
    $result = $session->get_request( -varbindlist => [$OID_IN] );
    if (!defined($result)) { printf("ERROR : IN : %s.\n", $session->error); $session->close; exit($ERRORS{"CRITICAL"}); }
    $in_bits =  $result->{$OID_IN} * 8;


# get OUT bits #
    $result = $session->get_request( -varbindlist => [$OID_OUT] );
    if (!defined($result)) { printf("ERROR : OUT : %s.\n", $session->error); $session->close; exit($ERRORS{"CRITICAL"}); }
    $out_bits = $result->{$OID_OUT} * 8;


# Get SPEED of interface #
    if (!defined($opt_T) || $opt_T == 0) {
	    $result = $session->get_request( -varbindlist => [$OID_SPEED] );
	    if (!defined($result)) { printf("ERROR: %s.\n", $session->error); $session->close; exit($ERRORS{"CRITICAL"}); }
	    $speed = $result->{$OID_SPEED};
    }
    else {
	    $speed = $opt_T * 1000000;
    }


##
## RRDtools update
##

    if ($opt_g) {
        $start=time;
    	update_rrd ($rrd,$start,$in_bits,$out_bits);

	##
	## Get the real value in rrdfile
	##
   # ($update_time,$step,$ds_names,$data) = RRDs::fetch($rrd, "--resolution=300","--start=now-5min","--end=now","AVERAGE");
   #  foreach $line (@$data) {
    #    foreach $val (@$line) {
	#        if ( defined $val ) { $valeur[$i]=$val; } else { $valeur[$i]="undef"; }
	#        $i++;
   #    }
 #   }*/
    @valeur = fetch_rrd($rrd,"AVERAGE");
    $in_traffic = $valeur[0];
    $out_traffic = $valeur[1];
  }

    if (!(defined($in_traffic)) && !(defined($out_traffic))) {
	   $not_traffic = 0;
    } else {
	    $in_prefix = " ";
	    $out_prefix = " ";

	    if (!($in_traffic eq "undef")) {
	        if ($in_traffic > 1000000) {
		        $in_usage = sprintf("%.2f",($in_traffic/($speed))*100);
		        $in_traffic = sprintf("%.2f",$in_traffic/1000000);
		        $in_prefix = "M";
	        }
	        elsif ($in_traffic > 1000) {
		        $in_usage = sprintf("%.2f",($in_traffic/($speed))*100);
		        $in_traffic = sprintf("%.2f",$in_traffic/1000);
		        $in_prefix = "K";
	        }
	        elsif ($in_traffic < 1000) {
		        $in_usage = sprintf("%.2f",($in_traffic/($speed))*100);
		        $in_traffic = sprintf("%.2f",$in_traffic);
		        $in_prefix = " ";
	        }
	        else {
	        print "ERROR\n"; exit 1;
	        }

	    } else {
	    	 $in_usage = 0 ;
	    }

	    if (!($out_traffic eq "undef")) {
	        if ($out_traffic > 1000000) {
		        $out_usage = sprintf("%.2f",($out_traffic/($speed))*100);
		        $out_traffic = sprintf("%.2f",$out_traffic/1000000);
		        $out_prefix = "M";
	        }
	        elsif ($out_traffic > 1000) {
		        $out_usage = sprintf("%.2f",($out_traffic/($speed))*100);
		        $out_traffic = sprintf("%.2f",$out_traffic/1000);
		        $out_prefix = "K";
	        }
	        elsif ($out_traffic < 1000) {
		        $out_usage = sprintf("%.2f",($out_traffic/($speed))*100);
		        $out_traffic = sprintf("%.2f",$out_traffic);
		        $out_prefix = " ";
	        }
	    } else {
	    	$out_usage = 0 ;
	    }
    }

##
## Plugin return code && Status
##

    if ( $speed == 0 ) {
	    print "CRITICAL: Interface speed equal 0! Interface must be down.\n";
	    exit($ERRORS{"CRITICAL"});
    }
    else {
	    $speed = sprintf("%.2f",($speed/1000000));
    }

    if ($not_traffic != 1) {
	    print "Counter: IN = $in_bits bits and OUT = $out_bits bits - Traffic cannot be calculated when the last value from the rrdfile is `undef' (check if the `-g' option is enabled)\n"; exit($ERRORS{"OK"});
    } else {
	    if(($in_usage > $critical) || ($out_usage > $critical)) {
	        print "CRITICAL: (".$critical."%) depassed threshold. Traffic: $in_traffic ".$in_prefix."b/s (".$in_usage."%) in, $out_traffic ".$out_prefix."b/s (".$out_usage."%) out - Speed Interface = ".$speed." Mb/s \n";
    	    exit($ERRORS{"CRITICAL"});
	    }
    	if(($in_usage > $warning) || ($out_usage > $warning)) {
	        print "WARNING: (".$warning."%) depassed threshold. Traffic: $in_traffic ".$in_prefix."b/s (".$in_usage."%) in, $out_traffic ".$out_prefix."b/s (".$out_usage."%) out - Speed Interface = ".$speed." Mb/s\n";
    	    exit($ERRORS{"WARNING"});
    	}
    print "OK: Traffic: $in_traffic ".$in_prefix."b/s (".$in_usage."%) in, $out_traffic ".$out_prefix."b/s (".$out_usage."%) out - Speed Interface = ".$speed." Mb/s\n $opt_g"; exit($ERRORS{"OK"});
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
    print "   -D (--directory)  Path to rrdatabase (or create the .rrd in this directory)\n";
    print "                     by default: ".$pathtorrdbase."\n";
    print "                     (The path is valid with spaces '/my\ path/...')\n";
    print "   -s (--show)       Describes all interfaces number (debug mode)\n";
    print "   -i (--interface)  Set the interface number (2 by default)\n";
    print "   -T (--speed)      Set the speed interface in Mbit/s (by default speed interface capacity)\n";
    print "   --rrdstep         Set the rrdstep in second (5 minuntes by default)\n";
    print "   -w (--warn)       Signal strength at which a warning message will be generated\n";
    print "                     (default 80)\n";
    print "   -c (--crit)       Signal strength at which a critical message will be generated\n";
    print "                     (default 95)\n";
    print "   -S (--ServiceId)  Oreon Service Id\n";
    print "   -V (--version)    Plugin version\n";
    print "   -h (--help)       usage help\n\n";
}

main ();
