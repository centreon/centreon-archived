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

package snmp_standard::mode::stringvalue;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::misc;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "oid:s"                   => { name => 'oid' },
                                  "warning-regexp:s"        => { name => 'warning_regexp' },
                                  "critical-regexp:s"       => { name => 'critical_regexp' },
                                  "unknown-regexp:s"        => { name => 'unknown_regexp' },
                                  "format:s"                => { name => 'format', default => 'current value is %s' },
                                  "map-values:s"            => { name => 'map_values' },
                                  "map-values-separator:s"  => { name => 'map_values_separator', default => ',' },
                                  "regexp-map-values"       => { name => 'use_regexp_map_values' },
                                  "regexp-isensitive"       => { name => 'use_iregexp' },
                                });
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (!defined($self->{option_results}->{oid})) {
       $self->{output}->add_option_msg(short_msg => "Need to specify an OID.");
       $self->{output}->option_exit(); 
    }

    $self->{map_values} = {};
    if (defined($self->{option_results}->{map_values})) {
        foreach (split /$self->{option_results}->{map_values_separator}/, $self->{option_results}->{map_values}) {
            my ($name, $map) = split /=>/;
            $self->{map_values}->{centreon::plugins::misc::trim($name)} = centreon::plugins::misc::trim($map);
        }
    }
}

sub check_regexp {
    my ($self, %options) = @_;
    
    return 0 if (!defined($self->{option_results}->{$options{severity} . '_regexp'}));
    my $regexp = $self->{option_results}->{$options{severity} . '_regexp'};
    
    if (defined($self->{option_results}->{use_iregexp}) && $options{value} =~ /$regexp/i) {
        $self->{exit_code} = $options{severity};
        return 1;
    } elsif (!defined($self->{option_results}->{use_iregexp}) && $options{value} =~ /$regexp/) {
        $self->{exit_code} = $options{severity};
        return 1;
    }
    
    return 0;
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};    

    my $result = $self->{snmp}->get_leef(oids => [$self->{option_results}->{oid}], nothing_quit => 1);
    my $value_check = $result->{$self->{option_results}->{oid}};
    my $value_display = $value_check;
    
    if (defined($self->{option_results}->{map_values})) {
        # If we don't find it. We keep the original value
        $value_display = defined($self->{map_values}->{$value_check}) ? $self->{map_values}->{$value_check} : $value_check;
        if (defined($self->{option_results}->{use_regexp_map_values})) {
            $value_check = $value_display;
        }
    }
    
    $self->{exit_code} = 'ok';
    $self->check_regexp(severity => 'critical', value => $value_check) || 
        $self->check_regexp(severity => 'warning', value => $value_check) || 
        $self->check_regexp(severity => 'unknown', value => $value_check);

    $self->{output}->output_add(severity => $self->{exit_code},
                                short_msg => sprintf($self->{option_results}->{format}, $value_display));

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check an SNMP string value (can be a String or an Integer).

=over 8

=item B<--oid>

OID value to check (numeric format only).

=item B<--warning-regexp>

Return Warning if the oid value match the regexp.

=item B<--critical-regexp>

Return Critical if the oid value match the regexp.

=item B<--unknown-regexp>

Return Unknown if the oid value match the regexp.

=item B<--format>

Output format (Default: 'current value is %s').

=item B<--map-values>

Use to transform an integer value in most common case.
Example: --map-values='1=>ok,10=>fan failed,11=>psu recovery'

=item B<--map-values-separator>

Separator uses between values (default: coma).

=item B<--regexp-map-values>

Use the 'map values' to match in regexp (need --map-values option).

=item B<--regexp-isensitive>

Allows to use regexp non case-sensitive.

=back

=cut
