#!/usr/bin/env bash

##
## Copyright 2008 Guillaume Watteeux
## Copyright 2016 Centreon
##
## This file is part of Centreon Web.
##
## Centreon Web is free software: you can redistribute it and/or
## modify it under the terms of the GNU General Public License version 2
## as published by the Free Software Foundation.
##
## Centreon Web is distributed in the hope that it will be useful,
## but WITHOUT ANY WARRANTY; without even the implied warranty of
## MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
## General Public License for more details.
##
## You should have received a copy of the GNU General Public License
## along with Centreon Web. If not, see
## <http://www.gnu.org/licenses/>.
##

# Debug
#set -x

# Define centreon version.
version="2.7.1"

# Print script usage.
function usage() {
	local program=$0
	echo -e "$(gettext "Usage: $program -f <file>")"
	echo -e "  -i\t$(gettext "Install Centreon")"
	echo -e "  -f\t$(gettext "File with installation variables set")"
	echo -e "  -u\t$(gettext "Upgrade Centreon by providing your directory contaning instCent* files")"
	echo -e "  -v\t$(gettext "Verbose mode")"
	exit 1
}

# Define where are Centreon sources.
BASE_DIR=$(dirname $0)
BASE_DIR=$(cd $BASE_DIR; pwd)
export BASE_DIR
if [ -z "${BASE_DIR#/}" ] ; then
	echo -e "I think it is not right to have Centreon source on slash"
	exit 1
fi
INSTALL_DIR="$BASE_DIR/libinstall"
export INSTALL_DIR
INSTALL_VARS_DIR="$BASE_DIR/varinstall"
export INSTALL_VARS_DIR
PERL_LIB_DIR=`eval "\`perl -V:installvendorlib\`"; echo $installvendorlib`
# For FreeBSD.
if [ "$PERL_LIB_DIR" = "" -o "$PERL_LIB_DIR" = "UNKNOWN" ]; then
    PERL_LIB_DIR=`eval "\`perl -V:installsitelib\`"; echo $installsitelib`
fi

# Get default variables.
. "$INSTALL_VARS_DIR/vars"
line="------------------------------------------------------------------------"
export line

# Define a locale directory to use gettext (LC_MESSAGE).
TEXTDOMAINDIR=$BASE_DIR/locale
export TEXTDOMAINDIR
TEXTDOMAIN=install.sh
export TEXTDOMAIN

# Try to find gettext.
found="0"
OLDIFS="$IFS"
IFS=:
for p in $PATH ; do
	[ -x "$p/gettext" ] && found="1"
done
IFS=$OLDIFS
# If found, use official script.
if [ $found -eq 1 ] ; then
	. $INSTALL_DIR/gettext.sh
# Otherwise use dummy script.
else
	PATH="$PATH:$INSTALL_DIR"
fi

# Load all functions used in this script.
. "$INSTALL_DIR/functions"

# Use TRAPs to call clean_and_exit when user press CRTL+C or exec kill -TERM.
trap clean_and_exit SIGINT SIGTERM

# Define a default log file.
LOG_FILE=${LOG_FILE:=log\/install_centreon.log}

# Valid if you are root.
if [ "${FORCE_NO_ROOT:-0}" -ne 0 ]; then
	USERID=$(id -u)
	if [ "$USERID" != "0" ]; then
	    echo -e "$(gettext "You must run this script as root user")"
	    exit 1
	fi
fi

# Installation-related variables.
_tmp_install_opts="0"
silent_install="0"
upgrade="0"
user_install_vars=""
inst_upgrade_dir=""
use_upgrade_files="0"
cinstall_opts=""

# Getopts :)
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

# Export variable for all programs.
export silent_install user_install_vars cinstall_opts inst_upgrade_dir

# Backup old log file and create a new one.
[ ! -d "$LOG_DIR" ] && mkdir -p "$LOG_DIR"
if [ -e "$LOG_FILE" ] ; then
	mv "$LOG_FILE" "$LOG_FILE.`date +%Y%m%d-%H%M%S`"
fi
${CAT} << __EOL__ > "$LOG_FILE"
__EOL__

# Init GREP,CAT,SED,CHMOD,CHOWN variables
define_specific_binary_vars

${CAT} << __EOT__
###############################################################################
#                                                                             #
#                         Centreon (www.centreon.com)                         #
#                          Thanks for using Centreon                          #
#                                                                             #
#                                    v$version                                   #
#                                                                             #
#                               infos@centreon.com                            #
#                                                                             #
#                   Make sure you have installed and configured               #
#                   sudo - sed - php - apache - rrdtool - mysql               #
#                                                                             #
###############################################################################
__EOT__

# Test base binaries.
BINARIES="rm cp mv ${CHMOD} ${CHOWN} echo more mkdir find ${GREP} ${CAT} ${SED}"
echo "$line"
echo -e "\t$(gettext "Checking base binaries")"
echo "$line"
binary_fail="0"
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
if [ "$binary_fail" -eq 1 ] ; then
	echo_info "$(gettext "Please install missing binaries and retry")"
	exit 1
fi

# When you exec this script without file, you must validate the GPLv2 licence.
if [ "$silent_install" -ne 1 ] ; then
	echo -e "\n$(gettext "You will now read Centreon Licence (GPLv2).\\n\\tPress enter to continue.")"
	read
	tput clear
	more "$BASE_DIR/LICENSE"
	yes_no_default "$(gettext "Do you accept the GPLv2 license ?")"
	if [ "$?" -ne 0 ] ; then
		echo_info "$(gettext "You do not agree to GPLv2 license ? Okay... have a nice day.")"
		exit 1
	else
		log "INFO" "$(gettext "You accepted GPLv2 license")"
	fi
else
	if [ "$upgrade" -eq 0 ] ; then
		. $user_install_vars
	fi
fi

# Check if is an upgrade or new install.
if [ "$upgrade" -eq 1 ] ; then
	# Test if instCent* file exist
	if [ "$(ls $inst_upgrade_dir/instCent* | wc -l )" -ge 1 ] ; then
		inst_upgrade_dir=${inst_upgrade_dir%/}
		echo "$line"
		echo -e "\t$(gettext "Detecting previous installation")"
		echo "$line"
		echo_success "$(gettext "Found previous configuration files in: ") $inst_upgrade_dir" "$ok"
		log "INFO" "$(gettext "Found previous configuration files ") $(ls $inst_upgrade_dir/instCent*)"
		echo_info "$(gettext "You seem to have an existing Centreon.")\n"
		yes_no_default "$(gettext "Do you want to use the previous Centreon installation parameters ?")" "$yes"
		if [ "$?" -eq 0 ] ; then
			echo_passed "\n$(gettext "Using: ") $(ls $inst_upgrade_dir/instCent*)"
			use_upgrade_files="1"
		fi
	fi
fi

if [ "$silent_install" -ne 1 ] ; then
	echo "$line"
	echo -e "\t$(gettext "Please choose what you want to install")"
	echo "$line"
fi

# Install process starts.
dir_test_create "$PERL_LIB_DIR/centreon"
if [ $? -ne 0 ] ; then
	echo "Aborting installation."
	exit 1
fi

# Install Centreon Plugins ?
# 0 = do not install
# 1 = install
# 2 = question in console
[ -z $PROCESS_CENTREON_PLUGINS ] && PROCESS_CENTREON_PLUGINS="2"
if [ "$PROCESS_CENTREON_PLUGINS" -eq 2 ] ; then
	yes_no_default "$(gettext "Do you want to install") Centreon Nagios Plugins ?"
	if [ "$?" -eq 0 ] ; then
		PROCESS_CENTREON_PLUGINS="1"
		log "INFO" "$(gettext "You chose to install") Centreon Nagios Plugins"
	fi
fi

# Start Centreon Web Front install.
if [ "$use_upgrade_files" -eq 1 -a -e "$inst_upgrade_dir/instCentWeb.conf" ] ; then
	log "INFO" "$(gettext "Load variables:") $inst_upgrade_dir/instCentWeb.conf"
	. $inst_upgrade_dir/instCentWeb.conf
	if [ -n "$NAGIOS_USER" ]; then
		echo_info "$(gettext "Convert variables for upgrade:")"
		MONITORINGENGINE_USER=$NAGIOS_USER
		[ -n "$NAGIOS_GROUP" ] && MONITORINGENGINE_GROUP=$NAGIOS_GROUP
		[ -n "$NAGIOS_ETC" ] && MONITORINGENGINE_ETC=$NAGIOS_ETC
		[ -n "$NAGIOS_BINARY" ] && MONITORINGENGINE_BINARY=$NAGIOS_BINARY
		[ -n "$NAGIOS_INIT_SCRIPT" ] && MONITORINGENGINE_INIT_SCRIPT=$NAGIOS_INIT_SCRIPT
	fi
fi
. $INSTALL_DIR/CentWeb.sh

# Start CentStorage install.
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

# Start CentCore install
if [ "$use_upgrade_files" -eq 1 -a -e "$inst_upgrade_dir/instCentCore.conf" ] ; then
	log "INFO" "$(gettext "Load variables:") $inst_upgrade_dir/instCentCore.conf"
	. $inst_upgrade_dir/instCentCore.conf
	if [ -n "$NAGIOS_USER" ]; then
		echo_info "$(gettext "Convert variables for upgrade:")"
		MONITORINGENGINE_USER=$NAGIOS_USER
		[ -n "$NAGIOS_GROUP" ] && MONITORINGENGINE_GROUP=$NAGIOS_GROUP
		[ -n "$NAGIOS_ETC" ] && MONITORINGENGINE_ETC=$NAGIOS_ETC
	fi
fi
. $INSTALL_DIR/CentCore.sh

# Start CentPluginsTraps install.
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

# Start Centreon Plugins install.
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

# Purge working directories.
purge_centreon_tmp_dir "silent"
server=$(hostname -f)

${CAT} << __EOT__
###############################################################################
#                                                                             #
#                 Go to the URL : http://$server/centreon/                    #
#                   	     to finish the setup                              #
#                                                                             #
#           Report bugs at https://github.com/centreon/centreon/issues        #
#           Read documentation at https://documentation.centreon.com          #
#                                                                             #
#                         Thanks for using Centreon.                          #
#                          -----------------------                            #
#                        Contact : infos@centreon.com                         #
#                          http://www.centreon.com                            #
#                                                                             #
###############################################################################
__EOT__

exit 0
