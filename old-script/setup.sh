#!/bin/sh
###################################################################
# Centreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
#
# Developped by : Julien Mathis - Romain Le Merlus 
#                 Christophe Coraboeuf - Mathieu Chateau
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
cat <<EOF
###############################################################################
#                       Centreon Project (www.centreon.com)                   #
#                            Thanks for using Centreon                        #
#                                                                             #
#                                    v 2.x                                    #
#                                                                             #
#                             infos@oreon-project.org                         #
#                                                                             #
#                     Make sure you have installed and configured             #
#                                   sudo - sed                                #
#                          php - apache - rrdtool - mysql                     #
#                                                                             #
#                                                                             #
###############################################################################
#                               The Team Centreon                             #
###############################################################################
EOF

# Load install script functions
# Check if script can be lanuch with bash

if [ -z "$BASH" ]; then # Test if BASH is in path
    if ! which bash > /dev/null 2>&1; then
	echo "Install bash and try `bash install.sh`."
    fi # Exit if we are not in BASH
    echo "Error: The script must be run with BASH shell. Try:"
    echo "# bash install.sh"
    exit 1
fi

. functions

#
# Define VARIABLES
#
# Make sure you know what you do if you modify it !!

#==== Init log file ==== 

LOG_FILE="$PWD/log/install_oreon.log"
date > $LOG_FILE

#==== Init Tools path ====
 
PWD=`pwd`

#==== Where are sources ? ====

SRC_OREON="oreon_src"
PLUGINS_DIR="Plugins/"

TRUETYPE="/usr/X11R6/lib/X11/fonts/truetype"


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

if test -a $OREON_CONF ; then
	echo ""
	echo "------------------------------------------------------------------------"
	echo "                   Detecting old Installation"
	echo "------------------------------------------------------------------------"
	echo ""
	echo ""
	echo_success "Finding Centreon configuration file '$CENTREON_CONF' :" "OK"
	echo "You already seem to have to install Centreon."
	echo ""
    echo "Do you want use last Centreon install parameters ?"
	echo -n "[y/n], default to [y]:"
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
	else
		echo "------------------------------------------------------------------------"
		echo "   First, centreon setup need some information about your system... "
		echo "------------------------------------------------------------------------"
		echo ""
		echo ""
	fi
fi

if [ -z $INSTALL_DIR_NAGIOS ];then
	INSTALL_DIR_NAGIOS="/usr/local/nagios"
	echo "Where is installed Nagios ?"
	echo -n "default to [$INSTALL_DIR_NAGIOS]:"
	read temp
	test_answer INSTALL_DIR_NAGIOS $temp
	INSTALL_DIR_NAGIOS=${INSTALL_DIR_NAGIOS%/}
	echo ""
fi

if [ -z $NAGIOS_ETC ];then
	#nagios etc directory for oreon
	NAGIOS_ETC="$INSTALL_DIR_NAGIOS/etc"
	echo "Where are your nagios etc directory ?"
	echo -n "default to [$NAGIOS_ETC]:"
	read temp
	test_answer NAGIOS_ETC $temp
	NAGIOS_ETC=${NAGIOS_ETC%/}
	if [ -a "${NAGIOS_ETC}/nagios.cfg" ]; then
	     echo_success "Path $NAGIOS_ETC/nagios.cfg" "OK"
	else
		echo_passed "${NAGIOS_ETC}/nagios.cfg not found" "CRITICAL"
		echo "Where are your nagios etc directory ?"
		echo -n " :"
		read temp
		test_answer NAGIOS_ETC $temp
		NAGIOS_ETC=${NAGIOS_ETC%/}
	fi
	echo ""
fi

if [ -z $NAGIOS_PLUGIN ];then
	#nagios plugins directory for oreon
	NAGIOS_PLUGIN="$INSTALL_DIR_NAGIOS/libexec"
	echo "Where are your nagios plugins / libexec  directory ?"
	echo -n "default to [$NAGIOS_PLUGIN]:"
	read temp
	test_answer NAGIOS_PLUGIN $temp
	NAGIOS_PLUGIN=${NAGIOS_PLUGIN%/}
	echo ""
fi

if [ -z $NAGIOS_BIN ];then
	#nagios plugins directory for oreon
	NAGIOS_BIN="$INSTALL_DIR_NAGIOS/bin"
	echo "Where are your nagios bin  directory ?"
	echo -n "default to [$NAGIOS_BIN]:"
	read temp
	test_answer NAGIOS_BIN $temp
	NAGIOS_BIN=${NAGIOS_BIN%/}
	echo ""
fi


if [ -z $NAGIOS_IMG ];then
      #nagios plugins directory for oreon
  NAGIOS_IMG="$INSTALL_DIR_NAGIOS/share/image"
  echo "Where are your nagios image  directory ?"
  echo -n "default to [$NAGIOS_IMG]:"
  read temp
  test_answer NAGIOS_IMG $temp
  NAGIOS_IMG=${NAGIOS_IMG%/}
  echo ""
fi


if [ -z $INSTALL_DIR_OREON ];then
	#setup directory for oreon
	INSTALL_DIR_OREON="/usr/local/oreon"
	echo "Where do I install Oreon ?"
	echo -n "default to [$INSTALL_DIR_OREON]:"
	read temp
	test_answer INSTALL_DIR_OREON $temp
	INSTALL_DIR_OREON=${INSTALL_DIR_OREON%/}
	echo ""
fi

if [ -z $SUDO_FILE ];then
	#Configuration file for sudo
	SUDO_FILE="/etc/sudoers"
	echo "Where is sudo ?"
	echo -n "default to [$SUDO_FILE]:"
	read temp
	test_answer SUDO_FILE $temp
	SUDO_FILE=${SUDO_FILE%/}
	echo ""
fi

if [ -z $RRD_PERL ];then
	#RRDTOOL perl module directory
	RRD_PERL="/usr/local/rrdtool/lib/perl"
	echo "Where is installed RRD perl modules (RRDs.pm) ?"
	echo "Just put directory, not full path."
	echo -n "default to [$RRD_PERL]:"
	read temp
	test_answer RRD_PERL $temp
	RRD_PERL=${RRD_PERL%/}
	echo ""
fi

if [ -z $BIN_RRDTOOL ];then
	#RRDTOOL binary path
	BIN_RRDTOOL="/usr/bin/rrdtool"
	echo "Where is rrdtool binary ?"
	echo -n "default to [$BIN_RRDTOOL]:"
	read temp
	test_answer BIN_RRDTOOL $temp
	BIN_RRDTOOL=${BIN_RRDTOOL%/}
	if [ -x "$BIN_RRDTOOL" ]; then
	     echo_success "$BIN_RRDTOOL" "OK"
	else
		echo_passed "$BIN_RRDTOOL not found" "CRITICAL"
		echo "Where is rrdtool binary ?"
		echo -n " :"
		read temp
		test_answer BIN_RRDTOOL $temp
		BIN_RRDTOOL=${BIN_RRDTOOL%/}
	fi
	echo ""
fi


if [ -z $BIN_MAIL ];then
	#MAIL binary path
	BIN_MAIL="/usr/bin/mail"
	echo "Where is mail binary ?"
	echo -n "default to [$BIN_MAIL]:"
	read temp
	test_answer BIN_MAIL $temp
	BIN_MAIL=${BIN_MAIL%/}
	if [ -x "$BIN_MAIL" ]; then
	     echo_success "$BIN_MAIL" "OK"
	else
		echo_passed "$BIN_MAIL not found" "CRITICAL"
		echo "Where is mail binary ?"
		echo -n " :"
		read temp
		test_answer BIN_MAIL $temp
		BIN_MAIL=${BIN_MAIL%/}
	fi

	echo ""
fi

if [ -z $PEAR_PATH ];then
	PEAR_PATH="/usr/share/pear"
	echo "Where is PEAR Path ?"
	echo -n "default to [$PEAR_PATH]:"
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
        echo_success "PEAR Path $PEAR_PATH/PEAR.php" "OK"
	echo ""
fi



