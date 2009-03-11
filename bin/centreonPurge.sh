#! /bin/sh

/etc/init.d/centcore stop
/etc/init.d/centstorage stop
sleep 10
/usr/local/centreon/bin/centreonPurge
sleep 10
/etc/init.d/centcore start
/etc/init.d/centstorage start
