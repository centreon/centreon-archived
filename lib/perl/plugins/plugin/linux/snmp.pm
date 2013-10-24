package plugin::linux::snmp;

use strict;
use warnings;
use base qw(centreon::plugins::script_snmp);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    # $options->{options} = options object

    $self->{version} = '0.1';
    %{$self->{modes}} = (
                         'traffic' => 'snmp_standard::mode::traffic',
                         'storage' => 'snmp_standard::mode::storage',
                         'load' => 'snmp_standard::mode::loadaverage'
                         );
    #$self->{default} = [{option_mode => 'traffic', option_name => 'warning', option_value => '-1'}];

    return $self;
}

1;

__END__

=head1 PLUGIN DESCRIPTION

Check Linux Operating systems in SNMP.

=cut
