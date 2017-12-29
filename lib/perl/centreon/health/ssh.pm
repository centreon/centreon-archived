################################################################################
# Copyright 2005-2013 Centreon
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give Centreon 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of Centreon choice, provided that 
# Centreon also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

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
        $command_results->{$options{userdata}} = $options{stdout};
    } else {
        $command_results->{$options{userdata}} = "Failed action on ssh or plugin";
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
    $self->{session}->execute(commands => $options{command_pool}, timeout => 5000, timeout_nodata => 10, parallel => 5);
    $self->{data} = $command_results;
    return $self->{data}
}
        
1;
