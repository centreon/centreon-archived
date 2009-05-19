#!/bin/bash

sudo /etc/init.d/centstorage stop
sleep 10
/usr/local/centreon/bin/centreonPurge
sudo /etc/init.d/centstorage start

exit 0
