#! /usr/bin/perl -w

use strict;

my $NAGIOS_VAR_DIR = "/srv/nagios/var"; 
my $STATUS_FILE = "status.log"; 
my $DEST_FILE = "status_oreon.log"; 

# Not ';' because some perfparse use ';' in some outputs 
my $SEPARATOR = "#"; 
my $line = "";

open(FILE, "$NAGIOS_VAR_DIR/$STATUS_FILE"); open(DEST, "> 
$NAGIOS_VAR_DIR/$DEST_FILE");

while (<FILE>){
    if ($_ =~ m/^(host)/ || $_ =~ m/^(service)/ || $_ =~ m/^(program)/) {
        $line = $1 . $SEPARATOR;
        $line =~ s/host/h/g;
        $line =~ s/service/s/g;
	$line =~ s/program/p/g;
        while (<FILE>) {
            if ($_ =~ m/^\s*}/) {
                last;
            }
            $_ =~ s/^\s[a-z\_]+=//g;
            chomp $_;
            $line .= $_ . $SEPARATOR;
        }
        print DEST $line . "\n";
    }
}
close(FILE);
close(DEST);

