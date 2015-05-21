################################################################################
# Copyright 2005-2015 MERETHIS
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
#
####################################################################################

package centreon::common::objects::object;

use strict;
use warnings;

sub new {
    my ($class, %options) = @_;
    my $self  = {};
    $self->{logger} = $options{logger};
    $self->{db_centreon} = $options{db_centreon};
    
    bless $self, $class;
    return $self;
}

sub builder {
    my ($self, %options) = @_;

    my $where = defined($options{where}) ? ' WHERE ' . $options{where} : '';
    my $extra_suffix = defined($options{extra_suffix}) ? $options{extra_suffix} : '';
    my $request = $options{request} . " " . join(', ', @{$options{fields}}) . 
                    " FROM " . join(', ', @{$options{tables}}) . $where . $extra_suffix;
    return $request;
}

sub do {
    my ($self, %options) = @_;
    my $mode = defined($options{mode}) ? $options{mode} : 0;
    
    my ($status, $sth) = $self->{db_centreon}->query($options{request});
    if ($mode == 0) {
        return ($status, $sth);
    } elsif ($mode == 1) {
        my $result = $sth->fetchall_hashref($options{keys});
        if (!defined($result)) {
            $self->{logger}->writeLogError("Cannot fetch database data: " . $sth->errstr . " [request = $options{request}]");
            return (-1, undef);
        }
        return ($status, $result);
    }
    my $result = $sth->fetchall_arrayref();
    if (!defined($result)) {
        $self->{logger}->writeLogError("Cannot fetch database data: " . $sth->errstr . " [request = $options{request}]");
        return (-1, undef);
    }
    return ($status, $result);
}

sub custom_execute {
    my ($self, %options) = @_;
    
    return $self->do(%options);
}

sub execute {
    my ($self, %options) = @_;
    
    my $request = $self->builder(%options);
    return $self->do(request => $request, %options);
}

1;
