
package modules::centreondpull::hooks;

use warnings;
use strict;
use centreon::centreond::clientzmq;

my $config_core;
my $config;
my $module_id = 'centreondpull';
my $events = [
];
my $stop = 0;
my $client;
my $socket_to_internal;
my $logger;

sub register {
    my (%options) = @_;
    
    $config = $options{config};
    $config_core = $options{config_core};
    return ($events, $module_id);
}

sub init {
    my (%options) = @_;

    $logger = $options{logger};
    # Connect internal
    $socket_to_internal = centreon::centreond::common::connect_com(zmq_type => 'ZMQ_DEALER', name => 'centreondpull',
                                                                   logger => $options{logger},
                                                                   type => $config_core->{internal_com_type},
                                                                   path => $config_core->{internal_com_path},
                                                                   linger => $config->{linger}
                                                                   );
    $client = centreon::centreond::clientzmq->new(identity => $config_core->{id}, 
                                                  cipher => $config->{cipher}, 
                                                  vector => $config->{vector},
                                                  pubkey => $config->{pubkey},
                                                  target_type => $config->{target_type},
                                                  target_path => $config->{target_path},
                                                  logger => $options{logger},
                                                  ping => $config->{ping},
                                                  ping_timeout => $config->{ping_timeout}
                                                  );
    $client->init(callback => \&read_message);
    
    $client->send_message(action => 'REGISTERNODE', data => { id => $config_core->{id} }, 
                          json_encode => 1);
    centreon::centreond::common::add_zmq_pollin(socket => $socket_to_internal,
                                                callback => \&from_router,
                                                poll => $options{poll});
}

sub routing {
    my (%options) = @_;

}

sub gently {
    my (%options) = @_;

    $stop = 1;
    $client->send_message(action => 'UNREGISTERNODE', data => { id => $config_core->{id} }, 
                          json_encode => 1);
    $client->close();
    return 0;
}

sub kill {
    my (%options) = @_;

    return 0;
}

sub kill_internal {
    my (%options) = @_;

    return 0;
}

sub check {
    my (%options) = @_;

    if ($stop == 0) {
        # If distant server restart, it's a not problem. It save the key. 
        # But i don't have the registernode anymore. The ping is the 'registernode' for pull mode.
        $client->ping(poll => $options{poll}, action => 'REGISTERNODE', data => { id => $config_core->{id} }, json_encode => 1);
    }
    return 0;
}

####### specific

sub transmit_back {
    my (%options) = @_;

    if ($options{message} =~ /^\[ACK\]\s+\[(.*?)\]\s+(.*)/m) {
        my $data;
        eval {
            $data = JSON->new->utf8->decode($2);
        };
        if ($@) {
            return $options{message};
        }
        
        if (defined($data->{data}->{action}) && $data->{data}->{action} eq 'getlog') {
            return '[SETLOGS] [' . $1 . '] [] ' . $2;
        }
        return undef;
    } elsif ($options{message} =~ /^\[PONG\]/) {
        return $options{message};
    }
    return undef;
}

sub from_router {
    while (1) {        
        my $message = transmit_back(message => centreon::centreond::common::zmq_dealer_read_message(socket => $socket_to_internal));
        # Only send back SETLOGS and PONG
        if (defined($message)) {
            $logger->writeLogDebug("centreond-pull: hook: read message from internal: $message");
            $client->send_message(message => $message);
        }
        last unless (centreon::centreond::common::zmq_still_read(socket => $socket_to_internal));
    }
}

sub read_message {
    my (%options) = @_;

    # We skip. Dont need to send it in centreond-core
    if ($options{data} =~ /^\[ACK\]/) {
        return undef;
    }
    
    $logger->writeLogDebug("centreond-pull: hook: read message from external: $options{data}");
    centreon::centreond::common::zmq_send_message(socket => $socket_to_internal,
                                                  message => $options{data});
}


1;
