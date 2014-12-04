
package centreon::script::centreondcore;

use strict;
use warnings;
use POSIX ":sys_wait_h";
use Sys::Hostname;
use ZMQ::LibZMQ3;
use ZMQ::Constants qw(:all);
use centreon::centreond::common;
use centreon::script;

my ($centreond, $centreond_config);

use base qw(centreon::script);

my $VERSION = "1.0";
my %handlers = (TERM => {}, HUP => {}, CHLD => {});

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreond",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noconfig => 1
    );

    bless $self, $class;
    $self->add_options(
        "config-extra:s" => \$self->{opt_extra},
    );

    $self->{opt_extra} = '';
    $self->{return_child} = {};
    $self->{stop} = 0;
    $self->{internal_register} = {};
    $self->{modules_register} = {};
    $self->{modules_events} = {};
    $self->{modules_id} = {};
    $self->{sessions_timer} = time();
    $self->{kill_timer} = undef;
    
    return $self;
}

sub init {
    my $self = shift;
    $self->SUPER::init();

    # redefine to avoid out when we try modules
    $SIG{__DIE__} = undef;

    ## load config ini
    if (! -f $self->{opt_extra}) {
        $self->{logger}->writeLogError("Can't find extra config file '$self->{opt_extra}'");
        exit(1);
    }
    $centreond_config = centreon::centreond::common::read_config(config_file => $self->{opt_extra},
                                                                 logger => $self->{logger});
    if (defined($centreond_config->{centreondcore}{external_com_type}) && $centreond_config->{centreondcore}{external_com_type} ne '') {
        centreon::centreond::common::loadprivkey(logger => $self->{logger}, privkey => $centreond_config->{centreondcore}{privkey});
    }
    
    # Database connections:
    #    We add in centreond database
    $centreond->{db_centreond} = centreon::common::db->new(type => $centreond_config->{centreondcore}{centreond_db_type},
                                                           db => $centreond_config->{centreondcore}{centreond_db_name},
                                                           host => $centreond_config->{centreondcore}{centreond_db_host},
                                                           port => $centreond_config->{centreondcore}{centreond_db_port},
                                                           user => $centreond_config->{centreondcore}{centreond_db_user},
                                                           password => $centreond_config->{centreondcore}{centreond_db_password},
                                                           force => 2,
                                                           logger => $centreond->{logger});
    $centreond->{db_centreond}->set_inactive_destroy();
    if ($centreond->{db_centreond}->connect() == -1) {
        $centreond->{logger}->writeLogInfo("Cannot connect. We quit!!");
        exit(1);
    }
    
    $self->{hostname} = $centreond_config->{centreondcore}{hostname};
    if (!defined($self->{hostname}) || $self->{hostname} eq '') {
        $self->{hostname} = hostname();
    }
    
    $self->{id} = $centreond_config->{centreondcore}{id};
    if (!defined($self->{hostname}) || $self->{hostname} eq '') {
        #$self->{id} = get_poller_id(dbh => $dbh, name => $self->{hostname});
    }
    
    $self->load_modules();
    
    $self->set_signal_handlers;
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{TERM}->{$self} = sub { $self->handle_TERM() };
    $SIG{HUP} = \&class_handle_HUP;
    $handlers{HUP}->{$self} = sub { $self->handle_HUP() };
    $SIG{CHLD} = \&class_handle_CHLD;
    $handlers{CHLD}->{$self} = sub { $self->handle_CHLD() };
}

sub class_handle_TERM {
    foreach (keys %{$handlers{TERM}}) {
        &{$handlers{TERM}->{$_}}();
    }
}

sub class_handle_HUP {
    foreach (keys %{$handlers{HUP}}) {
        &{$handlers{HUP}->{$_}}();
    }
}

sub class_handle_CHLD {
    foreach (keys %{$handlers{CHLD}}) {
        &{$handlers{CHLD}->{$_}}();
    }
}

sub handle_TERM {
    my $self = shift;
    $self->{logger}->writeLogInfo("$$ Receiving order to stop...");
    $self->{stop} = 1;
    
    foreach my $name (keys %{$self->{modules_register}}) {
        $self->{modules_register}->{$name}->{gently}->(logger => $self->{logger});
    }
    $self->{kill_timer} = time();
}

sub handle_HUP {
    my $self = shift;
    $self->{logger}->writeLogInfo("$$ Receiving order to reload...");
    # TODO
}

sub handle_CHLD {
    my $self = shift;
    my $child_pid;

    while (($child_pid = waitpid(-1, &WNOHANG)) > 0) {
        $self->{return_child}->{$child_pid} = time();
    }
    
    $SIG{CHLD} = \&class_handle_CHLD;
}

sub load_modules {
    my $self = shift;

    foreach my $section (keys %{$centreond_config}) {
        next if (!defined($centreond_config->{$section}{module}));
        
        my $name = $centreond_config->{$section}{module};
        (my $file = "$name.pm") =~ s{::}{/}g;
        require $file;
        $self->{logger}->writeLogInfo("Module '$section' is loading");
        $self->{modules_register}->{$name} = {};
        
        foreach my $method_name (('register', 'routing', 'kill', 'kill_internal', 'gently', 'check', 'init')) {
            unless ($self->{modules_register}->{$name}->{$method_name} = $name->can($method_name)) {
                $self->{logger}->writeLogError("No function '$method_name' for module '$section'");
                exit(1);
            }
        }

        my ($events, $id) = $self->{modules_register}->{$name}->{register}->(config => $centreond_config->{$section},
                                                                             config_core => $centreond_config->{centreondcore});
        $self->{modules_id}->{$id} = $name;
        foreach my $event (@{$events}) {
            $self->{modules_events}->{$event} = [] if (!defined($self->{modules_events}->{$event}));
            push @{$self->{modules_events}->{$event}}, $name;
        }
                
        $self->{logger}->writeLogInfo("Module '$section' is loaded");
    }    
    
    # Load internal functions
    foreach my $method_name (('putlog', 'getlog', 'kill', 'ping', 'constatus')) {
        unless ($self->{internal_register}->{$method_name} = centreon::centreond::common->can($method_name)) {
            $self->{logger}->writeLogError("No function '$method_name'");
            exit(1);
        }
    }
}

sub message_run {
    my ($self, %options) = @_;

    if ($options{message} !~ /^\[(.+?)\]\s+\[(.*?)\]\s+\[(.*?)\]\s+(.*)$/) {
        return (undef, 1, { mesage => 'request not well formatted' });
    }
    my ($action, $token, $target, $data) = ($1, $2, $3, $4);
    if ($action !~ /^(PUTLOG|GETLOG|KILL|PING|CONSTATUS)$/ && !defined($self->{modules_events}->{$action})) {
        centreon::centreond::common::add_history(dbh => $self->{db_centreond},
                                                 code => 1, token => $token,
                                                 data => { msg => "action '$action' is not known" },
                                                 json_encode => 1);
        return (undef, 1, { message => "action '$action' is not known" });
    }
    if (!defined($token) || $token eq '') {
        $token = centreon::centreond::common::generate_token();
    }

    if ($self->{stop} == 1) {
        centreon::centreond::common::add_history(dbh => $self->{db_centreond},
                                                 code => 1, token => $token,
                                                 data => { msg => 'centreond is stopping/restarting. Not proceed request.' },
                                                 json_encode => 1);
        return ($token, 1, { message => "centreond is stopping/restarting. Not proceed request." });
    }
    
    # Check Routing
    if (defined($target) && $target ne '') {
        # Check if not myself ;)
        if ($target ne $self->{id}) {
            $self->{modules_register}->{ $self->{modules_id}->{$centreond_config->{centreondcore}{proxy_name}}  }->{routing}->(socket => $self->{internal_socket}, dbh => $self->{db_centreond}, logger => $self->{logger}, 
                                                                                            action => $1, token => $token, target => $target, data => $data,
                                                                                            hostname => $self->{hostname});
            return ($token, 0);
        }
    }
    
    if ($action =~ /^(PUTLOG|GETLOG|KILL|PING|CONSTATUS)$/) {
        my ($code, $response, $response_type) = $self->{internal_register}->{lc($action)}->(centreond => $self,
                                                                            centreond_config => $centreond_config,
                                                                            id => $self->{id},
                                                                            data => $data,
                                                                            token => $token,
                                                                            logger => $self->{logger});
        return ($token, $code, $response, $response_type);
    } else {
        foreach (@{$self->{modules_events}->{$action}}) {
            $self->{modules_register}->{$_}->{routing}->(socket => $self->{internal_socket}, 
                                                         dbh => $self->{db_centreond}, logger => $self->{logger},
                                                         action => $1, token => $token, target => $target, data => $data,
                                                         hostname => $self->{hostname});
        }
    }
    return ($token, 0);
}

sub router_internal_event {
    while (1) {
        my ($identity, $message) = centreon::centreond::common::zmq_read_message(socket => $centreond->{internal_socket});
        my ($token, $code, $response, $response_type) = $centreond->message_run(message => $message);
        centreon::centreond::common::zmq_core_response(socket => $centreond->{internal_socket},
                                                       identity => $identity, response_type => $response_type,
                                                       data => $response, code => $code,
                                                       token => $token);
        last unless (centreon::centreond::common::zmq_still_read(socket => $centreond->{internal_socket}));
    }
}

sub handshake {
    my ($self, %options) = @_;

    my ($identity, $message) = centreon::centreond::common::zmq_read_message(socket => $self->{external_socket});
    my ($status, $key) = centreon::centreond::common::is_handshake_done(dbh => $self->{db_centreond}, identity => $identity);

    if ($status == 1) {
        ($status, my $response) = centreon::centreond::common::uncrypt_message(cipher => $centreond_config->{centreondcore}{cipher}, 
                                                        message => $message,
                                                        symkey => $key,
                                                        vector => $centreond_config->{centreondcore}{vector}
                                                        );
        if ($status == 0 && $response =~ /^\[.*\]/) {
            centreon::centreond::common::update_identity(dbh => $self->{db_centreond}, identity => $identity);
            return ($identity, $key, $response);
        }
        
        # Maybe he want to redo a handshake
        $status = 0;    
    }
    
    if ($status == -1) {
        centreon::centreond::common::zmq_core_response(socket => $self->{external_socket}, identity => $identity,
                                                       code => 1, data => { message => 'Database issue' });
        return undef;
    } elsif ($status == 0) {
        # We try to uncrypt
        if (centreon::centreond::common::is_client_can_connect(message => $message,
                                                               logger => $self->{logger}) == -1) {
            centreon::centreond::common::zmq_core_response(socket => $self->{external_socket}, identity => $identity,
                                                           code => 1, data => { message => 'handshake issue' } );
        }
        my ($status, $symkey) = centreon::centreond::common::generate_symkey(logger => $self->{logger},
                                                                             cipher => $centreond_config->{centreondcore}{cipher},
                                                                             keysize => $centreond_config->{centreondcore}{keysize});
        if ($status == -1) {
            centreon::centreond::common::zmq_core_response(socket => $self->{external_socket}, identity => $identity,
                                                           code => 1, data => { message => 'handshake issue' });
        }
        if (centreon::centreond::common::add_identity(dbh => $self->{db_centreond}, identity => $identity, symkey => $symkey) == -1) {
            centreon::centreond::common::zmq_core_response(socket => $self->{external_socket}, identity => $identity,
                                                           code => 1, data => { message => 'handshake issue' });
        }
        
        if (centreon::centreond::common::zmq_core_key_response(logger => $self->{logger}, socket => $self->{external_socket}, identity => $identity,
                                                               hostname => $self->{hostname}, symkey => $symkey) == -1) {
            centreon::centreond::common::zmq_core_response(socket => $self->{external_socket}, identity => $identity,
                                                           code => 1, data => { message => 'handshake issue' });
        }
        return undef;
    }    
}

sub router_external_event {
    while (1) {
        my ($identity, $key, $message) = $centreond->handshake();
        if (defined($message)) {
            my ($token, $code, $response, $response_type) = $centreond->message_run(message => $message);
            centreon::centreond::common::zmq_core_response(socket => $centreond->{external_socket},
                                                           identity => $identity, response_type => $response_type,
                                                           cipher => $centreond_config->{centreondcore}{cipher},
                                                           vector => $centreond_config->{centreondcore}{vector},
                                                           symkey => $key,
                                                           token => $token, code => $code,
                                                           data => $response);
        }
        last unless (centreon::centreond::common::zmq_still_read(socket => $centreond->{external_socket}));
    }
}

sub waiting_ready_pool {
    my (%options) = @_;
    
    my $time = time();
    # We wait 10 seconds
    while (time() - $time < 10) {
        foreach my $pool_id (keys %{$options{pool}})  {
            return 1 if ($options{pool}->{$pool_id}->{ready} == 1);
        }
        zmq_poll($centreond->{poll}, 5000);
    }
    foreach my $pool_id (keys %{$options{pool}})  {
        return 1 if ($options{pool}->{$pool_id}->{ready} == 1);
    }
    
    return 0;
}

sub waiting_ready {
    my (%options) = @_;

    return 1 if (${$options{ready}} == 1);
    
    my $time = time();
    # We wait 10 seconds
    while (${$options{ready}} == 0 && 
           time() - $time < 10) {
        zmq_poll($centreond->{poll}, 5000);
    }
    
    if (${$options{ready}} == 0) {
        return 0;
    }
    
    return 1;
}

sub clean_sessions {
    my ($self, %options) = @_;
    
    if ($self->{sessions_timer} - time() > $centreond_config->{centreondcore}{purge_sessions_time}) {
        $self->{logger}->writeLogInfo("purge sessions in progress...");
        $self->{db_centreond}->query("DELETE FROM centreond_identity WHERE `ctime` <  " . $self->{db_centreond}->quote(time() - $centreond_config->{centreondcore}{sessions_time}));
        $self->{sessions_timer} = time();
    }
}

sub quit {
    my ($self, %options) = @_;
    
    $self->{logger}->writeLogInfo("Quit main process");
    zmq_close($self->{internal_socket});
    if (defined($centreond_config->{centreondcore}{external_com_type}) && $centreond_config->{centreondcore}{external_com_type} ne '') {
        zmq_close($self->{external_socket});
    }
    exit(0);
}

sub run {
    $centreond = shift;

    $centreond->SUPER::run();
    $centreond->{logger}->redirect_output();

    $centreond->{logger}->writeLogDebug("centreond launched....");
    $centreond->{logger}->writeLogDebug("PID: $$");

    if (centreon::centreond::common::add_history(dbh => $centreond->{db_centreond},
                                                 code => 0,
                                                 data => { msg => 'centreond is starting...' },
                                                 json_encode => 1) == -1) {
        $centreond->{logger}->writeLogInfo("Cannot write in history. We quit!!");
        exit(1);
    }
    
    $centreond->{internal_socket} = centreon::centreond::common::create_com(type => $centreond_config->{centreondcore}{internal_com_type},
                                            path => $centreond_config->{centreondcore}{internal_com_path},
                                            zmq_type => 'ZMQ_ROUTER', name => 'router-internal',
                                            logger => $centreond->{logger});
    if (defined($centreond_config->{centreondcore}{external_com_type}) && $centreond_config->{centreondcore}{external_com_type} ne '') {
        $centreond->{external_socket} = centreon::centreond::common::create_com(type => $centreond_config->{centreondcore}{external_com_type},
                                                path => $centreond_config->{centreondcore}{external_com_path},
                                                zmq_type => 'ZMQ_ROUTER', name => 'router-external',
                                                logger => $centreond->{logger});
    }

    # Initialize poll set
    $centreond->{poll} = [
        {
            socket  => $centreond->{internal_socket},
            events  => ZMQ_POLLIN,
            callback => \&router_internal_event,
        }
    ];
    
    if (defined($centreond->{external_socket})) {
        push @{$centreond->{poll}}, {
            socket  => $centreond->{external_socket},
            events  => ZMQ_POLLIN,
            callback => \&router_external_event,
        };
    }
    
    # init all modules
    foreach my $name (keys %{$centreond->{modules_register}}) {
        $centreond->{logger}->writeLogInfo("Call init function from module '$name'");
        $centreond->{modules_register}->{$name}->{init}->(logger => $centreond->{logger}, id => $centreond->{id},
                                                          poll => $centreond->{poll},
                                                          external_socket => $centreond->{external_socket},
                                                          internal_socket => $centreond->{internal_socket},
                                                          dbh => $centreond->{db_centreond});
    }
    
    $centreond->{logger}->writeLogInfo("[Server accepting clients]");

    while (1) {
        my $count = 0;
        my $poll = [@{$centreond->{poll}}];
        
        foreach my $name (keys %{$centreond->{modules_register}}) {
            $count += $centreond->{modules_register}->{$name}->{check}->(logger => $centreond->{logger},
                                                                         dead_childs => $centreond->{return_child},
                                                                         internal_socket => $centreond->{internal_socket},
                                                                         dbh => $centreond->{db_centreond},
                                                                         poll => $poll);
        }
        
        if ($centreond->{stop} == 1) {
            # No childs
            if ($count == 0) {
                $centreond->quit();
            }
            
            # Send KILL
            if (time() - $centreond->{kill_timer} > $centreond_config->{centreondcore}{timeout}) {
                foreach my $name (keys %{$centreond->{modules_register}}) {
                    $centreond->{modules_register}->{$name}->{kill_internal}->(logger => $centreond->{logger});
                }
                $centreond->quit();
            }
        }
    
        zmq_poll($poll, 5000);
        
        $centreond->clean_sessions();
    }
}

1;

__END__