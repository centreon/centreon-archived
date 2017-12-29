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

package centreon::health::misc;

use strict;
use warnings;
use Libssh::Session qw(:all);

sub get_ssh_connection {
    my %options = @_;

    my $session = Libssh::Session->new();
    if ($session->options(host => $options{host}, port => $options{port}, user => $options{user}) != SSH_OK) {
        print $session->error() . "\n";
        return 1
    }

    if ($session->connect() != SSH_OK) {
        print $session->error() . "\n";
        return 1
    }

    if ($session->auth_publickey_auto() != SSH_AUTH_SUCCESS) {
        printf("auth issue pubkey: %s\n", $session->error(GetErrorSession => 1));
        if ($session->auth_password(password => $options{password}) != SSH_AUTH_SUCCESS) {
            printf("auth issue: %s\n", $session->error(GetErrorSession => 1));
            return 1
        }
    }

    return $session

}

sub change_seconds {
    my %options = @_;
    my ($str, $str_append) = ('', '');
    my $periods = [
                    { unit => 'y', value => 31556926 },
                    { unit => 'M', value => 2629743 },
                    { unit => 'w', value => 604800 },
                    { unit => 'd', value => 86400 },
                    { unit => 'h', value => 3600 },
                    { unit => 'm', value => 60 },
                    { unit => 's', value => 1 },
    ];
    my %values = ('y' => 1, 'M' => 2, 'w' => 3, 'd' => 4, 'h' => 5, 'm' => 6, 's' => 7);

    foreach (@$periods) {
        next if (defined($options{start}) && $values{$_->{unit}} < $values{$options{start}});
        my $count = int($options{value} / $_->{value});

        next if ($count == 0);
        $str .= $str_append . $count . $_->{unit};
        $options{value} = $options{value} % $_->{value};
        $str_append = ' ';
    }

    return $str;
}

sub format_bytes {
    my (%options) = @_;
    my $size = $options{bytes_value};
    $size =~ s/\D//g;

    if ($size > 1099511627776) {
        return sprintf("%.0fT", $size / 1099511627776);
    }
    elsif ($size > 1073741824) {
        return sprintf("%.0fG", $size / 1073741824);
    }
    elsif ($size > 1048576) {
        return sprintf("%.0fM", $size / 1048576);
    }
    elsif ($size > 1024) {
        return sprintf("%.0fK", $size / 1024);
    }
    else {
        return sprintf("%.0fB", $size);
    }
}

        
1;
