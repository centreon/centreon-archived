package plugin::os::linux::snmp;

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
                         'cpu' => 'snmp_standard::mode::cpu',
                         'load' => 'snmp_standard::mode::loadaverage',
                         'processcount' => 'snmp_standard::mode::processcount',
                         'storage' => 'snmp_standard::mode::storage',
                         'traffic' => 'snmp_standard::mode::traffic',
                         'uptime' => 'snmp_standard::mode::uptime',
                         );
    #$self->{default} = [{option_mode => 'traffic', option_name => 'warning', option_value => '-1'}];

    return $self;
}

1;

__END__

=head1 PLUGIN DESCRIPTION

Check Linux operating systems in SNMP.

=cut
