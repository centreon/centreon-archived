
use strict;
use warnings;

package centreon::reporting::CentreonAck;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
# $centreon: Instance of centreonDB class for connection to Centreon database
# $dbLayer : Database Layer : ndo | broker
# $centstorage: (optionnal) Instance of centreonDB class for connection to Centstorage database
sub new {
    my $class = shift;
    my $self  = {};
    $self->{"logger"} = shift;
    $self->{"centstatus"} = shift;
    $self->{'dbLayer'} = shift;
    if (@_) {
        $self->{"centstorage"}  = shift;
    }    
    bless $self, $class;
    return $self;
}

# returns first ack time for a service or a host event
sub getServiceAckTime {
    my $self = shift;
    my $centreon = $self->{"centstatus"};
    my $start = shift;
    my $end = shift;
    my $hostName = shift;
    my $serviceDescription = shift;
    my $dbLayer = $self->{'dbLayer'};
    my $query;
    
    if ($dbLayer eq "ndo") {
        $query = "SELECT UNIX_TIMESTAMP(`entry_time`) as ack_time ".
            " FROM `nagios_acknowledgements` a, `nagios_objects` o".
            " WHERE o.`object_id` = a.`object_id`".
            " AND `acknowledgement_type` = '1'".
            " AND `entry_time` >= FROM_UNIXTIME(".$start.")".
			" AND `entry_time` <= FROM_UNIXTIME(".$end.")".
            " AND objecttype_id = '2'".
            " AND o.`name1` = '".$hostName. "'".
            " AND o.`name2` = '".$serviceDescription. "'".    
            " ORDER BY `entry_time` asc";
    } elsif ($dbLayer eq "broker") {
        $query = "SELECT `entry_time` as ack_time ".
            " FROM `acknowledgements` a, `services` s, `hosts` h ".
            " WHERE h.`host_id` = a.`host_id`".
            " AND a.`host_id` = s.`host_id`".
            " AND `type` = 1".
            " AND `entry_time` >= ".$start.
            " AND `entry_time` <= ".$end.
            " AND h.`name` = '".$hostName. "'".
            " AND s.`description` = '".$serviceDescription. "'".    
            " ORDER BY `entry_time` asc";
    }

    my $sth = $centreon->query($query);
    my $ackTime = "NULL";
    if (my $row = $sth->fetchrow_hashref()) {
        $ackTime = $row->{'ack_time'};
    }
    $sth->finish();
    return ($ackTime);
}

# returns first ack time for a service or a host event
sub getHostAckTime {
    my $self = shift;
    my $centreon = $self->{"centstatus"};
    my $start = shift;
    my $end = shift;
    my $hostName = shift;
    my $dbLayer = $self->{'dbLayer'};
    my $query;
    
    if ($dbLayer eq "ndo") {
        $query = "SELECT UNIX_TIMESTAMP(`entry_time`) as ack_time ".
            " FROM `nagios_acknowledgements` a, `nagios_objects` o".
            " WHERE o.`object_id` = a.`object_id`".
            " AND `acknowledgement_type` = '0'".
            " AND UNIX_TIMESTAMP(`entry_time`) >= ".$start.
            " AND UNIX_TIMESTAMP(`entry_time`) <= ".$end.
            " AND o.`name1` = '".$hostName. "'".
            " ORDER BY `entry_time` asc";
    } elsif ($dbLayer eq "broker") {
        $query = "SELECT entry_time as ack_time ".
            " FROM `acknowledgements` a, `hosts` h".
            " WHERE h.`host_id` = a.`host_id`".
            " AND `type` = 0".
            " AND `entry_time` >= ".$start.
            " AND `entry_time` <= ".$end.
            " AND h.`name` = '".$hostName. "'".
            " ORDER BY `entry_time` asc";
    }

    my $sth = $centreon->query($query);
    my $ackTime = "NULL";
    if (my $row = $sth->fetchrow_hashref()) {
        $ackTime = $row->{'ack_time'};
    }
    $sth->finish();
    return ($ackTime);
}

1;