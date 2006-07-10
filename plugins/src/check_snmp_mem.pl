#!/usr/bin/perl -w
############################## check_snmp_mem ##############
# Version : 0.9
# Date : Jul 20 2005
# Author  : Patrick Proy (patrick at proy.org)
# Help : http://www.manubulon.com/nagios/
# Licence : GPL - http://www.fsf.org/licenses/gpl.txt
# TODO : snmpv3
#################################################################
#
# Help : ./check_snmp_mem.pl -h
#

use strict;
use Net::SNMP;
use Getopt::Long;

# Nagios specific

use lib "@NAGIOS_PLUGINS@";
use utils qw(%ERRORS $TIMEOUT);
#my $TIMEOUT = 15;
#my %ERRORS=('OK'=>0,'WARNING'=>1,'CRITICAL'=>2,'UNKNOWN'=>3,'DEPENDENT'=>4);

# Oreon specific

#use lib "@NAGIOS_PLUGINS@";
if (eval "require oreon" ) {
  use oreon qw(get_parameters create_rrd update_rrd &is_valid_serviceid);
  use vars qw($VERSION %oreon);
  %oreon=get_parameters();
} else {
  print "Unable to load oreon perl module\n";
    exit $ERRORS{'UNKNOWN'};
}

my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};

# SNMP Datas

# Net-snmp memory

my $nets_ram_free	= "1.3.6.1.4.1.2021.4.6.0";  # Real memory free
my $nets_ram_total	= "1.3.6.1.4.1.2021.4.5.0";  # Real memory total
my $nets_swap_free	= "1.3.6.1.4.1.2021.4.4.0";  # swap memory free
my $nets_swap_total	= "1.3.6.1.4.1.2021.4.3.0";  # Swap memory total
my @nets_oids		= ($nets_ram_free,$nets_ram_total,$nets_swap_free,$nets_swap_total);

# Cisco

my $cisco_mem_pool      = "1.3.6.1.4.1.9.9.48.1.1.1"; # Cisco memory pool
my $cisco_index         = "1.3.6.1.4.1.9.9.48.1.1.1.2"; # memory pool name and index
my $cisco_valid         = "1.3.6.1.4.1.9.9.48.1.1.1.4"; # Valid memory if 1
my $cisco_used          = "1.3.6.1.4.1.9.9.48.1.1.1.5"; # Used memory
my $cisco_free          = "1.3.6.1.4.1.9.9.48.1.1.1.6"; # Used memory
# .1 : type, .2 : name, .3 : alternate, .4 : valid, .5 : used, .6 : free, .7 : max free

# HP Procurve

my $hp_mem_pool		= "1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1";   # HP memory pool
my $hp_mem_index	= "1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.1"; # memory slot index
my $hp_mem_total	= "1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.5"; # Total Bytes
my $hp_mem_free		= "1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.6"; # Free Bytes
my $hp_mem_free_seg	= "1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.3"; # Free segments

# AS/400

# Windows NT/2K/(XP?)

# check_snmp_storage.pl -C <community> -H <hostIP> -m "^Virtual Memory$"  -w <warn %> -c <crit %>


# Globals

my $Version='0.9';

my $o_host = 	undef; 		# hostname
my $o_community = undef; 	# community
my $o_port = 	161; 		# port
my $o_help=	undef; 		# wan't some help ?
my $o_verb=	undef;		# verbose mode
my $o_version=	undef;		# print version
my $o_netsnmp=	1;		# Check with netsnmp (default)
my $o_cisco=	undef;		# Check cisco router mem
my $o_hp=	undef;		# Check hp procurve mem
my $o_warn=	undef;		# warning level option
my $o_warnR=	undef;		# warning level for Real memory
my $o_warnS=	undef;		# warning levels for swap
my $o_crit=	undef;		# Critical level option
my $o_critR=	undef;		# critical level for Real memory
my $o_critS=	undef;		# critical level for swap
my $o_perf=	undef;		# Performance data option
my $o_timeout=  5;             	# Default 5s Timeout
my $o_version2= undef;          # use snmp v2c
# SNMPv3 specific
my $o_login=	undef;		# Login for snmpv3
my $o_passwd=	undef;		# Pass for snmpv3

# Oreon specific
my $o_step=	undef;
my $o_g=	undef;
my $o_S=	undef;
my $step=	undef;
my $rrd=	undef;
my $start=	undef;
my $ServiceId=	undef;


# functions

sub p_version { print "check_snmp_mem version : $Version\n"; }

sub print_usage {
    print "Usage: $0 [-v] -H <host> -C <snmp_community> [-2] | (-l login -x passwd)  [-p <port>] -w <warn level> -c <crit level> [-I|-N|-E] [-f] [-t <timeout>] [-V]\n";
}

sub isnnum { # Return true if arg is not a number
  my $num = shift;
  if ( $num =~ /^(\d+\.?\d*)|(^\.\d+)$/ ) { return 0 ;}
  return 1;
}

sub round ($$) {
    sprintf "%.$_[1]f", $_[0];
}

sub help {
   print "\nSNMP Memory Monitor for Nagios version ",$Version,"\n";
   print "(c)2004 to my cat Ratoune - Author: Patrick Proy\n\n";
   print_usage();
   print <<EOT;
-v, --verbose
   print extra debugging information (including interface list on the system)
-h, --help
   print this help message
-H, --hostname=HOST
   name or IP address of host to check
-C, --community=COMMUNITY NAME
   community name for the host's SNMP agent (implies SNMP v1 or v2c with option)
-2, --v2c
   Use snmp v2c
-l, --login=LOGIN
   Login for snmpv3 authentication (implies v3 protocol with MD5)
-x, --passwd=PASSWD
   Password for snmpv3 authentication
-P, --port=PORT
   SNMP port (Default 161)
-w, --warn=INTEGER | INT,INT
   warning level for memory in percent (0 for no checks)
     Default (-N switch) : comma separated level for Real Memory and Swap
     -I switch : warning level
-c, --crit=INTEGER | INT,INT
   critical level for memory in percent (0 for no checks)
     Default (-N switch) : comma separated level for Real Memory and Swap
     -I switch : critical level
-N, --netsnmp (default)
   check linux memory & swap provided by Net SNMP
-I, --cisco
   check cisco memory (sum of all memory pools)
-E, --hp
   check HP proccurve memory
-f, --perfdata
   Performance data output
-t, --timeout=INTEGER
   timeout for SNMP in seconds (Default: 5)
-V, --version
   prints version number
-g (--rrdgraph)   Create a rrd base if necessary and add datas into this one
--rrd_step	     Specifies the base interval in seconds with which data will be fed into the RRD (300 by default)
-S (--ServiceId)  Oreon Service Id
EOT
}

# For verbose output
sub verb { my $t=shift; print $t,"\n" if defined($o_verb) ; }

# Get the alarm signal (just in case snmp timout screws up)
$SIG{'ALRM'} = sub {
     print ("ERROR: Alarm signal (Nagios time-out)\n");
     exit $ERRORS{"UNKNOWN"};
};

sub check_options {
    Getopt::Long::Configure ("bundling");
    GetOptions(
   	'v'	=> \$o_verb,		'verbose'	=> \$o_verb,
        'h'     => \$o_help,    	'help'        	=> \$o_help,
        'H:s'   => \$o_host,		'hostname:s'	=> \$o_host,
        'p:i'   => \$o_port,   		'port:i'	=> \$o_port,
        'C:s'   => \$o_community,	'community:s'	=> \$o_community,
	'l:s'	=> \$o_login,		'login:s'	=> \$o_login,
	'x:s'	=> \$o_passwd,		'passwd:s'	=> \$o_passwd,
        't:i'   => \$o_timeout,       	'timeout:i'     => \$o_timeout,
	'V'	=> \$o_version,		'version'	=> \$o_version,
	'I'	=> \$o_cisco,		'cisco'		=> \$o_cisco,
	'N'	=> \$o_netsnmp,		'netsnmp'	=> \$o_netsnmp,
        'E'	=> \$o_hp,		'hp'		=> \$o_hp,
        '2'     => \$o_version2,        'v2c'           => \$o_version2,
        'c:s'   => \$o_crit,            'critical:s'    => \$o_crit,
        'w:s'   => \$o_warn,            'warn:s'        => \$o_warn,
        'f'     => \$o_perf,            'perfdata'      => \$o_perf,
# For Oreon rrdtool graph
  "rrd_step:s" => \$o_step,
  "g"   => \$o_g, "rrdgraph"     => \$o_g,
  "S=s" => \$o_S, "ServiceId=s"  => \$o_S
    );
    if (defined ($o_help) ) { help(); exit $ERRORS{"UNKNOWN"}};
    if (defined($o_version)) { p_version(); exit $ERRORS{"UNKNOWN"}};
    if ( ! defined($o_host) ) # check host and filter
	{ print "No host defined!\n";print_usage(); exit $ERRORS{"UNKNOWN"}}
    # check snmp information
    if ( !defined($o_community) && (!defined($o_login) || !defined($o_passwd)) )
	{ print "Put snmp login info!\n"; print_usage(); exit $ERRORS{"UNKNOWN"}}
    #Check Warning and crit are present
    if ( ! defined($o_warn) || ! defined($o_crit))
 	{ print "Put warning and critical values!\n"; print_usage(); exit $ERRORS{"UNKNOWN"}}
    # Get rid of % sign
    $o_warn =~ s/\%//g;
    $o_crit =~ s/\%//g;
    # if -N or -E switch , undef $o_netsnmp
    if (defined($o_cisco) || defined($o_hp) ) {
      $o_netsnmp=undef;
      if ( isnnum($o_warn) || isnnum($o_crit))
	{ print "Numeric value for warning or critical !\n";print_usage(); exit $ERRORS{"UNKNOWN"} }
      if ( ($o_crit != 0) && ($o_warn > $o_crit) )
        { print "warning <= critical ! \n";print_usage(); exit $ERRORS{"UNKNOWN"}}
    }
    if (defined($o_netsnmp)) {
      my @o_warnL=split(/,/ , $o_warn);
      my @o_critL=split(/,/ , $o_crit);
      if (($#o_warnL != 1) || ($#o_critL != 1))
	{ print "2 warnings and critical !\n";print_usage(); exit $ERRORS{"UNKNOWN"}}
      for (my $i=0;$i<2;$i++) {
	if ( isnnum($o_warnL[$i]) || isnnum($o_critL[$i]))
	    { print "Numeric value for warning or critical !\n";print_usage(); exit $ERRORS{"UNKNOWN"} }
	if (($o_critL[$i]!= 0) && ($o_warnL[$i] > $o_critL[$i]))
	   { print "warning <= critical ! \n";print_usage(); exit $ERRORS{"UNKNOWN"}}
 	if ( $o_critL[$i] > 100)
	   { print "critical percent must be < 100 !\n";print_usage(); exit $ERRORS{"UNKNOWN"}}
      }
      $o_warnR=$o_warnL[0];$o_warnS=$o_warnL[1];
      $o_critR=$o_critL[0];$o_critS=$o_critL[1];
    }

    ###### Oreon #######

	if (!defined($o_S)) { $o_S="1_1" }
	$ServiceId = is_valid_serviceid($o_S);

	if (!defined($o_step)) { $o_step="300" }
	$step = $1 if ($o_step =~ /(\d+)/);

}

########## MAIN #######

check_options();

$rrd = $pathtorrdbase.$ServiceId.".rrd";
$start=time;

# Check gobal timeout if snmp screws up
if (defined($TIMEOUT)) {
  verb("Alarm at $TIMEOUT");
  alarm($TIMEOUT);
} else {
  verb("no timeout defined : $o_timeout + 10");
  alarm ($o_timeout+10);
}

# Connect to host
my ($session,$error);
if ( defined($o_login) && defined($o_passwd)) {
  # SNMPv3 login
  verb("SNMPv3 login");
  ($session, $error) = Net::SNMP->session(
      -hostname   	=> $o_host,
      -version		=> '3',
      -username		=> $o_login,
      -authpassword	=> $o_passwd,
      -authprotocol	=> 'md5',
      -privpassword	=> $o_passwd,
      -timeout          => $o_timeout
   );
} else {
   if (defined ($o_version2)) {
     # SNMPv2 Login
	 ($session, $error) = Net::SNMP->session(
	-hostname  => $o_host,
	    -version   => 2,
	-community => $o_community,
	-port      => $o_port,
	-timeout   => $o_timeout
     );
   } else {

    # SNMPV1 login
    ($session, $error) = Net::SNMP->session(
       -hostname  => $o_host,
       -community => $o_community,
       -port      => $o_port,
       -timeout   => $o_timeout
    );
  }
}
if (!defined($session)) {
   printf("ERROR opening session: %s.\n", $error);
   exit $ERRORS{"UNKNOWN"};
}

# Global variable
my $resultat=undef;

########### Cisco memory check ############
if (defined ($o_cisco)) {

  # Get Cisco memory table
  $resultat = (Net::SNMP->VERSION < 4) ?
                 $session->get_table($cisco_mem_pool)
                 :$session->get_table(Baseoid => $cisco_mem_pool);

  if (!defined($resultat)) {
    printf("ERROR: Description table : %s.\n", $session->error);
    $session->close;
    exit $ERRORS{"UNKNOWN"};
  }
  my (@oid,@index)=(undef,undef);
  my $nindex=0;
  foreach my $key ( keys %$resultat) {
     verb("OID : $key, Desc : $$resultat{$key}");
     if ( $key =~ /$cisco_index/ ) {
	@oid=split (/\./,$key);
	$index[$nindex++] = pop(@oid);
     }
  }

  # Check if at least 1 memory pool exists
  if ($nindex == 0) {
   printf("ERROR: No memory pools found");
   $session->close;
   exit $ERRORS{"UNKNOWN"};
  }

  # Consolidate the datas
  my ($used,$free)=(0,0);
  my ($c_output,$prct_free)=(undef,undef);
  foreach (@index) {
    if ( $$resultat{$cisco_valid . "." . $_} == 1 ) {
      $c_output .="," if defined ($c_output);
      $used += $$resultat{$cisco_used . "." . $_};
      $free += $$resultat{$cisco_free . "." . $_};
      $c_output .= $$resultat{$cisco_index . "." . $_} . ":"
		 .round($$resultat{$cisco_used . "." . $_}*100/($$resultat{$cisco_free . "." . $_}+$$resultat{$cisco_used . "." . $_}) ,0)
		 . "%";
    }
  }
  my $total=$used+$free;
  $prct_free=round($used*100/($total),0);
  verb("Used : $used, Free: $free, Output : $c_output");

  ##
  ## RRD management
  ##

	if ($o_g) {
		$start=time;
		 if (! -e $rrd) {
	        create_rrd($rrd,1,$start,$step,0,100,"GAUGE");
	     }
	     update_rrd($rrd,$start,$prct_free);
	}

  my $c_status="OK";
  $c_output .=" : " . $prct_free ."% : ";
  if (($o_crit!=0)&&($o_crit <= $prct_free)) {
    $c_output .= " > " . $o_crit ;
    $c_status="CRITICAL";
  } else {
    if (($o_warn!=0)&&($o_warn <= $prct_free)) {
      $c_output.=" > " . $o_warn;
      $c_status="WARNING";
    }
  }
  $c_output .= " ; ".$c_status;
  if (defined ($o_perf)) {
    $c_output .= " | ram_used=" . $used.";";
    $c_output .= ($o_warn ==0)? ";" : round($o_warn * $total/100,0).";";
    $c_output .= ($o_crit ==0)? ";" : round($o_crit * $total/100,0).";";
    $c_output .= "0;" . $total ;
  }
  $session->close;
  print "$c_output \n";
  exit $ERRORS{$c_status};
}

########### HP Procurve memory check ############
if (defined ($o_hp)) {

  # Get hp memory table
  $resultat = (Net::SNMP->VERSION < 4) ?
                 $session->get_table($hp_mem_pool)
                 :$session->get_table(Baseoid => $hp_mem_pool);

  if (!defined($resultat)) {
    printf("ERROR: Description table : %s.\n", $session->error);
    $session->close;
    exit $ERRORS{"UNKNOWN"};
  }
  my (@oid,@index)=(undef,undef);
  my $nindex=0;
  foreach my $key ( keys %$resultat) {
     verb("OID : $key, Desc : $$resultat{$key}");
     if ( $key =~ /$hp_mem_index/ ) {
	@oid=split (/\./,$key);
	$index[$nindex++] = pop(@oid);
     }
  }

  # Check if at least 1 memory slots exists
  if ($nindex == 0) {
   printf("ERROR: No memory slots found");
   $session->close;
   exit $ERRORS{"UNKNOWN"};
  }

  # Consolidate the datas
  my ($total,$free)=(0,0);
  my ($c_output,$prct_free)=(undef,undef);
  foreach (@index) {
    $c_output .="," if defined ($c_output);
    $total += $$resultat{$hp_mem_total . "." . $_};
    $free += $$resultat{$hp_mem_free . "." . $_};
    $c_output .= "Slot " . $$resultat{$hp_mem_index . "." . $_} . ":"
 		 .round(
		   100 - ($$resultat{$hp_mem_free . "." . $_} *100 /
                        $$resultat{$hp_mem_total . "." . $_}) ,0)
		 . "%";
  }
  my $used = $total - $free;
  $prct_free=round($used*100/($total),0);
  verb("Used : $used, Free: $free, Output : $c_output");

  ##
  ## RRD management
  ##

	if ($o_g) {
		$start=time;
		 if (! -e $rrd) {
	        create_rrd($rrd,1,$start,$step,0,100,"GAUGE");
	     }
	     update_rrd($rrd,$start,$prct_free);
	}

  my $c_status="OK";
  $c_output .=" : " . $prct_free ."% : ";
  if (($o_crit!=0)&&($o_crit <= $prct_free)) {
    $c_output .= " > " . $o_crit ;
    $c_status="CRITICAL";
  } else {
    if (($o_warn!=0)&&($o_warn <= $prct_free)) {
      $c_output.=" > " . $o_warn;
      $c_status="WARNING";
    }
  }
  $c_output .= " ; ".$c_status;
  if (defined ($o_perf)) {
    $c_output .= " | ram_used=" . $used.";";
    $c_output .= ($o_warn ==0)? ";" : round($o_warn * $total/100,0).";";
    $c_output .= ($o_crit ==0)? ";" : round($o_crit * $total/100,0).";";
    $c_output .= "0;" . $total ;
  }
  $session->close;
  print "$c_output \n";
  exit $ERRORS{$c_status};
}

########### Net snmp memory check ############
if (defined ($o_netsnmp)) {

  # Get NetSNMP memory values
  $resultat = (Net::SNMP->VERSION < 4) ?
		$session->get_request(@nets_oids)
		:$session->get_request(-varbindlist => \@nets_oids);

  if (!defined($resultat)) {
    printf("ERROR: netsnmp : %s.\n", $session->error);
    $session->close;
    exit $ERRORS{"UNKNOWN"};
  }

  my ($realused,$swapused)=(undef,undef);

  $realused= ($$resultat{$nets_ram_total} == 0) ? 0 :
		($$resultat{$nets_ram_total}-$$resultat{$nets_ram_free})/$$resultat{$nets_ram_total};
  $swapused= ($$resultat{$nets_swap_total} == 0) ? 0 :
		($$resultat{$nets_swap_total}-$$resultat{$nets_swap_free})/$$resultat{$nets_swap_total};
  $realused=round($realused*100,0);
  $swapused=round($swapused*100,0);
  verb ("Ram : $$resultat{$nets_ram_free} / $$resultat{$nets_ram_total} : $realused");
  verb ("Swap : $$resultat{$nets_swap_free} / $$resultat{$nets_swap_total} : $swapused");

  ##
  ## RRD management
  ##

	if ($o_g) {
		$start=time;
		 if (! -e $rrd) {
	        create_rrd($rrd,2,$start,$step,0,100,"GAUGE");
	     }
	     update_rrd($rrd,$start,$realused, $swapused );
	}



  my $n_status="OK";
  my $n_output="Ram : " . $realused . "%, Swap : " . $swapused . "% :";
  if ((($o_critR!=0)&&($o_critR <= $realused)) || (($o_critS!=0)&&($o_critS <= $swapused))) {
    $n_output .= " > " . $o_critR . ", " . $o_critS;
    $n_status="CRITICAL";
  } else {
    if ((($o_warnR!=0)&&($o_warnR <= $realused)) || (($o_warnS!=0)&&($o_warnS <= $swapused))) {
      $n_output.=" > " . $o_warnR . ", " . $o_warnS;
      $n_status="WARNING";
    }
  }
  $n_output .= " ; ".$n_status;
  if (defined ($o_perf)) {
    $n_output .= " | ram_used=" . ($$resultat{$nets_ram_total}-$$resultat{$nets_ram_free}).";";
    $n_output .= ($o_warnR ==0)? ";" : round($o_warnR * $$resultat{$nets_ram_total}/100,0).";";
    $n_output .= ($o_critR ==0)? ";" : round($o_critR * $$resultat{$nets_ram_total}/100,0).";";
    $n_output .= "0;" . $$resultat{$nets_ram_total}. " ";
    $n_output .= "swap_used=" . ($$resultat{$nets_swap_total}-$$resultat{$nets_swap_free}).";";
    $n_output .= ($o_warnS ==0)? ";" : round($o_warnS * $$resultat{$nets_swap_total}/100,0).";";
    $n_output .= ($o_critS ==0)? ";" : round($o_critS * $$resultat{$nets_swap_total}/100,0).";";
    $n_output .= "0;" . $$resultat{$nets_swap_total};
  }
  $session->close;
  print "$n_output \n";
  exit $ERRORS{$n_status};

}
