
package centreon::plugins::statefile;
use Data::Dumper;
use vars qw($datas);

sub new {
    my ($class, %options) = @_;
    my $self  = {};
    bless $self, $class;

    if (defined($options{options})) {
        $options{options}->add_options(arguments =>
                                {
                                  "memcached:s"         => { name => 'memcached' },
                                  "statefile-dir:s"     => { name => 'statefile_dir', default => '/var/lib/centreon/centplugins' },
                                });
        $options{options}->add_help(package => __PACKAGE__, sections => 'RETENTION OPTIONS', once => 1);
    }
    
    $self->{output} = $options{output};
    $self->{datas} = {};
    $self->{memcached} = undef;
    $self->{statefile_dir} = undef;
    
    return $self;
}

sub check_options {
    my ($self, %options) = @_;

    if (defined($options{option_results}) && defined($options{option_results}->{memcached})) {
        $self->{memcached} = $options{option_results}->{memcached};
    }
    $self->{statefile_dir} = $options{option_results}->{statefile_dir};
}

sub read {
    my ($self, %options) = @_;
    $self->{statefile_dir} = defined($options{statefile_dir}) ? $options{statefile_dir} : $self->{statefile_dir};
    $self->{statefile} =  defined($options{statefile}) ? $options{statefile} : $self->{statefile};

    if (! -e $self->{statefile_dir} . "/" . $self->{statefile}) {
        if (! -w $self->{statefile_dir}) {
        $self->{output}->add_option_msg(short_msg =>  "Cannot write statefile '" . $self->{statefile_dir} . "/" . $self->{statefile} . "'. Need write permissions on directory.");
            $self->{output}->option_exit();
        }
        return 0;
    } elsif (! -w $self->{statefile_dir} . "/" . $self->{statefile}) {
        $self->{output}->add_option_msg(short_msg => "Cannot write statefile '" . $self->{statefile_dir} . "/" . $self->{statefile} . "'. Need write permissions on file.");
        $self->{output}->option_exit();
    }
    
    unless (my $return = do $self->{statefile_dir} . "/" . $self->{statefile}) {
        if ($@) {
            $self->{output}->add_option_msg(short_msg => "Couldn't parse '" . $self->{statefile_dir} . "/" . $self->{statefile} . "': $@");
            $self->{output}->option_exit();
        }
        unless (defined($return)) {
            $self->{output}->add_option_msg(short_msg => "Couldn't do '" . $self->{statefile_dir} . "/" . $self->{statefile} . "': $!");
            $self->{output}->option_exit();
        }
        unless ($return) {
            $self->{output}->add_option_msg(short_msg => "Couldn't run '" . $self->{statefile_dir} . "/" . $self->{statefile} . "': $!");
            $self->{output}->option_exit();
        }
    }
    $self->{datas} = $datas;
    $datas = {};

    return 1;
}

sub get_string_content {
    my ($self, %options) = @_;

    return Data::Dumper::Dumper($self->{datas});
}

sub get {
    my ($self, %options) = @_;

    if (defined($self->{datas}->{$options{name}})) {
        return $self->{datas}->{$options{name}};
    }
    return undef;
}

sub write {
    my ($self, %options) = @_;

    open FILE, ">", $self->{statefile_dir} . "/" . $self->{statefile};
    print FILE Data::Dumper->Dump([$options{data}], ["datas"]);
    close FILE;
}

1;

__END__

=head1 NAME

Statefile class

=head1 SYNOPSIS

-

=head1 RETENTION OPTIONS

=over 8

=item B<--memcached>

Memcached server to use (only one server).

=item B<--statefile-dir>

Directory for statefile (Default: '/var/lib/centreon/centplugins').

=back

=head1 DESCRIPTION

B<statefile>.

=cut
