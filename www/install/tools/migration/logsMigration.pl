#! /usr/bin/perl -w
###################################################################
# Centreon is developped with GPL Licence 2.0
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
#
# Developped by : Julien Mathis - Romain Le Merlus - Sylvestre Ho
#
###################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
#    For information : contact@merethis.com
####################################################################

use strict;

####################################################################
# Required libs
####################################################################
use DBI;
use POSIX;
use Getopt::Long;
use Time::Local;

####################################################################
# Global Variables
####################################################################

# Include Centreon DB Configuration Variables
use vars
  qw ($mysql_database_oreon $mysql_database_ods $mysql_host $mysql_user $mysql_passwd);
require "@CENTREON_ETC@/conf.pm";

#Hash tables that will contain host and services availability stats by day
my %hosts;
my %services;

# DB Connection instance
my $dbh;
my $dbhoreon;

# DB Layer
my $dbLayer;

# status variables
my %svc_status_code;
my %host_status_code;
my %status_code_string;
my %type_code;
my %instances;
my $log_table;

####################################################################
#FUNCTIONS
####################################################################

################################################################


################################################################
# Init env
################################################################
sub initEnv() {
	%svc_status_code = (
		"OK"       => 0,
		"WARNING"  => 1,
		"CRITICAL" => 2,
		"UNKNOWN"  => 3
	);

	%host_status_code = (
		"UP"	  	=> 0,
		"DOWN"	  	=> 1,
		"UNREACHABLE"   => 2,
		"PENDING"	=> 4
	);

	%type_code = (
		"SOFT" => 0,
		"HARD" => 1
	);

	my $query = "SELECT instance_id, instance_name FROM instance";
	my $sth = $dbh->prepare($query);
	die "Error : " . $dbh->errstr . "\n" if ( !$sth );
        $sth->execute();
	die "Error : " . $dbh->errstr . "\n" if ( !$sth );
	while (my $row = $sth->fetchrow_hashref() ) {
		$instances{$row->{'instance_id'}} = $row->{'instance_name'};
	}

	$query = "SELECT host_id, host_name FROM host WHERE host_register = '1'";
	$sth = $dbhoreon->prepare($query);
	die "Error : " . $dbhoreon->errstr . "\n" if ( !$sth );
        $sth->execute();
        die "Error : " . $dbhoreon->errstr . "\n" if ( !$sth );
        while (my $row = $sth->fetchrow_hashref()) {
		$hosts{$row->{'host_name'}} = $row->{'host_id'};
        }

	$query = "SELECT tb.host_id, tb.host_name, tb.service_id, tb.service_description
		  FROM (SELECT h.host_id, h.host_name, s.service_id, s.service_description
			FROM host h, service s, host_service_relation hsr
		  	WHERE h.host_id = hsr.host_host_id
			AND hsr.service_service_id = s.service_id 
			AND h.host_register = '1'
			
			UNION
		
			SELECT h.host_id, h.host_name, s.service_id, s.service_description
                        FROM host h, service s, hostgroup_relation hgr, host_service_relation hsr
                        WHERE h.host_id = hgr.host_host_id
			AND hgr.hostgroup_hg_id = hsr.hostgroup_hg_id
                        AND hsr.service_service_id = s.service_id 
                        AND h.host_register = '1'
			) tb ";
        $sth = $dbhoreon->prepare($query);
        die "Error : " . $dbhoreon->errstr . "\n" if ( !$sth );
        $sth->execute();
        die "Error : " . $dbhoreon->errstr . "\n" if ( !$sth );
        while (my $row = $sth->fetchrow_hashref()) {
		$services{$row->{'host_name'}}{$row->{'service_description'}} = $row->{'service_id'};
        }
}

##################################################################################
# Synchronize day
##################################################################################
sub syncDay ($$) {
	my ( $start, $end ) = ( shift, shift );
	my $query = " SELECT ctime, host_name, service_description, status, output, 
			     notification_cmd, notification_contact, type, retry, msg_type, instance
 		      FROM `log` WHERE `ctime` >= ".$start." AND `ctime` < ". $end .
	  	    " ORDER BY `ctime`";
	my $sth = $dbh->prepare($query);
	die "Error : " . $dbh->errstr . "\n" if ( !$sth );
	$sth->execute();
	die "Error : " . $dbh->errstr . "\n" if ( !$sth );
	my $newQuery = "";
	my $counter = 0;
	while ( my $row = $sth->fetchrow_hashref() ) {
		$newQuery = "INSERT INTO logs (";
		if (defined($row->{'ctime'})) { $newQuery .= "ctime,"; }
		if (defined($row->{'host_name'})) { 
			$newQuery .= "host_name,";
			if (defined($hosts{$row->{'host_name'}})) {
				$newQuery .= "host_id,";
			}
		}
		if (defined($row->{'service_description'})) { 
			$newQuery .= "service_description,"; 
			if (defined($services{$row->{'host_name'}}{$row->{'service_description'}})) {
				$newQuery .= "service_id,";
			}
		}
		if (defined($row->{'status'})) { $newQuery .= "status,"; }
		if (defined($row->{'output'})) { $newQuery .= "output,"; }
		if (defined($row->{'notification_cmd'})) { $newQuery .= "notification_cmd,"; }
		if (defined($row->{'notification_contact'})) { $newQuery .= "notification_contact,"; }
		if (defined($row->{'type'})) { $newQuery .= "type,"; }
		if (defined($row->{'retry'})) { $newQuery .= "retry,"; }
		if (defined($row->{'msg_type'})) { $newQuery .= "msg_type,"; }
		if (defined($row->{'instance'})) { $newQuery .= "instance_name,"; }
		$newQuery = substr($newQuery, 0, -1);
		$newQuery .= ")";
		$newQuery .= " VALUES ( ";
		if (defined($row->{'ctime'})) { $newQuery .= $row->{'ctime'}.","; }
		if (defined($row->{'host_name'})) { 
			$newQuery .= $dbh->quote($row->{"host_name"}).",";
			if (defined($hosts{$row->{'host_name'}})) {
                                $newQuery .= $hosts{$row->{'host_name'}}.",";
                        }
		}
                if (defined($row->{'service_description'})) { 
			$newQuery .= $dbh->quote($row->{"service_description"}).","; 
			if (defined($services{$row->{'host_name'}}{$row->{'service_description'}})) {
                                $newQuery .= $services{$row->{'host_name'}}{$row->{'service_description'}}.",";
                        }
		}
                if (defined($row->{'status'})) { 
			if (defined($row->{'service_description'}) && 
       			    defined($svc_status_code{$row->{'status'}})) {
				$newQuery .= $svc_status_code{$row->{'status'}} . ",";
			} elsif (defined($host_status_code{$row->{'status'}})) {
				$newQuery .= $host_status_code{$row->{'status'}} . ",";
			}
		}
                if (defined($row->{'output'})) { $newQuery .= $dbh->quote($row->{"output"}).","; }
                if (defined($row->{'notification_cmd'})) { $newQuery .= $dbh->quote($row->{"notification_cmd"}).","; }
                if (defined($row->{'notification_contact'})) { $newQuery .= $dbh->quote($row->{"notification_contact"}).","; }
                if (defined($row->{'type'})) { 
			if (defined($row->{'service_description'}) &&
                            defined($type_code{$row->{'type'}})) {
                                $newQuery .= $type_code{$row->{'type'}} . ",";
                        } elsif (defined($type_code{$row->{'type'}})) {
                                $newQuery .= $type_code{$row->{'type'}} . ",";
                        }
		}
                if (defined($row->{'retry'})) { $newQuery .= $row->{"retry"}.","; }
                if (defined($row->{'msg_type'})) { $newQuery .= $row->{"msg_type"}.","; }
                if (defined($row->{'instance'})) { 
			if (defined($instances{$row->{'instance'}})) {
				$newQuery .= $dbh->quote($instances{$row->{"instance"}}).",";
			} else {
				$newQuery .= "'',";
			}
		}
		$newQuery = substr($newQuery, 0, -1);
		$newQuery .= ")";
		my $sth = $dbh->prepare($newQuery);
	        die "Error : " . $dbh->errstr . "\n" if ( !$sth );
        	$sth->execute();
	        die "Error : " . $dbh->errstr . "\n" if ( !$sth );
		$counter++;		
	}
	print $counter . " queries executed\n";
	$sth->finish();
}

############################################################################################################
# Main function parse parameters
# and select to build archive for one day log or to rebuild all archives
# The table contact_param from Centreon DB contains infos that allows to filter days of week and time period
# to archive stats between two given hours and specific days of week
#############################################################################################################

sub main {
	# Initializing MySQL DB connection
	$dbh = DBI->connect(
		"DBI:mysql:database=" . $mysql_database_ods . ";host=" . $mysql_host,
		$mysql_user,
		$mysql_passwd,
		{ 'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1 }
	);
	$dbhoreon = DBI->connect(
		"DBI:mysql:database=" . $mysql_database_oreon . ";host=" . $mysql_host,
		$mysql_user,
		$mysql_passwd,
		{ 'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1 }
	);
	
	initEnv();
	processData();
	$dbh->disconnect();
	$dbhoreon->disconnect();
}

sub processData() {
	my $time_period           = shift;
	my $one_day_real_duration = 60 * 60 * 24;

	# Getting first log and last log times
	my $query = "SELECT min(`ctime`) as minc, max(`ctime`) as maxc 
				 FROM `log` 
				 ORDER BY `ctime` ASC";
	my $sth = $dbh->prepare($query);
	$sth->execute;
	my ( $start, $end ) = ( 0, 0 );
	if ( my $row = $sth->fetchrow_hashref() ) {
		( $start, $end ) = ( $row->{"minc"}, $row->{"maxc"} );
	}
	my ( $day, $month, $year ) = ( localtime(time) )[ 3, 4, 5 ];
	my $today_begin = mktime( 0, 0, 0, $day, $month, $year, 0, 0, -1 );

	#my $today_begin = $now - ($now % $one_day_real_duration);
	if ( $end > $today_begin ) {
		$end = $today_begin;
	}
	$sth->finish;

	# Getting list of days between the first log and last log
	my @days;
	( $day, $month, $year ) = ( localtime($start) )[ 3, 4, 5 ];
	$start = mktime( 0, 0, 0, $day, $month, $year, 0, 0, -1 );
	while ( $start < $end ) {
		# getting day end and start defined with timeperiod in table `contact_param` from centreon DB
		my %period = (
			"day_start" => $start,
			"day_end"   => $start + $one_day_real_duration
		);
		push @days, \%period;
		$day++;
		$start = mktime( 0, 0, 0, $day, $month, $year, 0, 0, -1 );
	}
	my @days_in_order;
	for ( my $i = 0 ; $i < scalar(@days) ; $i++ ) {
		if ( defined( $days[$i] ) ) {
			push @days_in_order, $days[$i];
		}
	}
	
	# archiving logs for each days
	foreach (@days_in_order) {
		print "rebuilding : "
		  . ( ( localtime( $_->{"day_start"} ) )[4] + 1 ) . "/"
		  . ( localtime( $_->{"day_start"} ) )[3] . "/"
		  . ( 1900 + ( localtime( $_->{"day_start"} ) )[5] ) . " "
		  . ( localtime( $_->{"day_start"} ) )[2] . ":"
		  . ( localtime( $_->{"day_start"} ) )[1];
		print " To "
		  . ( ( localtime( $_->{"day_end"} ) )[4] + 1 ) . "/"
		  . ( localtime( $_->{"day_end"} ) )[3] . "/"
		  . ( 1900 + ( localtime( $_->{"day_end"} ) )[5] ) . " "
		  . ( localtime( $_->{"day_end"} ) )[2] . ":"
		  . ( localtime( $_->{"day_end"} ) )[1] . " => ";
		syncDay( $_->{"day_start"}, $_->{"day_end"} );
		undef(%services);
	}
}


#####################################################
# MAIN EXECUTION
#####################################################
main();

