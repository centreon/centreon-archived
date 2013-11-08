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

package centreon::plugins::options;
use Pod::Usage;
use Pod::Find qw(pod_where);
use Getopt::Long;
Getopt::Long::Configure("pass_through");
Getopt::Long::Configure('bundling');

sub new {
    my $class = shift;
    my $self  = {};
    bless $self, $class;

    $self->{options_stored} = {};
    $self->{options} = {};
    @{$self->{pod_package}} = ();
    $self->{pod_packages_once} = {};
    return $self;
}

sub set_output {
    my ($self, %options) = @_;
    
    $self->{output} = $options{output};
}

sub display_help {
    my ($self, %options) = @_;
    
    foreach (@{$self->{pod_package}}) {
        my $stdout;
       
        {
            local *STDOUT;
            open STDOUT, '>', \$stdout;
            pod2usage(-exitval => 'NOEXIT', -input => pod_where({-inc => 1}, $_->{package}),
                      -verbose => 99, 
                      -sections => $_->{sections});
        }
        
        $self->{output}->add_option_msg(long_msg => $stdout) if (defined($stdout));
    }
}

sub add_help {
    my ($self, %options) = @_;
    # $options{package} = string package
    # $options{sections} = string sections
    # $options{help_first} = put at the beginning
    # $options{once} = put help only one time for a package
    
    if (defined($options{once}) && defined($self->{pod_packages_once}->{$options{package}})) {
        return ;
    }
    
    if (defined($options{help_first})) {
        shift @{$self->{pod_package}}, {package => $options{package}, sections => $options{sections}};
    } else {
        push @{$self->{pod_package}}, {package => $options{package}, sections => $options{sections}};
    }
    
    $self->{pod_packages_once}->{$options{package}} = 1;
}

sub add_options {
    my ($self, %options) = @_;
    # $options{arguments} = ref to hash table with string and name to store (example: { 'mode:s' => { name => 'mode', default => 'defaultvalue' )
    
    foreach (keys %{$options{arguments}}) {
        if (defined($options{arguments}->{$_}->{default})) {
            $self->{options_stored}->{$options{arguments}->{$_}->{name}} = $options{arguments}->{$_}->{default};
        } else {
            $self->{options_stored}->{$options{arguments}->{$_}->{name}} = undef;
        }
        $self->{options}->{$_} = \$self->{options_stored}->{$options{arguments}->{$_}->{name}};
    }
}

sub parse_options {
    my $self = shift;
    #%{$self->{options_stored}} = ();

    GetOptions(
       %{$self->{options}}
    );
    %{$self->{options}} = ();
}

sub get_option {
    my ($self, %options) = @_;

    return $self->{options_stored}->{$options{argument}};
}

sub get_options {
    my $self = shift;

    return $self->{options_stored};
}

sub clean {
    my $self = shift;
    
    $self->{options_stored} = {};
}

1;

__END__
