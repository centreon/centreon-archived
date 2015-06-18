#!/bin/bash

cp init_data.sh init_data_hardcoded.sh
sed -i 's/\$_HOSTSNMPVERSION\$/v2c/' init_data_hardcoded.sh
sed -i 's/\$_HOSTSNMPCOMMUNITY\$/public/' init_data_hardcoded.sh
sed -i 's#\$USER1\$#/usr/lib/nagios/plugins#' init_data_hardcoded.sh
