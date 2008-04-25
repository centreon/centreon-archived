#!/bin/sh
###################################################################
# Centreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
#
# Developped by : Julien Mathis - Romain Le Merlus 
# Contribute	: Guillaume Watteeux - Maximilien Bersoult
#
###################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
#    For information : infos@centreon.com
####################################################################
## Need to find a correct wrapper to exec
#TEXTDOMAINDIR=`pwd`/locale
#export TEXTDOMAINDIR
#TEXTDOMAIN=install.sh
#export TEXTDOMAIN

BASE_DIR=`dirname $0`
## set directory
OLD_DIR=$PWD ; cd $BASE_DIR ; BASE_DIR=$PWD ; cd $OLD_DIR
if [ -z ${BASE_DIR#/} ] ; then
	echo -e "I think is not good to have Centreon source on slash"
	exit 1
fi
INSTALL_DIR=$BASE_DIR/libinstall
INSTALL_VARS_DIR=$BASE_DIR/varinstall

## Load local gettext.sh (not dependancy
. $INSTALL_DIR/gettext.sh

## log default vars 
. $INSTALL_VARS_DIR/vars

## load all functions used in this script
. $INSTALL_DIR/functions


## Valid if you are root 
USERID=`id -u`
if [ $USERID != 0 ]; then
    echo -e "`gettext \"You must exec with root user\"`"
    exit 1
fi

silent_install=0
upgrade=0
user_install_vars=""

## Getopts :)
while getopts "f:u:h" Options
do
	case ${Options} in
		f )	silent_install=1
			user_install_vars="${OPTARG}" ;;
		u )	silent_install=1
			CENTREON_CONF="${OPTARG}"
			upgrade=1 ;;
		\?)	usage ;;
		h )	usage ;;
		* )	usage ;;
	esac
done

version="2.0"

cat << __EOT__
###############################################################################
#                                                                             #
#                         Centreon (www.centreon.com)                         #
#                          Thanks for using Centreon                          #
#                                                                             #
#                                    v$version 				    #
#                                                                             #
#                             infos@centreon.com                         #
#                                                                             #
#                   Make sure you have installed and configured               #
#                   sudo - sed - php - apache - rrdtool - mysql               #
#                                                                             #
###############################################################################
__EOT__


## init LOG_FILE
# backup old log file...
[ ! -d $LOG_DIR ] && mkdir -p $LOG_DIR
if [ -e $LOG_FILE ] ; then
	mv $LOG_FILE $LOG_FILE.`date +%Y%m%d-%H%M%S`
fi
echo " " > $LOG_FILE

## Test all binaries
BINARIES="rm cp mv chmod chown echo cat more mkdir find sed"

echo "------------------------------------------------------------------------"
echo -e "\t`gettext \"Checking all needed binaries\"`"
echo "------------------------------------------------------------------------"
binary_fail=0
for binary in $BINARIES; do
	pathfind $binary
	if [ $? -eq 0 ] ; then
		echo_success "${binary}" "$ok"
	else 
		echo_failure "${binary}" "$fail"
		log "ERR" "`gettext \"\$binary not found in \\$PATH \"`"
		binary_fail=1

	fi
done

if [ $binary_fail -eq 1 ] ; then
	echo_info "`gettext \"Please check fail binary and retry\"`"
	exit 1
fi

if [ $silent_install -ne 1 ] ; then 
	echo -e "\n`gettext \"You will now read Centreon Licence.\\n\\tPress enter to continue.\"`"
	read 
	tput clear 
	more $BASE_DIR/LICENSE

	yes_no_default "`gettext \"Do you accept GPL license ?\"`" 
	if [ $? -ne 0 ] ; then 
		echo_info "`gettext \"Your not agree with GPL license ? Okay... have a nice day.\"`"
		exit 1
	fi
else 
	. $user_install_vars
fi

## Check if is an upgrade or new install
## Use this on silent install ???
# Check for old configfile
# use for centreon1.x upgrade
if [ ! -z "`ls $CENTREON_CONF_1_4 2>/dev/null`" ] ; then 
	count=0
	CENTREON_CONF=""
	for conffile in $CENTREON_CONF_1_4 ; do
		CENTREON_CONF[$count]=$conffile
		let "count += 1"
	done
	
	if [ $count -gt 1 ] ; then
		echo_failure "`gettext \"You are $count config file, please select one\"`"
		select_in_array "CENTREON_CONF" ${CENTREON_CONF[@]}
	fi
fi

if [ -e $CENTREON_CONF ] ; then
	echo "------------------------------------------------------------------------"
	echo -e "\t`gettext \"Detecting old installation\"`"
	echo "------------------------------------------------------------------------"
	echo -e "\n\n"
	echo_success "`gettext \"Finding configuration file \$CENTREON_CONF :\"`" "$ok"
	log "INFO" "`gettext \"Old config found in \"`$CENTREON_CONF"
	echo -e "`gettext \"You already seem to have to install Centreon.\"`\n"
	yes_no_default "`gettext \"Do you want use last Centreon install parameters ?\"`" "$yes"
	if [ $? -eq 0 ] ; then
		echo_passed "\n`gettext \"Using \$CENTREON_CONF : \"`" "$passed"
		log "INFO" "`gettext \"Import old install config\"`"
		. $CENTREON_CONF
	fi
fi

echo "------------------------------------------------------------------------"
echo -e "\t`gettext \"Please choose what do you want to install\"`"
echo "------------------------------------------------------------------------"

## init install process
# I prefer split install script.
[ -z $PROCESS_CENTREON_WWW ] && PROCESS_CENTREON_WWW=0
[ -z $PROCESS_CENTSTORAGE ] && PROCESS_CENTSTORAGE=0
[ -z $PROCESS_CENTCORE ] && PROCESS_CENTCORE=0
[ -z $PROCESS_CENTREON_PLUGINS ] && PROCESS_CENTREON_PLUGINS=0
[ -z $PROCESS_CENTREON_SNMP_TRAPS ] && PROCESS_CENTREON_SNMP_TRAPS=0

## resquest centreon_www
if [ $PROCESS_CENTREON_WWW -eq 0 ] ; then 
	yes_no_default "`gettext \"Do you want to install Centreon Web Front\"`"
	if [ $? -eq 0 ] ; then
		PROCESS_CENTREON_WWW=1
		log "INFO" "`gettext \"You select to install Centreon Web Front\"`"
		## CentStorage dependancy
		PROCESS_CENTSTORAGE=1
	fi
fi

## resquest centreon_centstorage
# CentWeb/CentStorage dependancy
[ $PROCESS_CENTREON_WWW -eq 1 ] && PROCESS_CENTSTORAGE=1
if [ $PROCESS_CENTSTORAGE -eq 0 ] ; then 
	yes_no_default "`gettext \"Do you want to install Centreon CentStorage\"`"
	if [ $? -eq 0 ] ; then
		PROCESS_CENTSTORAGE=1
		log "INFO" "`gettext \"You select to install CentStorage\"`"
	fi
fi

## resquest centreon_centcore
if [ $PROCESS_CENTCORE -eq 0 ] ; then 
	yes_no_default "`gettext \"Do you want to install Centreon CentCore\"`"
	if [ $? -eq 0 ] ; then
		PROCESS_CENTCORE=1
		log "INFO" "`gettext \"You select to install CentCore\"`"
	fi
fi

## resquest centreon_plugins
if [ $PROCESS_CENTREON_PLUGINS -eq 0 ] ; then 
	yes_no_default "`gettext \"Do you want to install Centreon Nagios Plugins\"`"
	if [ $? -eq 0 ] ; then
		PROCESS_CENTREON_PLUGINS=1
		log "INFO" "`gettext \"You select to install Centreon Nagios Plugins\"`"
	fi
fi

## resquest centreon_snmp_traps
if [ $PROCESS_CENTREON_SNMP_TRAPS -eq 0 ] ; then 
	yes_no_default "`gettext \"Do you want to install Centreon Snmp Traps process\"`"
	if [ $? -eq 0 ] ; then
		PROCESS_CENTREON_SNMP_TRAPS=1
		log "INFO" "`gettext \"You select to install Centreon Snmp Traps process\"`"
	fi
fi

## Start Centreon Web Front install
if [ $PROCESS_CENTREON_WWW -eq 1 ] ; then 
	. $INSTALL_DIR/CentWeb.sh
fi

## Start CentStorage install
if [ $PROCESS_CENTSTORAGE -eq 1 ] ; then
	. $INSTALL_DIR/CentStorage.sh
fi

## Start CentCore install
if [ $PROCESS_CENTCORE -eq 1 ] ; then
	. $INSTALL_DIR/CentCore.sh
fi

## Start CentPlugins install
if [ $PROCESS_CENTREON_PLUGINS -eq 1 ] ; then
	. $INSTALL_DIR/CentPlugins.sh
fi
exit 0
