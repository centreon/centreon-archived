
package modules::centreondproxy::hooks;

use warnings;
use strict;
use centreon::script::centreondcore;
use modules::centreondproxy::class;

my $config_core;
my $config;
my $module_id = 'centreondproxy';
my $events = [
    'PROXYREADY', 'ADDPOLLER'
];

my $last_pollers = {}; # Last values from centreon database
my $pools = {};
my $pools_pid = {};
my $poller_pool = {};
my $rr_current = 0;
my $stop = 0;

sub register {
    my (%options) = @_;
    
    $config = $options{config};
    $config_core = $options{config_core};
    return ($events, $module_id);
}

sub init {
    my (%options) = @_;

    $last_pollers = get_pollers();
    for my $pool_id (1..$config->{pool}) {
        create_child(pool_id => $pool_id, logger => $options{logger});
    }
}

sub routing {
    my (%options) = @_;

    my $data;
    eval {
        $data = JSON->new->utf8->decode($options{data});
    };
    if ($@) {
        $options{logger}->writeLogError("Cannot decode json data: $@");
        centreon::centreond::common::add_history(dbh => $options{dbh},
                                                 ctime => time(), code => 20, token => $options{token},
                                                 data => { msg => 'centreondproxy: cannot decode json' },
                                                 json_encode => 1);
        return undef;
    }
    
    if ($options{action} eq 'PROXYREADY') {
        $pools->{$data->{pool_id}}->{ready} = 1;
        return undef;
    }
    
    if (!defined($options{target}) || !defined($last_pollers->{$options{target}})) {
        centreon::centreond::common::add_history(dbh => $options{dbh},
                                                 ctime => time(), code => 20, token => $options{token},
                                                 data => { msg => 'centreondproxy: need a valid poller id' },
                                                 json_encode => 1);
        return undef;
    }
    
    if (centreon::script::centreondcore::waiting_ready_pool(pool => $pools) == 0) {
        centreon::centreond::common::add_history(dbh => $options{dbh},
                                                 ctime => time(), code => 20, token => $options{token},
                                                 data => { msg => 'centreondproxy: still none ready' },
                                                 json_encode => 1);
        return undef;
    }
    
    my $identity = 'centreondproxy-';
    if (defined($poller_pool->{$options{target}})) {
        $identity .= $poller_pool->{$options{target}};
    } else {
        $identity .= rr_pool();
    }
    
    centreon::centreond::common::zmq_send_message(socket => $options{socket}, identity => $identity,
                                                  action => $options{action}, data => $options{data}, token => $options{token},
                                                  target => $options{target}
                                                  );
}

sub gently {
    my (%options) = @_;

    $stop = 1;
    foreach my $pool_id (keys %{$pools}) {
        $options{logger}->writeLogInfo("centreond-proxy: Send TERM signal for pool '" . $pool_id . "'");
        if ($pools->{$pool_id}->{running} == 1) {
            kill('TERM', $pools->{$pool_id}->{pid});
        }
    }
}

sub kill {
    my (%options) = @_;

    foreach (keys %{$pools}) {
        if ($pools->{$_}->{running} == 1) {
            $options{logger}->writeLogInfo("centreond-proxy: Send KILL signal for pool '" . $_ . "'");
            kill('KILL', $pools->{$_}->{pid});
        }
    }
}

sub kill_internal {
    my (%options) = @_;

}

sub check {
    my (%options) = @_;

    my $count = 0;
    foreach my $pid (keys %{$options{dead_childs}}) {
        # Not me
        next if (!defined($pools_pid->{$pid}));
        
        # If someone dead, we recreate
        my $pool_id = $pools_pid->{$pid};
        delete $pools->{$pools_pid->{$pid}};
        delete $pools_pid->{$pid};
        delete $options{dead_childs}->{$pid};
        if ($stop == 0) {
            create_child(pool_id => $pool_id, logger => $options{logger});
        }
    }
    
    foreach (keys %{$pools}) {
        $count++  if ($pools->{$_}->{running} == 1);
    }
    
    return $count;
}

# Specific functions
sub get_pollers {
    my (%options) = @_;
    # TODO 
    my $pollers = { 1 => 1, 2 => 1, 10 => 1 };
    
    return $pollers;
}

sub rr_pool {
    my (%options) = @_;
    
    while (1) {
        $rr_current = $rr_current % $config->{pool};
        if ($pools->{$rr_current + 1}->{ready} == 1) {
            return $rr_current + 1;
        }
    }
}

sub create_child {
    my (%options) = @_;
    
    $options{logger}->writeLogInfo("Create centreondproxy for pool id '" . $options{pool_id} . "'");
    my $child_pid = fork();
    if ($child_pid == 0) {
        my $module = modules::centreondproxy::class->new(logger => $options{logger},
                                                         config_core => $config_core,
                                                         config => $config,
                                                         pool_id => $options{pool_id}
                                                        );
        $module->run();
        exit(0);
    }
    $options{logger}->writeLogInfo("PID $child_pid centreondproxy for pool id '" . $options{pool_id} . "'");
    $pools->{$options{pool_id}} = { pid => $child_pid, ready => 0, running => 1 };
    $pools_pid->{$child_pid} = $options{pool_id};
}

1;
