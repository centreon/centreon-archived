
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

    # Need to save to check multiples times (centos 5 Getopt don't have 'GetOptionsFromArray'
    my @save_argv = @ARGV;
    GetOptions(
       %{$self->{options}}
    );
    @ARGV = @save_argv;
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
