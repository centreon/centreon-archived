
package modules::centreondpull::hooks;

use warnings;
use strict;
use centreon::centreond::clientzmq;

my $config_core;
my $config;
my $module_id = 'centreondpull';
my $events = [
];
my $client;
my $socket_to_internal;

sub register {
    my (%options) = @_;
    
    $config = $options{config};
    $config_core = $options{config_core};
    return ($events, $module_id);
}

sub init {
    my (%options) = @_;

    # Connect internal
    $socket_to_internal = centreon::centreond::common::connect_com(zmq_type => 'ZMQ_DEALER', name => 'centreondpull',
                                                                   logger => $options{logger},
                                                                   type => $config_core->{internal_com_type},
                                                                   path => $config_core->{internal_com_path});
    $client = centreon::centreond::clientzmq->new(identity => $config_core->{id}, 
                                                  cipher => $config->{cipher}, 
                                                  vector => $config->{vector},
                                                  pubkey => $config->{pubkey},
                                                  target_type => $config->{target_type},
                                                  target_path => $config->{target_path},
                                                  logger => $options{logger}
                                                  );
    $client->init(callback => \&read_message);
    
    $client->send_message(action => 'REGISTERNODE', data => { id => $config_core->{id} }, 
                          json_encode => 1);
    my $poll_client = $client->get_poll();
    push @{$options{poll}}, $poll_client;
    centreon::centreond::common::add_zmq_pollin(socket => $socket_to_internal,
                                                callback => \&router_internal_event);
}

sub routing {
    my (%options) = @_;

}

sub gently {
    my (%options) = @_;

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

    return 0;
}

####### specific

sub from_router {    
    while (1) {
        my $message = centreon::centreond::common::zmq_dealer_read_message(socket => $socket_to_internal);
        # Should check if it's ack from getlog request to send it back. With an action: SETLOGS
        $client->send_message(message => $message);
        last unless (centreon::centreond::common::zmq_still_read(socket => $socket_to_internal));
    }
}

sub read_message {
    my (%options) = @_;

    # We skip. Dont need to send it in centreond-core
    if ($options{data} =~ /^\[ACK\]/) {
        return undef;
    }
    
    print "===== READ MESSAGE " . Data::Dumper::Dumper($options{data}) . " ====\n";
    centreon::centreond::common::zmq_send_message(socket => $socket_to_internal,
                                                  message => $options{data});
}


1;
