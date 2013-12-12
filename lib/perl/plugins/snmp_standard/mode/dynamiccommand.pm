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

package snmp_standard::mode::dynamiccommand;

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
                                  "label:s"             => { name => 'label' },
                                  "command:s"           => { name => 'command' },
                                  "args:s"              => { name => 'args' },
                                  "shell"               => { name => 'shell' },
                                });
    $self->{statefile_cache} = centreon::plugins::statefile->new(%options);
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (!defined($self->{option_results}->{label})) {
       $self->{output}->add_option_msg(short_msg => "Need to specify an label.");
       $self->{output}->option_exit(); 
    }
    if (!defined($self->{option_results}->{command})) {
       $self->{output}->add_option_msg(short_msg => "Need to specify a command.");
       $self->{output}->option_exit(); 
    }
    if (!defined($self->{option_results}->{args})) {
       $self->{output}->add_option_msg(short_msg => "Need to specify arguments (can be empty).");
       $self->{output}->option_exit(); 
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    $self->{hostname} = $self->{snmp}->get_hostname();
    
    my $oid_nsExtendArgs = '.1.3.6.1.4.1.8072.1.3.2.2.1.3';
    my $oid_nsExtendStatus = '.1.3.6.1.4.1.8072.1.3.2.2.1.21'; # 4 = CreateAndGo
    my $oid_nsExtendCommand = '.1.3.6.1.4.1.8072.1.3.2.2.1.2';
    my $oid_nsExtendExecType = '.1.3.6.1.4.1.8072.1.3.2.2.1.6'; # 1 = exec, 2 = sub shell
    
    my $oid_nsExtendOutput1Line = '.1.3.6.1.4.1.8072.1.3.2.3.1.1';
    my $oid_nsExtendOutNumLines = '.1.3.6.1.4.1.8072.1.3.2.3.1.3';
    my $oid_nsExtendOutputFull = '.1.3.6.1.4.1.8072.1.3.2.3.1.2';
    my $oid_nsExtendResult = '.1.3.6.1.4.1.8072.1.3.2.3.1.4';
    
    # nsExtendStatus.4.101.101.101.102 = 4 ('CreateAndGo')
    # nsExtendStatus.LengthStr.CharacterInDecimal

    # snmpset -On -c test -v 2c localhost \
    #    '.1.3.6.1.4.1.8072.1.3.2.2.1.21.4.104.102.101.102'  = 4 \
    #    '.1.3.6.1.4.1.8072.1.3.2.2.1.2.4.104.102.101.102' = /bin/echo \
    #    '.1.3.6.1.4.1.8072.1.3.2.2.1.3.4.104.102.101.102'    = 'myplop' 
    #

    my $result = $self->{snmp}->get_leef(oids => ['']);
    my $value = $result->{$self->{option_results}->{oid}};

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Execute command through SNMP.
Some prerequisites:
- 'net-snmp' and 'NET-SNMP-EXTEND-MIB' support ;
- a write account.

=over 8

=item B<--label>

Label which identify the command

=item B<--command>

Command executable.

=item B<--args>

Command arguments.

=item B<--shell>

Use a sub-shell to execute the command.

=back

=cut
