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

package centreon::script::centreon_health;

use strict;
use warnings;
use POSIX;
use centreon::script;
use base qw(centreon::script);

use centreon::health::checkservers;
use centreon::health::checkrrd;
use centreon::health::checkdb;
use centreon::health::checkmodules;
use centreon::health::checksystems;
use centreon::health::checkbroker;
use centreon::health::checklogs;
use centreon::health::output;

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreon_health",
                                   centreon_db_conn => 1,
                                   centstorage_db_conn => 1,
				   noroot => 1,
        			);
    bless $self, $class;

    $self->add_options(
	"centstorage-db=s"	=> \$self->{opt_csdb},
	"centreon-branch=s"	=> \$self->{opt_majorversion},
	"check-protocol=s"	=> \$self->{opt_checkprotocol},
	"output-format=s"	=> \$self->{opt_outputformat},
	"snmp-community=s"	=> \$self->{opt_community},
	"skip-rrd"		=> \$self->{opt_skiprrd},
	"skip-db"		=> \$self->{opt_skipdb},
	"skip-logs"		=> \$self->{opt_skiplogs},
	"anonymous"		=> \$self->{opt_anonymous}
    );
	
    $self->{data} = {};
    $self->{final_output} = {};
    $self->{opt_checkprotocol} = 'snmp';
    $self->{opt_outputformat} = 'JSON' if (!defined$self->{opt_outputformat}); 
    $self->{opt_community} = 'public' if (!defined $self->{opt_community});
    $self->{opt_csdb} = 'centreon_storage' if (!defined $self->{opt_csdb});
    $self->{opt_majorversion} = '2.8' if (!defined $self->{opt_majorversion});

    return $self;
}

sub run {
    my $self = shift;
    $self->SUPER::run();

    $self->{data}->{rrd} = centreon::health::checkrrd->new->run($self->{csdb}) if (!defined($self->{opt_skiprrd}));
    $self->{data}->{database} = centreon::health::checkdb->new->run($self->{cdb}, $self->{csdb}, $self->{opt_csdb}) if (!defined($self->{opt_skipdb}));    
    $self->{data}->{module} = centreon::health::checkmodules->new->run($self->{cdb});
    $self->{data}->{server} = centreon::health::checkservers->new->run($self->{cdb}, $self->{csdb}, $self->{opt_majorversion});
    $self->{data}->{systems} = centreon::health::checksystems->new->run($self->{data}->{server}->{poller}, $self->{opt_checkprotocol}, $self->{opt_community}, $self->{opt_majorversion});
    $self->{data}->{broker} = centreon::health::checkbroker->new->run($self->{cdb}, $self->{data}->{server}->{poller}, $self->{opt_majorversion});
    $self->{data}->{logs} = centreon::health::checklogs->new->run($self->{cdb}, $self->{data}->{server}->{poller}) if (!defined($self->{opt_skiplogs}));

    centreon::health::output->new->run($self->{data}, $self->{opt_outputformat}, defined($self->{opt_skiprrd}), defined($self->{opt_skipdb}), defined($self->{opt_skiplogs}));

}

1;
