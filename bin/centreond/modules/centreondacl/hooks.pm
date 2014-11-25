
package modules::centreondacl::hooks;

use warnings;
use strict;
use centreon::script::centreondcore;
use modules::centreondacl::class;

my $config_core;
my $config;
my $module_id = 'centreondacl';
my $events = [
    'ACLREADY', 'ACLADDHOST', 'ACLADDSERVICE', 'PURGEORGANIZATION',
];

my $last_organizations = {}; # Last values from centreon database
my $organizations = {};
my $organizations_pid = {};
my $stop = 0;
my $timer_check = time();
my $config_check_organizations_time;
my $on_demand = 0;

sub register {
    my (%options) = @_;
    
    $config = $options{config};
    $config_core = $options{config_core};
    $config_check_organizations_time = defined($config->{check_organizations_time}) ? $config->{check_organizations_time} : 3600;
    $on_demand = defined($config->{on_demand}) && $config->{on_demand} == 1 ? 1 : 0;
    return ($events, $module_id);
}

sub init {
    my (%options) = @_;

    $last_organizations = get_organizations();
    # We quit
    return if ($on_demand == 1);
    foreach my $organization_id (keys %{$last_organizations}) {
        create_child(organization_id => $organization_id, logger => $options{logger});
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
                                                 code => 10, token => $options{token},
                                                 data => { msg => 'centreondacl: cannot decode json' },
                                                 json_encode => 1);
        return undef;
    }
    
    if ($options{action} eq 'ACLREADY') {
        $organizations->{$data->{organization_id}}->{ready} = 1;
        return undef;
    }
    
    if (!defined($data->{organization_id}) || !defined($last_organizations->{$data->{organization_id}})) {
        centreon::centreond::common::add_history(dbh => $options{dbh},
                                                 code => 10, token => $options{token},
                                                 data => { msg => 'centreondacl: need a valid organization id' },
                                                 json_encode => 1);
        return undef;
    }
    
    if ($on_demand == 1) {
        if (!defined($organizations->{$data->{organization_id}})) {
            create_child(organization_id => $data->{organization_id}, logger => $options{logger}, on_demand => 1);
        }
    }
    
    if (centreon::script::centreondcore::waiting_ready(ready => \$organizations->{$data->{organization_id}}->{ready}) == 0) {
        centreon::centreond::common::add_history(dbh => $options{dbh},
                                                 code => 10, token => $options{token},
                                                 data => { msg => 'centreondacl: still no ready' },
                                                 json_encode => 1);
        return undef;
    }
    
    centreon::centreond::common::zmq_send_message(socket => $options{socket}, identity => 'centreondacl-' . $data->{organization_id},
                                                  action => $options{action}, data => $options{data}, token => $options{token},
                                                  );
}

sub gently {
    my (%options) = @_;

    $stop = 1;
    # They stop from themself in 'on_demand' mode
    return if ($on_demand == 1);
    foreach my $organization_id (keys %{$organizations}) {
        $options{logger}->writeLogInfo("centreond-acl: Send TERM signal for organization '" . $organization_id . "'");
        if ($organizations->{$organization_id}->{running} == 1) {
            kill('TERM', $organizations->{$organization_id}->{pid});
        }
    }
}

sub kill_internal {
    my (%options) = @_;

    foreach (keys %{$organizations}) {
        if ($organizations->{$_}->{running} == 1) {
            $options{logger}->writeLogInfo("centreond-acl: Send KILL signal for organization '" . $_ . "'");
            kill('KILL', $organizations->{$_}->{pid});
        }
    }
}

sub kill {
    my (%options) = @_;

    
}

sub check {
    my (%options) = @_;

    if ($timer_check - time() > $config_check_organizations_time) {
        sync_organization_childs(logger => $options{logger});
        $timer_check = time();
    }
    
    my $count = 0;
    foreach my $pid (keys %{$options{dead_childs}}) {
        # Not me
        next if (!defined($organizations_pid->{$pid}));
        
        # If someone dead, we recreate
        delete $organizations->{$organizations_pid->{$pid}};
        delete $organizations_pid->{$pid};
        delete $options{dead_childs}->{$pid};
        if ($stop == 0 && $on_demand == 0) {
            # Need to check if we need to recreate (can be a organization destruction)!!!
            sync_organization_childs(logger => $options{logger});
        }
    }
    
    foreach (keys %{$organizations}) {
        $count++  if ($organizations->{$_}->{running} == 1);
    }
    
    return $count;
}

# Specific functions
sub get_organizations {
    my (%options) = @_;

    my $orgas = { 10 => 1, 25 => 1, 50 => 1, 100 => 1, 13 => 1 };    
    return $orgas;
}

sub sync_organization_childs {
    my (%options) = @_;
    
    $last_organizations = get_organizations();
    foreach my $organization_id (keys %{$last_organizations}) {
        if (!defined($organizations->{$organization_id}) && $on_demand == 0) {
            create_child(organization_id => $organization_id, logger => $options{logger});
        }
    }
    
    # TODO. Check Orga ID in my tables with a distinct to get what i need to destroy
}

sub create_child {
    my (%options) = @_;
    
    $options{logger}->writeLogInfo("Create centreondacl for organization id '" . $options{organization_id} . "'");
    my $child_pid = fork();
    if ($child_pid == 0) {
        my $module = modules::centreondacl::class->new(logger => $options{logger},
                                                       config_core => $config_core,
                                                       config => $config,
                                                       organization_id => $options{organization_id}
                                                       );
        $module->run(on_demand => $options{on_demand});
        exit(0);
    }
    $options{logger}->writeLogInfo("PID $child_pid for centreondacl for organization id '" . $options{organization_id} . "'");
    $organizations->{$options{organization_id}} = { pid => $child_pid, ready => 0, running => 1 };
    $organizations_pid->{$child_pid} = $options{organization_id};
}

1;
