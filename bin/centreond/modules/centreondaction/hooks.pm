
package modules::centreondaction::hooks;

use warnings;
use strict;

my $config;
my $module_id = 'centreondaction';
my $events = [
    
];

sub register {
    my (%options) = @_;
    
    $config = $options{config};
    return ($events, $module_id);
}

sub init {
    my (%options) = @_;

}

sub routing {
    my (%options) = @_;

}

sub gently {
    my (%options) = @_;

}

sub kill {
    my (%options) = @_;

}

sub kill_internal {
    my (%options) = @_;

}

sub check {
    my (%options) = @_;

    return 0;
}

1;
