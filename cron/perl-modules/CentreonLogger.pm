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