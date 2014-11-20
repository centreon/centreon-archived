
package modules::centreondaction::hooks;

use warnings;
use strict;
use centreon::script::centreondcore;
use modules::centreondaction::class;

my $config_core;
my $config;
my $module_id = 'centreondaction';
my $events = [
    'ACTIONREADY', 'COMMAND',
];
my $action = {};
my $stop = 0;

sub register {
    my (%options) = @_;
    
    $config = $options{config};
    $config_core = $options{config_core};
    return ($events, $module_id);
}

sub init {
    my (%options) = @_;

    create_child(logger => $options{logger});
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
                                                 ctime => time(), code => 30, token => $options{token},
                                                 data => { msg => 'centreondaction: cannot decode json' },
                                                 json_encode => 1);
        return undef;
    }
    
    if ($options{action} eq 'ACTIONREADY') {
        $action->{ready} = 1;
        return undef;
    }
    
    if (centreon::script::centreondcore::waiting_ready(ready => \$action->{ready}) == 0) {
        centreon::centreond::common::add_history(dbh => $options{dbh},
                                                 ctime => time(), code => 30, token => $options{token},
                                                 data => { msg => 'centreondaction: still no ready' },
                                                 json_encode => 1);
        return undef;
    }
    
    centreon::centreond::common::zmq_send_message(socket => $options{socket}, identity => 'centreondaction',
                                                  action => $options{action}, data => $options{data}, token => $options{token},
                                                  );
}

sub gently {
    my (%options) = @_;

    $stop = 1;
    $options{logger}->writeLogInfo("centreond-action: Send TERM signal");
    if ($action->{running} == 1) {
        kill('TERM', $action->{pid});
    }
}

sub kill {
    my (%options) = @_;

    if ($action->{running} == 1) {
        $options{logger}->writeLogInfo("centreond-action: Send KILL signal for pool");
        kill('KILL', $action->{pid});
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
        next if ($action->{pid} != $pid);
        
        $action = {};
        delete $options{dead_childs}->{$pid};
        if ($stop == 0) {
            create_child(logger => $options{logger});
        }
    }
    
    $count++  if (defined($action->{running}) && $action->{running} == 1);
    
    return $count;
}

# Specific functions
sub create_child {
    my (%options) = @_;
    
    $options{logger}->writeLogInfo("Create centreondaction process");
    my $child_pid = fork();
    if ($child_pid == 0) {
        my $module = modules::centreondaction::class->new(logger => $options{logger},
                                                          config_core => $config_core,
                                                          config => $config,
                                                          );
        $module->run();
        exit(0);
    }
    $options{logger}->writeLogInfo("PID $child_pid centreondaction");
    $action = { pid => $child_pid, ready => 0, running => 1 };
}

1;
