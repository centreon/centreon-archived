#!/bin/sh
#
# $Id: trap_handler.sh,v 1.0 2006/06/30 12:30:00 Nicolas Cordier for Merethis $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Nicolas Cordier for Merethis
#
# The Software is provided to you AS IS and WITH ALL FAULTS.
# OREON makes no representation and gives no warranty whatsoever,
# whether express or implied, and without limitation, with regard to the quality,
# safety, contents, performance, merchantability, non-infringement or suitability for
# any particular or intended purpose of the Software found on the OREON web site.
# In no event will OREON be liable for any direct, indirect, punitive, special,
# incidental or consequential damages however they may arise and even if OREON has
# been previously advised of the possibility of such damages.

read host
read ip
vars=""

while read oid val
do
vars="$vars, $oid = $val"
done

exec=$1
shift
while [ "$1" != "" ]
do
args="$args $1"
shift
done

args="$ip $args $vars"

$exec $args 
