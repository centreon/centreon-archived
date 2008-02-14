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
DEFAULT_INSTALL_DIR_OREON=/usr/local/oreon
DEFAULT_NAGIOS_ETC=/usr/local/nagios/etc
DEFAULT_NAGIOS_PLUGIN=/usr/local/nagios/libexec
DEFAULT_NAGIOS_IMG=/usr/local/nagios/share/images
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
#                     Make sure you have installed and configured             #
#                                   sudo - sed                                #
#                          php - apache - rrdtool - mysql                     #
#                                                                             #
###############################################################################
EOF

#Load install script functions
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

date > $LOG_FILE

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
      echo -n "[y/n], default to [n]:"
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

test_answer(){
    #$1 variable to fill
    #$2 text typed by user
    if [ ! -z $2 ];then
        if [ $2 != "" ];then
	    eval $1=$2
        fi
    fi
}

##
## CONFIGURATION
##
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
    fi
fi


if [ -z $INSTALL_DIR_NAGIOS ];then
    INSTALL_DIR_NAGIOS="/usr/local/nagios"
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
    NAGIOS_VAR="$INSTALL_DIR_NAGIOS/var"
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
    #nagios plugins directory for oreon
    NAGIOS_PLUGIN="$INSTALL_DIR_NAGIOS/libexec"
    echo "Where is your nagios plugins (libexec) directory ?"
    echo -n "default to [$NAGIOS_PLUGIN]:"
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
		echo -n "default to [$NAGIOS_PLUGIN]:"
		read temp
		if [ -z "$temp" ]; then
		    temp="$NAGIOS_PLUGIN"
		fi
	    fi
	else
	    echo "Where is your nagios plugins (libexec) directory ?"
	    echo -n "default to [$NAGIOS_PLUGIN]:"
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
    #nagios plugins directory for oreon
        NAGIOS_BIN="$INSTALL_DIR_NAGIOS/bin"
	    echo "Where is your nagios bin directory?"
	        echo -n "default to [$NAGIOS_BIN]:"
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
          #nagios plugins directory for oreon
        NAGIOS_IMG="$INSTALL_DIR_NAGIOS/share/images"
	    echo "Where is your nagios image directory ?"
	        echo -n "default to [$NAGIOS_IMG]:"
		    read temp
		        if [ -z "$temp" ]; then
			    temp="$NAGIOS_IMG"
			        fi
			    while [ ! -d "$temp" ]; do
				echo_passed "$temp is not a directory." "CRITICAL"
				echo "Where is your nagios image directory ?"
				echo -n "default to [$NAGIOS_IMG]:"
				read temp
				if [ -z "$temp" ]; then
				        temp="$NAGIOS_IMG"
					fi
				    done
			        NAGIOS_IMG="$temp"
				    echo_success "Path $NAGIOS_IMG" "OK"
				        echo ""
					fi

if [ -z $INSTALL_DIR_OREON ];then
    #setup directory for oreon
    INSTALL_DIR_OREON="/usr/local/centreon"
    echo "Where do I install centreon ?"
    echo -n "default to [$INSTALL_DIR_OREON]:"
    read temp
    if [ -z "$temp" ]; then
	    temp="$INSTALL_DIR_OREON"
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
					        echo -n "default to [$INSTALL_OREON_DIR]:"
						    read temp
						        if [ -z "$temp" ]; then
							    temp="$INSTALL_DIR_OREON"
							        fi
							    fi
			        else
			    echo "Where do I install Centreon ?"
			    echo -n "default to [$INSTALL_DIR_OREON]:"
			    read temp
			    if [ -z "$temp" ]; then
				    temp="$INSTALL_DIR_OREON"
				    fi
			        fi
			done
    INSTALL_DIR_OREON="$temp"
    echo_success "Path $INSTALL_DIR_OREON" "OK"
    echo ""
    fi

if [ -z $SUDO_FILE ];then
    #Configuration file for sudo
        SUDO_FILE="/etc/sudoers"
	    echo "Where is sudo configuration file?"
	        echo -n "default to [$SUDO_FILE]:"
		    read temp
		        if [ -z "$temp" ]; then
			    temp="$SUDO_FILE"
			        fi
			    while [ ! -f "$temp" ]; do
				echo_passed "$temp if not a file." "CRITICAL"
				echo "Where is sudo configuration file?"
				echo -n "default to [$SUDO_FILE]:"
				read temp
				if [ -z "$temp" ]; then
				        temp="$SUDO_FILE"
					fi
				    done
			        SUDO_FILE="$temp"
				    echo_success "File $SUDO_FILE" "OK"
				        echo ""
					fi
#"/usr/share/pear"
if [ -z $RRD_PERL ];then
    #RRDTOOL perl module directory
        RRD_PERL="/usr/local/rrdtool/lib/perl"
	    echo "Where is installed RRD perl modules [RRDs.pm] ?"
	        echo "Just put directory, not full path."
		    echo -n "default to [$RRD_PERL]:"
		        read temp
			    if [ -z "$temp" ]; then
                temp="$RRD_PERL"
            fi
			        while [ ! -f "$temp/RRDs.pm" ]; do
                echo_passed "Cannot find ${temp}/RRDs.pm." "CRITICAL"
                echo "Where is installed RRD perl modules [RRDs.pm] ?"
		echo "Just put directory, not full path."
		echo -n "default to [$RRD_PERL]:"
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
    #RRDTOOL binary path
        BIN_RRDTOOL="/usr/bin/rrdtool"
	    echo "Where is rrdtool binary ?"
	    echo -n "default to [$BIN_RRDTOOL]:"
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
    #MAIL binary path
    BIN_MAIL="/usr/bin/mail"
    echo "Where is mail binary ?"
    echo -n "default to [$BIN_MAIL]:"
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
				    echo_success "PEAR Path $PEAR_PATH" "OK" 
				        echo ""
					fi



_______________________________________________________

if [ -z $INSTALL_DIR_NAGIOS ];then
    INSTALL_DIR_NAGIOS=$DEFAULT_INSTALL_DIR_NAGIOS
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


##
## Functions
##

# When exit on error

function error()
{
    echo "ERROR"
    exit 2
}

# Check apache version, and configure it. Ask to restart apache server
# Make a copy of the original file as httpd.conf.initial

function configureApache()
{
    echo ""
    echo "------------------------------------------------------------------------"
    echo "                        Configure Apache server"
    echo "------------------------------------------------------------------------"
    echo ""
    echo ""
    
    if test -d $INSTALL_DIR_OREON ; then
      	echo_passed "$INSTALL_DIR_OREON already exists" "PASSED"
    else
      	mkdir $INSTALL_DIR_OREON 2>&1 >> ${LOG_FILE}
      	echo_success "Creating $INSTALL_DIR_OREON" "OK"
    fi

    # configure httpd.conf
    if test -e $DIR_APACHE_CONF/centreon.conf ; then
	echo "Finding Apache Centreon configuration file"
	echo_success "'$DIR_APACHE_CONF/centreon.conf' :" "OK"
	echo "Do you want rewrite Apache configuration file ?"
	echo -n "[y/n], default to [y]:"
	read temp
	if [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z $temp ] ;then
	    while [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z $temp ]
	      do
	      echo "Do you want rewrite Apache configuration file ?"
	      echo -n "[y/n], default to [y]:"
	      read temp
	    done
	fi
	if [ -z $temp ];then
	    temp=y
	fi
    else
	temp=y
    fi
    
    if [ $temp = "y" ];then
	echo "" > $DIR_APACHE_CONF/centreon.conf
	cat << EOF >> $DIR_APACHE_CONF/centreon.conf
##
## Section add by Centreon Install Setup
##
	
AddType application/x-java-jnlp-file .jnlp
Alias /centreon $INSTALL_DIR_OREON/www/
<Directory "$INSTALL_DIR_OREON/www">
    Options None
    AllowOverride AuthConfig Options
    Order allow,deny
    Allow from all
</Directory>

EOF

        echo_success "Create '$DIR_APACHE_CONF/centreon.conf'" "OK"
        echo_success "Configuring Apache" "OK"
    else
	echo_passed "Apache is already configurated" "PASSED"
    fi

    # add apache user to nagios group
    #usermod -G $NAGIOS_GROUP,$WEB_USER $WEB_USER >> $LOG_FILE 2>> $LOG_FILE
    #echo_success "User $WEB_USER added to nagios group" "OK"
    #echo ""

    # After finishing the configuration -> 
    # restart apache !
    if test -x /etc/init.d/apache ; then
	/etc/init.d/apache restart >> $LOG_FILE 2>> $LOG_FILE
    else if test -x /etc/init.d/httpd ; then
	/etc/init.d/httpd restart
    else if test -e /etc/init.d/apache2 ; then
	/etc/init.d/apache2 restart >> $LOG_FILE 2>> $LOG_FILE
    else
	echo_warning "Unable to restart apache server" "WARNING"
    fi
    fi
    fi
    
}

# install OREON interface

function confirm_oreon()
{
    if test -f $INSTALL_DIR_OREON/www/centreon.conf.php ; then
	  echo ""
	  echo "Oreon is already install on your server !"

	  echo -n "Are you sure you want to install OREON ?"
      echo -n "[y/n], default to [n]:"
	  read answer
	  if [ "$answer" != "y" ] && [ "$answer" != "n" ] && [ ! -z $answer ] ;then
	      while [ "$answer" != "y" ] && [ "$answer" != "n" ] && [ ! -z $answer ]
		do
		echo "Are you sure you want to install OREON ?"
		echo -n "[y/n], default to [n]:"
		read answer
	      done
	  fi
	  if [ -z $answer ];then
	  	answer=n
	  fi
	  if [ $answer == 'n' ]; then
	      echo "Ok, so bye bye !! "
	      exit
	  else if [ $answer == 'y' ]; then
	      install_oreon
	      install_ods
	      config_cron
	      config_sudo
	      #restart_mysql
	  else
	      echo "Please answer y or n ! "
	      confirm_oreon
	  fi
	  fi
    else
	    install_oreon
	    install_ods
        config_cron
	    config_sudo
	    #restart_mysql
    fi
}

function configureSUDO(){
    echo ""
    echo "------------------------------------------------------------------------"
    echo "                            Configure Sudo"
    echo "------------------------------------------------------------------------"
    echo ""
    echo ""
    
    # Find Nagios Init Script
    check_nagios_init_script

    sudo=`cat $SUDO_FILE | grep CENTREON > /dev/null; echo $?`
    
    if [ $sudo == '1' ]; then
	echo "#Add by CENTREON installation script" >> $SUDO_FILE
	echo "User_Alias      CENTREON= $WEB_USER" >> $SUDO_FILE
	echo "## Nagios Restart" >> $SUDO_FILE
	echo "CENTREON   ALL = NOPASSWD: $NAGIOS_INIT_SCRIPT restart" >> $SUDO_FILE
	echo "## Nagios reload" >> $SUDO_FILE
	echo "CENTREON   ALL = NOPASSWD: $NAGIOS_INIT_SCRIPT reload" >> $SUDO_FILE
	echo "## Snmptrapd Restart" >> $SUDO_FILE
	echo "CENTREON   ALL = NOPASSWD: /etc/init.d/snmptrapd restart" >> $SUDO_FILE
	echo "" >> $SUDO_FILE
	echo_success "Configuring Sudo" "OK"
    else
	echo_passed "Sudo is already configurated" "PASSED"
    fi
}

function createConfFile()
{
	echo ""
	echo "------------------------------------------------------------------------"
	echo "                          Oreon Post Install"
	echo "------------------------------------------------------------------------"
	echo ""
	echo ""

	INSTALL_DIR_OREON_CONF="$INSTALL_DIR_OREON/www/install/installoreon.conf.php"
	echo "<?" > $INSTALL_DIR_OREON_CONF
	echo "/**" >> $INSTALL_DIR_OREON_CONF
	echo "Oreon is developped with GPL Licence 2.0 :" >> $INSTALL_DIR_OREON_CONF
	echo "http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt" >> $INSTALL_DIR_OREON_CONF
	echo "Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf" >> $INSTALL_DIR_OREON_CONF
	echo "" >> $INSTALL_DIR_OREON_CONF
	echo "The Software is provided to you AS IS and WITH ALL FAULTS." >> $INSTALL_DIR_OREON_CONF
	echo "OREON makes no representation and gives no warranty whatsoever," >> $INSTALL_DIR_OREON_CONF
	echo "whether express or implied, and without limitation, with regard to the quality," >> $INSTALL_DIR_OREON_CONF
	echo "safety, contents, performance, merchantability, non-infringement or suitability for" >> $INSTALL_DIR_OREON_CONF
	echo "any particular or intended purpose of the Software found on the OREON web site." >> $INSTALL_DIR_OREON_CONF
	echo "In no event will OREON be liable for any direct, indirect, punitive, special," >> $INSTALL_DIR_OREON_CONF
	echo "incidental or consequential damages however they may arise and even if OREON has" >> $INSTALL_DIR_OREON_CONF
	echo "been previously advised of the possibility of such damages." >> $INSTALL_DIR_OREON_CONF
	echo "" >> $INSTALL_DIR_OREON_CONF
	echo "For information : contact@oreon-project.org" >> $INSTALL_DIR_OREON_CONF
	echo "	*/" >> $INSTALL_DIR_OREON_CONF
	echo "" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['oreon_dir'] = \"$INSTALL_DIR_OREON/\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['oreon_dir_www'] = \"$INSTALL_DIR_OREON/www/\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['oreon_dir_rrd'] = \"$INSTALL_DIR_OREON/rrd/\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['nagios'] = \"$INSTALL_DIR_NAGIOS/\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['nagios_conf'] = \"$NAGIOS_ETC/\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['nagios_plugins'] = \"$NAGIOS_PLUGIN/\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['nagios_bin'] = \"$NAGIOS_BIN/\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['nagios_init_script'] = \"$NAGIOS_INIT_SCRIPT\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['rrdtool_dir'] = \"$BIN_RRDTOOL\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['apache_user'] = \"$WEB_USER\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['apache_group'] = \"$WEB_GROUP\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['nagios_user'] = \"$NAGIOS_USER\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['nagios_group'] = \"$NAGIOS_GROUP\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['mail'] = \"$BIN_MAIL\";" >> $INSTALL_DIR_OREON_CONF
	echo "\$conf_installoreon['pear_dir'] = \"$PEAR_PATH\";" >> $INSTALL_DIR_OREON_CONF
	
	for fichier in `cat $NAGIOS_ETC/nagios.cfg | grep _file | grep -v \#`
	  do
	  echo -n "\$conf_installoreon['" >> $INSTALL_DIR_OREON_CONF
	  tmp=`echo  "$fichier" | cut -d = -f1` >> $INSTALL_DIR_OREON_CONF
	  echo -n $tmp >> $INSTALL_DIR_OREON_CONF
	  echo -n "'] = \"" >> $INSTALL_DIR_OREON_CONF
	  tmp=`echo "$fichier" | cut -d = -f2` >> $INSTALL_DIR_OREON_CONF
	  echo -n $tmp >> $INSTALL_DIR_OREON_CONF
	  echo "\";" >> $INSTALL_DIR_OREON_CONF
	done
	for fichier in `cat $NAGIOS_ETC/nagios.cfg | grep _path | grep -v \#`
	  do
	  echo -n "\$conf_installoreon['" >> $INSTALL_DIR_OREON_CONF
	  tmp=`echo  "$fichier" | cut -d = -f1` >> $INSTALL_DIR_OREON_CONF
	  echo -n $tmp >> $INSTALL_DIR_OREON_CONF
	  echo -n "'] = \"" >> $INSTALL_DIR_OREON_CONF
	  tmp=`echo "$fichier" | cut -d = -f2` >> $INSTALL_DIR_OREON_CONF
	  echo -n $tmp >> $INSTALL_DIR_OREON_CONF
	  echo "\";" >> $INSTALL_DIR_OREON_CONF
	done
        for fichier in `cat $NAGIOS_ETC/cgi.cfg | grep physical_html_path | grep -v \#`
	  do
	  echo -n "\$conf_installoreon['" >> $INSTALL_DIR_OREON_CONF
	  tmp=`echo  "$fichier" | cut -d = -f1` >> $INSTALL_DIR_OREON_CONF
	  echo -n $tmp >> $INSTALL_DIR_OREON_CONF
	  echo -n "'] = \"" >> $INSTALL_DIR_OREON_CONF
	  tmp=`echo "$fichier" | cut -d = -f2` >> $INSTALL_DIR_OREON_CONF
	  echo -n $tmp >> $INSTALL_DIR_OREON_CONF
	  echo "\";" >> $INSTALL_DIR_OREON_CONF
	done
	
	echo "?>" >> $INSTALL_DIR_OREON_CONF
	echo_success "Create $INSTALL_DIR_OREON_CONF" "OK"
	
	echo "INSTALL_DIR_OREON=$INSTALL_DIR_OREON" > $OREON_CONF
	echo "NAGIOS_ETC=$NAGIOS_ETC" >> $OREON_CONF
	echo "NAGIOS_PLUGIN=$NAGIOS_PLUGIN" >> $OREON_CONF
	echo "NAGIOS_BIN=$NAGIOS_BIN" >> $OREON_CONF
	echo "NAGIOS_IMG=$NAGIOS_IMG" >> $OREON_CONF
	echo "INSTALL_DIR_NAGIOS=$INSTALL_DIR_NAGIOS" >> $OREON_CONF
	echo "RRD_PERL=$RRD_PERL" >> $OREON_CONF
	echo "SUDO_FILE=$SUDO_FILE" >> $OREON_CONF
	echo "WEB_USER=$WEB_USER" >> $OREON_CONF
	echo "WEB_GROUP=$WEB_GROUP" >> $OREON_CONF
	echo "NAGIOS_USER=$NAGIOS_USER" >> $OREON_CONF
	echo "NAGIOS_GROUP=$NAGIOS_GROUP" >> $OREON_CONF
	echo "BIN_RRDTOOL=$BIN_RRDTOOL" >> $OREON_CONF
	echo "BIN_MAIL=$BIN_MAIL" >> $OREON_CONF
	echo "PEAR_PATH=$PEAR_PATH" >> $OREON_CONF
	
	echo_success "Create $OREON_CONF " "OK"
	echo_success "Configuring Oreon post-install" "OK"
}


if test -d $NAGIOS_PLUGIN ; then
    echo_success "Nagios libexec directory" "OK"
else
    mkdir -p $NAGIOS_PLUGIN > /dev/null
    echo_success "Nagios libexec directory created" "OK"
fi


#########################################
# Launch install
#########################################

copyInTempFile
replaceMacro

# check for httpd directory
check_httpd_directory

## Config Apache

check_group_apache
check_user_apache

## Config Nagios

check_group_nagios
check_user_nagios

# installation script

#check_group_nagiocmd

configure_apache
confirm_oreon
oreon_post_install

configureApache
configureSUDO
configureCron




echo ""
cat <<EOF
###############################################################################
#      Go to the URL : http://your-server/oreon/  to finish the setup         #
#                                                                             #
#                    Report bugs at bugs@oreon-project.org                    #
#                                                                             #
#                             Thanks for using OREON.                         #
#                             -----------------------                         #
#                        Contact : infos@oreon-project.org                    #
#                           http://www.oreon-project.org                      #
###############################################################################
EOF
