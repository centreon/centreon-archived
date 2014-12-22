
use strict;
use warnings;

use ZMQ::LibZMQ3;
use ZMQ::Constants qw(:all);
use UUID;
use Data::Dumper;
use Sys::Hostname;
use centreon::centreond::clientzmq;
use centreon::centreond::common;

my ($client, $client2);
my $identities_token = {};
my $stopped = {};
my $results = {};

sub get_command_result {
    my ($current_retries, $retries) = (0, 4);
    $stopped->{$client2->{identity}} = '^([0-9]+0|32)$'; 
    $client2->send_message(action => 'COMMAND', data => { command => 'ls /' }, target => 120, 
                           json_encode => 1);
    while (1) {
        my $poll = [];
     
        $client2->ping(poll => $poll);
        my $rev = zmq_poll($poll, 15000);
        
        if (defined($results->{$client2->{identity}})) {
            print "The result: " . Data::Dumper::Dumper($results->{$client2->{identity}});
            last;
        }
        
        if (!defined($rev) || $rev == 0) {
            $current_retries++;
            last if ($current_retries >= $retries);
            
            if (defined($identities_token->{$client2->{identity}})) {
                # We ask a sync
                print "==== send logs ===\n";
                $client2->send_message(action => 'GETLOG', target => 120, token => $identities_token->{$client2->{identity}},
                                       json_encode => 1);
                $client2->send_message(action => 'GETLOG', token => $identities_token->{$client2->{identity}}, data => { token => $identities_token->{$client2->{identity}} }, 
                                       json_encode => 1);
            }
        }
        
    }
}

sub read_response_result {
    my (%options) = @_;
    
    $options{data} =~ /^\[ACK\]\s+\[(.*?)\]\s+(.*)$/m;
    $identities_token->{$options{identity}} = $1;
        
    my $data;
    eval {
        $data = JSON->new->utf8->decode($2);
    };
    if ($@) {
        return undef;
    }
        
    if (defined($data->{data}->{action}) && $data->{data}->{action} eq 'getlog') {
        if (defined($data->{data}->{result})) {
            foreach my $key (keys %{$data->{data}->{result}}) {
                if ($data->{data}->{result}->{$key}->{code} =~ /$stopped->{$options{identity}}/) {
                    $results->{$options{identity}} = $data->{data}->{result};
                    last;
                }
            }
        }
    }
}

sub read_response {
    my (%options) = @_;
    
    print "==== PLOP = " . $options{data} . "===\n";
}

my ($symkey, $status, $hostname, $ciphertext);

my $uuid;
#$uuid = 'toto';
UUID::generate($uuid);

$client = centreon::centreond::clientzmq->new(identity => 'toto', 
                                                 cipher => 'Crypt::OpenSSL::AES', 
                                                 vector => '0123456789012345',
                                                 pubkey => 'keys/central/pubkey.crt',
                                                 target_type => 'tcp',
                                                 target_path => '127.0.0.1:5555',
                                                 ping => 60,
                                                 );
$client->init(callback => \&read_response);
$client2 = centreon::centreond::clientzmq->new(identity => 'tata', 
                                                 cipher => 'Crypt::OpenSSL::AES', 
                                                 vector => '0123456789012345',
                                                 pubkey => 'keys/central/pubkey.crt',
                                                 target_type => 'tcp',
                                                 target_path => '127.0.0.1:5555'
                                                 );
$client2->init(callback => \&read_response_result);

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
#$client2->send_message(action => 'ENGINECOMMAND', data => { command => '[1417705150] ENABLE_HOST_CHECK;host1', engine_pipe => '/var/lib/centreon-engine/rw/centengine.cmd' }, target => 120, 
#                       json_encode => 1);

#$client2->send_message(action => 'COMMAND', data => { cmd => 'ls' }, target => 140, 
#                       json_encode => 1);
#$client2->send_message(action => 'CONSTATUS');

# It will transform
#$client2->send_message(action => 'GETLOG', data => { cmd => 'ls' }, target => 120, 
#                       json_encode => 1);
#$client2->send_message(action => 'GETLOG', data => { cmd => 'ls' }, target => 140, 
#                       json_encode => 1);

get_command_result();
                    
#while (1) {
#    my $poll = [];

#    $client->ping(poll => $poll);
#    $client2->ping(poll => $poll);
#    zmq_poll($poll, 5000);
#}

$client->close();
$client2->close();  
exit(0);

#zmq_close($requester);

