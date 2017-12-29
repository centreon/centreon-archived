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

package centreon::script::centreon_health;

use strict;
use warnings;
use POSIX;
use centreon::script;
use centreon::health::checkservers;
use centreon::health::checkrrd;
use centreon::health::checkdb;
use centreon::health::checkmodules;
use centreon::health::checksystems;
use centreon::health::checkbroker;
use centreon::health::checkengine;
use centreon::health::checklogs;

use Data::Dumper;
use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreon_health",
                                   centreon_db_conn => 1,
                                   centstorage_db_conn => 1,
				   noroot => 1,
        			);
    bless $self, $class;

    $self->add_options(
	"centstorage-db=s"     => \$self->{opt_csdb},
	"centreon-branch=s"    => \$self->{opt_majorversion},
	"anonymous"	       => \$self->{opt_anonymous}
    );
	
    $self->{current_time} = time();
    $self->{global_output} = {};

    $self->{opt_csdb} = 'centreon_storage' if (!defined $self->{opt_csdb});
    $self->{opt_majorversion} = '2.8' if (!defined $self->{opt_majorversion});
    return $self;
}

sub run {
    my $self = shift;
    $self->SUPER::run();

    $self->{logger}->writeLogInfo("Starting centreon_health check");
    $self->{global_output}->{rrd} = centreon::health::checkrrd->new->run($self->{csdb});
    $self->{global_output}->{database} = centreon::health::checkdb->new->run($self->{cdb}, $self->{csdb}, $self->{opt_csdb});    
    $self->{global_output}->{module} = centreon::health::checkmodules->new->run($self->{cdb});
    $self->{global_output}->{server} = centreon::health::checkservers->new->run($self->{cdb}, $self->{csdb}, $self->{opt_majorversion});
    $self->{global_output}->{systems} = centreon::health::checksystems->new->run($self->{global_output}->{server}->{poller}, 'snmp');

   print Dumper($self->{global_output}->{systems});
}

1;
