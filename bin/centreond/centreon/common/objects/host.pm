################################################################################
# Copyright 2005-2015 MERETHIS
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
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::common::objects::host;

use strict;
use warnings;

use base qw(centreon::common::objects::object);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(%options);
    
    bless $self, $class;
    return $self;
}

# special options: 
#   'organization_name' or 'organization_id'
#   'with_services'
sub get_hosts_by_organization {
    my ($self, %options) = @_;

    my %defaults = (request => 'SELECT', tables => ['cfg_hosts'], fields => ['*']);
    if (defined($options{organization_name})) {
        $defaults{tables} = ['cfg_hosts', 'cfg_organizations'];
        $defaults{where} = 'cfg_organizations.name = ' . $self->{db_centreon}->quote($options{organization_name});
    } elsif (defined($options{organization_id})) {
        $defaults{where} = 'cfg_hosts.organization_id = ' . $self->{db_centreon}->quote($options{organization_id});
    } else {
        $self->{logger}->writeLogError("Please specify 'organization_name' or 'organization_id' parameter.");
        return (-1, undef);
    }
    if (defined($options{with_services})) {
        push @{$defaults{tables}}, 'cfg_hosts_services_relations', 'cfg_services';
        $defaults{where} .= ' AND cfg_hosts.host_id = cfg_hosts_services_relations.host_host_id AND cfg_hosts_services_relations.service_service_id = cfg_services.service_id';
    }

    my $options_builder = {%defaults, %options};
    return $self->execute(%$options_builder);
}

1;
