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

    for (my $i = 0; $self->is_set() && $i < $self->{timeout}; $i++) {
        sleep 1;
    }
    die "Failed to set lock for $self->{name}" if $self->is_set();
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
        "SELECT id,running,pid,time_launch FROM cron_operation WHERE name LIKE '$self->{name}'"
    );
    
    return 1 if ($status == -1);
    my $data = $sth->fetchrow_hashref();

    if (!defined $data->{id}) {
        $self->{not_created_yet} = 1;
        $self->{previous_launch_time} = 0;
        return 0;
    }
    $self->{id} = $data->{id};
    $data->{pid} = -1 if (!defined($data->{pid}));
    $self->{pid} = $data->{pid};
    $self->{previous_launch_time} = $data->{time_launch};
    if (defined $data->{running} && $data->{running} == 1) {
        my $line = `ps -ef | grep -v grep | grep $self->{pid} | grep $self->{name}`;
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
INSERT INTO cron_operation
(name, system, activate)
VALUES ('$self->{name}', '1', '1')
EOQ
        goto error if $status == -1;
        $self->{id} = $self->{dbc}->last_insert_id();
        return;
    }
    $status = $self->{dbc}->do(<<"EOQ");
UPDATE cron_operation
SET running = '1', time_launch = '$self->{launch_time}', pid = '$self->{pid}'
WHERE id = '$self->{id}'
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
UPDATE cron_operation
SET running = '0', last_execution_time = '$exectime', pid = '-1'
WHERE id = '$self->{id}'
EOQ
    }
}

1;
