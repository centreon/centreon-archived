#!/bin/bash
#----
## @Synopsis	Install Script for Centreon project
## @Copyright	Copyright 2008, Guillaume Watteeux
## @Copyright	Copyright 2008-2020, Centreon
## @License	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Centreon Install Script
## Use 
## <pre>
## Usage: sh install.sh [OPTION]
## Options:
##  -f	Input file with all variables define (use for with template)
##  -u	Input file with all variables define for update centreon
##  -v	Verbose mode
##  -h	print usage
## </pre>
#----
## Centreon is developed with GPL Licence 2.0
##
## GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
##
## Developed by : Julien Mathis - Romain Le Merlus
## Contributors : Guillaume Watteeux - Maximilien Bersoult
##
## This program is free software; you can redistribute it and/or
## modify it under the terms of the GNU General Public License
## as published by the Free Software Foundation; either version 2
## of the License, or (at your option) any later version.
##
## This program is distributed in the hope that it will be useful,
## but WITHOUT ANY WARRANTY; without even the implied warranty of
## MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
## GNU General Public License for more details.
##
##    For information : infos@centreon.com
#

#----
## Usage information for install.sh
## @Sdtout	Usage information
#----
usage() {
	local program=$0
	echo -e "$(gettext "Usage: $program -f <file>")"
	echo -e "  -i\t$(gettext "install centreon")"
	echo -e "  -f\t$(gettext "file with all variable")"
	echo -e "  -u\t$(gettext "upgrade centreon with specify your directory with instCent* files")"
	echo -e "  -v\t$(gettext "verbose mode")"
	exit 1
}

# define where is a centreon source 
BASE_DIR=$(dirname $0)
## set directory
BASE_DIR=$( cd $BASE_DIR; pwd )
export BASE_DIR
if [ -z "${BASE_DIR#/}" ] ; then
	echo -e "You cannot select the filesystem root folder"
	exit 1
fi
INSTALL_DIR="$BASE_DIR/libinstall"
export INSTALL_DIR
INSTALL_VARS_DIR="$BASE_DIR/varinstall"
export INSTALL_VARS_DIR
PERL_LIB_DIR=`eval "\`perl -V:installvendorlib\`"; echo $installvendorlib`
# for freebsd
if [ "$PERL_LIB_DIR" = "" -o "$PERL_LIB_DIR" = "UNKNOWN" ]; then
    PERL_LIB_DIR=`eval "\`perl -V:installsitelib\`"; echo $installsitelib`
fi
# define a locale directory for use gettext (LC_MESSAGE)
TEXTDOMAINDIR=$BASE_DIR/locale
export TEXTDOMAINDIR
TEXTDOMAIN=install.sh
export TEXTDOMAIN

# init variables
line="------------------------------------------------------------------------"
export line

## log default vars
. $INSTALL_VARS_DIR/vars

## Test if gettext was installed
# I use PATH variable to find
found="0"
OLDIFS="$IFS"
IFS=:
for p in $PATH ; do
    if [ -x "$p/gettext.sh" ]; then
        found="1"
        . $p/gettext.sh
    fi
done
IFS=$OLDIFS
if [ $found -eq 0 ] ; then # if not, use dummy gettext
	PATH="$PATH:$INSTALL_DIR"
fi

## load all functions used in this script
. $INSTALL_DIR/functions

## Use TRAPs to call clean_and_exit when user press
## CRTL+C or exec kill -TERM.
trap clean_and_exit SIGINT SIGTERM

## Define a default log file
LOG_FILE=${LOG_FILE:=log\/install_centreon.log}

## Valid if you are root 
if [ "${FORCE_NO_ROOT:-0}" -ne 0 ]; then
	USERID=$(id -u)
	if [ "$USERID" != "0" ]; then
	    echo -e "$(gettext "You must launch this script using a root user")"
	    exit 1
	fi
fi

_tmp_install_opts="0"
silent_install="0"
upgrade="0"
user_install_vars=""
inst_upgrade_dir=""
use_upgrade_files="0"

#define cinstall options
cinstall_opts=""

## Getopts :)
# When you use options, by default I set silent_install to 1.
while getopts "if:u:hv" Options
do
	case ${Options} in
		i )	silent_install="0"
			_tmp_install_opts="1"
			;;
		f )	silent_install="1"
			user_install_vars="${OPTARG}"
			_tmp_install_opts="1"
			;;
		u )	silent_install="1"
			inst_upgrade_dir="${OPTARG%/}"
			cinstall_opts="$cinstall_opts -f"
			upgrade="1" 
			_tmp_install_opts="1"
			;;
		v )	cinstall_opts="$cinstall_opts -v" 
			# need one variable to parse debug log 
			;;
		\?|h)	usage ; exit 0 ;;
		* )	usage ; exit 1 ;;
	esac
done

if [ "$_tmp_install_opts" -eq 0 ] ; then
	usage
	exit 1
fi

#Export variable for all programs
export silent_install user_install_vars CENTREON_CONF cinstall_opts inst_upgrade_dir upgrade

## init LOG_FILE
# backup old log file...
[ ! -d "$LOG_DIR" ] && mkdir -p "$LOG_DIR"
if [ -e "$LOG_FILE" ] ; then
	mv "$LOG_FILE" "$LOG_FILE.`date +%Y%m%d-%H%M%S`"
fi
# Clean (and create) my log file
${CAT} << __EOL__ > "$LOG_FILE"
__EOL__

# Init GREP,CAT,SED,CHMOD,CHOWN variables
define_specific_binary_vars

${CAT} << __EOT__
###############################################################################
#                                                                             #
#                           Centreon (www.centreon.com)                       #
#                                                                             #
#                               infos@centreon.com                            #
#                                                                             #
#                   Make sure you have installed and configured               #
#         centreon-gorgone - sudo - sed - php - apache - rrdtool - mysql      #
#                                                                             #
###############################################################################
__EOT__

## Test all binaries
BINARIES="rm cp mv ${CHMOD} ${CHOWN} echo more mkdir find ${GREP} ${CAT} ${SED}"

echo "$line"
echo -e "\t$(gettext "Checking all needed binaries")"
echo "$line"

binary_fail="0"
# For the moment, I check if all binary exists in path.
# After, I must look a solution to use complet path by binary
for binary in $BINARIES; do
	if [ ! -e ${binary} ] ; then 
		pathfind "$binary"
		if [ "$?" -eq 0 ] ; then
			echo_success "${binary}" "$ok"
		else 
			echo_failure "${binary}" "$fail"
			log "ERR" "$(gettext "\$binary not found in \$PATH")"
			binary_fail=1
		fi
	else
		echo_success "${binary}" "$ok"
	fi
done

if [ "$silent_install" -ne 1 ] ; then 
	###### Mandatory step
	# ask if gorgone is already installed
	echo -e "\n$line"
	echo -e "\t$(gettext "Check mandatory gorgone service status")"
	echo -e "$line"

	yes_no_default "$(gettext "Is the Gorgone module already installed?")"
	if [ "$?" -ne 0 ] ; then
		echo_failure "\n$(gettext "Gorgone is required.\nPlease install it before launching this script")" "$fail"
		echo -e "\n\t$(gettext "Please read the documentation to manage the Gorgone daemon installation")"
		echo -e "\t$(gettext "Available on github") : https://github.com/centreon/centreon-gorgone"
		echo -e "\t$(gettext "or on the centreon documentation") : https://documentation.centreon.com/\n"
		exit 1
	fi
fi

# Script stop if one binary wasn't found
if [ "$binary_fail" -eq 1 ] ; then
	echo_info "$(gettext "Please check fail binary and retry")"
	exit 1
fi

# When you exec this script without file, you must valid a GPL licence.
if [ "$silent_install" -ne 1 ] ; then 
	echo -e "\n$(gettext "You will now read Centreon Licence.\\n\\tPress enter to continue.")"
	read 
	tput clear 
	more "$BASE_DIR/LICENSE.md"

	yes_no_default "$(gettext "Do you accept GPL license ?")" 
	if [ "$?" -ne 0 ] ; then 
		echo_info "$(gettext "As you did not accept the license, we cannot continue.")"
		log "INFO" "Installation aborted - License not accepted"
		exit 1
	else
		log "INFO" "Accepted the license"
	fi
else 
	if [ "$upgrade" -eq 0 ] ; then
		. $user_install_vars
	fi
fi

if [ "$upgrade" -eq 1 ] ; then
	# Test if instCent* file exist
	if [ "$(ls $inst_upgrade_dir/instCent* | wc -l )" -ge 1 ] ; then
		inst_upgrade_dir=${inst_upgrade_dir%/}
		echo "$line"
		echo -e "\t$(gettext "Detecting old installation")"
		echo "$line"
		echo_success "$(gettext "Finding configuration file in:") $inst_upgrade_dir" "$ok"
		log "INFO" "$(gettext "Old configuration found in ") $(ls $inst_upgrade_dir/instCent*)"
		echo_info "$(gettext "You seem to have an existing Centreon.")\n"
		yes_no_default "$(gettext "Do you want to use the last Centreon install parameters ?")" "$yes"
		if [ "$?" -eq 0 ] ; then
			echo_passed "\n$(gettext "Using: ") $(ls $inst_upgrade_dir/instCent*)" "$ok"
			use_upgrade_files="1"
		fi
	fi
fi

if [ "$silent_install" -ne 1 ] ; then
	echo "$line"
	echo -e "\t$(gettext "Please choose what you want to install")"
	echo "$line"
fi

## init install process
# I prefer split install script.
# 0 = do not install
# 1 = install
# 2 = question in console
[ -z $PROCESS_CENTREON_WWW ] && PROCESS_CENTREON_WWW="2"
## For a moment, isn't possible to install standalone CentStorage daemon
## without CentWeb
[ -z $PROCESS_CENTSTORAGE ] && PROCESS_CENTSTORAGE="0"
[ -z $PROCESS_CENTREON_PLUGINS ] && PROCESS_CENTREON_PLUGINS="2"
[ -z $PROCESS_CENTREON_SNMP_TRAPS ] && PROCESS_CENTREON_SNMP_TRAPS="2"

## install centreon perl lib
if [ ! -d "$PERL_LIB_DIR/centreon/" ] ; then
    mkdir "$PERL_LIB_DIR/centreon/"
    log "INFO" "$(gettext "Created perl library directory")"
fi

## request centreon_www
if [ "$PROCESS_CENTREON_WWW" -eq 2 ] ; then 
	yes_no_default "$(gettext "Do you want to install") : Centreon Web Front"
	if [ "$?" -eq 0 ] ; then
		PROCESS_CENTREON_WWW="1"
		log "INFO" "$(gettext "You chose to install") : Centreon Web Front"
		## CentStorage dependency
		PROCESS_CENTSTORAGE="1"
	fi
fi

## request centreon_plugins
if [ "$PROCESS_CENTREON_PLUGINS" -eq 2 ] ; then 
	yes_no_default "$(gettext "Do you want to install") : Centreon Nagios Plugins"
	if [ "$?" -eq 0 ] ; then
		PROCESS_CENTREON_PLUGINS="1"
		log "INFO" "$(gettext "You chose to install") : Centreon Nagios Plugins"
	fi
fi

## request centreon_snmp_traps
if [ "$PROCESS_CENTREON_SNMP_TRAPS" -eq 2 ] ; then 
	yes_no_default "$(gettext "Do you want to install") : CentreonTrapd process"
	if [ "$?" -eq 0 ] ; then
		PROCESS_CENTREON_SNMP_TRAPS="1"
		log "INFO" "$(gettext "You chose to install") : CentreonTrapd process"
	fi
fi

## Start Centreon Web Front install
if [ "$PROCESS_CENTREON_WWW" -eq 1 ] ; then 
	if [ "$use_upgrade_files" -eq 1 -a -e "$inst_upgrade_dir/instCentWeb.conf" ] ; then
		log "INFO" "$(gettext "Load variables:") $inst_upgrade_dir/instCentWeb.conf"

		. $inst_upgrade_dir/instCentWeb.conf
		if [ -n "$NAGIOS_USER" ]; then
			echo_info "$(gettext "Convert variables for upgrade:")"
			MONITORINGENGINE_USER=$NAGIOS_USER
			[ -n "$NAGIOS_GROUP" ] && MONITORINGENGINE_GROUP=$NAGIOS_GROUP
			[ -n "$NAGIOS_ETC" ] && MONITORINGENGINE_ETC=$NAGIOS_ETC
			[ -n "$NAGIOS_BINARY" ] && MONITORINGENGINE_BINARY=$NAGIOS_BINARY
		fi
	fi
	. $INSTALL_DIR/CentWeb.sh
fi

## Start CentStorage install
if [ "$PROCESS_CENTSTORAGE" -eq 1 ] ; then
	if [ "$use_upgrade_files" -eq 1 -a -e "$inst_upgrade_dir/instCentStorage.conf" ] ; then
		log "INFO" "$(gettext "Load variables:") $inst_upgrade_dir/instCentStorage.conf"

		. $inst_upgrade_dir/instCentStorage.conf
		if [ -n "$NAGIOS_USER" ]; then
			echo_info "$(gettext "Convert variables for upgrade:")"
			MONITORINGENGINE_USER=$NAGIOS_USER
			[ -n "$NAGIOS_GROUP" ] && MONITORINGENGINE_GROUP=$NAGIOS_GROUP
		fi
	fi
	. $INSTALL_DIR/CentStorage.sh
fi

## Start CentPlugins install
if [ "$PROCESS_CENTREON_PLUGINS" -eq 1 ] ; then
	if [ "$use_upgrade_files" -eq 1 -a -e "$inst_upgrade_dir/instCentPlugins.conf" ] ; then
		log "INFO" "$(gettext "Load variables:") $inst_upgrade_dir/instCentPlugins.conf"

		. $inst_upgrade_dir/instCentPlugins.conf
		if [ -n "$NAGIOS_USER" ]; then
			echo_info "$(gettext "Convert variables for upgrade:")"
			MONITORINGENGINE_USER=$NAGIOS_USER
			[ -n "$NAGIOS_GROUP" ] && MONITORINGENGINE_GROUP=$NAGIOS_GROUP
			[ -n "$NAGIOS_ETC" ] && MONITORINGENGINE_ETC=$NAGIOS_ETC
			[ -n "$NAGIOS_PLUGIN" ] && PLUGIN_DIR=$NAGIOS_PLUGIN
		fi
	fi
	. $INSTALL_DIR/CentPlugins.sh
fi

## Start CentPluginsTraps install
if [ "$PROCESS_CENTREON_SNMP_TRAPS" -eq 1 ] ; then
	if [ "$use_upgrade_files" -eq 1 -a -e "$inst_upgrade_dir/instCentPlugins.conf" ] ; then
		log "INFO" "$(gettext "Load variables:") $inst_upgrade_dir/instCentPlugins.conf"

		. $inst_upgrade_dir/instCentPlugins.conf
		if [ -n "$NAGIOS_USER" ]; then
			echo_info "$(gettext "Convert variables for upgrade:")"
			MONITORINGENGINE_USER=$NAGIOS_USER
			[ -n "$NAGIOS_GROUP" ] && MONITORINGENGINE_GROUP=$NAGIOS_GROUP
			[ -n "$NAGIOS_ETC" ] && MONITORINGENGINE_ETC=$NAGIOS_ETC
			[ -n "$NAGIOS_PLUGIN" ] && PLUGIN_DIR=$NAGIOS_PLUGIN
		fi
	fi
	. $INSTALL_DIR/CentPluginsTraps.sh
fi

## Purge working directories
purge_centreon_tmp_dir "silent"
server=$(hostname -f)

# Replace global variables

${CAT} << __EOT__

###############################################################################
#                                                                             #
#                         Thanks for using Centreon.                          #
#                          -----------------------                            #
#                                                                             #
#                 Go to the URL : http://$server/centreon/                    #
#                   	     to finish the setup                              #
#                                                                             #
#                Please read the documentation available here :               #
#                         documentation.centreon.com                          #
#                                                                             #
#      ------------------------------------------------------------------     #
#                                                                             #
#         Report bugs at https://github.com/centreon/centreon/issues          #
#                                                                             #
#                        Contact : contact@centreon.com                       #
#                          http://www.centreon.com                            #
#                                                                             #
#                          -----------------------                            #
#              For security issues, please read our security policy           #
#              https://github.com/centreon/centreon/security/policy           #
#                                                                             #
###############################################################################

__EOT__


exit 0
