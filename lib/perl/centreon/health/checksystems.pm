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

package centreon::health::checksystems;

use strict;
use warnings;
use centreon::common::misc;
use centreon::health::ssh;

sub new {
    my $class = shift;
    my $self = {};
    $self->{cmd_system_health} = [];
    $self->{output} = {};

    bless $self, $class;
    return $self;
}

sub build_command_hash {
    my ($self, %options) = @_;

    if ($options{medium} eq "snmp") {
        $self->{cmd_system_health} = [ { cmd => "/usr/lib/" . $options{plugin_path} . "/plugins/" . $options{plugins} . " --plugin os::linux::snmp::plugin --mode cpu-detailed \\
							 --hostname localhost \\
							 --statefile-suffix='_diag-cpu' \\
							 --filter-perfdata='^(?!(wait|guest|user|softirq|kernel|interrupt|guestnice|idle|steal|system|nice))' \\
							 --snmp-community " . $options{community} ,
                                         callback => \&centreon::health::ssh::ssh_callback,
					 userdata => "cpu_usage" },
                                       { cmd => "/usr/lib/" . $options{plugin_path} . "/plugins/" . $options{plugins} . " --plugin os::linux::snmp::plugin --mode load \\
							 --hostname localhost \\
							 --filter-perfdata='^(?!(load))' \\
							 --snmp-community " . $options{community},
                                         callback => \&centreon::health::ssh::ssh_callback,
                                         userdata => "load" },
                                       { cmd => "/usr/lib/" . $options{plugin_path} . "/plugins/" . $options{plugins} . " --plugin os::linux::snmp::plugin --mode memory \\
							 --hostname localhost \\
							 --filter-perfdata='^(?!(cached|buffer|used))' \\
							 --snmp-community " . $options{community},
                                         callback => \&centreon::health::ssh::ssh_callback,
                                         userdata => "mem_usage" },
                                       { cmd => "/usr/lib/" . $options{plugin_path} . "/plugins/" . $options{plugins} . " --plugin os::linux::snmp::plugin --mode swap \\
							 --hostname localhost \\
							 --filter-perfdata='^(?!(used))' \\
							 --snmp-community " . $options{community},
                                         callback => \&centreon::health::ssh::ssh_callback,
                                         userdata => "swap_usage" },
                                       { cmd => "/usr/lib/" . $options{plugin_path} . "/plugins/" . $options{plugins} . " --plugin os::linux::snmp::plugin --mode storage \\
							 --hostname localhost \\
							 --storage='^(?!(/dev/shm|/sys/fs/cgroup|/boot|/run.*))' --name --regexp \\
							 --filter-perfdata='^(?!(used))' --statefile-suffix='_diag-storage' \\
							 --verbose \\
							 --snmp-community " . $options{community},
                                        callback => \&centreon::health::ssh::ssh_callback,
                                        userdata => "storage_usage" },
                                     ];
    } else {
	return -1
    }


}

sub get_remote_infos {
    my ($self, %options) = @_;
  
    
    return centreon::health::ssh::new->main(host => $options{host}, port => $options{ssh_port}, command_pool => $self->{cmd_system_health}); 
    
}
     
sub get_local_infos {
    my ($self, %options) = @_;
    my ($lerror, $stdout);    
    
    my $results;

    foreach my $command (@{$self->{cmd_system_health}}) {
        ($lerror, $stdout) = centreon::common::misc::backtick(command => $command->{cmd});
	$results->{$command->{userdata}} = $stdout;
	while ($stdout =~ m/Buffer creation/) {
	    # Replay command to bypass cache creation
	    sleep 1;
	    ($lerror, $stdout) = centreon::common::misc::backtick(command => $command->{cmd});
	    $results->{$command->{userdata}} = $stdout;
	}
    }

    return $results 
    
}

sub run {
    my $self = shift;
    my ($server_list, $medium, $community, $centreon_ver) = @_;

    $self->build_command_hash(medium => $medium,
			      plugins => ($centreon_ver eq 2.8) ? 'centreon_linux_snmp.pl' : 'centreon_plugins.pl',
			      plugin_path => ($centreon_ver eq 2.8) ? 'centreon' : 'nagios',
			      community => $community);

    foreach my $server (keys %$server_list) {
	my $name = $server_list->{$server}->{name};
	if ($server_list->{$server}->{localhost} eq "NO") {
	    $self->{output}->{$name} = $self->get_remote_infos(host => $server_list->{$server}->{address}, ssh_port => $server_list->{$server}->{ssh_port});
        } else {
	    $self->{output}->{$name} = $self->get_local_infos(poller_name => $name);
	}
    }
    
    return $self->{output}
}

1;
