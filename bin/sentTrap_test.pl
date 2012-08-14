#!/usr/bin/perl

use strict;
use Net::SNMP qw(:ALL);

my ($snmp_session, $error);
my $snmp_destination = "localhost";
my $snmp_community = "public";
my $snmp_version = "snmpv2c";
my $snmp_port = 162;
my $timestamp = time();
my $date = "date test";
my $severity = "critical severiy";
my $host_name = "Host-Traps-Receiver 'test'";
my $alarm_name = 'my alarm name "test2" "test3"';
my $value = "value";

my @oid_value;
my $oid_inform = '.1.3.6.1.4.1.12182.5.2.3.6';

push @oid_value,($oid_inform.".1", OCTET_STRING, $date);
push @oid_value,($oid_inform.".2", OCTET_STRING, $host_name);
push @oid_value,($oid_inform.".3", OCTET_STRING, $severity);
push @oid_value,($oid_inform.".4", OCTET_STRING, $alarm_name);
push @oid_value,($oid_inform.".5", OCTET_STRING, $value);
push @oid_value,($oid_inform.".6", OCTET_STRING, 4);

unshift @oid_value,('1.3.6.1.6.3.1.1.4.1.0', OBJECT_IDENTIFIER, '.1.3.6.1.4.1.12182.5.2.3.1.2.43.2');
unshift @oid_value,('1.3.6.1.2.1.1.3.0', TIMETICKS, $timestamp);

($snmp_session, $error) = Net::SNMP->session(-hostname    => $snmp_destination,
                        -community    => $snmp_community,
                        -port        => $snmp_port,
                                             -version   => $snmp_version);
if (!defined($snmp_session)) {
    print("UNKNOWN: SNMP Session : $error\n");
    exit 1;
}

if (defined($snmp_session)) {
    $snmp_session->translate(Net::SNMP->TRANSLATE_NONE);
}


use Data::Dumper;
#print Data::Dumper::Dumper(\@oid_value);
my $result = $snmp_session->snmpv2_trap(-varbindlist => \@oid_value);
$snmp_session->close();
