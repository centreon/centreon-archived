
package modules::centreondacl::hooks;

use warnings;
use strict;
use centreon::script::centreondcore;
use modules::centreondacl::class;

my ($config_core, $config);
my $config_db_centreon;
my $module_id = 'centreondacl';
my $events = [
    'ACLREADY', 
    'ACLPURGEORGANIZATION', 'ACLRESYNC',
    'ACLADDHOST', 'ACLDELHOST', 'ACLADDSERVICE', 'ACLDELSERVICE',
    'ACLUPDATETAG', 'ACLDELTAG',
    'ACLUPDATEDOMAIN', 'ACLDELDOMAIN',
    'ACLUPDATEENVIRONMENT', 'ACLDELENVIRONMENT',
    'ACLUPDATEPOLLER', 'ACLDELPOLLER',
];

my $last_organizations = {}; # Last values from centreon database
my $organizations = {};
my $organizations_pid = {};
my $stop = 0;
my $timer_check = time();
my $config_check_organizations_time;
my $on_demand = 0;
my ($resync_auto_disable, $resync_time, $resync_random_windows) = (0, 28800, 7200);

sub register {
    my (%options) = @_;
    
    $config = $options{config};
    $config_core = $options{config_core};
    $config_db_centreon = $options{config_db_centreon};
    $config_check_organizations_time = defined($config->{check_organizations_time}) ? $config->{check_organizations_time} : 3600;
    $on_demand = defined($config->{on_demand}) && $config->{on_demand} == 1 ? 1 : 0;
    $resync_auto_disable = defined($config->{resync_auto_disable}) && $config->{resync_auto_disable} == 1 ? 1 : 0;
    $resync_time = defined($config->{resync_time}) && $config->{resync_time} > 0 ? $config->{resync_time} : 28800;
    $resync_random_windows = defined($config->{resync_random_windows}) && $config->{resync_random_windows} > 0 ? $config->{resync_random_windows} : 7200;
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
                                                 code => 100, token => $options{token},
                                                 data => { message => 'centreondacl: cannot decode json' },
                                                 json_encode => 1);
        return undef;
    }
    
    if ($options{action} eq 'ACLREADY') {
        $organizations->{$data->{organization_id}}->{ready} = 1;
        return undef;
    }
    
    if (!defined($data->{organization_id}) || !defined($last_organizations->{$data->{organization_id}})) {
        centreon::centreond::common::add_history(dbh => $options{dbh},
                                                 code => 100, token => $options{token},
                                                 data => { message => 'centreondacl: need a valid organization id' },
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
                                                 code => 100, token => $options{token},
                                                 data => { message => 'centreondacl: still no ready' },
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
        if ($organizations->{$_}->{running} == 1) {
            $count++;
            # Test resync
            if ($resync_auto_disable == 0 && defined($last_organizations->{$_}) && time() > $last_organizations->{$_}) {
                routing(dbh => $options{dbh}, socket => $options{internal_socket}, logger => $options{logger}, 
                        action => 'ACLRESYNC', data => '{ "organization_id": ' . $_ . ' } ',
                        token => 'internal_action_aclresync_' . $_ . '');
                $last_organizations->{$_} = time() + $resync_time + int(rand($resync_random_windows));
            }
        }
    }
    
    return $count;
}

# Specific functions
sub get_organizations {
    my (%options) = @_;

    my $db = centreon::common::db->new(dsn => $config_db_centreon->{dsn},
                                       user => $config_db_centreon->{username},
                                       password => $config_db_centreon->{password},
                                       force => 1,
                                       logger => $options{logger});
    my ($status, $sth) = $db->query("SELECT organization_id FROM cfg_organizations WHERE active = '1'");
    my $org = {};
    while ((my $row = $sth->fetchrow_arrayref())) {
        $org->{$$row[0]} = time() + $resync_time + int(rand($resync_random_windows));
    }
    return $org;
}

sub sync_organization_childs {
    my (%options) = @_;
    
    $last_organizations = get_organizations(logger => $options{logger});
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
                                                       config_db_centreon => $config_db_centreon,
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
