#!/usr/bin/perl -w
############################## check_snmp_process ##############
# Version : 1.2.1
# Date : Dec 12 2004
# Author  : Patrick Proy (patrick at proy.org)
# Help : http://www.manubulon.com/nagios/
# Licence : GPL - http://www.fsf.org/licenses/gpl.txt
# TODO : put $o_delta as an option
###############################################################
#
# help : ./check_snmp_process -h

############### BASE DIRECTORY FOR TEMP FILE ########
my $o_base_dir="/tmp/tmp_Nagios_proc.";
my $file_history=200;   # number of data to keep in files.
my $delta_of_time_to_make_average=300;  # 5minutes by default

use strict;
use Net::SNMP;
use Getopt::Long;

# Nagios specific

use lib "@NAGIOS_PLUGINS@";
use utils qw(%ERRORS $TIMEOUT);
#my $TIMEOUT = 5;
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
my $process_table= '1.3.6.1.2.1.25.4.2.1';
my $index_table = '1.3.6.1.2.1.25.4.2.1.1';
my $run_name_table = '1.3.6.1.2.1.25.4.2.1.2';
my $run_path_table = '1.3.6.1.2.1.25.4.2.1.4';
my $proc_mem_table = '1.3.6.1.2.1.25.5.1.1.2'; # Kbytes
my $proc_cpu_table = '1.3.6.1.2.1.25.5.1.1.1'; # Centi sec of CPU
my $proc_run_state = '1.3.6.1.2.1.25.4.2.1.7';

# Globals

my $Version='1.2.1';

my $o_host = 	undef; 		# hostname
my $o_community =undef; 	# community
my $o_port = 	161; 		# port
my $o_descr = 	undef; 		# description filter
my $o_warn = 	0; 		# warning limit
my @o_warnL=	undef;		# warning limits (min,max)
my $o_crit=	0; 		# critical limit
my @o_critL=	undef;		# critical limits (min,max)
my $o_help=	undef; 		# wan't some help ?
my $o_verb=	undef;		# verbose mode
my $o_version=   undef;         # print version
my $o_noreg=	undef;		# Do not use Regexp for name
my $o_path=	undef;		# check path instead of name
my $o_inverse=	undef;		# checks max instead of min number of process
my $o_timeout=  5;            	# Default 5s Timeout
# SNMP V3 specific
my $o_login=	undef;		# snmp v3 login
my $o_passwd=	undef;		# snmp v3 passwd
# Memory & CPU
my $o_mem=	undef;		# checks memory (max)
my @o_memL=	undef;		# warn and crit level for mem
my $o_mem_avg=	undef;		# cheks memory average
my $o_cpu=	undef;		# checks CPU usage
my @o_cpuL=	undef;		# warn and crit level for cpu
my $o_delta=	$delta_of_time_to_make_average;		# delta time for CPU check
# Oreon specific
my $o_step=	undef;
my $o_g=	undef;
my $o_S=	undef;
my $step=	undef;
my $rrd=	undef;
my $start=	undef;
my $ServiceId=	undef;
my @rrd_data= undef;

# functions

sub p_version { print "check_snmp_process version : $Version\n"; }

sub print_usage {
    print "Usage: $0 [-v] -H <host> -C <snmp_community> | (-l login -x passwd) [-p <port>] -n <name> [-w <min_proc>[,<max_proc>] -c <min_proc>[,max_proc] ] [-m<warn Mb>,<crit Mb> -a -u<warn %>,<crit%> ] [-t <timeout>] [-f ] [-r] [-V]\n";
}

sub isnotnum { # Return true if arg is not a number
  my $num = shift;
  if ( $num =~ /^(\d+\.?\d*)|(^\.\d+)$/ ) { return 0 ;}
  return 1;
}

# Get the alarm signal (just in case snmp timout screws up)
$SIG{'ALRM'} = sub {
     print ("ERROR: Alarm signal (Nagios time-out)\n");
     exit $ERRORS{"UNKNOWN"};
};

sub read_file {
        # Input : File, items_number
        # Returns : array of value : [line][item]
  my ($traffic_file,$items_number)=@_;
  my ($ligne,$n_rows)=(undef,0);
  my (@last_values,@file_values,$i);
  open(FILE,"<".$traffic_file) || return (1,0,0);

  while($ligne = <FILE>)
  {
    chomp($ligne);
    @file_values = split(":",$ligne);
    #verb("@file_values");
    if ($#file_values >= ($items_number-1)) {
        # check if there is enough data, else ignore line
      for ( $i=0 ; $i< $items_number ; $i++ ) {$last_values[$n_rows][$i]=$file_values[$i]; }
      $n_rows++;
    }
  }
  close FILE;
  if ($n_rows != 0) {
    return (0,$n_rows,@last_values);
  } else {
    return (1,0,0);
  }
}

sub write_file {
        # Input : file , rows, items, array of value : [line][item]
        # Returns : 0 / OK, 1 / error
  my ($file_out,$rows,$item,@file_values)=@_;
  my $start_line= ($rows > $file_history) ? $rows -  $file_history : 0;
  if ( open(FILE2,">".$file_out) ) {
    for (my $i=$start_line;$i<$rows;$i++) {
      for (my $j=0;$j<$item;$j++) {
        print FILE2 $file_values[$i][$j];
        if ($j != ($item -1)) { print FILE2 ":" };
      }
      print FILE2 "\n";
    }
    close FILE2;
    return 0;
  } else {
    return 1;
  }
}

sub help {
   print "\nSNMP Process Monitor for Nagios version ",$Version,"\n";
   print "(c)2004 to my cat Ratoune - Author: Patrick Proy\n\n";
   print_usage();
   print <<EOT;
-v, --verbose
   print extra debugging information (and lists all storages)
-h, --help
   print this help message
-H, --hostname=HOST
   name or IP address of host to check
-C, --community=COMMUNITY NAME
   community name for the host's SNMP agent (implies SNMP v1)
-l, --login=LOGIN
   Login for snmpv3 authentication (implies v3 protocol with MD5)
-x, --passwd=PASSWD
   Password for snmpv3 authentication
-p, --port=PORT
   SNMP port (Default 161)
-n, --name=NAME
   Name of the process (regexp)
   No trailing slash !
-r, --noregexp
   Do not use regexp to match NAME in description OID
-f, --fullpath
   Use full path name instead of process name
   (Windows doesn't provide full path name)
-w, --warn=MIN[,MAX]
   Number of process that will cause a warning
-c, --critical=MIN[,MAX]
   number of process that will cause an error
Notes on warning and critical :
   with the following options : -w m1,x1 -c m2,x2
   you must have : m2 <= m1 < x1 <= x2
   you can omit x1 or x2 or both
-m, --memory=WARN,CRIT
   checks memory usage (default max of all process)
   values are warning and critical values in Mb
-a, --average
   makes an average of memory used by process instead of max
-u, --cpu=WARN,CRIT
   checks cpu usage of all process
   values are warning and critical values in % of CPU usage
   if more than one CPU, value can be > 100% : 100%=1 CPU
-t, --timeout=INTEGER
   timeout for SNMP in seconds (Default: 5)
-V, --version
   prints version number
-g (--rrdgraph)   Create a rrd base if necessary and add datas into this one
--rrd_step	     Specifies the base interval in seconds with which data will be fed into the RRD (300 by default)
-S (--ServiceId)  Oreon Service Id

Note :
  CPU usage is in % of one cpu, so maximum can be 100% * number of CPU
  example :
  Browse process list : <script> -C <community> -H <host> -n <anything> -v
  the -n option allows regexp in perl format :
  All process of /opt/soft/bin  	: -n /opt/soft/bin/ -f
  All 'named' process			: -n named

EOT
}

sub verb { my $t=shift; print $t,"\n" if defined($o_verb) ; }

sub check_options {
    my $compat_o_cpu_sum;
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
        'n:s'   => \$o_descr,		'name:s'	=> \$o_descr,
        'r'     => \$o_noreg,           'noregexp'      => \$o_noreg,
        'f'     => \$o_path,           	'fullpath'      => \$o_path,
        'm:s'   => \$o_mem,           	'memory:s'    	=> \$o_mem,
        'a'     => \$o_mem_avg,       	'average'      	=> \$o_mem_avg,
        'u:s'   => \$o_cpu,       	'cpu'      	=> \$o_cpu,
	#### To be compatible with version 1.2, will be removed... ####
	's'     => \$compat_o_cpu_sum,  'cpusum'        => \$compat_o_cpu_sum,
	##########
	'V'     => \$o_version,         'version'       => \$o_version,
		# For Oreon rrdtool graph
  "rrd_step:s" => \$o_step,
  "g"   => \$o_g, "rrdgraph"     => \$o_g,
  "S=s" => \$o_S, "ServiceId=s"  => \$o_S
    );
    if (defined ($o_help)) { help(); exit $ERRORS{"UNKNOWN"}};
    if (defined($o_version)) { p_version(); exit $ERRORS{"UNKNOWN"}};
    # check snmp information
    if ( !defined($o_community) && (!defined($o_login) || !defined($o_passwd)) )
        { print "Put snmp login info!\n"; print_usage(); exit $ERRORS{"UNKNOWN"}}
    # Check compulsory attributes
    if ( ! defined($o_descr) ||  ! defined($o_host) ) { print_usage(); exit $ERRORS{"UNKNOWN"}};
    @o_warnL=split(/,/,$o_warn);
    @o_critL=split(/,/,$o_crit);
    verb("$#o_warnL $#o_critL");
    if ( isnotnum($o_warnL[0]) || isnotnum($o_critL[0]))
       { print "Numerical values for warning and critical\n";print_usage(); exit $ERRORS{"UNKNOWN"};}
    if ((defined($o_warnL[1]) && isnotnum($o_warnL[1])) || (defined($o_critL[1]) && isnotnum($o_critL[1])))
       { print "Numerical values for warning and critical\n";print_usage(); exit $ERRORS{"UNKNOWN"};}
    # Check for positive numbers
    if (($o_warnL[0] < 0) || ($o_critL[0] < 0))
      { print " warn and critical > 0 \n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    if ((defined($o_warnL[1]) && ($o_warnL[1] < 0)) || (defined($o_critL[1]) && ($o_critL[1] < 0)))
      { print " warn and critical > 0 \n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    # Check min_crit < min warn < max warn < crit warn
    if ($o_warnL[0] < $o_critL[0]) { print " warn minimum must be >= crit minimum\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    if (defined($o_warnL[1])) {
      if ($o_warnL[1] <= $o_warnL[0])
        { print "warn minimum must be < warn maximum\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    } elsif ( defined($o_critL[1]) && ($o_critL[1] <= $o_warnL[0]))
       { print "warn minimum must be < crit maximum when no crit warning defined\n";print_usage(); exit $ERRORS{"UNKNOWN"};}
    if ( defined($o_critL[1]) && defined($o_warnL[1]) && ($o_critL[1]<$o_warnL[1]))
       { print "warn max must be <= crit maximum\n";print_usage(); exit $ERRORS{"UNKNOWN"};}
    #### Memory checks
    if (defined ($o_mem)) {
      @o_memL=split(/,/,$o_mem);
      if ($#o_memL != 1)
        {print "2 values (warning,critical) for memory\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
      if (isnotnum($o_memL[0]) || isnotnum($o_memL[1]))
       {print "Numeric values for memory!\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
      if ($o_memL[0]>$o_memL[1])
       {print "Warning must be <= Critical for memory!\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
    }
    #### CPU checks
    if (defined ($o_cpu)) {
      @o_cpuL=split(/,/,$o_cpu);
      if ($#o_cpuL != 1)
        {print "2 values (warning,critical) for cpu\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
      if (isnotnum($o_cpuL[0]) || isnotnum($o_cpuL[1]))
       {print "Numeric values for cpu!\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
      if ($o_cpuL[0]>$o_cpuL[1])
       {print "Warning must be <= Critical for cpu!\n";print_usage(); exit $ERRORS{"UNKNOWN"}};
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
      -hostname         => $o_host,
      -version          => '3',
      -username         => $o_login,
      -authpassword     => $o_passwd,
      -authprotocol     => 'md5',
      -privpassword     => $o_passwd,
      -timeout          => $o_timeout
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

if (!defined($session)) {
   printf("ERROR: %s.\n", $error);
   exit $ERRORS{"UNKNOWN"};
}

# Look for process in name or path name table
my $resultat=undef;
if ( !defined ($o_path) ) {
  $resultat = $session->get_table(
	Baseoid => $run_name_table
  );
} else {
  $resultat = $session->get_table(
	Baseoid => $run_path_table
  );
}

if (!defined($resultat)) {
   printf("ERROR: Process name table : %s.\n", $session->error);
   $session->close;
   exit $ERRORS{"UNKNOWN"};
}

my @tindex = undef;
my @oids = undef;
my @descr = undef;
my $num_int = 0;
my $count_oid = 0;
# Select storage by regexp of exact match
# and put the oid to query in an array

verb("Filter : $o_descr");

foreach my $key ( keys %$resultat) {
   verb("OID : $key, Desc : $$resultat{$key}");
   # test by regexp or exact match
   my $test = defined($o_noreg)
                ? $$resultat{$key} eq $o_descr
                : $$resultat{$key} =~ /$o_descr/;
  if ($test) {
     # get the index number of the interface
     my @oid_list = split (/\./,$key);
     $tindex[$num_int] = pop (@oid_list);
     # get the full description
     $descr[$num_int]=$$resultat{$key};
     # put the oid of running and mem (check this maybe ?) in an array.
     $oids[$count_oid++]=$proc_mem_table . "." . $tindex[$num_int];
     $oids[$count_oid++]=$proc_cpu_table . "." . $tindex[$num_int];
     $oids[$count_oid++]=$proc_run_state . "." . $tindex[$num_int];
     #verb("Name : $descr[$num_int], Index : $tindex[$num_int]");
     verb($oids[$count_oid-1]);
     $num_int++;
  }
}

if ( $num_int == 0) {
   print "No process ",(defined ($o_noreg)) ? "named " : "matching ", $o_descr, " found : CRITICAL\n";
   exit $ERRORS{"CRITICAL"};
}

my $result=undef;
my $num_int_ok=0;
my %result_cons=();
#$session->max_msg_size([214748]);
if ( $count_oid >= 50) {
  my @toid=undef;
  my $tmp_num=0;
  my $tmp_index=0;
  my $tmp_count=$count_oid;
  my $tmp_result=undef;
  verb("More than 50 oid, splitting");
  while ( $tmp_count != 0 ) {
    $tmp_num = ($tmp_count >=50) ? 50 : $tmp_count;
    for (my $i=0; $i<$tmp_num;$i++) {
       $toid[$i]=$oids[$i+$tmp_index];
       #verb("$i :  $toid[$i] : $oids[$i+$tmp_index]");
    }
    $tmp_result = $session->get_request(
	Varbindlist => \@toid
    );
    if (!defined($tmp_result)) { printf("ERROR: running table : %s.\n", $session->error); $session->close;
        exit $ERRORS{"UNKNOWN"};
    }
    foreach (@toid) { $result_cons{$_}=$$tmp_result{$_}; }
    $tmp_count-=$tmp_num;
    $tmp_index+=$tmp_num;
  }

} else {
  $result = $session->get_request(
     Varbindlist => \@oids
  );
  if (!defined($result)) { printf("ERROR: running table : %s.\n", $session->error); $session->close;
   exit $ERRORS{"UNKNOWN"};
  }
  foreach (@oids) {$result_cons{$_}=$$result{$_};}
}

#Check if process are in running or runnable state
for (my $i=0; $i< $num_int; $i++) {
   my $state=$result_cons{$proc_run_state . "." . $tindex[$i]};
   my $tmpmem=$result_cons{$proc_mem_table . "." . $tindex[$i]};
   my $tmpcpu=$result_cons{$proc_cpu_table . "." . $tindex[$i]};
   verb ("Process $tindex[$i] in state $state using $tmpmem, and $tmpcpu CPU");
   $num_int_ok++ if (($state == 1) || ($state ==2));
}

$session->close;

$rrd_data[0] = $num_int_ok;

my $final_status=0;
my ($res_memory,$res_cpu)=(0,0);
my $memory_print="";
my $cpu_print="";
###### Checks memory usage
if (defined ($o_mem) ) {
 if (defined ($o_mem_avg)) {
   for (my $i=0; $i< $num_int; $i++) { $res_memory += $result_cons{$proc_mem_table . "." . $tindex[$i]};}
   $res_memory /= ($num_int_ok*1024);
   verb("Memory average : $res_memory");
 } else {
   for (my $i=0; $i< $num_int; $i++) {
     $res_memory = ($result_cons{$proc_mem_table . "." . $tindex[$i]} > $res_memory) ? $result_cons{$proc_mem_table . "." . $tindex[$i]} : $res_memory;
   }
   $res_memory /=1024;
   verb("Memory max : $res_memory");
 }
 if ($res_memory > $o_memL[1]) {
   $final_status=2;
   $memory_print=", Mem : ".sprintf("%.1f",$res_memory)."Mb > ".$o_memL[1]." CRITICAL";
 } elsif ( $res_memory > $o_memL[0]) {
   $final_status=1;
   $memory_print=", Mem : ".sprintf("%.1f",$res_memory)."Mb > ".$o_memL[0]." WARNING";
 } else {
   $memory_print=", Mem : ".sprintf("%.1f",$res_memory)."Mb OK";
 }

push @rrd_data, $res_memory;

}

######## Checks CPU usage

if (defined ($o_cpu) ) {
  my $timenow=time;
  my $temp_file_name;
  my ($return,@file_values)=(undef,undef);
  my $n_rows=0;
  my $n_items_check=2;
  my $trigger=$timenow - ($o_delta - ($o_delta/10));
  my $trigger_low=$timenow - 3*$o_delta;
  my ($old_value,$old_time)=undef;
  my $found_value=undef;

  #### Get the current values
  for (my $i=0; $i< $num_int; $i++) { $res_cpu += $result_cons{$proc_cpu_table . "." . $tindex[$i]};}

  verb("Time: $timenow , cpu (centiseconds) : $res_cpu");

  #### Read file
  $temp_file_name=$o_descr;
  $temp_file_name =~ s/ /_/g;
  $temp_file_name = $o_base_dir . $o_host ."." . $temp_file_name;
  # First, read entire file
  my @ret_array=read_file($temp_file_name,$n_items_check);
  $return = shift(@ret_array);
  $n_rows = shift(@ret_array);
  if ($n_rows != 0) { @file_values = @ret_array };
  verb ("File read returns : $return with $n_rows rows");
  #make the checks if the file is OK
  if ($return ==0) {
    my $j=$n_rows-1;
    do {
      if ($file_values[$j][0] < $trigger) {
        if ($file_values[$j][0] > $trigger_low) {
          # found value = centiseconds / seconds = %cpu
          $found_value= ($res_cpu-$file_values[$j][1]) / ($timenow - $file_values[$j][0] );
        }
      }
      $j--;
    } while ( ($j>=0) && (!defined($found_value)) );
  }
  ###### Write file
  $file_values[$n_rows][0]=$timenow;
  $file_values[$n_rows][1]=$res_cpu;
  $n_rows++;
  $return=write_file($temp_file_name,$n_rows,$n_items_check,@file_values);
  if ($return != 0) { $cpu_print.="! ERROR writing file $temp_file_name !";$final_status=3;}
  ##### Check values (if something to check...)
  if (defined($found_value)) {
    if ($found_value > $o_cpuL[1]) {
      $final_status=2;
      $cpu_print.=", Cpu : ".sprintf("%.0f",$found_value)."% > ".$o_cpuL[1]." CRITICAL";
    } elsif ( $found_value > $o_cpuL[0]) {
      $final_status=($final_status==2)?2:1;
      $cpu_print.=", Cpu : ".sprintf("%.0f",$found_value)."% > ".$o_cpuL[0]." WARNING";
    } else {
      $cpu_print.=", Cpu : ".sprintf("%.0f",$found_value)."% OK";
    }
	push @rrd_data, $found_value;
  } else {
    if ($final_status==0) { $final_status=3 };
    $cpu_print.=", No data for CPU (".$n_rows." line(s)):UNKNOWN";
  }

}


  ##
  ## RRD management
  ##

	if ($o_g) {
		$start=time;
		 if (! -e $rrd) {
	        create_rrd($rrd,$#rrd_data+1,$start,$step,0,"U","GAUGE");
	     }
	     update_rrd($rrd,$start,@rrd_data);
	}



print $num_int_ok, " process ", (defined ($o_noreg)) ? "named " : "matching ", $o_descr, " ";

#### Check for min and max number of process
if ( $num_int_ok <= $o_critL[0] ) {
   print "(<= ",$o_critL[0]," : CRITICAL)";
   $final_status=2;
} elsif ( $num_int_ok <= $o_warnL[0] ) {
   print "(<= ",$o_warnL[0]," : WARNING)";
   $final_status=($final_status==2)?2:1;
} else {
   print "(> ",$o_warnL[0],")";
}
if (defined($o_critL[1]) && ($num_int_ok > $o_critL[1])) {
  print " (> ",$o_critL[1]," : CRITICAL)";
  $final_status=2;
} elsif (defined($o_warnL[1]) && ($num_int_ok > $o_warnL[1])) {
   print " (> ",$o_warnL[1]," : WARNING)";
   $final_status=($final_status==2)?2:1;
} elsif (defined($o_warnL[1])) {
   print " (<= ",$o_warnL[1],"):OK";
}

print $memory_print,$cpu_print,"\n";

if ($final_status==2) { exit $ERRORS{"CRITICAL"};}
if ($final_status==1) { exit $ERRORS{"WARNING"};}
if ($final_status==3) { exit $ERRORS{"UNKNOWN"};}
exit $ERRORS{"OK"};

