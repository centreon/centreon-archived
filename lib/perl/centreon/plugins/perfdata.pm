
package centreon::plugins::perfdata;

sub new {
    my ($class, %options) = @_;
    my $self  = {};
    bless $self, $class;

    $self->{output} = $options{output};
    # Typical Nagios Perfdata 'with ~ @ ..'
    $self->{threshold_label} = {};

    return $self;
}

sub get_perfdata_for_output {
    my ($self, %options) = @_;
    # $options{label} : threshold label
    # $options{total} : percent threshold to transform in global
    # $options{cast_int} : cast absolute to int
    
    my $perf_output = $self->{threshold_label}->{$options{label}}->{value};
    if (defined($perf_output) && $perf_output ne '' && defined($options{total})) {
            $perf_output = ($self->{threshold_label}->{$options{label}}->{arobase} == 1 ? "@" : "") . 
                            (($self->{threshold_label}->{$options{label}}->{infinite_neg} == 0) ? (defined($options{cast_int}) ? sprintf("%d", ($self->{threshold_label}->{$options{label}}->{start} * $options{total} / 100)) : sprintf("%.2f", ($self->{threshold_label}->{$options{label}}->{start} * $options{total} / 100))) : "") . 
                             ":" . 
                             (($self->{threshold_label}->{$options{label}}->{infinite_pos} == 0) ? (defined($options{cast_int}) ? sprintf("%d", ($self->{threshold_label}->{$options{label}}->{end} * $options{total} / 100)) : sprintf("%.2f", ($self->{threshold_label}->{$options{label}}->{end} * $options{total} / 100))) : "");
    }

    if (!defined($perf_output)) {
        $perf_output = '';
    }
    return $perf_output;
}

sub threshold_validate {
    my ($self, %options) = @_;
    # $options{label} : threshold label
    # $options{value} : threshold value

    my $status = 1;
    $self->{threshold_label}->{$options{label}} = {'value' => $options{value}, 'start' => undef, 'end' => undef, 'arobase' => undef, infinite_neg => undef, intinite_pos => undef};
    if (!defined($options{value}) || $options{value} eq '') {
        return $status;
    }

    ($status, $self->{threshold_label}->{$options{label}}->{start}, $self->{threshold_label}->{$options{label}}->{end}, $self->{threshold_label}->{$options{label}}->{arobase}, $self->{threshold_label}->{$options{label}}->{infinite_neg}, $self->{threshold_label}->{$options{label}}->{infinite_pos}) = $self->parse_threshold($options{value});

    return $status;
}

sub threshold_check {
    my ($self, %options) = @_;
    # Can check multiple threshold. First match: out. Order is important
    # options{value}: value to compare
    # options{threshold}: ref to an array (example: [ {label => 'warning', exit_litteral => 'warning' }, {label => 'critical', exit_litteral => 'critical'} ]
    foreach (@{$options{threshold}}) {
        next if (!defined($self->{threshold_label}->{$_->{label}}));
        next if (!defined($self->{threshold_label}->{$_->{label}}->{value}) || $self->{threshold_label}->{$_->{label}}->{value} eq '');
        if ($self->{threshold_label}->{$_->{label}}->{arobase} == 0 && ($options{value} < $self->{threshold_label}->{$_->{label}}->{start} || $options{value} > $self->{threshold_label}->{$_->{label}}->{end})) {
            return $_->{exit_litteral};
        } elsif ($self->{threshold_label}->{$_->{label}}->{arobase}  == 1 && ($options{value} >= $self->{threshold_label}->{$_->{label}}->{end} && $options{value} <= $self->{threshold_label}->{$_->{label}}->{end})) {
            return $_->{exit_litteral};
        }
    }

    return 'ok';
}

sub trim {
    my ($self, $value) = @_;
    
    $value =~ s/^[ \t]+//;
    $value =~ s/[ \t]+$//;
    return $value;
}

sub continue_to {
    my $self = shift;
    my ($forbidden, $stop1, $not_stop_after) = @_;
    my $value = "";

    while ($self->{perfdata_pos} < $self->{perfdata_size}) {
        if (defined($forbidden) && ${$self->{perfdata_chars}}[$self->{perfdata_pos}] =~ /$forbidden/) {
            return undef;
        }
        if (${$self->{perfdata_chars}}[$self->{perfdata_pos}] =~ /$stop1/) {
            if (!defined($not_stop_after)) {
                return $value;
            }
            if (!($self->{perfdata_pos} + 1 < $self->{perfdata_size} && ${$self->{perfdata_chars}}[$self->{perfdata_pos} + 1] =~ /$not_stop_after/)) {
                $self->{perfdata_pos}++;
                return $value;
            }
            $self->{perfdata_pos}++;
        }

        $value .= ${$self->{perfdata_chars}}[$self->{perfdata_pos}];
        $self->{perfdata_pos}++;
    }

    return $value;
}

sub parse_threshold {
    my $self = shift;

    @{$self->{perfdata_chars}} = split //, $self->trim($_[0]);
    $self->{perfdata_pos} = 0;
    $self->{perfdata_size} = scalar(@{$self->{perfdata_chars}});

    my $neg = 1;
    my $value_tmp = "";

    my $arobase = 0;
    my $infinite_neg = 0;
    my $infinite_pos = 0;
    my $value_start = "";
    my $value_end = "";
    my $global_status = 1;
    
    if (defined(${$self->{perfdata_chars}}[$self->{perfdata_pos}]) && ${$self->{perfdata_chars}}[$self->{perfdata_pos}] eq "@") {
        $arobase = 1;
        $self->{perfdata_pos}++;
    }

    if (defined(${$self->{perfdata_chars}}[$self->{perfdata_pos}]) && ${$self->{perfdata_chars}}[$self->{perfdata_pos}] eq "~") {
        $infinite_neg = 1;
        $self->{perfdata_pos}++;
    } else {
        if (defined(${$self->{perfdata_chars}}[$self->{perfdata_pos}]) && ${$self->{perfdata_chars}}[$self->{perfdata_pos}] eq "-") {
            $neg = -1;
            $self->{perfdata_pos}++;
        }
        $value_tmp = $self->continue_to(undef, "[^0-9\.,]");
        if (defined($value_tmp) && $value_tmp ne "") {
            $value_tmp =~ s/,/./g;
            $value_tmp = $value_tmp * $neg;
        }
        $neg = 1;
    }

    if (defined(${$self->{perfdata_chars}}[$self->{perfdata_pos}]) && ${$self->{perfdata_chars}}[$self->{perfdata_pos}] eq ":") {
        if ($value_tmp ne "") {
            $value_start = $value_tmp;
        } else {
            $value_start = 0;
        }
        $self->{perfdata_pos}++;

        if (defined(${$self->{perfdata_chars}}[$self->{perfdata_pos}]) && ${$self->{perfdata_chars}}[$self->{perfdata_pos}] eq "-") {
            $neg = -1;
            $self->{perfdata_pos}++;
        }
        $value_end = $self->continue_to(undef, "[^0-9\.,]");
        if (defined($value_tmp) && $value_end ne "") {
            $value_end =~ s/,/./g;
            $value_end = $value_end * $neg;
        } else {
            $infinite_pos = 1;
        }
    } else {
        $value_start = 0;
        $value_end = $value_tmp;
    }
    
    my $value = $self->continue_to(undef, "[ \t;]");
    if ($value ne '') {
        $global_status = 0;
    }

    if ($infinite_neg == 1) {
        $value_start = '-1e500';
    }
    if ($infinite_pos == 1) {
        $value_end = '1e500';
    }

    return ($global_status, $value_start, $value_end, $arobase, $infinite_neg, $infinite_pos);
}

sub change_bytes {
    my ($self, %options) = @_;

    my $unit = defined($options{network}) ? 'b' : 'B';
    my $divide = defined($options{network}) ? 1000 : 1024;
    
    if (($options{value} / $divide) >= 1) {
        $options{value} = $options{value} / $divide;
        $unit = defined($options{network}) ? 'Kb' : 'KB';
    }
    if (($options{value} / $divide) >= 1) {
        $options{value} = $options{value} / $divide;
        $unit = defined($options{network}) ? 'Mb' : 'MB';
    }
    if (($options{value} / $divide) >= 1) {
        $value = $options{value} / $divide;
        $unit = defined($options{network}) ? 'Gb' : 'GB';
    }
    return (sprintf("%.2f", $options{value}), $unit);
}

1;

__END__

=head1 NAME

Perfdata class

=head1 SYNOPSIS

-

=head1 DESCRIPTION

B<perfdata>.

=cut
