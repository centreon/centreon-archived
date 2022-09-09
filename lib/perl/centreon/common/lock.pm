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

package centreon::common::lock;

use strict;
use warnings;

sub new {
    my ($class, $name, %options) = @_;
    my %defaults = (name => $name, pid => $$, timeout => 10);
    my $self = {%defaults, %options};

    bless $self, $class;
    return $self;
}

sub is_set {
    die "Not implemented";
}

sub set {
    my $self = shift;

    for (my $i = 0; $i < $self->{timeout}; $i++) {
        return if (!$self->is_set());
        sleep 1;
    }

    die "Failed to set lock for $self->{name}";
}

package centreon::common::lock::file;

use base qw(centreon::common::lock);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new(@_);

    if (!defined $self->{storagedir}) {
        die "Can't build lock, required arguments not provided";
    }
    bless $self, $class;
    $self->{pidfile} = "$self->{storagedir}/$self->{name}.lock";
    return $self;
}

sub is_set {
    return -e shift->{pidfile};
}

sub set {
    my $self = shift;

    $self->SUPER::set();
    open LOCK, ">", $self->{pidfile};
    print LOCK $self->{pid};
    close LOCK;
}

sub DESTROY {
    my $self = shift;

    if (defined $self->{pidfile} && -e $self->{pidfile}) {
        unlink $self->{pidfile};
    }
}

package centreon::common::lock::sql;

use base qw(centreon::common::lock);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new(@_);

    if (!defined $self->{dbc}) {
        die "Can't build lock, required arguments not provided";
    }
    bless $self, $class;
    $self->{launch_time} = time();
    return $self;
}

sub is_set {
    my $self = shift;
    my ($status, $sth) = $self->{dbc}->query(
        "SELECT `id`,`running`,`pid`,`time_launch` FROM `cron_operation` WHERE `name` LIKE '$self->{name}'"
    );

    return 1 if ($status == -1);
    my $data = $sth->fetchrow_hashref();

    if (!defined $data->{id}) {
        $self->{not_created_yet} = 1;
        $self->{previous_launch_time} = 0;
        return 0;
    }
    $self->{id} = $data->{id};
	my $pid = defined($data->{pid}) ? $data->{pid} : -1;
    $self->{previous_launch_time} = $data->{time_launch};
    if (defined $data->{running} && $data->{running} == 1) {
        my $line = `ps -ef | grep -v grep | grep -- $pid | grep $self->{name}`;
        return 0 if !length $line;
        return 1;
    }
    return 0;
}

sub set {
    my $self = shift;
    my $status;

    $self->SUPER::set();
    if (defined $self->{not_created_yet}) {
        $status = $self->{dbc}->do(<<"EOQ");
INSERT INTO `cron_operation`
(`name`, `system`, `activate`)
VALUES ('$self->{name}', '1', '1')
EOQ
        goto error if $status == -1;
        $self->{id} = $self->{dbc}->last_insert_id();
        return;
    }
    $status = $self->{dbc}->do(<<"EOQ");
UPDATE `cron_operation`
SET `running` = '1', `time_launch` = '$self->{launch_time}', `pid` = '$self->{pid}'
WHERE `id` = '$self->{id}'
EOQ
    goto error if $status == -1;
    return;

  error:
    die "Failed to set lock for $self->{name}";
}

sub DESTROY {
    my $self = shift;

    if (defined $self->{dbc}) {
        my $exectime = time() - $self->{launch_time};
        $self->{dbc}->do(<<"EOQ");
UPDATE `cron_operation`
SET `last_execution_time` = '$exectime'
WHERE `id` = '$self->{id}'
EOQ
    }
}

1;
