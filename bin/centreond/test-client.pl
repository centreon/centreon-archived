
use strict;
use warnings;

use ZMQ::LibZMQ3;
use ZMQ::Constants qw(:all);
use UUID;
use Data::Dumper;
use Sys::Hostname;
use centreon::centreond::clientzmq;
use centreon::centreond::common;

sub read_response {
    my (%options) = @_;
    
    print "==== PLOP = " . $options{data} . "===\n";
}

my ($symkey, $status, $hostname, $ciphertext);

my $uuid;
#$uuid = 'toto';
UUID::generate($uuid);

my $client = centreon::centreond::clientzmq->new(identity => 'toto', 
                                                 cipher => 'Crypt::OpenSSL::AES', 
                                                 vector => '0123456789012345',
                                                 pubkey => 'keys/pubkey.crt',
                                                 target_type => 'tcp',
                                                 target_path => '127.0.0.1:5555'
                                                 );
$client->init(callback => \&read_response);
my $poll_client = $client->get_poll();
my $poll = [

];
push @$poll, $poll_client;

my $client2 = centreon::centreond::clientzmq->new(identity => 'tata', 
                                                 cipher => 'Crypt::OpenSSL::AES', 
                                                 vector => '0123456789012345',
                                                 pubkey => 'keys/pubkey.crt',
                                                 target_type => 'tcp',
                                                 target_path => '127.0.0.1:5555'
                                                 );
$client2->init(callback => \&read_response);
$poll_client = $client2->get_poll();
push @$poll, $poll_client;

$client->send_message(action => 'ACLADDHOST', data => {'organization_id' => 10}, 
                      json_encode => 1);
$client->send_message(action => 'PUTLOG', data => '[120] [' . time() . '] ' . ' [plopplop] ' . centreon::centreond::common::json_encode(data => { 'nawak' => 'nawak2' }));
$client->send_message(action => 'ACLADDHOST', data => {'organization_id' => 10}, target => 10,
                      json_encode => 1);
$client2->send_message(action => 'ACLADDHOST', data => {'organization_id' => 14}, 
                       json_encode => 1);

# We send a request to a poller
$client2->send_message(action => 'ACLADDHOST', data => {'organization_id' => 14}, target => 120, 
                       json_encode => 1);
           
while (1) {
    zmq_poll($poll, 5000);
}
    
exit(0);

#zmq_close($requester);

