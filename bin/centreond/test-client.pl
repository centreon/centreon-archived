
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
                                                 pubkey => 'keys/central/pubkey.crt',
                                                 target_type => 'tcp',
                                                 target_path => '127.0.0.1:5555',
                                                 ping => 60,
                                                 );
$client->init(callback => \&read_response);
my $client2 = centreon::centreond::clientzmq->new(identity => 'tata', 
                                                 cipher => 'Crypt::OpenSSL::AES', 
                                                 vector => '0123456789012345',
                                                 pubkey => 'keys/central/pubkey.crt',
                                                 target_type => 'tcp',
                                                 target_path => '127.0.0.1:5555'
                                                 );
$client2->init(callback => \&read_response);

#$client->send_message(action => 'ACLADDHOST', data => { organization_id => 10 }, 
#                      json_encode => 1);
#$client->send_message(action => 'PUTLOG', data => { code => 120, etime => time(), token => 'plopplop', data => { 'nawak' => 'nawak2' } },
#                      json_encode => 1);
#$client->send_message(action => 'ACLADDHOST', data => { organization_id => 10 }, target => 10,
#                      json_encode => 1);
#$client2->send_message(action => 'ACLADDHOST', data => { organization_id => 14 }, 
#                       json_encode => 1);
#$client2->send_message(action => 'RELOADCRON', data => { }, 
#                       json_encode => 1);

# We send a request to a poller
#$client2->send_message(action => 'COMMAND', data => { cmd => 'ls' }, target => 120, 
#                       json_encode => 1);
#$client2->send_message(action => 'COMMAND', data => { cmd => 'ls' }, target => 140, 
#                       json_encode => 1);

# It will transform
#$client2->send_message(action => 'GETLOG', data => { cmd => 'ls' }, target => 120, 
#                       json_encode => 1);
$client2->send_message(action => 'GETLOG', data => { cmd => 'ls' }, target => 140, 
                       json_encode => 1);
                     
while (1) {
    my $poll = [];

    $client->ping(poll => $poll);
    $client2->ping(poll => $poll);
    zmq_poll($poll, 5000);
}
    
exit(0);

#zmq_close($requester);

