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

package database::postgres::mode::hitratio;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::statefile;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "warning:s"               => { name => 'warning', },
                                  "critical:s"              => { name => 'critical', },
                                  "lookback"                => { name => 'lookback', },
                                  "exclude:s"               => { name => 'exclude', },
                                });
    $self->{statefile_cache} = centreon::plugins::statefile->new(%options);

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

    $self->{statefile_cache}->check_options(%options);
}

sub run {
    my ($self, %options) = @_;
    # $options{sql} = sqlmode object
    $self->{sql} = $options{sql};

    $self->{sql}->connect();
    
    $self->{sql}->query(query => q{
SELECT sd.blks_hit, sd.blks_read, d.datname
FROM pg_stat_database sd, pg_database d
WHERE d.oid=sd.datid
    });

    $self->{statefile_cache}->read(statefile => 'postgres_' . $self->{mode} . '_' . $self->{sql}->get_unique_id4save());
    my $old_timestamp = $self->{statefile_cache}->get(name => 'last_timestamp');
    
    my $database_check = 0;
    my $new_datas = {};
    $new_datas->{last_timestamp} = time();
    my $result = $self->{sql}->fetchall_arrayref();
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => "All databases hitratio are ok.");

    
    foreach my $row (@{$result}) {
        $new_datas->{$$row[2] . '_blks_hit'} = $$row[0];
        $new_datas->{$$row[2] . '_blks_read'} = $$row[1];
        
        if (defined($self->{option_results}->{exclude}) && $$row[2] !~ /$self->{option_results}->{exclude}/) {
            $self->{output}->output_add(long_msg => "Skipping database '" . $$row[2] . '"');
            next;
        }

        my $old_blks_hit = $self->{statefile_cache}->get(name => $$row[2] . '_blks_hit');
        my $old_blks_read = $self->{statefile_cache}->get(name => $$row[2] . '_blks_read');
        
        next if (!defined($old_blks_hit) || !defined($old_blks_read));
        $old_blks_hit = 0 if ($$row[0] <= $old_blks_hit);
        $old_blks_read = 0 if ($$row[1] <= $old_blks_read);
        
        $database_check++;
        my %prcts = ();
        my $total_read_requests = $new_datas->{$$row[2] . '_blks_hit'} - $old_blks_hit;
        my $total_read_disk = $new_datas->{$$row[2] . '_blks_read'} - $old_blks_read;
        $prcts{hitratio_now} = ($total_read_requests == 0) ? 100 : ($total_read_requests - $total_read_disk) * 100 / $total_read_requests;
        $prcts{hitratio} = ($new_datas->{$$row[2] . '_blks_hit'} == 0) ? 100 : ($new_datas->{$$row[2] . '_blks_hit'} - $new_datas->{$$row[2] . '_blks_read'}) * 100 / $new_datas->{$$row[2] . '_blks_hit'};
        
        my $exit_code = $self->{perfdata}->threshold_check(value => $prcts{'hitratio' . ((defined($self->{option_results}->{lookback})) ? '_now' : '' )}, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        $self->{output}->output_add(long_msg => sprintf("Database '%s' hitratio at %.2f%%", 
                                                    $$row[2], $prcts{'hitratio' . ((defined($self->{option_results}->{lookback})) ? '' : '_now')})
                                    );
        
        if (!$self->{output}->is_status(value => $exit_code, compare => 'ok', litteral => 1)) {
            $self->{output}->output_add(severity => $exit_code,
                                        short_msg => sprintf("Database '%s' hitratio at %.2f%%", 
                                                    $$row[2], $prcts{'hitratio' . ((defined($self->{option_results}->{lookback})) ? '' : '_now')})
                                        );
        }
        $self->{output}->perfdata_add(label => $$row[2] . '_hitratio' . ((defined($self->{option_results}->{lookback})) ? '' : '_now'), unit => '%',
                                      value => sprintf("%.2f", $prcts{'hitratio' . ((defined($self->{option_results}->{lookback})) ? '_now' : '')}),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                      min => 0, max => 100);
        $self->{output}->perfdata_add(label => $$row[2] . '_hitratio' . ((defined($self->{option_results}->{lookback})) ? '_now' : ''), unit => '%',
                                      value => sprintf("%.2f", $prcts{'hitratio' . ((defined($self->{option_results}->{lookback})) ? '_now' : '')}),
                                      min => 0, max => 100);
    }
    
    $self->{statefile_cache}->write(data => $new_datas); 
    if (!defined($old_timestamp)) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => "Buffer creation...");
    }
    if (defined($old_timestamp) && $database_check == 0) {
        $self->{output}->output_add(severity => 'UNKNOWN',
                                    short_msg => 'No database checked. (permission or a wrong exclude filter)');
    }
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check hitratio (in buffer cache) for databases.

=over 8

=item B<--warning>

Threshold warning.

=item B<--critical>

Threshold critical.

=item B<--lookback>

Threshold isn't on the percent calculated from the difference ('xxx_hitratio_now').

=item B<--exclude>

Filter databases.

=back

=cut
