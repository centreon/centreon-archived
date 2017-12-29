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
        $self->{cmd_system_health} = [ { cmd => "/usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin os::linux::snmp::plugin --mode cpu-detailed \\
							 --hostname localhost \\
							 --statefile-suffix='_diag-cpu' \\
							 --filter-perfdata='^(?!(wait|guest|user|softirq|kernel|interrupt|guestnice|idle|steal|system|nice))' \\
							 --snmp-community " . $options{community} ,
                                         callback => \&centreon::health::ssh::ssh_callback,
					 userdata => "cpu_usage" },
                                       { cmd => "/usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin os::linux::snmp::plugin --mode load \\
							 --hostname localhost \\
							 --filter-perfdata='^(?!(load))' \\
							 --snmp-community " . $options{community},
                                         callback => \&centreon::health::ssh::ssh_callback,
                                         userdata => "load" },
                                       { cmd => "/usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin os::linux::snmp::plugin --mode memory \\
							 --hostname localhost \\
							 --filter-perfdata='^(?!(cached|buffer|used))' \\
							 --snmp-community " . $options{community},
                                         callback => \&centreon::health::ssh::ssh_callback,
                                         userdata => "mem_usage" },
                                       { cmd => "/usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin os::linux::snmp::plugin --mode swap \\
							 --hostname localhost \\
							 --filter-perfdata='^(?!(used))' \\
							 --snmp-community " . $options{community},
                                         callback => \&centreon::health::ssh::ssh_callback,
                                         userdata => "swap_usage" },
                                       { cmd => "/usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin os::linux::snmp::plugin --mode storage \\
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
    my ($server_list, $medium, $community, $logger) = @_;

    foreach my $server (keys $server_list) {
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
