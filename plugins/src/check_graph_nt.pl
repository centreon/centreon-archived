#! /usr/bin/perl -w
#
# $Id: check_graph_nt.pl,v 1.3 2005/08/01 18:04:00 gollum123 Exp $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Julien Mathis - Mathieu Mettre
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
use vars qw($opt_H $opt_p $opt_s $opt_v $opt_V $opt_h $opt_w $opt_c $opt_S $opt_g $opt_t $opt_l $opt_d $opt_D $opt_step $step $opt_f);

##
## Plugin var init
##
my $pathtorrdbase = $oreon{GLOBAL}{DIR_RRDTOOL};
my $pathtolibexecnt = $oreon{GLOBAL}{NAGIOS_LIBEXEC}."check_nt";

my($op_v, $op_d, $op_s, $op_t, $op_l, $port, @values,  @test, @test2, @test3, @test4, @test5, $warning, $critical, @w, @c, $uptime);
my($warning2, $critical2, $warning3, $critical3, $warning4, $critical4, @output);
$PROGNAME = "check_graph_nt";
sub print_help ();
sub print_usage ();

Getopt::Long::Configure('bundling');
GetOptions
    ("h"   => \$opt_h, "help"         => \$opt_h,
     "p=s" => \$opt_p, "port=s"       => \$opt_p,
     "V"   => \$opt_V, "version"      => \$opt_V,
     "g"   => \$opt_g, "rrdgraph"     => \$opt_g,
     "rrd_step=s" => \$opt_step,
     "s=s" => \$opt_s, "password=s"   => \$opt_s,
     "d=s" => \$opt_d, "showall=s"    => \$opt_d,
     "v=s" => \$opt_v, "variable=s"   => \$opt_v,
     "D=s" => \$opt_D, "directory=s"  => \$opt_D,
     "t=s" => \$opt_t, "timeout=s"    => \$opt_t,
     "l:s" => \$opt_l, "parameter:s"  => \$opt_l,
     "w=s" => \$opt_w, "warning=s"    => \$opt_w,
     "c=s" => \$opt_c, "critical=s"   => \$opt_c,
     "S=s" => \$opt_S, "ServiceId=s"  => \$opt_S,
     "H=s" => \$opt_H, "hostname=s"   => \$opt_H);

if ($opt_h) {
    print_help();
    exit $ERRORS{'OK'};
}

if ($opt_V) {
    $_ = `$pathtolibexecnt -V`;
    print "$_";
    exit $ERRORS{'OK'};
}

if ($opt_p) {
  if ($opt_p =~ /([0-9]+)/){
    $port = $1;
  }
  else{
    print "Unknown -p number expected... or it doesn't exist, try another port - number\n";
    exit $ERRORS{'UNKNOWN'};
  }
}

$opt_H = shift unless ($opt_H);
(print_usage() && exit $ERRORS{'OK'}) unless ($opt_H);


if ($opt_c) {
  ($opt_c) || ($opt_c = shift);
  $critical = $1 if ($opt_c =~ /([0-9]+)/);
}

if ($opt_w) {
  ($opt_w) || ($opt_w = shift);
  $warning = $1 if ($opt_w =~ /([0-9]+)/);
}

if (($critical && $warning) && ($critical <= $warning)) {
    print "(--crit) must be superior to (--warn)";
    print_usage();
    exit $ERRORS{'OK'};
}


if ($opt_t) {
  ($opt_t) || ($opt_t = shift);
  $op_t = $1 if ($opt_t =~ /([-\.,\w]+)/);
}

if ($opt_l) {
  ($opt_l) || ($opt_l = shift);
   $op_l = $1 if ($opt_l =~ /(.+)/);
}

if ($opt_s) {
  ($opt_s) || ($opt_s = shift);
  $op_s = $1 if ($opt_s =~ /([-.,A-Za-z0-9]+)/);
}

if ($opt_d) {
  ($opt_d) || ($opt_d = shift);
  $op_d = $1 if ($opt_d =~ /([-.,A-Za-z0-9]+)/);
}

if ($opt_v) {
  ($opt_v) || ($opt_v = shift);
  $op_v = $1 if ($opt_v =~ /([-.,A-Za-z0-9]+)/);
}

($opt_step) || ($opt_step = shift) || ($opt_step = 300);
$step = $1 if ($opt_step =~ /(\d+)/);

($opt_S) || ($opt_S = shift) || ($opt_S = "1_1");
my $ServiceId = is_valid_serviceid($opt_S);

##
## RRDTool var init
##

my $rrd = $pathtorrdbase.$ServiceId.".rrd";
my $name = $0;
$name =~ s/\.pl.*//g;
my $return_code;
##
## Plugin requests
##
my $start=time;
if ($op_v) {
    if ($op_v) {$op_v = "-v ".$op_v;}
    if ($port) {$port = "-p ".$port;} else { $port = " ";}
    if ($warning) {$warning = "-w ".$warning;} else { $warning = " ";}
    if ($critical) {$critical = "-c ".$critical;} else { $critical = " ";}
    if ($op_l) {$op_l = "-l \"".$op_l ."\"";} else { $op_l = " ";}
    if ($op_t) {$op_t = "-t ".$op_t;} else { $op_t = " ";}
    if ($op_s) {$op_s = "-s ".$op_s;} else { $op_s = " ";}
    if ($op_d) {$op_d = "-d ".$op_d;} else { $op_d = " ";}
#   print "$pathtolibexecnt -H $opt_H $op_v $port $warning $critical $op_l $op_t $op_s $op_d\n";
    $_ = `$pathtolibexecnt -H $opt_H $op_v $port $warning $critical $op_l $op_t $op_s $op_d 2>/dev/null`;
    my $return = $_;
    $return =~ s/\\//g;
    $return_code = $? / 256;

    ##
    ## CLIENTVERSION
    ##
    if ($op_v =~ /CLIENTVERSION/){
        print "CLIENTVERSION impossible to Graph!\n";
        exit $ERRORS{'UNKNOWN'};
    }
    
    if (($op_v =~ /CPULOAD/) && ($op_l =~ /([-\.,\w]+)/)){    ## CPULOAD
	@output = split(/\|/,$_);
        @values = $output[0] =~ /(\d*)\%/g  ;
        $start=time;
        if ($opt_g)  {
            unless (-e $rrd) {
		create_rrd ($rrd,$#values +1,$start,$step,"U","U","GAUGE");
            }
	    update_rrd ($rrd,$start,@values);
        }
        ## Print Plugins Output
        $return =~ s/\n/ /g;
        if (@values){
	    if (defined($opt_c) && defined($opt_w)){
		print $return . "|cpu=@values;$opt_w;$opt_c\n";
	    } else {
		print $return . "|cpu=@values\n";
	    }
	} else {
	    print $return . "\n";
	}
        exit $return_code;
    } elsif ($op_v =~ /UPTIME/){		## UPTIME
        if ($_ =~ /.*[-:]+\s(\d+)\s.*$/ ) {
            $uptime = $1;
        } else {
            print "unable to parse check_nt output: $_\n" ;
            exit $ERRORS{'UNKNOWN'};
        }
        if ($opt_g) {
        	if (! -e $rrd) {
          		create_rrd ($rrd,1,$start,$step,"U","U","GAUGE");
			} else {
                update_rrd ($rrd,$start,$uptime);
         	}
        }
        $_ =~ s/\n/ /g;
        if (defined($uptime)){
	        print $_ . "|uptime=".$uptime."d\n";
    	} else {
    	    print $_ . "\n";
    	}
        exit $return_code;
    } elsif (($op_v =~ /USEDDISKSPACE/) && ($op_l =~ /([-\.,\w]+)/)){	## USEDDISKSPACE
        my @test = split(/ /,$_);
        if (defined($test[9]) && defined($test2[1])){
	        @test2 = split(/\(/, $test[9]);
    	    @test3 = split(/\%/, $test2[1]);
        }
        @c = split(/ /, $critical);
        $critical = $c[1];
        @w = split(/ /, $warning);
        $warning = $w[1];
        if ($opt_g) {
            unless (-e $rrd) {
            	create_rrd ($rrd,3,$start,$step,"U","U","GAUGE");
            }
        	$test[3] =~ s/,/\./ ;
        	$test[7] =~ s/,/\./ ;
        	$test[12] =~ s/,/\./ ;
        	update_rrd ($rrd,$start,$test[3],$test[7],$test[12]);
        }
        ## Print Plugins Output
        $return =~ s/\n/ /g;
        $return =~ s/%/ pct/g;
        if (defined($test[3]) && defined($test[7]) && defined($test[12])){
	        print $return . "|total=".$test[3]."Mo used=".$test[7]."Mo free=".$test[12]."Mo\n";
		} else {
			print $return . "\n";
		}
		exit $return_code;
    } elsif ($op_v =~ /MEMUSE/){    ## MEMUSE
        $start=time;
        my @test = split(/ /,$_);
        if (defined($test[2])){
        	@test4 = split(/:/, $test[2]);
        }
        @c = split(/ /, $critical);
        $critical = $c[1];
        @w = split(/ /, $warning);
        $warning = $w[1];
        if ($opt_g) {
        	unless (-e $rrd) {
            	create_rrd ($rrd,3,$start,$step,"U","U","GAUGE");
            }
            # Replace , by . to convert in real float number (for rrdtool)
            $test4[1] =~ s/,/\./ ;
            $test[6] =~ s/,/\./ ;
            $test[11] =~ s/,/\./ ;
            update_rrd ($rrd,$start,$test4[1],$test[6],$test[11]);
        }
        ## Print Plugins Output
        $return =~ s/\n/ /g;
        $return =~ s/%/ pct/g;
        if ($test4[1] && $test[6] && $test[11]){
	        print $return . "|total=".$test4[1]." used=".$test[6]." free=".$test[11]."\n";
        } else {
        	print $return . "\n";
        }
        exit $return_code;
    } elsif ($op_v =~ /SERVICESTATE/){## SERVICESTATE
       	my (@tab, $process, $nom, $etat);
       	@tab = split (' - ',$_);
       	foreach $process (@tab) {
       		($nom,$etat) = split (': ', $process);
            if (defined($etat)) {
               	$etat =~ s/\n//;
            } else {
               	$etat = "Unknow";
            }
            if ($etat =~ /Started/)
              	{$etat=1;}
            elsif ($etat =~ /Stopped/)
               	{$etat=0;}
            elsif ($etat =~ /Unknown/)
               	{$etat=-1;}
            else {
               	print "Unable to get $nom status [$etat]: \n\t$_\n";
                exit $ERRORS{'UNKNOWN'};
            }
        }
        if ($opt_g) {
        	if (! -e $rrd) {
            	create_rrd ($rrd,1,$start,$step,"U","U","GAUGE");
            } else {
                update_rrd ($rrd,$start,$etat);
            }
        }
        $return =~ s/%/ pct/g;
        print $return;
        exit $return_code;
    } elsif ($op_v =~ /PROCSTATE/){## PROCSTATE
        print "PROCSTATE not graphed\n";
        exit $ERRORS{'UNKNOWN'};
    } elsif (($op_v =~ /COUNTER/) && ($op_l =~ /(.+)/))  {    ## COUNTER
		@output = split(/\|/,$_);
        @values = $output[0] =~ /([,\.\d]*)\s?\%/  ;
        if (!@values) {@values = $output[0] =~ /([\d]*)/;}
        $start=time;
        if ($opt_g)  {
            unless (-e $rrd) {
	            create_rrd ($rrd,$#values +1,$start,$step,"U","U","GAUGE");
            }
        	update_rrd ($rrd,$start,@values);
        }
        ## Print Plugins Output
        $return =~ s/\n/ /g;
        $return =~ s/%/ pct/g;
        print $return . "|counter=".@values."\n";
        exit $return_code;
    }
} else {
	print "Could not parse arguments\n";
    exit $ERRORS{'UNKNOWN'};
}

##
## Plugin return code
##

sub print_usage () {
    print "\nUsage:\n";
    print "$PROGNAME\n";
    print "   Usage: check_graph_nt -H host -v variable [-p port] [-s password] [-w warning] [-c critical] [-l params] [-d SHOWALL] [-t timeout] [-D rrd directory] -g -S ServiceID\n";
    print "   Options:\n";
    print "   -H, --hostname=HOST\n";
    print "      Name of the host to check\n";
    print "   -p, --port=INTEGER\n";
    print "      Optional port number (default: 1248)\n";
    print "   -s <password>\n";
    print "      Password needed for the request\n";
    print "   -v, --variable=STRING\n";
    print "      Variable to check.  Valid variables are:\n";
    print "         CLIENTVERSION = Not Graphed. Get the NSClient version\n";
    print "         CPULOAD = Average CPU load on last x minutes. Request a -l parameter with the following syntax:\n";
    print "           -l <minutes range>,<warning threshold>,<critical threshold>. <minute range> should be less than 24*60.\n";
    print "          Thresholds are percentage and up to 10 requests can be done in one shot. ie: -l 60,90,95,120,90,95\n";
    print "          and 4 requests can be graphed.\n";
    print "         UPTIME = Only Days are graphed. Get the uptime of the machine. No specific parameters. No warning or critical threshold.\n";
    print "         USEDDISKSPACE = Size and percentage of disk use. Request a -l parameter containing the drive letter only.\n";
    print "                         Warning and critical thresholds can be specified with -w and -c.\n";
    print "         MEMUSE = Memory use. Warning and critical thresholds can be specified with -w and -c.\n";
    print "         SERVICESTATE = Check and graph the state of one service. Request a -l parameters with the following syntax:\n";
    print "           -l <service1>... You MUST specify -d SHOWALL in the input command.\n";
    print "           1: Service Started - 0: Service Stopped - -1: Service Unknown.\n";
#    print "         SERVICESTATE = Not Graphed. Check the state of one or several services. Request a -l parameters with the following syntax:\n";
#    print "           -l <service1>,<service2>,<service3>,... You can specify -d SHOWALL in case you want to see working services\n";
#    print "           in the returned string.\n";
    print "         PROCSTATE = Not Graphed. Check if one or several process are running. Same syntax as SERVICESTATE.\n";
    print "         COUNTER = Check any performance counter of Windows NT/2000. Request a -l parameters with the following syntax:\n";
    print "           -l \"<performance object>counter\",\"<description>\"  The <description> parameter is optional and\n";
    print "           is given to a printf output command which require a float parameters. Some examples:\n";
    print "             \"Paging file usage is %.2f %%\" or \"%.f %% paging file used.\"\n";
    print "    -w, --warning=INTEGER\n";
    print "      Threshold which will result in a warning status\n";
    print "    -c, --critical=INTEGER\n";
    print "      Threshold which will result in a critical status\n";
    print "    -t, --timeout=INTEGER\n";
    print "      Seconds before connection attempt times out (default: 10)\n";
    print "   -h, --help\n";
    print "      Print this help screen\n";
    print "   -V, --version\n";
    print "      Print version information\n";
    print "   -g (--rrdgraph)   create  à rrd base and add datas into this one\n";
    print "   --rrd_step	    Specifies the base interval in seconds with which data will be fed into the RRD (300 by default)\n";
    print "   -D (--directory)  Path to rrdatabase (or create the .rrd in this directory)\n";
    print "                     by default: ".$pathtorrdbase."\n";
    print "                     (The path is valid with spaces '/my\ path/...')\n";
    print "   -S (--ServiceId)  Oreon Service Id\n";
}

sub print_help () {
    print "Copyright (c) 2004 OREON\n";
    print "Bugs to http://www.oreon.org/\n";
    print "\n";
    print_usage();
    print "\n";
}
