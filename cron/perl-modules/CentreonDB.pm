################################################################################
# Copyright 2005-2011 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
# For more information : contact@centreon.com
# 
# SVN : $URL
# SVN : $Id
#
####################################################################################

use strict;
use warnings;
use DBI;

package CentreonDB;

# Constructor
# Parameters:
# $logger: instance of class CentreonLogger
# $db: Database name
# $host: database hosting server
# user: mysql user
# password: mysql password
sub new {
	my $class = shift;
	my $self  = {};
	$self->{"logger"}	= shift;
	$self->{"db"}       = shift;
	$self->{"host"}     = shift;
	$self->{"user"}     = shift;
	$self->{"password"} = shift;
	$self->{"port"}     = shift;
	$self->{"type"}     = "mysql";
	bless $self, $class;
	$self->connect();
	return $self;
}

# Getter/Setter DB name
sub db {
	my $self = shift;
	if (@_) {
		$self->{"db"} = shift;
	}
	return $self->{"db"};
}

# Getter/Setter DB host
sub host {
	my $self = shift;
	if (@_) {
		$self->{"host"} = shift;
	}
	return $self->{"host"};
}

# Getter/Setter DB user
sub user {
	my $self = shift;
	if (@_) {
		$self->{"user"} = shift;
	}
	return $self->{"user"};
}

# Getter/Setter DB passord
sub password {
	my $self = shift;
	if (@_) {
		$self->{"password"} = shift;
	}
	return $self->{"password"};
}

# Connection initializer
sub connect {
	my $self = shift;
	my $logger = $self->{"logger"};
	$self->{"instance"} = DBI->connect(
		"DBI:".$self->{"type"} 
			.":".$self->{"db"}
			.":".$self->{"host"}
			.":".$self->{"port"},
		$self->{"user"},
		$self->{"password"},
		{ "RaiseError" => 0, "PrintError" => 0, "AutoCommit" => 1 }
	  ); 
	  my $instance = $self->{"instance"};
	  if (!defined($self->{"instance"})) {
	  	$logger->writeLog("FATAL", "MySQL error : cannot connect to database ".$self->{"db"});
	  }
	  
	return $self->{"instance"};
}

# Destroy connection
sub disconnect {
	my $self = shift;
	my $instance = $self->{"instance"};
	$instance->disconnect;
}

sub query {
	my $self = shift;
	my $query = shift;
	
	my $instance = $self->{"instance"};
	my $logger = $self->{"logger"};
	
	my $statement_handle = $instance->prepare($query);
	#$logger->writeLog("DEBUG", "MySQL error : ".$query);
	if (defined($instance->errstr)) {
	  	$logger->writeLog("DEBUG", "MySQL error : ".$query);
	  	$logger->writeLog("FATAL", "MySQL error : ".$instance->errstr);
	}
	
    $statement_handle->execute;
    if (defined($instance->errstr)) {
		  	$logger->writeLog("DEBUG", "MySQL error : ".$query);
	  		$logger->writeLog("FATAL", "MySQL error : ".$instance->errstr);
	}
	
    return $statement_handle;
}

1;
