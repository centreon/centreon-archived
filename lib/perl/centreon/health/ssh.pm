#
# Copyright 2017 Centreon (http://www.centreon.com/)
#
# Centreon is a full-fledged industry-strength solution that meets
# the needs in IT infrastructure and application monitoring for
# service performance.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#

package centreon::health::ssh;

use strict;
use warnings;
use Libssh::Session qw(:all);

my $command_results = {};

sub new {
    my $class = shift @_ || __PACKAGE__;
    my $self; 
    $self->{session} = undef;
    $self->{host} = undef;
    $self->{port} = undef;
    $self->{logger} = undef;
    $self->{data} = {};

    bless $self, $class;
    return $self;
}

sub ssh_callback {
    my (%options) = @_;

    if ($options{exit} == SSH_OK || $options{exit} == SSH_AGAIN) { # AGAIN means timeout
	chomp($options{stdout});
        $command_results->{multiple}->{$options{userdata}} = $options{stdout};
    } else {
        $command_results->{multiple}->{failed_action} = "Failed action on ssh or plugin";
	return -1
    }
    return 0
}

sub create_ssh_channel {
    my ($self, %options) = @_;

    $self->{session} = Libssh::Session->new();
    if ($self->{session}->options(host => $options{host}, port => $options{port}, user => $options{user}) != SSH_OK) {
        return 1
    }

    if ($self->{session}->connect() != SSH_OK) {
        return 1
    }

    if ($self->{session}->auth_publickey_auto() != SSH_AUTH_SUCCESS) {
        printf("auth issue pubkey: %s\n", $self->{session}->error(GetErrorSession => 1));
        if ($self->{session}->auth_password(password => $options{password}) != SSH_AUTH_SUCCESS) {
            printf("auth issue: %s\n", $self->{session}->error(GetErrorSession => 1));
            return 1
        }
    }
    return 0
}

sub main {
    my ($self, %options) = @_;

    $self->create_ssh_channel(host => $options{host}, port => $options{port}, user => 'centreon');
    if (defined($options{command_pool})) {
        $self->{session}->execute(commands => $options{command_pool}, timeout => 5000, timeout_nodata => 10, parallel => 5);
	 $self->{data} = $command_results->{multiple};
    } else {
    	my $return = $self->{session}->execute_simple(cmd => $options{command}, userdata => $options{userdata}, timeout => 10, timeout_nodata => 5);
	$command_results->{simple} = $return->{stdout};
	$self->{data} = $command_results->{simple};
    }

    return $self->{data}
}
        
1;
