
package modules::centreondcron::hooks;

use warnings;
use strict;
use centreon::script::centreondcore;
use modules::centreondcron::class;

my $config_core;
my $config;
my $module_id = 'centreondcron';
my $events = [
    'CRONREADY', 'RELOADCRON',
];
my $cron = {};
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
                                                 code => 10, token => $options{token},
                                                 data => { message => 'centreondcron: cannot decode json' },
                                                 json_encode => 1);
        return undef;
    }
    
    if ($options{action} eq 'CRONREADY') {
        $cron->{ready} = 1;
        return undef;
    }
    
    if (centreon::script::centreondcore::waiting_ready(ready => \$cron->{ready}) == 0) {
        centreon::centreond::common::add_history(dbh => $options{dbh},
                                                 code => 10, token => $options{token},
                                                 data => { message => 'centreondcron: still no ready' },
                                                 json_encode => 1);
        return undef;
    }
    
    centreon::centreond::common::zmq_send_message(socket => $options{socket}, identity => 'centreondcron',
                                                  action => $options{action}, data => $options{data}, token => $options{token},
                                                  );
}

sub gently {
    my (%options) = @_;

    $stop = 1;
    $options{logger}->writeLogInfo("centreond-cron: Send TERM signal");
    if ($cron->{running} == 1) {
        kill('TERM', $cron->{pid});
    }
}

sub kill {
    my (%options) = @_;

    if ($cron->{running} == 1) {
        $options{logger}->writeLogInfo("centreond-cron: Send KILL signal for pool");
        kill('KILL', $cron->{pid});
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
        next if ($cron->{pid} != $pid);
        
        $cron = {};
        delete $options{dead_childs}->{$pid};
        if ($stop == 0) {
            create_child(logger => $options{logger});
        }
    }
    
    $count++  if (defined($cron->{running}) && $cron->{running} == 1);
    
    return $count;
}

# Specific functions
sub create_child {
    my (%options) = @_;
    
    $options{logger}->writeLogInfo("Create centreondaction process");
    my $child_pid = fork();
    if ($child_pid == 0) {
        my $module = modules::centreondcron::class->new(logger => $options{logger},
                                                        config_core => $config_core,
                                                        config => $config,
                                                        );
        $module->run();
        exit(0);
    }
    $options{logger}->writeLogInfo("PID $child_pid centreondaction");
    $cron = { pid => $child_pid, ready => 0, running => 1 };
}

1;
