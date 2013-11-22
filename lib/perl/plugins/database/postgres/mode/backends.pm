################################################################################
# Copyright 2005-2013 MERETHIS
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
# For more information : contact@centreon.com
# Authors : Quentin Garnier <qgarnier@merethis.com>
#
####################################################################################

package database::postgres::mode::backends;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "warning:s"               => { name => 'warning', },
                                  "critical:s"              => { name => 'critical', },
                                  "exclude:s"               => { name => 'exclude', },
                                  "noidle"                  => { name => 'noidle', },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (($self->{perfdata}->threshold_validate(label => 'warning', value => $self->{option_results}->{warning})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning threshold '" . $self->{warn1} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical', value => $self->{option_results}->{critical})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical threshold '" . $self->{critical} . "'.");
       $self->{output}->option_exit();
    }
    
}

sub run {
    my ($self, %options) = @_;
    # $options{sql} = sqlmode object
    $self->{sql} = $options{sql};

    $self->{sql}->connect();
    
    my $noidle = '';
    if (defined($self->{option_results}->{noidle})) {
        if ($self->{sql}->is_version_minimum(version => '9.2')) {
            $noidle = " AND state <> 'idle'";
        } else {
            $noidle = " AND current_query <> '<IDLE>'";
        }
    }

    my $query = "SELECT COUNT(datid) AS current,
  (SELECT setting AS mc FROM pg_settings WHERE name = 'max_connections') AS mc,
  d.datname
FROM pg_database d
LEFT JOIN pg_stat_activity s ON (s.datid = d.oid $noidle)
GROUP BY d.datname
ORDER BY d.datname";
    $self->{sql}->query(query => $query);

    $self->{output}->output_add(severity => 'OK',
                                short_msg => "All client database connections are ok.");

    my $database_check = 0;
    my $result = $self->{sql}->fetchall_arrayref();
    
    foreach my $row (@{$result}) {
        if (defined($self->{option_results}->{exclude}) && $$row[2] !~ /$self->{option_results}->{exclude}/) {
            $self->{output}->output_add(long_msg => "Skipping database '" . $$row[2] . '"');
            next;
        }       
        
        $database_check++;
        my $used = $$row[0];
        my $max_connections = $$row[1];
        my $database_name = $$row[2];
        
        my $prct_used = ($used * 100) / $max_connections;
        my $exit_code = $self->{perfdata}->threshold_check(value => $prct_used, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        $self->{output}->output_add(long_msg => sprintf("Database '%s': %.2f%% client connections limit reached (%d of max. %d)",
                                                    $database_name, $prct_used, $used, $max_connections));
        if (!$self->{output}->is_status(value => $exit_code, compare => 'ok', litteral => 1)) {
            $self->{output}->output_add(severity => $exit_code,
                                        short_msg => sprintf("Database '%s': %.2f%% client connections limit reached (%d of max. %d)",
                                                    $database_name, $prct_used, $used, $max_connections));
        }
        
        $self->{output}->perfdata_add(label => 'connections_' . $database_name,
                                      value => $used,
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                      min => 0, max => $max_connections);
    }
    if ($database_check == 0) {
        $self->{output}->output_add(severity => 'UNKNOWN',
                                    short_msg => 'No database checked. (permission or a wrong exclude filter)');
    }

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check the current number of connections for one or more databases

=over 8

=item B<--warning>

Threshold warning in percent.

=item B<--critical>

Threshold critical in percent.

=item B<--exclude>

Filter databases.

=item B<--noidle>

Idle connections are not counted.

=back

=cut
