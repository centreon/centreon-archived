#!/bin/sh
###################################################################
# Centreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
#
# Developped by : Julien Mathis - Romain Le Merlus 
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
#    For information : infos@oreon-project.org
####################################################################

USERID=`id -u`
if [ $USERID != 0 ]; then
    echo "You must exec with root user"
    exit 1
fi

DEFAULT_INSTALL_DIR_NAGIOS="/usr/local/nagios"
DEFAULT_INSTALL_DIR_CENTREON="/usr/local/centreon"
DEFAULT_NAGIOS_ETC=/usr/local/nagios/etc
DEFAULT_NAGIOS_PLUGIN=/usr/local/nagios/libexec
DEFAULT_NAGIOS_IMG=/usr/local/nagios/share/images/logos
DEFAULT_NAGIOS_BIN=/usr/local/nagios/bin
DEFAULT_NAGIOS_VAR=/usr/local/nagios/var
DEFAULT_RRD_PERL=/usr/lib/perl5
DEFAULT_SUDO_FILE=/etc/sudoers
DEFAULT_WEB_USER=www-data
DEFAULT_WEB_GROUP=www-data
DEFAULT_NAGIOS_USER=nagios
DEFAULT_NAGIOS_GROUP=nagios
DEFAULT_BIN_RRDTOOL=/usr/bin/rrdtool
DEFAULT_BIN_MAIL=/usr/bin/mail
DEFAULT_PEAR_PATH=/usr/share/php

TMPDIR=/tmp/centreon-setup

cat <<EOF
###############################################################################
#                                                                             #
#                         Centreon (www.centreon.com)                         #
#                          Thanks for using Centreon                          #
#                                                                             #
#                                    v 2.x                                    #
#                                                                             #
#                             infos@oreon-project.org                         #
#                                                                             #
#                   Make sure you have installed and configured               #
#                   sudo - sed - php - apache - rrdtool - mysql               #
#                                                                             #
###############################################################################
EOF

# Load install script functions
if [ -z "$BASH" ]; then # Test if BASH is in path
    if ! which bash > /dev/null 2>&1; then
	echo "Install bash and try `bash install.sh`."
    fi # Exit if we are not in BASH
    echo "Error: The script must be run with BASH shell. Try:"
    echo "# bash install.sh"
    exit 1
fi
. functions

##
## VARIABLES
##
## Make sure you know what you do if you modify it !!

SRC_OREON="oreon_src"
PLUGINS_DIR="Plugins/"
CENTREON_CONF="/etc/centreon-install.conf"
PWD=`pwd`
LOG_FILE="$PWD/centreon-install.log"

RM=`which rm`
CP=`which cp`
MV=`which mv`
CHMOD=`which chmod`
CHOWN=`which chown`
ECHO=`which echo`
CAT=`which cat`
MORE=`which more`
MKDIR=`which mkdir`
FIND=`which find`
SED=`which sed`

date > $LOG_FILE

test_yes_or_not(){
    if [ $1 != "y" ] && [ $1 != "n" ] && [ ! -z $1 ] ;then
	res=$1
	while [ $res != "y" ] && [ $res != "n" ] && [ ! -z $res ]
	  do
	  echo $2
	  echo -n $3
	  read res
	done
	eval $1=$res
    fi
}

echo ""
echo "You Will now read Centreon License. Push enter to continue."
read temp
tput clear
more ./LICENSE

$SETCOLOR_NORMAL
echo ""
echo "Do you accept GPL License ?"
echo -n "[y/n], default to [n]:"
read temp
if [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z "$temp" ] ;then
    while [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z "$temp" ]
      do
      echo "Do you accept GPL License ?"
      echo -n "    [y/n], default to [n]:"
      read temp
    done
fi
if [ -z $temp ];then
    temp="n"
fi
if [ $temp = "n" ];then
    echo "Okay... have a nice day!"
    exit
fi

# License accepted ! let's go to install centreon

test_answer(){
    #$1 variable to fill
    #$2 text typed by user
    if [ ! -z $2 ];then
        if [ $2 != "" ];then
	    eval $1=$2
        fi
    fi
}

# CONFIGURATION

if test -a $CENTREON_CONF ; then
    echo ""
    echo "------------------------------------------------------------------------"
    echo "                   Detecting old Installation"
    echo "------------------------------------------------------------------------"
    echo ""
    echo ""
    echo_success "Finding configuration file '$CENTREON_CONF' :" "OK"
    echo "You already seem to have to install Centreon."
    echo ""
    echo "Do you want use last Centreon install parameters ?"
    echo -n "     [y/n], default to [y]:"
    read temp
    if [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z $temp ] ;then
		while [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z $temp ]
		  do
		  echo "Do you want use last Centreon install parameters ?"
		  echo -n "[y/n], default to [y]:"
		  read temp
		done
    fi
    if [ -z $temp ];then
		temp=y
    fi  
    if [ $temp = "y" ];then
		echo ""
		echo_passed "Using '$CENTREON_CONF' :" "PASSED"
		. $CENTREON_CONF
		echo ""
    fi
fi

if [ -z $INSTALL_DIR_NAGIOS ];then
    INSTALL_DIR_NAGIOS=$DEFAULT_INSTALL_DIR_NAGIOS
    echo "Where is installed Nagios ?"
    echo -n "default to [$INSTALL_DIR_NAGIOS]:"
    read temp
    if [ -z "$temp" ]; then
		temp="$INSTALL_DIR_NAGIOS"
    fi
    valueok=0
    while [ ! -d "$temp/" ]; do
	echo_passed "$temp is not a directory." "CRITICAL"
	echo "Where is installed Nagios ?"
	echo -n "default to [$INSTALL_DIR_NAGIOS]:"
	read temp
	if [ -z "$temp" ]; then
	    temp="$INSTALL_DIR_NAGIOS"
	fi
	echo 
    done
    INSTALL_DIR_NAGIOS="$temp"
    echo ""
fi

if [ -z $NAGIOS_ETC ];then
    NAGIOS_ETC=$DEFAULT_NAGIOS_ETC
    echo "Where is your nagios etc directory ?"
    echo -n "default to [$NAGIOS_ETC]:"
    read temp
    if [ -z "$temp" ]; then
		temp="$NAGIOS_ETC"
    fi
    while [ ! -f "${temp}/nagios.cfg" ]; do
	echo_passed "${NAGIOS_ETC}/nagios.cfg not found" "CRITICAL"
	echo "Where is your nagios etc directory ?"
	echo -n "default to [$NAGIOS_ETC]:"
	read temp
	if [ -z "$temp" ]; then
	    temp="$NAGIOS_ETC"
	fi
    done
    NAGIOS_ETC="$temp"
    echo_success "Path $NAGIOS_ETC" "OK"
    echo ""
fi

if [ -z $NAGIOS_VAR ];then
    NAGIOS_VAR=$DEFAULT_NAGIOS_VAR
    echo "Where is your nagios var directory ?"
    echo -n "default to [$NAGIOS_VAR]:"
    read temp
    if [ -z "$temp" ]; then
	temp="$NAGIOS_VAR"
    fi
    while [ ! -d "${temp}/" ]; do
	echo_passed "${temp} is not a directory" "CRITICAL"
	echo "Where is your nagios var directory ?"
	echo -n "default to [$NAGIOS_VAR]:"
	read temp
	if [ -z "$temp" ]; then
	    temp="$NAGIOS_VAR"
	fi
    done
    NAGIOS_VAR="$temp"
    echo_success "Path $NAGIOS_VAR" "OK"
    echo ""
fi

if [ -z $NAGIOS_PLUGIN ];then
    NAGIOS_PLUGIN="$DEFAULT_NAGIOS_PLUGIN"
    echo "Where is your nagios plugins (libexec) directory ?"
    echo -n "    default to [$NAGIOS_PLUGIN]:"
    read temp
    if [ -z "$temp" ]; then
		temp="$NAGIOS_PLUGIN"
    fi
    while [ ! -d "$temp" ] ; do
	create_libexec="null"
	valid_directory=`echo $temp | grep "^/"`
	if [ "$valid_directory" != "" ]; then
	    echo_passed "Directory $temp does not exits." "CRITICAL"
	    while [ "$create_libexec" != "y" ] && [ "$create_libexec" != "Y" ] && [ "$create_libexec" != "n" ] && [ "$create_libexec" != "N" ]; do
		echo ""
		echo -n "Do you want me to create libexec directory [$temp]?[Y/n]"
		read create_libexec
		if [ -z "$create_libexec" ]; then
		    create_libexec="y"
		fi
	    done
	else
	    echo_passed "$temp is not a valid directory." "CRITICAL"
	fi
	if [ $create_libexec = "y" ] || [ $create_libexec = "Y" ]; then
	    mkdir -p $temp
	    if [ $? = 1 ]; then
			echo_passed "Could not create directory" "CRITICAL"
			echo ""
			echo "Where is your nagios plugins (libexec) directory ?"
			echo -n "    default to [$NAGIOS_PLUGIN] : "
			read temp
			if [ -z "$temp" ]; then
			    temp="$NAGIOS_PLUGIN"
			fi
	    fi
	else
	    echo "Where is your nagios plugins (libexec) directory ?"
	    echo -n "    default to [$NAGIOS_PLUGIN]:"
	    read temp
	    if [ -z "$temp" ]; then
			temp="$NAGIOS_PLUGIN"
	    fi
	fi
    done
    NAGIOS_PLUGIN="$temp"
    echo_success "Path $NAGIOS_PLUGIN" "OK"
    echo ""
fi

if [ -z $NAGIOS_BIN ];then
    NAGIOS_BIN="$INSTALL_DIR_NAGIOS/bin"
    echo "Where is your nagios bin directory?"
    echo -n "    default to [$NAGIOS_BIN] : "
    read temp
    if [ -z "$temp" ]; then
		temp="$NAGIOS_BIN"
    fi
    while [ ! -x "${temp}/nagios" ]; do
	echo_passed "Cannot find ${temp}/nagios" "CRITICAL"
	echo "Where is your nagios bin directory?"
	echo -n "default to [$NAGIOS_BIN]:"
	read temp
	if [ -z "$temp" ]; then
	    temp="$NAGIOS_BIN"
	fi
    done
    NAGIOS_BIN="$temp"
    echo_success "Path $NAGIOS_BIN" "OK"
    echo ""
fi

if [ -z $NAGIOS_IMG ];then
    NAGIOS_IMG=$DEFAULT_NAGIOS_IMG
    echo "Where is your nagios image directory ?"
    echo -n "     default to [$NAGIOS_IMG]:"
    read temp
    if [ -z "$temp" ]; then
		temp="$NAGIOS_IMG"
    fi
    while [ ! -d "$temp" ]; do
	echo_passed "$temp is not a directory." "CRITICAL"
	echo "Where is your nagios image directory ?"
	echo -n "    default to [$NAGIOS_IMG]:"
	read temp
	if [ -z "$temp" ]; then
	    temp="$NAGIOS_IMG"
	fi
    done
    NAGIOS_IMG="$temp"
    echo_success "Path $NAGIOS_IMG" "OK"
    echo ""
fi

if [ -z $SUDO_FILE ];then
    SUDO_FILE="$DEFAULT_SUDO_FILE"
    echo "Where is sudo configuration file?"
    echo -n "    default to [$SUDO_FILE]:"
    read temp
    if [ -z "$temp" ]; then
		temp="$SUDO_FILE"
    fi
    while [ ! -f "$temp" ]; do
	echo_passed "$temp if not a file." "CRITICAL"
	echo "Where is sudo configuration file?"
	echo -n "    default to [$SUDO_FILE]:"
	read temp
	if [ -z "$temp" ]; then
	    temp="$SUDO_FILE"
	fi
    done
    SUDO_FILE="$temp"
    echo_success "File $SUDO_FILE" "OK"
    echo ""
fi

if [ -z $RRD_PERL ];then
    RRD_PERL="$DEFAULT_RRD_PERL"
    echo "Where is installed RRD perl modules [RRDs.pm] ?"
    echo "Just put directory, not full path."
    echo -n "   default to [$RRD_PERL]:"
    read temp
    if [ -z "$temp" ]; then
		temp="$RRD_PERL"
    fi
    while [ ! -f "$temp/RRDs.pm" ]; do
	echo_passed "Cannot find ${temp}/RRDs.pm." "CRITICAL"
	echo "Where is installed RRD perl modules [RRDs.pm] ?"
	echo "Just put directory, not full path."
	echo -n "    default to [$RRD_PERL]:"
	read temp
	if [ -z "$temp" ]; then
	    temp="$RRD_PERL"
	fi
    done
    RRD_PERL="$temp"
    echo_success "File $RRD_PERL" "OK"
    echo ""
fi

if [ -z $BIN_RRDTOOL ];then
    BIN_RRDTOOL="$DEFAULT_BIN_RRDTOOL"
    echo "Where is rrdtool binary ?"
    echo -n "    default to [$BIN_RRDTOOL]:"
    read temp
    if [ -z "$temp" ]; then
		temp="$BIN_RRDTOOL"
    fi
    while [ ! -x "$temp" ]; do
	echo_passed "$temp is not found or is not runnable" "CRITICAL"
	echo "Where is rrdtool binary ?"
	echo -n "default to [$BIN_RRDTOOL]:"
	read temp
	if [ -z "$temp" ]; then
	    temp="$BIN_RRDTOOL"
	fi
    done
    BIN_RRDTOOL="$temp"
    echo_success "$BIN_RRDTOOL" "OK"
    echo ""
fi

if [ -z $BIN_MAIL ];then
    BIN_MAIL="$DEFAULT_BIN_MAIL"
    echo "Where is mail binary ?"
    echo -n "   default to [$BIN_MAIL]:"
    read temp
    if [ -z "$temp" ]; then
    	temp="$BIN_MAIL"
    fi
    while [ ! -x "$temp" ]; do
	echo_passed "$temp not found or not runnable" "CRITICAL"
	echo "Where is mail binary ?"
	echo -n " default to [$BIN_MAIL]:"
	read temp
	if [ -z "$temp" ]; then
	    temp="$BIN_MAIL"
	fi
    done
    BIN_MAIL="$temp"
    echo_success "$BIN_MAIL" "OK"
    echo ""
fi

if [ -z $INSTALL_DIR_CENTREON ];then
    INSTALL_DIR_CENTREON="$DEFAULT_INSTALL_DIR_CENTREON"
    echo "Where do I install centreon ?"
    echo -n "    default to [$INSTALL_DIR_CENTREON]:"
    read temp
    if [ -z "$temp" ]; then
		temp="$INSTALL_DIR_CENTREON"
    fi
    while [ ! -d "$temp" ] ; do
	create_oreon="null"
	valid_directory=`echo $temp | grep "^/"`
	if [ "$valid_directory" != "" ]; then
	    echo_passed "Directory $temp does not exits." "CRITICAL"
	    while [ "$create_oreon" != "y" ] && [ "$create_oreon" != "Y" ] && [ "$create_oreon" != "n" ] && [ "$create_oreon" != "N" ]; do
		echo ""
		echo -n "Do you want me to create this directory [$temp]?[Y/n]"
		read create_oreon
		if [ -z "$create_oreon" ]; then
		    create_oreon="y"
		fi
	    done
	else
	    echo_passed "$temp is not a valid directory." "CRITICAL"
	fi
	if [ $create_oreon = "y" ] || [ $create_oreon = "Y" ]; then
	    mkdir -p $temp
	    if [ $? = 1 ]; then
			echo_passed "Could not create directory" "CRITICAL"
			echo ""
			echo "Where do I install Centreon ?"
			echo -n "    default to [$INSTALL_DIR_CENTREON]:"
			read temp
			if [ -z "$temp" ]; then
			    temp="$INSTALL_DIR_CENTREON"
			fi
	    fi
	else
	    echo "Where do I install Centreon ?"
	    echo -n "    default to [$INSTALL_DIR_CENTREON]:"
	    read temp
	    if [ -z "$temp" ]; then
			temp="$INSTALL_DIR_CENTREON"
	    fi
	fi
    done
    INSTALL_DIR_OREON="$temp"
    echo_success "Path $INSTALL_DIR_OREON" "OK"
    echo ""
fi

if [ -z $PEAR_PATH ];then
    PEAR_PATH=$DEFAULT_PEAR_PATH
	$ECHO "Where is PEAR Path ?"
	$ECHO -n "default to [$PEAR_PATH]:"
	read temp
	test_answer PEAR_PATH $temp
	PEAR_PATH=${PEAR_PATH%/}
	while [ ! -f "${PEAR_PATH}/PEAR.php" ]
	do 
		echo_passed "${PEAR_PATH}/PEAR.php not found" "CRITICAL"
		echo "Where is PEAR Path ?"
		PEAR_PATH="/usr/share/pear"
	  	echo -n "default to [$PEAR_PATH]:"
	    read temp
	    test_answer PEAR_PATH $temp
	    PEAR_PATH=${PEAR_PATH%/}
	done
	echo_success "PEAR Path $PEAR_PATH" "OK" 
	$ECHO ""
fi


function installCentreon(){
    $ECHO ""
	$ECHO "------------------------------------------------------------------------"
	$ECHO "                    Start Centreon Installation"
	$ECHO "------------------------------------------------------------------------"
	$ECHO ""
	$ECHO ""
	
	if test -d $TMPDIR/filesGeneration/nagiosCFG ; then
		echo_passed "$TMPDIR/filesGeneration/nagiosCFG already exists" "PASSED"
    else
		echo_success "Creating '$TMPDIR/filesGeneration/nagiosCFG'" "OK"
		$MKDIR $TMPDIR/filesGeneration/nagiosCFG
    fi

	if test -d $TMPDIR/filesUpload/nagiosCFG ; then
		echo_passed "$TMPDIR/filesUpload/nagiosCFG already exists" "PASSED"
    else
		echo_success "Creating '$TMPDIR/filesUpload/nagiosCFG'" "OK"
		mkdir $TMPDIR/filesUpload/nagiosCFG
    fi

	`$SED -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' -e 's|@NAGIOS_BIN@|'"$NAGIOS_BIN"'|g' -e 's|@NAGIOS_IMG@|'"$NAGIOS_IMG"'|g' -e 's|@INSTALL_DIR_NAGIOS@|'"$INSTALL_DIR_NAGIOS"'|g' -e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' -e 's|@NAGIOS_GROUP@|'"$NAGIOS_GROUP"'|g' -e 's|@NAGIOS_ETC@|'"$NAGIOS_ETC"'|g' -e 's|@NAGIOS_PLUGINS@|'"$NAGIOS_PLUGIN"'|g' -e 's|@RRDTOOL_PERL_LIB@|'"$RRD_PERL"'|g' -e 's|@INSTALL_DIR_OREON@|'"$INSTALL_DIR_OREON"'|g' -e 's|@BIN_RRDTOOL@|'"$BIN_RRDTOOL"'|g' -e 's|@BIN_MAIL@|'"$BIN_MAIL"'|g' "$TMPDIR/www/install/insertBaseConf.sql" > "$TMPDIR/www/install/insertBaseConf.sql2"`
	$MV $TMPDIR/www/install/insertBaseConf.sql2 $TMPDIR/www/install/insertBaseConf.sql 2>&1 >> $LOG_FILE
    
    `$SED -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' -e 's|@NAGIOS_BIN@|'"$NAGIOS_BIN"'|g' -e 's|@INSTALL_DIR_NAGIOS@|'"$INSTALL_DIR_NAGIOS"'|g' -e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' -e 's|@NAGIOS_GROUP@|'"$NAGIOS_GROUP"'|g' -e 's|@NAGIOS_ETC@|'"$NAGIOS_ETC"'|g' -e 's|@NAGIOS_PLUGINS@|'"$NAGIOS_PLUGIN"'|g' -e 's|@RRDTOOL_PERL_LIB@|'"$RRD_PERL"'|g' -e 's|@RRD_PERL@|'"$RRD_PERL"'|g'  -e 's|@INSTALL_DIR_OREON@|'"$INSTALL_DIR_OREON"'|g' -e 's|@BIN_RRDTOOL@|'"$BIN_RRDTOOL"'|g' -e 's|@BIN_MAIL@|'"$BIN_MAIL"'|g'  "$TMPDIR/www/install/createTablesODS.sql" > "$TMPDIR/www/install/createTablesODS.sql2"`
	$MV $TMPDIR/www/install/createTablesODS.sql2 $TMPDIR/www/install/createTablesODS.sql  2>&1 >> $LOG_FILE
    
    $CHMOD -R 755 $TMPDIR/www/  2>&1 >> $LOG_FILE
    $CHOWN -R root:root $TMPDIR/www/  2>&1 >> $LOG_FILE
    
    $CHMOD -R 775 $TMPDIR/etc/  2>&1 >> $LOG_FILE
    $CHOWN -R $WEB_USER:$WEB_GROUP $TMPDIR/etc/  2>&1 >> $LOG_FILE
        
    $CHMOD 775 $TMPDIR/filesGeneration 2>&1 >> $LOG_FILE
    $CHOWN -R $WEB_USER:$WEB_GROUP $TMPDIR/filesGeneration 2>&1 >> $LOG_FILE

    $CHMOD 775 $TMPDIR/filesUpload/nagiosCFG 2>&1 >> $LOG_FILE
    $CHOWN -R $WEB_USER:$WEB_GROUP $TMPDIR/filesUpload/nagiosCFG 2>&1 >> $LOG_FILE
    
    $CHMOD 775 $TMPDIR/log 2>&1 >> $LOG_FILE
    $CHOWN $WEB_USER:$NAGIOS_GROUP $TMPDIR/log 2>&1 >> $LOG_FILE

    # A enlever si WaTT fait un truc spécial pour le remplacer...
    $CHMOD 775 $NAGIOS_ETC 2>&1 >> $LOG_FILE
    $CHOWN -R $WEB_USER:$NAGIOS_GROUP $NAGIOS_ETC 2>&1 >> $LOG_FILE

	# Set access to var dir
	$CHMOD 775 $TMPDIR/var
	$CHOWN -R $WEB_USER:$NAGIOS_GROUP $TMPDIR/var
	
	# Prepare con : Macro chmod and chown
	prepareCron
	
	# Prepare Traps
	prepareTraps
	
	# Prepare Centstorage
	installCentstorage
	
	# Prepare Centcore
	installCentcore
	
	for directory in "bin" "cron" "doc" "etc" "filesGeneration" "filesUpload" "GPL_LIB" "lib" "log" "var" "temp" "www" 
		do
	  	if test -d $directory ; then
			$FIND $TMPDIR/$directory/ -name "*.php" -exec dos2unix -d {} \;  2>&1 >> $LOG_FILE
			$FIND $TMPDIR/$directory/ -name "*.ihtml" -exec dos2unix -d {} \;  2>&1 >> $LOG_FILE
			$FIND $TMPDIR/$directory/ -name "*.html" -exec dos2unix -d {} \;  2>&1 >> $LOG_FILE
			$FIND $TMPDIR/$directory/ -name "*.pl" -exec dos2unix -d {} \;  2>&1 >> $LOG_FILE
			$FIND $TMPDIR/$directory/ -name "*.sh" -exec dos2unix -d {} \;  2>&1 >> $LOG_FILE
	      	cp -pR $TMPDIR/$directory/ $INSTALL_DIR_CENTREON/ >> $LOG_FILE 2>> $LOG_FILE
	      	echo_success "Copy '$directory'" "OK"
	  	fi
	done
	
	# Config Cron
	configCron
}

function prepareTraps(){
	$ECHO ""
	$ECHO "------------------------------------------------------------------------"
	$ECHO "                     Start Traps Handler Installation"
	$ECHO "------------------------------------------------------------------------"
	$ECHO ""
	$ECHO ""
    $ECHO "Preparing traps Module..."

	`$SED -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' -e 's|@INSTALL_DIR_NAGIOS@|'"$INSTALL_DIR_NAGIOS"'|g' -e 's|@NAGIOS_ETC@|'"$NAGIOS_ETC"'|g' -e 's|@NAGIOS_PLUGINS@|'"$NAGIOS_PLUGIN"'|g' -e 's|@RRDTOOL_PERL_LIB@|'"$RRD_PERL"'|g' -e 's|@INSTALL_DIR_OREON@|'"$INSTALL_DIR_OREON"'|g'  "$TMPDIR/bin/centFillTrapDB" > "$TMPDIR/bin/centFillTrapDB-new"`
	$MV $TMPDIR/bin/centFillTrapDB-new $TMPDIR/bin/centFillTrapDB 2>&1 >> $LOG_FILE
	`$SED -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' -e 's|@INSTALL_DIR_NAGIOS@|'"$INSTALL_DIR_NAGIOS"'|g' -e 's|@NAGIOS_ETC@|'"$NAGIOS_ETC"'|g' -e 's|@NAGIOS_PLUGINS@|'"$NAGIOS_PLUGIN"'|g' -e 's|@RRDTOOL_PERL_LIB@|'"$RRD_PERL"'|g' -e 's|@INSTALL_DIR_OREON@|'"$INSTALL_DIR_OREON"'|g'  "$TMPDIR/bin/centGenSnmpttConfFile" > "$TMPDIR/bin/centGenSnmpttConfFile-new"`
	$MV $TMPDIR/bin/centGenSnmpttConfFile-new $TMPDIR/bin/centGenSnmpttConfFile 2>&1 >> $LOG_FILE
	`$SED -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' -e 's|@INSTALL_DIR_NAGIOS@|'"$INSTALL_DIR_NAGIOS"'|g' -e 's|@NAGIOS_ETC@|'"$NAGIOS_ETC"'|g' -e 's|@NAGIOS_PLUGINS@|'"$NAGIOS_PLUGIN"'|g' -e 's|@RRDTOOL_PERL_LIB@|'"$RRD_PERL"'|g' -e 's|@INSTALL_DIR_OREON@|'"$INSTALL_DIR_OREON"'|g'  "$TMPDIR/bin/centTrapHandler" > "$TMPDIR/bin/centTrapHandler-new"`
	$MV $TMPDIR/bin/centTrapHandler-new $TMPDIR/bin/centTrapHandler 2>&1 >> $LOG_FILE
	
	echo "";
	echo "Where is your SNMP configuration file ?";
	echo -n "    default to [/etc/snmp/]:";
	read tmp
	if [ ! -z "$tmp" ] ;then
		SNMP_DIR=$tmp;
	fi
	if [ ! -d "$SNMP_DIR" ] ;then
		while [ ! -d "$SNMP_DIR" ]
        do
        	$ECHO ""
	  		echo_warning "$SNMP_DIR is not a directory" "WARNING"
          	$ECHO "Where is your SNMP configuration file ? "
          	SNMP_DIR="/etc/snmp/";
         	$ECHO -n "    default to [/etc/snmp/]:"
          	read tmp
	    	if [ ! -z "$tmp" ] ;then
				SNMP_DIR=$tmp;
			fi
        done
	fi
	
	# Create dir in snmp for adding configuration directory
	if [ -d "$SNMP_DIR" ] ;then
		if [ -e "${SNMP_DIR}/centreon_traps" ]; then
			echo_passed "${SNMP_DIR}/centreon_traps/ exists" "PASSED" 
		else 
			$MKDIR ${SNMP_DIR}/centreon_traps/
			echo_success "${SNMP_DIR}/centreon_traps/ created" "OK" 
		fi
		if [ -e "$TMPDIR/snmptrapd/snmptrapd.conf" ]; then
			if [ -e "$SNMP_DIR/snmptrapd.conf" ]; then
				$MV $SNMP_DIR/snmptrapd.conf $SNMP_DIR/snmptrapd.conf.old
			fi
			`$SED -e 's|@SNMPTT_INI_FILE@|'"${SNMP_DIR}centreon_traps/snmptt.ini"'|g'  "$TMPDIR/snmptrapd/snmptrapd.conf" > "$SNMP_DIR/snmptrapd.conf"`
			echo_success "Moving snmptrapd.conf to $SNMP_DIR" "OK"
		else
			echo_passed "Cannot found $TMPDIR/snmptrapd/snmptrapd.conf" "CRITICAL"
		fi
	fi
	
	# Backup SNMPTT
	if [ -e "$TMPDIR/snmptt/snmptt.ini" ]; then	
		if [ -e "${SNMP_DIR}centreon_traps/snmptt.ini" ]; then
			$MV ${SNMP_DIR}centreon_traps/snmptt.ini ${SNMP_DIR}centreon_traps/snmptt.ini.old
		fi
		$CP -pR $TMPDIR/snmptt/snmptt.ini ${SNMP_DIR}centreon_traps/
		echo_success "Moving snmptt.ini to ${SNMP_DIR}centreon_traps/" "OK"
	else
		echo_passed "Cannot find $TMPDIR/snmptt/snmptt.ini" "CRITICAL"
	fi
	
	# Configure SNMP 
	if [ -e "$TMPDIR/snmptrapd/snmp.conf" ]; then	
		if [ -e "${SNMP_DIR}snmp.conf" ]; then
			$MV ${SNMP_DIR}snmp.conf ${SNMP_DIR}snmp.conf.old
		fi
		$CP -pR $TMPDIR/snmptrapd/snmp.conf ${SNMP_DIR}
		echo_success "Moving snmp.conf to ${SNMP_DIR}" "OK"
	else
		echo_passed "Cannot found $TMPDIR/snmptrapd/snmp.conf" "CRITICAL"
	fi
		
	$CHMOD -R 755 ${SNMP_DIR}centreon_traps/
	$CHOWN -R ${WEB_USER}.${NAGIOS_GROUP} ${SNMP_DIR}centreon_traps/
	if [ -e "/etc/init.d/snmptrapd" ] ;then
		/etc/init.d/snmptrapd restart 2>&1 >> /dev/null
	fi

	# Create conf dir for SNMPTT
	if [ -e "$TMPDIR/snmptt/snmptt" ]; then	
		$CHMOD 755 $TMPDIR/snmptt/snmptt 
		$CP -pR $TMPDIR/snmptt/snmptt /usr/sbin/
		echo_success "Moving snmptt to /usr/sbin/" "OK"
	else
		echo_passed "Cannot find $TMPDIR/snmptt/snmptt" "CRITICAL"
	fi
	
	# Install SNMPTTConvertMib
	if [ -e "$TMPDIR/snmptt/snmpttconvertmib" ]; then	
		$CP $TMPDIR/snmptt/snmpttconvertmib /usr/sbin/
		$CHMOD 755 $TMPDIR/snmptt/snmpttconvertmib
		echo_success "Moving snmpttconvertmib to /usr/sbin/" "OK"
	else
		echo_passed "Cannot find $TMPDIR/snmptt/snmpttconvertmib" "CRITICAL"
	fi
}

function installCentstorage(){
	$ECHO ""
    $ECHO "------------------------------------------------------------------------"
    $ECHO "                    Start Centstorage Installation"
    $ECHO "------------------------------------------------------------------------"
    $ECHO ""
    $ECHO ""
    
    $ECHO "Checking Centstorage data folder : "
    if test -d /var/lib/centreon ; then
		echo_passed "Centstorage Directory already exists" "OK"
    else
		$MKDIR /var/lib/centreon/ 2>&1 >> $LOG_FILE
		$MKDIR /var/lib/centreon/database 2>&1 >> $LOG_FILE
		$CHOWN $NAGIOS_USER:$NAGIOS_GROUP /var/lib/centreon/database/ 2>&1 >> $LOG_FILE
		$CHMOD 775 /var/lib/centreon/database/ 2>&1 >> $LOG_FILE
		echo_success "Creating Centreon Directory '/var/lib/centreon/database/'" "OK"
    fi
    
    echo "Checking ODS database folder : "
    if test -d /var/lib/centreon/database/ ; then
		echo_passed "Centreon Directory already exists" "PASSED"
    else
		$MKDIR /var/lib/centreon/database/ >> $LOG_FILE 2>> $LOG_FILE
		echo_success "Creating Centreon Directory '/var/lib/centreon/database/'" "OK"
    fi
    
    $SED -e 's|@CENTREON_PATH@|'"$INSTALL_DIR_CENTREON"'|g' -e 's|@RRD_PERL@|'"$RRD_PERL"'|g'  $TMPDIR/bin/centstorage > $TMPDIR/bin/centstorage-new
    $MV $TMPDIR/bin/centstorage-new $TMPDIR/bin/centstorage
    echo_success "Replace Centstorage Macro " "OK"
    
 	$CHOWN $NAGIOS_USER:$NAGIOS_GROUP $TMPDIR/bin/centstorage
	$CHMOD 7755 $TMPDIR/bin/centstorage
	echo_success "Set centstorage properties " "OK"
    	
	$CHMOD 755 $TMPDIR/init.d.centstorage
	$SED -e 's|@CENTREON_PATH@|'"$INSTALL_DIR_CENTREON"'|g' -e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' -e 's|@NAGIOS_GROUP@|'"$NAGIOS_GROUP"'|g' $TMPDIR/init.d.centstorage > /etc/init.d/centstorage
}

function installCentcore(){
	$ECHO ""
    $ECHO "------------------------------------------------------------------------"
    $ECHO "                      Start Centcore Installation"
    $ECHO "------------------------------------------------------------------------"
    $ECHO ""
    $ECHO ""
       
    $SED -e 's|@CENTREON_PATH@|'"$INSTALL_DIR_CENTREON"'|g' -e 's|@RRD_PERL@|'"$RRD_PERL"'|g'  $TMPDIR/bin/centcore > $TMPDIR/bin/centcore-new
    $MV $TMPDIR/bin/centcore-new $TMPDIR/bin/centcore
    echo_success "Replace centcore Macro " "OK"
    
 	chown $NAGIOS_USER:$NAGIOS_GROUP $TMPDIR/bin/centcore
	chmod 7755 $TMPDIR/bin/centcore
	echo_success "Set centcore properties " "OK"
    	
	$CHMOD 755 $TMPDIR/init.d.centstorage
	$SED -e 's|@CENTREON_PATH@|'"$INSTALL_DIR_CENTREON"'|g' -e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' -e 's|@NAGIOS_GROUP@|'"$NAGIOS_GROUP"'|g' $TMPDIR/init.d.centcore > /etc/init.d/centcore
}

function prepareCron(){
	$ECHO ""
	$ECHO "------------------------------------------------------------------------"
	$ECHO "                       Replace Macro In scripts"
	$ECHO "------------------------------------------------------------------------"
	$ECHO ""
	$ECHO ""
	
	# Inventory Update Cron
	$SED -e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' $TMPDIR/cron/inventory_update.php > $TMPDIR/cron/inventory_update_new.php	
	$MV $TMPDIR/cron/inventory_update_new.php $TMPDIR/cron/inventory_update.php 2>&1>> $LOG_FILE
	$CHOWN -R $WEB_USER:$NAGIOS_GROUP $TMPDIR/cron/inventory_update.php 2>&1>> $LOG_FILE
    $CHMOD 775 $TMPDIR/cron/inventory_update.php 2>&1>> $LOG_FILE
    echo_success "in $TMPDIR/cron/inventory_update.php" "OK"
	
	# ArchiveLog script
	$SED -e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' $TMPDIR/cron/reporting/ArchiveLogInDB.php > $TMPDIR/cron/reporting/ArchiveLogInDB_new.php	
	$MV $TMPDIR/cron/reporting/ArchiveLogInDB_new.php $TMPDIR/cron/reporting/ArchiveLogInDB.php
	$CHOWN -R $WEB_USER:$NAGIOS_GROUP $TMPDIR/cron/reporting/ArchiveLogInDB.php 2>&1>> $LOG_FILE
    $CHMOD 775 $TMPDIR/cron/reporting/ArchiveLogInDB.php 2>&1>> $LOG_FILE
	echo_success "in $TMPDIR/cron/reporting/ArchiveLogInDB.php" "OK"
	
	# Parsing Log
	$SED -e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' $TMPDIR/bin/logAnalyser > $TMPDIR/bin/logAnalyser-new
	$MV $TMPDIR/bin/logAnalyser-new $TMPDIR/bin/logAnalyser
	$CHOWN -R $WEB_USER:$NAGIOS_GROUP $TMPDIR/bin/logAnalyser 2>&1>> $LOG_FILE
    $CHMOD 775 $TMPDIR/bin/logAnalyser >> $LOG_FILE 2>> $LOG_FILE
	echo_success "in $TMPDIR/bin/logAnalyser" "OK"
	
	# Parsing Log
	$SED -e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' $TMPDIR/bin/nagiosPerfTrace > $TMPDIR/bin/nagiosPerfTrace-new
	$MV $TMPDIR/bin/nagiosPerfTrace-new $TMPDIR/bin/nagiosPerfTrace
	$CHOWN -R $WEB_USER:$NAGIOS_GROUP $TMPDIR/bin/nagiosPerfTrace 2>&1>> $LOG_FILE
    $CHMOD 775 $TMPDIR/bin/nagiosPerfTrace >> $LOG_FILE 2>> $LOG_FILE
	echo_success "in $TMPDIR/bin/nagiosPerfTrace" "OK"
}

function configCron(){

    $ECHO ""
	$ECHO "------------------------------------------------------------------------"
	$ECHO "                    Start Centreon Cron Configuration"
	$ECHO "------------------------------------------------------------------------"
	$ECHO ""
	$ECHO ""
	
	PHP_FLG=`which php > /dev/null 2> /dev/null; echo $?`
	if [ "$PHP_FLG" = "0" ] ; then
	    PHP_BIN="php"
	else
	    PHP_FLG=`which php5 > /dev/null 2> /dev/null; echo $?`
	    if [ "$PHP_FLG" == '0' ] ; then
	        PHP_BIN="php5"
	    else
	        echo "PHP not found. Centreon take php by default"
	        PHP_BIN="php"
	    fi
	fi
    
   	$SED -e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' -e 's|@PHP_BIN@|'"$PHP"'|g' $TMPDIR/centreon.cron.conf > $TMPDIR/centreon.conf
    $CHMOD 775 $TMPDIR/centreon.conf 2>&1>> $LOG_FILE
    $CP -pR $TMPDIR/centreon.conf /etc/cron.d
}

function removeTmpFiles(){
	$SETCOLOR_NORMAL
	$ECHO ""
	$ECHO "Do you want to remove temporary file ?"
	$ECHO -n "    [y/n], default to [n] : "
	read temp
	if [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z "$temp" ] ;then
	    while [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z "$temp" ]
	      do
	      echo "Do you want to remove temporary file ?"
	      echo -n "    [y/n], default to [n] : "
	      read temp
	    done
	fi
	if [ -z $temp ];then
	    temp="n"
	fi
	if [ $temp = "y" ];then
	    $RM -Rf $TMPDIR
	    exit
	fi
}

#########################################
# Launch install
#########################################

## Config Apache
check_httpd_directory
check_group_apache
check_user_apache

## Config Nagios
check_group_nagios
check_user_nagios

## Config sudo
configureSUDO

## Config Apache
configureApache

## Create temp file copy file Into
copyInTempFile

## Replace macro in all files.
InstallPlugins

## Install Web Interface
installCentreon

## Create ConfigFile
createConfFile

## Remove Temporary Files 
removeTmpFiles

$ECHO ""
cat <<EOF
###############################################################################
#      Go to the URL : http://your-server/centreon/  to finish the setup      #
#                                                                             #
#                    Report bugs at bugs@oreon-project.org                    #
#                                                                             #
#                           Thanks for using Centreon.                        #
#                             -----------------------                         #
#                        Contact : infos@oreon-project.org                    #
#                             http://www.centreon.com                         #
###############################################################################
EOF