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
