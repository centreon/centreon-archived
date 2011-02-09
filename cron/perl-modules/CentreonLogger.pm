################################################################################
# Copyright 2005-2010 MERETHIS
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
use Switch;

package CentreonLogger;

# Constructor, needs 4 parameters db name, host, user password
sub new {
	my $class = shift;
	my $self  = {};
	$self->{"file"}			= 0;
	$self->{"filehandler"}	= undef;
	$self->{"stderr"}		= 0;
	$self->{"severity"}		= 0;
	# my %severities 			= ("debug" => 0, "info" => 1, "warning" => 2, "error" => 3, "fatal" => 4);
	# $self->{"severities"}	= \%severities;
	$self->{"type"}			= undef;
	bless $self, $class;
	return $self;
}

# Getter/Setter Log flag and file handler
sub file {
	my $self = shift;
	if (@_) {
		my $file = shift;
		if (open($self->{"filehandler"} ,">>", $file)){
			$self->{"file"} = 1;
		}	
	}
	return $self->{"file"};
}

# Getter/Setter stderr
sub stderr {
	my $self = shift;
	if (@_) {
		$self->{"stderr"} = shift;
	}
	return $self->{"stderr"};
}

# Getter/Setter Log severity
sub severity {
	my $self = shift;
	if (@_) {
		my $severity = shift;
		switch ($severity) {
			case "debug"	{ $self->{"severity"} = 0}
			case "info"		{ $self->{"severity"} = 1}
			case "warning"	{ $self->{"severity"} = 2}
			case "error"	{ $self->{"severity"} = 3}
			case "fatal"	{ $self->{"severity"} = 4}
			else			{ $self->{"severity"} = 0}
		}
	}
	return $self->{"severity"};
}

# write log in all defined outputs
sub writeLog {
	my $self = shift;
	my $severity = shift;
	$severity = lc($severity);
	my $message = shift;
	my %severities = ("debug" => 0, "info" => 1, "warning" => 2, "error" => 3, "fatal" => 4);
	if (defined($severities{$severity}) && $severities{$severity}  >= $self->{"severity"}) {
		if ($self->{"stderr"}) {
			print STDOUT "[".time."] [".uc($severity)."] ".$message."\n";
		}
	}
	if ($severities{$severity}  >= 3) {
		if ($self->{"stderr"}) {
			print STDOUT "[".time."] [".uc($severity)."] Program terminated with errors\n";
			exit;
		}
	}
}

# close file handler
sub close {
	my $self = shift;
	if ($self->{"file"}) {
		my $filehandler = $self->{"filehandler"};
		$filehandler->close();
	}
}

1;