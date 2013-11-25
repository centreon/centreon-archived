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

package database::postgres::mode::querytime;

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
                                  "exclude-user:s"          => { name => 'exclude_user', },
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

    my $query;
    if ($self->{sql}->is_version_minimum(version => '9.2')) {
        $query = q{
SELECT datname, datid, pid, usename, client_addr, query AS current_query, state AS state,
       CASE WHEN client_port < 0 THEN 0 ELSE client_port END AS client_port,
       COALESCE(ROUND(EXTRACT(epoch FROM now()-query_start)),0) AS seconds
FROM pg_stat_activity WHERE (query_start IS NOT NULL AND (state NOT LIKE 'idle%' OR state IS NULL))
ORDER BY query_start, pid DESC
};
    } else {
        $query = q{
SELECT datname, datid, procpid AS pid, usename, client_addr, current_query AS current_query, '' AS state,
       CASE WHEN client_port < 0 THEN 0 ELSE client_port END AS client_port,
       COALESCE(ROUND(EXTRACT(epoch FROM now()-query_start)),0) AS seconds
FROM pg_stat_activity WHERE (query_start IS NOT NULL AND current_query NOT LIKE '<IDLE>%')
ORDER BY query_start, procpid DESC
};
    }
    
    $self->{sql}->query(query => $query);

    $self->{output}->output_add(severity => 'OK',
                                short_msg => "All databases queries time are ok.");
    my $dbquery = {};
    while ((my $row = $self->{sql}->fetchrow_hashref())) {
        if (defined($self->{option_results}->{exclude}) && $row->{datname} !~ /$self->{option_results}->{exclude}/) {
            next;
        }
        if (defined($self->{option_results}->{exclude_user}) && $row->{usename} !~ /$self->{option_results}->{exclude_user}/) {
            next;
        }
        
        my $exit_code = $self->{perfdata}->threshold_check(value => $row->{seconds}, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        if ($self->{output}->is_status(value => $exit_code, compare => 'ok', litteral => 1)) {
            $self->{output}->output_add(long_msg => sprintf("Request from client '%s' too long (%d sec) on database '%s': %s",
                                                            $row->{client_addr}, $row->{seconds}, $row->{datname}, $row->{current_query}));
            $dbquery->{$row->{datname}}->{total}++;
            $dbquery->{$row->{datname}}->{code}->{$exit_code}++;
        }
    }
    
    foreach my $dbname (keys %$dbquery) {
        $self->{output}->perfdata_add(label => $dbname . '_qtime_num',
                                      value => $dbquery->{$dbname}->{total},
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                      min => 0);
        foreach my $exit_code (keys %{$dbquery->{$dbname}->{code}}) {
            $self->{output}->output_add(severity => $exit_code,
                                        short_msg => sprintf("%d request exceed " . lc($exit_code) . " threshold on database '%s'",
                                                             $dbquery->{$dbname}->{code}->{$exit_code}, $dbname));
        }
    }

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Checks the time of running queries for one or more databases

=over 8

=item B<--warning>

Threshold warning in seconds.

=item B<--critical>

Threshold critical in seconds.

=item B<--exclude>

Filter databases.

=item B<--exclude-user>

Filter users.

=back

=cut
