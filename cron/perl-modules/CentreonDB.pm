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
	$self->{"port"}     = 3306;
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
			.":database=".$self->{"db"}
			.";host=".$self->{"host"},
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
