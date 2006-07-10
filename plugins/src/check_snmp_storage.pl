#!/usr/bin/perl -w
############################## check_snmp_storage ##############
# Version : 1.1.1.4 - DEV
# Date :  Aug 2 2005
# Author  : Patrick Proy ( patrick at proy.org)
# Help : http://www.manubulon.com/nagios/
# Licence : GPL - http://www.fsf.org/licenses/gpl.txt
# TODO : better options in snmpv3
#################################################################
#
# help : ./check_snmp_storage -h

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
my $storage_table= '1.3.6.1.2.1.25.2.3.1';
my $storagetype_table = '1.3.6.1.2.1.25.2.3.1.2';
my $index_table = '1.3.6.1.2.1.25.2.3.1.1';
my $descr_table = '1.3.6.1.2.1.25.2.3.1.3';
my $size_table = '1.3.6.1.2.1.25.2.3.1.5.';
my $used_table = '1.3.6.1.2.1.25.2.3.1.6.';
my $alloc_units = '1.3.6.1.2.1.25.2.3.1.4.';

#Storage types definition  - from /usr/share/snmp/mibs/HOST-RESOURCES-TYPES.txt
my %hrStorage;
$hrStorage{"Other"} = '1.3.6.1.2.1.25.2.1.1';
$hrStorage{"1.3.6.1.2.1.25.2.1.1"} = 'Other';
$hrStorage{"Ram"} = '1.3.6.1.2.1.25.2.1.2';
$hrStorage{"1.3.6.1.2.1.25.2.1.2"} = 'Ram';
$hrStorage{"VirtualMemory"} = '1.3.6.1.2.1.25.2.1.3';
$hrStorage{"1.3.6.1.2.1.25.2.1.3"} = 'VirtualMemory';
$hrStorage{"FixedDisk"} = '1.3.6.1.2.1.25.2.1.4';
$hrStorage{"1.3.6.1.2.1.25.2.1.4"} = 'FixedDisk';
$hrStorage{"RemovableDisk"} = '1.3.6.1.2.1.25.2.1.5';
$hrStorage{"1.3.6.1.2.1.25.2.1.5"} = 'RemovableDisk';
$hrStorage{"FloppyDisk"} = '1.3.6.1.2.1.25.2.1.6';
$hrStorage{"1.3.6.1.2.1.25.2.1.6"} = 'FloppyDisk';
$hrStorage{"CompactDisk"} = '1.3.6.1.2.1.25.2.1.7';
$hrStorage{"1.3.6.1.2.1.25.2.1.7"} = 'CompactDisk';
$hrStorage{"RamDisk"} = '1.3.6.1.2.1.25.2.1.8';
$hrStorage{"1.3.6.1.2.1.25.2.1.8"} = 'RamDisk';
$hrStorage{"FlashMemory"} = '1.3.6.1.2.1.25.2.1.9';
$hrStorage{"1.3.6.1.2.1.25.2.1.9"} = 'FlashMemory';
$hrStorage{"NetworkDisk"} = '1.3.6.1.2.1.25.2.1.10';
$hrStorage{"1.3.6.1.2.1.25.2.1.10"} = 'NetworkDisk';

# Globals

my $Name='check_snmp_storage';
my $Version='1.1.1.3';

my $o_host = 	undef; 		# hostname
my $o_community = undef; 	# community
my $o_port = 	161; 		# port
my $o_version2	= undef;	#use snmp v2c
my $o_descr = 	undef; 		# description filter
my $o_storagetype = undef;    # parse storage type also
my $o_warn = 	undef; 		# warning limit
my $o_crit=	undef; 		# critical limit
my $o_help=	undef; 		# wan't some help ?
my $o_type=	undef;		# pl, pu, mbl, mbu
my @o_typeok=   ("pu","pl","bu","bl"); # valid values for o_type
my $o_verb=	undef;		# verbose mode
my $o_version=  undef;          # print version
my $o_noreg=	undef;		# Do not use Regexp for name
my $o_sum=	undef;		# add all storage before testing
my $o_index=	undef;		# Parse index instead of description
my $o_negate=	undef;		# Negate the regexp if set
my $o_timeout=  5;            	# Default 5s Timeout
my $o_perf=	undef;		# Output performance data
my $o_short=	undef;	# Short output parameters
my @o_shortL=	undef;		# output type,where,cut
# SNMP V3 specific
my $o_login=	undef;		# snmp v3 login
my $o_passwd=	undef;		# snmp v3 passwd
# Oreon specific
my $o_step=	undef;
my $o_g=	undef;
my $o_S=	undef;
my $step=	undef;
my $rrd=	undef;
my $rrd_max=	undef;
my $start=	undef;
my $ServiceId=	undef;
my @rrd_data= undef;

# functions

sub p_version { print "$Name version : $Version\n"; }

sub print_usage {
    print "Usage: $Name [-v] -H <host> -C <snmp_community> [-2] | (-l login -x passwd) [-p <port>] -m <name in desc_oid> [-q storagetype] -w <warn_level> -c <crit_level> [-t <timeout>] [-T pl|pu|bl|bu ] [-r] [-s] [-i] [-e] [-S 0|1[,1,<car>]]\n";
}

sub round ($$) {
    sprintf "%.$_[1]f", $_[0];
}

sub is_pattern_valid { # Test for things like "<I\s*[^>" or "+5-i"
 my $pat = shift;
 if (!defined($pat)) { $pat=" ";} # Just to get rid of compilation time warnings
 return eval { "" =~ /$pat/; 1 } || 0;
}

# Get the alarm signal (just in case snmp timout screws up)
$SIG{'ALRM'} = sub {
     print ("ERROR: General time-out (Alarm signal)\n");
     exit $ERRORS{"UNKNOWN"};
};

sub isnnum { # Return true if arg is not a number
  my $num = shift;
  if ( $num =~ /^-?(\d+\.?\d*)|(^\.\d+)$/ ) { return 0 ;}
  return 1;
}

sub help {
   print "\nSNMP Disk Monitor for Nagios version ",$Version,"\n";
   print "(c)2004 to my cat Ratoune - Author : Patrick Proy\n\n";
   print_usage();
   print <<EOT;
By default, plugin will monitor %used on drives :
warn if %used > warn and critical if %used > crit
-v, --verbose
   print extra debugging information (and lists all storages)
-h, --help
   print this help message
-H, --hostname=HOST
   name or IP address of host to check
-C, --community=COMMUNITY NAME
   community name for the host's SNMP agent (implies SNMP v1)
-2, --v2c
   Use snmp v2c
-l, --login=LOGIN
   Login for snmpv3 authentication (implies v3 protocol with MD5)
-x, --passwd=PASSWD
   Password for snmpv3 authentication
-p, --port=PORT
   SNMP port (Default 161)
-m, --name=NAME
   Name in description OID (can be mounpoints '/home' or 'Swap Space'...)
   This is treated as a regexp : -m /var will match /var , /var/log, /opt/var ...
   Test it before, because there are known bugs (ex : trailling /)
   No trailing slash for mountpoints !
-q, --storagetype=[Other|Ram|VirtualMemory|FixedDisk|RemovableDisk|FloppyDisk
                    CompactDisk|RamDisk|FlashMemory|NetworkDisk]
   Also check the storage type in addition of the name
   It is possible to use regular expressions ( "FixedDisk|FloppyDisk" )
-r, --noregexp
   Do not use regexp to match NAME in description OID
-s, --sum
   Add all storages that match NAME (used space and total space)
   THEN make the tests.
-i, --index
   Parse index table instead of description table to select storage
-e, --exclude
   Select all storages except the one(s) selected by -m
   No action on storage type selection
-T, --type=TYPE
   pl : calculate percent left
   pu : calculate percent used (Default)
   bl : calculate MegaBytes left
   bu : calculate MegaBytes used
-w, --warn=INTEGER
   percent / MB of disk used to generate WARNING state
   you can add the % sign
-c, --critical=INTEGER
   percent / MB of disk used to generate CRITICAL state
   you can add the % sign
-f, --perfparse
   Perfparse compatible output
-S, --short=<type>[,<where>,<cut>]
   <type>: Make the output shorter :
     0 : only print the global result except the disk in warning or critical
         ex: "< 80% : OK"
     1 : Don't print all info for every disk
         ex : "/ : 66 %used  (<  80) : OK"
   <where>: (optional) if = 1, put the OK/WARN/CRIT at the beginning
   <cut>: take the <n> first caracters or <n> last if n<0
-t, --timeout=INTEGER
   timeout for SNMP in seconds (Default: 5)
-V, --version
   prints version number
-g (--rrdgraph)   Create a rrd base if necessary and add datas into this one
--rrd_step	     Specifies the base interval in seconds with which data will be fed into the RRD (300 by default)
-S (--ServiceId)  Oreon Service Id

Note :
  with T=pu or T=bu : OK < warn < crit
  with T=pl ot T=bl : crit < warn < OK

  If multiple storage are selected, the worse condition will be returned
  i.e if one disk is critical, the return is critical

  example :
  Browse storage list : <script> -C <community> -H <host> -m <anything> -w 1 -c 2 -v
  the -m option allows regexp in perl format :
  Test drive C,F,G,H,I on Windows 	: -m ^[CFGHI]:
  Test all mounts containing /var      	: -m /var
  Test all mounts under /var      	: -m ^/var
  Test only /var                 	: -m /var -r
  Test all swap spaces			: -m ^Swap
  Test all but swap spaces		: -m ^Swap -e

EOT
}

sub verb { my $t=shift; print $t,"\n" if defined($o_verb) ; }

sub check_options {
    Getopt::Long::Configure ("bundling");
    GetOptions(
		'v'	=> \$o_verb,		'verbose'	=> \$o_verb,
        'h'     => \$o_help,    	'help'        	=> \$o_help,
        'H:s'   => \$o_host,		'hostname:s'	=> \$o_host,
        'p:i'   => \$o_port,   		'port:i'	=> \$o_port,
        'C:s'   => \$o_community,	'community:s'	=> \$o_community,
        'l:s'   => \$o_login,           'login:s'       => \$o_login,
        'x:s'   => \$o_passwd,          'passwd:s'      => \$o_passwd,
        'c:s'   => \$o_crit,    	'critical:s'	=> \$o_crit,
        'w:s'   => \$o_warn,    	'warn:s'	=> \$o_warn,
	't:i'   => \$o_timeout,       	'timeout:i'     => \$o_timeout,
        'm:s'   => \$o_descr,		'name:s'	=> \$o_descr,
	'T:s'	=> \$o_type,		'type:s'	=> \$o_type,
        'r'     => \$o_noreg,           'noregexp'      => \$o_noreg,
        's'     => \$o_sum,           	'sum'      	=> \$o_sum,
        'i'     => \$o_index,          	'index'      	=> \$o_index,
        'e'     => \$o_negate,         	'exclude'    	=> \$o_negate,
        'V'     => \$o_version,         'version'       => \$o_version,
		'q:s'  	=> \$o_storagetype,	'storagetype:s'=> \$o_storagetype,
	'2'	=> \$o_version2,	'v2c'		=> \$o_version2,
	'S:s'   => \$o_short,         	'short:s'       => \$o_short,
	'f'	=> \$o_perf,		'perfparse'	=> \$o_perf,
	# For Oreon rrdtool graph
  "rrd_step:s" => \$o_step,
  "g"   => \$o_g, "rrdgraph"     => \$o_g,
#  "S=s" => \$o_S,
  "ServiceId=s"  => \$o_S
    );
    if (defined($o_help) ) { help(); exit $ERRORS{"UNKNOWN"}};
    if (defined($o_version) ) { p_version(); exit $ERRORS{"UNKNOWN"}};
    # check mount point regexp
    if (!is_pattern_valid($o_descr))
	{ print "Bad pattern for mount point !\n"; print_usage(); exit $ERRORS{"UNKNOWN"}}
    # check snmp information
    if ( !defined($o_community) && (!defined($o_login) || !defined($o_passwd)) )
        { print "Put snmp login info!\n"; print_usage(); exit $ERRORS{"UNKNOWN"}}
    # Check types
    if ( !defined($o_type) ) { $o_type="pu" ;}
    if ( ! grep( /^$o_type$/ ,@o_typeok) ) { print_usage(); exit $ERRORS{"UNKNOWN"}};
    # Check compulsory attributes
    if ( ! defined($o_descr) ||  ! defined($o_host) || !defined($o_warn) ||
	!defined($o_crit)) { print_usage(); exit $ERRORS{"UNKNOWN"}};
    # Check for positive numbers

    # check if warn or crit  in % and MB is tested
    if (  ( ( $o_warn =~ /%/ ) || ($o_crit =~ /%/)) && ( ( $o_type eq 'bu' ) || ( $o_type eq 'bl' ) ) ) {
	print "warning or critical cannot be in % when MB are tested\n";
	print_usage(); exit $ERRORS{"UNKNOWN"};
    }
    # Get rid of % sign
    $o_warn =~ s/\%//;
    $o_crit =~ s/\%//;
    if (($o_warn < 0) || ($o_crit < 0)) { print " warn and critical > 0 \n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    # Check warning and critical values
    if ( ( $o_type eq 'pu' ) || ( $o_type eq 'bu' )) {
	if ($o_warn >= $o_crit) { print " warn < crit if type=",$o_type,"\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    }
    if ( ( $o_type eq 'pl' ) || ( $o_type eq 'bl' )) {
	if ($o_warn <= $o_crit) { print " warn > crit if type=",$o_type,"\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    }
    if ( ($o_warn < 0 ) || ($o_crit < 0 )) { print "warn and crit must be > 0\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    if ( ( $o_type eq 'pl' ) || ( $o_type eq 'pu' )) {
        if ( ($o_warn > 100 ) || ($o_crit > 100 )) { print "percent must be < 100\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    }
	# Check short values
	if ( defined ($o_short)) {
 	  @o_shortL=split(/,/,$o_short);
	  if ((isnnum($o_shortL[0])) || ($o_shortL[0] !=0) && ($o_shortL[0]!=1)) {
	    print "-S first option must be 0 or 1\n";print_usage(); exit $ERRORS{"UNKNOWN"};
	  }
	  if (defined ($o_shortL[1])&& $o_shortL[1] eq "") {$o_shortL[1]=undef};
	  if (defined ($o_shortL[2]) && isnnum($o_shortL[2]))
	    {print "-S last option must be an integer\n";print_usage(); exit $ERRORS{"UNKNOWN"};}
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

# Check gobal timeout
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
      -hostname         => $o_host,
      -version          => '3',
      -username         => $o_login,
      -authpassword     => $o_passwd,
      -authprotocol     => 'md5',
      -privpassword     => $o_passwd,
      -timeout   	=> $o_timeout
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
  } else {  # SNMPV1 login
    ($session, $error) = Net::SNMP->session(
     -hostname  => $o_host,
     -community => $o_community,
     -port      => $o_port,
     -timeout   => $o_timeout
    );
  }
}

if (!defined($session)) {
   printf("ERROR: %s.\n", $error);
   exit $ERRORS{"UNKNOWN"};
}

my $resultat=undef;
my $stype=undef;
if (defined ($o_index)){
  if (Net::SNMP->VERSION < 4) {
    $resultat = $session->get_table($index_table);
  } else {
	$resultat = $session->get_table(Baseoid => $index_table);
  }
} else {
  if (Net::SNMP->VERSION < 4) {
    $resultat = $session->get_table($descr_table);
  } else {
    $resultat = $session->get_table(Baseoid => $descr_table);
  }
}
#get storage typetable for reference
if (defined($o_storagetype)){
  if (Net::SNMP->VERSION < 4) {
    $stype = $session->get_table($storagetype_table);
  } else {
    $stype = $session->get_table(Baseoid => $storagetype_table);
  }
}
if (!defined($resultat) | (!defined($stype) && defined($o_storagetype))) {
   printf("ERROR: Description/Type table : %s.\n", $session->error);
   $session->close;
   exit $ERRORS{"UNKNOWN"};
}

my @tindex = undef;
my @oids = undef;
my @descr = undef;
my $num_int = 0;
my $count_oid = 0;
my $test = undef;
my $perf_out=	undef;
# Select storage by regexp of exact match
# and put the oid to query in an array

verb("Filter : $o_descr");

foreach my $key ( keys %$resultat) {
   verb("OID : $key, Desc : $$resultat{$key}");
   # test by regexp or exact match / include or exclude
   if (defined($o_negate)) {
     $test = defined($o_noreg)
                ? $$resultat{$key} ne $o_descr
                : $$resultat{$key} !~ /$o_descr/;
   } else {
     $test = defined($o_noreg)
                ? $$resultat{$key} eq $o_descr
                : $$resultat{$key} =~ /$o_descr/;
   }
  if ($test) {
    # get the index number of the interface
    my @oid_list = split (/\./,$key);
    $tindex[$num_int] = pop (@oid_list);
       # Check if storage type is OK
       if (defined($o_storagetype)) {
	   my($skey)=$storagetype_table.".".$tindex[$num_int];
 	   verb("   OID : $skey, Storagetype: $hrStorage{$$stype{$skey}} ?= $o_storagetype");
           if ( $hrStorage{$$stype{$skey}} !~ $o_storagetype) {
	     $test=undef;
	   }
	}
	if ($test) {
       # get the full description
       $descr[$num_int]=$$resultat{$key};
       # put the oid in an array
       $oids[$count_oid++]=$size_table . $tindex[$num_int];
       $oids[$count_oid++]=$used_table . $tindex[$num_int];
       $oids[$count_oid++]=$alloc_units . $tindex[$num_int];

       verb("   Name : $descr[$num_int], Index : $tindex[$num_int]");
       $num_int++;
    }
  }
}
verb("storages selected : $num_int");
if ( $num_int == 0 ) { print "Unknown storage : $o_descr : ERROR\n" ; exit $ERRORS{"UNKNOWN"};}

my $result=undef;

if (Net::SNMP->VERSION < 4) {
  $result = $session->get_request(@oids);
} else {
  if ($session->version == 0) {
    # snmpv1
    $result = $session->get_request(Varbindlist => \@oids);
  } else {
    # snmp v2c or v3 : get_bulk_request is not really good for this, so do simple get
    $result = $session->get_request(Varbindlist => \@oids);
    foreach my $key ( keys %$result) { verb("$key  : $$result{$key}"); }
  }
}

if (!defined($result)) { printf("ERROR: Size table :%s.\n", $session->error); $session->close;
   exit $ERRORS{"UNKNOWN"};
}

$session->close;

# Only a few ms left...
alarm(0);

# Sum everything if -s and more than one storage
if ( defined ($o_sum) && ($num_int > 1) ) {
  verb("Adding all entries");
  $$result{$size_table . $tindex[0]} *= $$result{$alloc_units . $tindex[0]};
  $$result{$used_table . $tindex[0]} *= $$result{$alloc_units . $tindex[0]};
  $$result{$alloc_units . $tindex[0]} = 1;
  for (my $i=1;$i<$num_int;$i++) {
    $$result{$size_table . $tindex[0]} += ($$result{$size_table . $tindex[$i]}
					  * $$result{$alloc_units . $tindex[$i]});
    $$result{$used_table . $tindex[0]} += ($$result{$used_table . $tindex[$i]}
					  * $$result{$alloc_units . $tindex[$i]});
  }
  $num_int=1;
  $descr[0]="Sum of all $o_descr";
}

my $i=undef;
my $warn_state=0;
my $crit_state=0;
my ($p_warn,$p_crit);
my $output=undef;
for ($i=0;$i<$num_int;$i++) {
  verb("Descr : $descr[$i]");
  verb("Size :  $$result{$size_table . $tindex[$i]}");
  verb("Used : $$result{$used_table . $tindex[$i]}");
  verb("Alloc : $$result{$alloc_units . $tindex[$i]}");
  my $to = $$result{$size_table . $tindex[$i]} * $$result{$alloc_units . $tindex[$i]} / 1024**2;
  my $pu=undef;
  if ( $$result{$used_table . $tindex[$i]} != 0 ) {
    $pu = $$result{$used_table . $tindex[$i]}*100 / $$result{$size_table . $tindex[$i]};
  }else {
    $pu=0;
  }
  my $bu = $$result{$used_table . $tindex[$i]} *  $$result{$alloc_units . $tindex[$i]} / 1024**2;
  my $pl = 100 - $pu;
  my $bl = ($$result{$size_table . $tindex[$i]}- $$result{$used_table . $tindex[$i]}) * $$result{$alloc_units . $tindex[$i]} / 1024**2;
  # add a ' ' if some data exists in $perf_out
  $perf_out .= " " if (defined ($perf_out)) ;
  ##### Ouputs and checks
  # Keep complete description fot performance output (in MB)
  my $Pdescr=$descr[$i];
  $Pdescr =~ s/'/_/g;
  ##### TODO : subs "," with something
 if (defined($o_shortL[2])) {
   if ($o_shortL[2] < 0) {$descr[$i]=substr($descr[$i],$o_shortL[2]);}
   else {$descr[$i]=substr($descr[$i],0,$o_shortL[2]);}
 }
 if ($o_type eq "pu") { # Checks % used
    my $locstate=0;
	$p_warn=$o_warn*$to/100;$p_crit=$o_crit*$to/100;
        (($pu >= $o_crit) && ($locstate=$crit_state=1))
	   || (($pu >= $o_warn) && ($locstate=$warn_state=1));
	if (defined($o_shortL[2])) {}
	if (!defined($o_shortL[0]) || ($locstate==1)) { # print full output if warn or critical state
	  $output.=sprintf ("%s: %.0f%%used(%.0fMB/%.0fMB) ",$descr[$i],$pu,$bu,$to);
    } elsif ($o_shortL[0] == 1) {
	  $output.=sprintf ("%s: %.0f%% ",$descr[$i],$pu);
	}
  	$rrd_max = 100;
  	if (defined($rrd_data[0])) {
  		push @rrd_data, $pu;
  	} else {
  		$rrd_data[0]= $pu ;
  	}
  }

  if ($o_type eq 'bu') { # Checks MBytes used
    my $locstate=0;
	$p_warn=$o_warn;$p_crit=$o_crit;
    ( ($bu >= $o_crit) && ($locstate=$crit_state=1) )
	  || ( ($bu >= $o_warn) && ($locstate=$warn_state=1) );
	if (!defined($o_shortL[0]) || ($locstate==1)) { # print full output if warn or critical state
      $output.=sprintf("%s: %.0fMBused/%.0fMB (%.0f%%) ",$descr[$i],$bu,$to,$pu);
    } elsif ($o_shortL[0] == 1) {
	  $output.=sprintf("%s: %.0fMB ",$descr[$i],$bu);
    }
 	$rrd_max = "U";
 	if (defined($rrd_data[0])) {
  		push @rrd_data, $bu;
  	} else {
  		$rrd_data[0]= $bu ;
  	}

 }

  if ($o_type eq 'bl') {
    my $locstate=0;
    $p_warn=$to-$o_warn;$p_crit=$to-$o_crit;
    ( ($bl <= $o_crit) && ($locstate=$crit_state=1) )
	  || ( ($bl <= $o_warn) && ($locstate=$warn_state=1) );
	if (!defined($o_shortL[0]) || ($locstate==1)) { # print full output if warn or critical state
      $output.=sprintf ("%s: %.0fMBleft/%.0fMB (%.0f%%) ",$descr[$i],$bl,$to,$pl);
    } elsif ($o_shortL[0] == 1) {
	  $output.=sprintf ("%s: %.0fMB ",$descr[$i],$bl);
    }
 	$rrd_max = "U";
   	if (defined($rrd_data[0])) {
  		push @rrd_data, $pl;
  	} else {
  		$rrd_data[0]= $pl ;
  	}
 }

  if ($o_type eq 'pl') {  # Checks % left
    my $locstate=0;
    $p_warn=(100-$o_warn)*$to/100;$p_crit=(100-$o_crit)*$to/100;
    ( ($pl <= $o_crit) && ($locstate=$crit_state=1) )
	  || ( ($pl <= $o_warn) && ($locstate=$warn_state=1) );
	if (!defined($o_shortL[0]) || ($locstate==1)) { # print full output if warn or critical state
      $output.=sprintf ("%s: %.0f%%left(%.0fMB/%.0fMB) ",$descr[$i],$pl,$bl,$to);
    } elsif ($o_shortL[0] == 1) {
	  $output.=sprintf ("%s: %.0f%% ",$descr[$i],$pl);
    }
   	$rrd_max = 100;
   	if (defined($rrd_data[0])) {
  		push @rrd_data, $pl;
  	} else {
  		$rrd_data[0]= $pl ;
  	}

  }


  # Performance output (in MB)
  $perf_out .= "'".$Pdescr. "'=" . round($bu,0) . "MB;" . round($p_warn,0)
	       . ";" . round($p_crit,0) . ";0;" . round($to,0);
}


 ##
  ## RRD management
  ##

	if ($o_g) {
		$start=time;
		 if (! -e $rrd) {
	        create_rrd($rrd,$#rrd_data+1,$start,$step,0,$rrd_max,"GAUGE");
	     }
	     update_rrd($rrd,$start,@rrd_data);
	}



verb ("Perf data : $perf_out");

my $comp_oper=undef;
my $comp_unit=undef;
($o_type eq "pu") && ($comp_oper ="<") && ($comp_unit ="%");
($o_type eq "pl") && ($comp_oper =">") && ($comp_unit ="%");
($o_type eq "bu") && ($comp_oper ="<") && ($comp_unit ="MB");
($o_type eq 'bl') && ($comp_oper =">") && ($comp_unit ="MB");

if (!defined ($output)) { $output="All selected storages "; }

if ( $crit_state == 1) {
    $comp_oper = ($comp_oper eq "<") ? ">" : "<";  # Inverse comp operator
    if (defined($o_shortL[1])) {
	  print "CRITICAL : (",$comp_oper,$o_crit,$comp_unit,") ",$output;
	} else {
	  print $output,"(",$comp_oper,$o_crit,$comp_unit,") : CRITICAL";
	}
	(defined($o_perf)) ?  print " | ",$perf_out,"\n" : print "\n";
     exit $ERRORS{"CRITICAL"};
    }
if ( $warn_state == 1) {
    $comp_oper = ($comp_oper eq "<") ? ">" : "<";  # Inverse comp operator
    if (defined($o_shortL[1])) {
       print "WARNING : (",$comp_oper,$o_warn,$comp_unit,") ",$output;
	} else {
       print $output,"(",$comp_oper,$o_warn,$comp_unit,") : WARNING";
	}
	(defined($o_perf)) ?  print " | ",$perf_out,"\n" : print "\n";
     exit $ERRORS{"WARNING"};
   }
if (defined($o_shortL[1])) {
  print "OK : (",$comp_oper,$o_warn,$comp_unit,") ",$output;
} else {
  print $output,"(",$comp_oper,$o_warn,$comp_unit,") : OK";
}
(defined($o_perf)) ? print " | ",$perf_out,"\n" : print "\n";

exit $ERRORS{"OK"};

