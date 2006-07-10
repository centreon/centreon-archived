#!/usr/bin/perl -w
############################## check_snmp_load #################
# Version : 1.2 / BETA
# Date : Aug 27 2005
# Author  : Patrick Proy ( patrick at proy.org)
# Help : http://www.manubulon.com/nagios/
# Licence : GPL - http://www.fsf.org/licenses/gpl.txt
# Changelog : HP Procurve
# TODO :
#################################################################
#
# Help : ./check_snmp_load.pl -h
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

# Generic with host-ressource-mib
my $base_proc = "1.3.6.1.2.1.25.3.3.1";   # oid for all proc info
my $proc_id   = "1.3.6.1.2.1.25.3.3.1.1"; # list of processors (product ID)
my $proc_load = "1.3.6.1.2.1.25.3.3.1.2"; # %time the proc was not idle over last minute

# Linux load

my $linload_table= "1.3.6.1.4.1.2021.10.1";   # net-snmp load table
my $linload_name = "1.3.6.1.4.1.2021.10.1.2"; # text 'Load-1','Load-5', 'Load-15'
my $linload_load = "1.3.6.1.4.1.2021.10.1.3"; # effective load table

# Cisco cpu/load

my $cisco_cpu_5m = "1.3.6.1.4.1.9.2.1.58.0"; # Cisco CPU load (5min %)
my $cisco_cpu_1m = "1.3.6.1.4.1.9.2.1.57.0"; # Cisco CPU load (1min %)
my $cisco_cpu_5s = "1.3.6.1.4.1.9.2.1.56.0"; # Cisco CPU load (5sec %)

# AS/400 CPU

my $as400_cpu = "1.3.6.1.4.1.2.6.4.5.1.0"; # AS400 CPU load (10000=100%);

# Net-SNMP CPU

my $ns_cpu_idle   = "1.3.6.1.4.1.2021.11.11.0"; # Net-snmp cpu idle
my $ns_cpu_user   = "1.3.6.1.4.1.2021.11.9.0";  # Net-snmp user cpu usage
my $ns_cpu_system = "1.3.6.1.4.1.2021.11.10.0"; # Net-snmp system cpu usage

# Procurve CPU
my $procurve_cpu = "1.3.6.1.4.1.11.2.14.11.5.1.9.6.1.0"; # Procurve CPU Counter

# Nokia CPU
my $nokia_cpu = "1.3.6.1.4.1.94.1.21.1.7.1.0"; # Nokia CPU % usage

# Bluecoat Appliance
my $bluecoat_cpu = "1.3.6.1.4.1.3417.2.4.1.1.1.4.1"; # Bluecoat %cpu usage.

# Linkproof Appliance
my $linkproof_cpu= "1.3.6.1.4.1.89.35.1.53.0"; # Ressource utilisation (%) Considers network utilization and internal CPU utilization
# 1.3.6.1.4.1.89.35.1.54 : CPU only (%)
# 1.3.6.1.4.1.89.35.1.55 : network only (%)

# CPU OID array
my %cpu_oid = ("netsc",$ns_cpu_idle,"as400",$as400_cpu,"bc",$bluecoat_cpu,"nokia",$nokia_cpu,"hp",$procurve_cpu,"lp",$linkproof_cpu);

# Globals

my $Version='1.2';

my $o_host = 	undef; 		# hostname
my $o_community = undef; 	# community
my $o_port = 	161; 		# port
my $o_help=	undef; 		# wan't some help ?
my $o_verb=	undef;		# verbose mode
my $o_version=	undef;		# print version
my $o_check_type= "stand";	# check type  : stand | netsc |  netsl | as400 | cisco | bc | nokia | hp | lp
# For backward compatibility
my $o_linux=	undef;		# Check linux load instead of CPU
my $o_linuxC=	undef;		# Check Net-SNMP CPU
my $o_as400=	undef;		# Check for AS 400 load
my $o_cisco=	undef;		# Check for Cisco CPU
# End compatibility
my $o_warn=	undef;		# warning level
my @o_warnL=	undef;		# warning levels for Linux Load or Cisco CPU
my $o_crit=	undef;		# critical level
my @o_critL=	undef;		# critical level for Linux Load or Cisco CPU
my $o_timeout=  5;             	# Default 5s Timeout
my $o_perf=     undef;          # Output performance data
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

sub p_version { print "check_snmp_load version : $Version\n"; }

sub print_usage {
    print "Usage: $0 [-v] -H <host> -C <snmp_community> [-2] | (-l login -x passwd)  [-p <port>] -w <warn level> -c <crit level> -T=[stand|netsl|netsc|as400|cisco|bc|nokia|hp|lp] [-f] [-t <timeout>] [-V]\n";
}

sub isnnum { # Return true if arg is not a number
  my $num = shift;
  if ( $num =~ /^(\d+\.?\d*)|(^\.\d+)$/ ) { return 0 ;}
  return 1;
}

sub help {
   print "\nSNMP Load & CPU Monitor for Nagios version ",$Version,"\n";
   print "(c)2004 to my cat Ratoune - Author : Patrick Proy\n\n";
   print_usage();
   print <<EOT;
-v, --verbose
   print extra debugging information
-h, --help
   print this help message
-H, --hostname=HOST
   name or IP address of host to check
-C, --community=COMMUNITY NAME
   community name for the host's SNMP agent (implies v1 protocol)
-2, --v2c
   Use snmp v2c
-l, --login=LOGIN
   Login for snmpv3 authentication (implies v3 protocol with MD5)
-x, --passwd=PASSWD
   Password for snmpv3 authentication
-P, --port=PORT
   SNMP port (Default 161)
-w, --warn=INTEGER | INT,INT,INT
   warning level for cpu in percent (on one minute)
   if -L switch then comma separated level for load-1,load-5,load-15
   if -I switch then comma separated level for cpu 5s,cpu 1m,cpu 5m
-c, --crit=INTEGER | INT,INT,INT
   critical level for cpu in percent (on one minute)
   if -L switch then comma separated level for load-1,load-5,load-15
   if -I switch then comma separated level for cpu 5s,cpu 1m,cpu 5m
-T, --type=stand|netsl|netsc|as400|cisco|bc|nokia|hp|lp
  CPU check :
    stand : standard MIBII (works with Windows),
            can handle multiple CPU.
    netsl : check linux load provided by Net SNMP
    netsc : check cpu usage given by net-snmp (100-idle)
    as400 : check as400 CPU usage
    cisco : check cisco CPU usage
    bc    : check bluecoat CPU usage
    nokia : check nokia CPU usage
    hp    : check HP procurve switch CPU usage
    lp    : Linkproof CPU usage
-f, --perfparse
   Perfparse compatible output
-t, --timeout=INTEGER
   timeout for SNMP in seconds (Default: 5)
-V, --version
   prints version number
-L, --linux, -A, --as400, -I, --cisco, -N, --netsnmp
   These options are for backward compatibility (version<1.2)
-g (--rrdgraph)   Create a rrd base if necessary and add datas into this one
--rrd_step	     Specifies the base interval in seconds with which data will be fed into the RRD (300 by default)
-S (--ServiceId)  Oreon Service Id
EOT
}

# For verbose output
sub verb { my $t=shift; print $t,"\n" if defined($o_verb) ; }

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

  '2'     => \$o_version2,        'v2c'           => \$o_version2,
        'c:s'   => \$o_crit,            'critical:s'    => \$o_crit,
        'w:s'   => \$o_warn,            'warn:s'        => \$o_warn,
        'f'     => \$o_perf,            'perfparse'     => \$o_perf,
    'T:s'		=> \$o_check_type,			'type:s'		=> \$o_check_type,
#  For backward compatibility
  'L'	=> \$o_linux,		'linux'		=> \$o_linux,
  'A'	=> \$o_as400,		'as400'		=> \$o_as400,
  'I'	=> \$o_cisco,		'cisco'		=> \$o_cisco,
  'N'	=> \$o_linuxC,		'netsnmp'	=> \$o_linuxC,
# For Oreon rrdtool graph
  "rrd_step:s" => \$o_step,
  "g"   => \$o_g, "rrdgraph"     => \$o_g,
  "S=s" => \$o_S, "ServiceId=s"  => \$o_S
    );
  # For backward compat
  if (defined($o_linux)) { $o_check_type="netsl" }
  if (defined($o_linuxC)) { $o_check_type="netsc" }
  if (defined($o_as400)) { $o_check_type="as400"}
  if (defined($o_cisco)) { $o_check_type="cisco"}
    # TODO : check the -T option
    if (defined ($o_help) ) { help(); exit $ERRORS{"UNKNOWN"}};
    if (defined($o_version)) { p_version(); exit $ERRORS{"UNKNOWN"}};
    if ( ! defined($o_host) ) # check host and filter
  { print_usage(); exit $ERRORS{"UNKNOWN"}}
    # check snmp information
    if ( !defined($o_community) && (!defined($o_login) || !defined($o_passwd)) )
  { print "Put snmp login info!\n"; print_usage(); exit $ERRORS{"UNKNOWN"}}
    # Check warnings and critical
    if (!defined($o_warn) || !defined($o_crit))
   { print "put warning and critical info!\n"; print_usage(); exit $ERRORS{"UNKNOWN"}}
    # Get rid of % sign
    $o_warn =~ s/\%//g;
    $o_crit =~ s/\%//g;
    # Check for multiple warning and crit in case of -L
    if (($o_warn =~ /,/) || ($o_crit =~ /,/)) {
    if (($o_check_type ne "netsl") && ($o_check_type ne "cisco")) { print "Multiple warning without -L or -I switch\n";print_usage(); exit $ERRORS{"UNKNOWN"}}
    @o_warnL=split(/,/ , $o_warn);
    @o_critL=split(/,/ , $o_crit);
     if (($#o_warnL != 2) || ($#o_critL != 2))
      { print "3 warnings and critical !\n";print_usage(); exit $ERRORS{"UNKNOWN"}}
    for (my $i=0;$i<3;$i++) {
        if ( isnnum($o_warnL[$i]) || isnnum($o_critL[$i]))
      { print "Numeric value for warning or critical !\n";print_usage(); exit $ERRORS{"UNKNOWN"}}
      if ($o_warnL[$i] > $o_critL[$i])
       { print "warning <= critical ! \n";print_usage(); exit $ERRORS{"UNKNOWN"}}
  }
    } else {
        if (($o_check_type eq "netsl") || ($o_check_type eq "cisco")) { print "Multiple warn and crit levels needed with -L or -I switch\n";print_usage(); exit $ERRORS{"UNKNOWN"}}
        if ( isnnum($o_warn) || isnnum($o_crit) )
      { print "Numeric value for warning or critical !\n";print_usage(); exit $ERRORS{"UNKNOWN"}}
  if ($o_warn > $o_crit)
            { print "warning <= critical ! \n";print_usage(); exit $ERRORS{"UNKNOWN"}}
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
  verb("Alarm at $TIMEOUT + 5");
  alarm($TIMEOUT+5);
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

my $exit_val=undef;
########### Linux load check ##############

if ($o_check_type eq "netsl") {

verb("Checking linux load");
# Get load table
my $resultat = (Net::SNMP->VERSION < 4) ?
      $session->get_table($linload_table)
    : $session->get_table(Baseoid => $linload_table);

if (!defined($resultat)) {
   printf("ERROR: Description table : %s.\n", $session->error);
   $session->close;
   exit $ERRORS{"UNKNOWN"};
}
$session->close;

my @load = undef;
my @iload = undef;
my @oid=undef;
foreach my $key ( keys %$resultat) {
   verb("OID : $key, Desc : $$resultat{$key}");
   if ( $key =~ /$linload_name/ ) {
      @oid=split (/\./,$key);
      $iload[0]= pop(@oid) if ($$resultat{$key} eq "Load-1");
      $iload[1]= pop(@oid) if ($$resultat{$key} eq "Load-5");
      $iload[2]= pop(@oid) if ($$resultat{$key} eq "Load-15");
   }
}

for (my $i=0;$i<3;$i++) { $load[$i] = $$resultat{$linload_load . "." . $iload[$i]}};

##
## RRD management
##

if ($o_g) {
	$start=time;
	 if (! -e $rrd) {
        create_rrd($rrd,3,$start,$step,0,"U","GAUGE");
     }
     update_rrd($rrd,$start, $load[0] ,$load[1], $load[2]);
}


print "Load : $load[0] $load[1] $load[2] :";

$exit_val=$ERRORS{"OK"};
for (my $i=0;$i<3;$i++) {
  if ( $load[$i] > $o_critL[$i] ) {
   print " $load[$i] > $o_critL[$i] : CRITICAL";
   $exit_val=$ERRORS{"CRITICAL"};
  }
  if ( $load[$i] > $o_warnL[$i] ) {
     # output warn error only if no critical was found
     if ($exit_val eq $ERRORS{"OK"}) {
       print " $load[$i] > $o_warnL[$i] : WARNING";
       $exit_val=$ERRORS{"WARNING"};
     }
  }
}
print " OK" if ($exit_val eq $ERRORS{"OK"});
if (defined($o_perf)) {
   print " | load_1_min=$load[0];$o_warnL[0];$o_critL[0],";
   print "load_5_min=$load[1];$o_warnL[1];$o_critL[1],";
   print "load_15_min=$load[2];$o_warnL[2];$o_critL[2]\n";
} else {
 print "\n";
}
exit $exit_val;
}

############## Cisco CPU check ################

if ($o_check_type eq "cisco") {
my @oidlists = ($cisco_cpu_5m, $cisco_cpu_1m, $cisco_cpu_5s);
my $resultat = (Net::SNMP->VERSION < 4) ?
    $session->get_request(@oidlists)
  : $session->get_request(-varbindlist => \@oidlists);

if (!defined($resultat)) {
   printf("ERROR: Description table : %s.\n", $session->error);
   $session->close;
   exit $ERRORS{"UNKNOWN"};
}

$session->close;

if (!defined ($$resultat{$cisco_cpu_5s})) {
  print "No CPU information : UNKNOWN\n";
  exit $ERRORS{"UNKNOWN"};
}

my @load = undef;

$load[0]=$$resultat{$cisco_cpu_5s};
$load[1]=$$resultat{$cisco_cpu_1m};
$load[2]=$$resultat{$cisco_cpu_5m};

##
## RRD management
##

if ($o_g) {
	$start=time;
	 if (! -e $rrd) {
        create_rrd($rrd,3,$start,$step,0,"U","GAUGE");
     }
     update_rrd($rrd,$start,$load[0] ,$load[1], $load[2]);
}

print "CPU : $load[0] $load[1] $load[2] :";

$exit_val=$ERRORS{"OK"};
for (my $i=0;$i<3;$i++) {
  if ( $load[$i] > $o_critL[$i] ) {
   print " $load[$i] > $o_critL[$i] : CRITICAL";
   $exit_val=$ERRORS{"CRITICAL"};
  }
  if ( $load[$i] > $o_warnL[$i] ) {
     # output warn error only if no critical was found
     if ($exit_val eq $ERRORS{"OK"}) {
       print " $load[$i] > $o_warnL[$i] : WARNING";
       $exit_val=$ERRORS{"WARNING"};
     }
  }
}
print " OK" if ($exit_val eq $ERRORS{"OK"});
if (defined($o_perf)) {
   print " | load_5_sec=$load[0]%;$o_warnL[0];$o_critL[0],";
   print "load_1_min=$load[1]%;$o_warnL[1];$o_critL[1],";
   print "load_5_min=$load[2]%;$o_warnL[2];$o_critL[2]\n";
} else {
 print "\n";
}

exit $exit_val;
}

################## CPU for : AS/400 , Netsnmp, HP, Bluecoat, linkproof  ###########
if ( $o_check_type =~ /netsc|as400|bc|nokia|hp|lp/ ) {

# Get load table
my @oidlist = $cpu_oid{$o_check_type};
verb("Checking OID : @oidlist");
my $resultat = (Net::SNMP->VERSION < 4) ?
    $session->get_request(@oidlist)
  : $session->get_request(-varbindlist => \@oidlist);
if (!defined($resultat)) {
   printf("ERROR: Description table : %s.\n", $session->error);
   $session->close;
   exit $ERRORS{"UNKNOWN"};
}
$session->close;

if (!defined ($$resultat{$cpu_oid{$o_check_type}})) {
  print "No CPU information : UNKNOWN\n";
  exit $ERRORS{"UNKNOWN"};
}

my $load=$$resultat{$cpu_oid{$o_check_type}};
verb("OID returned $load");
# for AS400, divide by 100
if ($o_check_type eq "as400") {$load /= 100; };
# for Net-snmp : oid returned idle time so load = 100-idle.
if ($o_check_type eq "netsc") {$load = 100 - $load; };

##
## RRD management
##

if ($o_g) {
	$start=time;
	 if (! -e $rrd) {
        create_rrd($rrd,1,$start,$step,0,"U","GAUGE");
     }
     update_rrd($rrd,$start,$load);
}


printf("CPU used %.1f%% (",$load);

$exit_val=$ERRORS{"OK"};
if ($load > $o_crit) {
 print ">$o_crit) : CRITICAL";
 $exit_val=$ERRORS{"CRITICAL"};
} else {
  if ($load > $o_warn) {
   print ">$o_warn) : WARNING";
   $exit_val=$ERRORS{"WARNING"};
  }
}
print "<$o_warn) : OK" if ($exit_val eq $ERRORS{"OK"});
(defined($o_perf)) ?
   print " | cpu_prct_used=$load%;$o_warn;$o_crit\n"
 : print "\n";
exit $exit_val;

}

########## Standard cpu usage check ############
# Get desctiption table
my $resultat =  (Net::SNMP->VERSION < 4) ?
    $session->get_table($base_proc)
  : $session->get_table(Baseoid => $base_proc);

if (!defined($resultat)) {
   printf("ERROR: Description table : %s.\n", $session->error);
   $session->close;
   exit $ERRORS{"UNKNOWN"};
}

$session->close;

my ($cpu_used,$ncpu)=(0,0);
foreach my $key ( keys %$resultat) {
   verb("OID : $key, Desc : $$resultat{$key}");
   if ( $key =~ /$proc_load/) {
     $cpu_used += $$resultat{$key};
     $ncpu++;
   }
}

if ($ncpu==0) {
  print "Can't find CPU usage information : UNKNOWN\n";
  exit $ERRORS{"UNKNOWN"};
}

$cpu_used /= $ncpu;

##
## RRD management
##

if ($o_g) {
	$start=time;
	 if (! -e $rrd) {
        create_rrd($rrd,1,$start,$step,0,"U","GAUGE");
     }
     update_rrd($rrd,$start,$cpu_used);
}

print "$ncpu CPU, ", $ncpu==1 ? "load" : "average load";
printf(" %.1f",$cpu_used);
$exit_val=$ERRORS{"OK"};

if ($cpu_used > $o_crit) {
 print " > $o_crit : CRITICAL";
 $exit_val=$ERRORS{"CRITICAL"};
} else {
  if ($cpu_used > $o_warn) {
   print " > $o_warn : WARNING";
   $exit_val=$ERRORS{"WARNING"};
  }
}
print " < $o_warn : OK" if ($exit_val eq $ERRORS{"OK"});
(defined($o_perf)) ?
   print " | cpu_prct_used=$cpu_used%;$o_warn;$o_crit\n"
 : print "\n";
exit $exit_val;

