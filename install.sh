#!/bin/sh
###################################################################
# Oreon is developped with GPL Licence 2.0 
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
#                    OREON Project (www.oreon-project.org)                    #
#                            Thanks for using OREON                           #
#                                                                             #
#                                    v 1.4.1-RC2                              #
#                                                                             #
#                             infos@oreon-project.org                         #
#                                                                             #
#                     Make sure you have installed and configured             #
#                                   sudo - sed                                #
#                          php - apache - rrdtool - mysql                     #
#                                                                             #
#                                                                             #
###############################################################################
#                                 The Team OREON                              #
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

PWD=`pwd`

LOG_FILE="$PWD/log/install_oreon.log"

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

echo ""
$SETCOLOR_WARNING
echo "                     Make sure you have root permissions !"
echo ""
echo ""
echo " WARNING : Setup will delete all previous informations in your OREON DATABASE. "
$SETCOLOR_NORMAL
echo ""
echo "Are you sure to continue ?"
echo -n "[y/n], default to [n]:"
read temp
if [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z "$temp" ] ;then
    while [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z "$temp" ]
      do
      echo "Are you sure to continue ?"
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
if test -a $OREON_CONF ; then
	echo ""
	echo "------------------------------------------------------------------------"
	echo "                   Detecting old Installation"
	echo "------------------------------------------------------------------------"
	echo ""
	echo ""
	echo_success "Finding Oreon configuration file '$OREON_CONF' :" "OK"
	echo "You already seem to have to install Oreon."
	echo ""
    echo "Do you want use last Oreon install parameters ?"
	echo -n "[y/n], default to [y]:"
	read temp
	if [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z $temp ] ;then
	    while [ "$temp" != "y" ] && [ "$temp" != "n" ] && [ ! -z $temp ]
	      do
	      echo "Do you want use last Oreon install parameters ?"
	      echo -n "[y/n], default to [y]:"
	      read temp
	    done
	fi
	if [ -z $temp ];then
	    temp=y
	fi
	
	if [ $temp = "y" ];then
	    echo ""
		echo_passed "Using '$OREON_CONF' :" "PASSED"
	    . $OREON_CONF
	    echo ""
	else
		echo "------------------------------------------------------------------------"
		echo "                     First, let's talk about you !"
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
		if [ -a "${PEAR_PATH}/PEAR.php" ]; then
		     echo_success "PEAR Path $PEAR_PATH/PEAR.php" "OK"
		else
			echo_passed "${PEAR_PATH}/PEAR.php not found" "CRITICAL"
			echo "Where is PEAR Path ?"
			echo -n "default to [$PEAR_PATH]:"
			read temp
			test_answer PEAR_PATH $temp
			PEAR_PATH=${PEAR_PATH%/}
		fi
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

function configure_apache()
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
    if test -e $DIR_APACHE_CONF/oreon.conf ; then
	   	echo "Finding Apache Oreon configuration file"
	   	echo_success "'$DIR_APACHE_CONF/oreon.conf' :" "OK"
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
	     echo "" > $DIR_APACHE_CONF/oreon.conf
	     cat << EOF >> $DIR_APACHE_CONF/oreon.conf
##
## Section add by OREON Install Setup
##

AddType application/x-java-jnlp-file .jnlp
Alias /oreon/ $INSTALL_DIR_OREON/www/
<Directory "$INSTALL_DIR_OREON/www">
    Options None
    AllowOverride AuthConfig Options
    Order allow,deny
    Allow from all
</Directory>

EOF
	    echo_success "Create '$DIR_APACHE_CONF/oreon.conf'" "OK"
	    echo_success "Configuring Apache" "OK"
    else
	    echo_passed "Apache is already configurated" "PASSED"
    fi

    # add apache user to nagios group
    usermod -G $NAGIOS_GROUP,$WEB_USER $WEB_USER >> $LOG_FILE 2>> $LOG_FILE
    echo_success "User $WEB_USER added to nagios group" "OK"
    echo ""

    #restart apache !
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
    if test -f $INSTALL_DIR_OREON/www/oreon.conf.php ; then
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

## old install_oreon


function restart_mysql()
{
  # restart mysql to be sure that mysqld is running !
    echo ""
    echo "------------------------------------------------------------------------"
    echo "                         Restart Mysql server"
    echo "------------------------------------------------------------------------"
  	echo ""
	echo ""
  	if test -x /etc/init.d/mysqld ; then
      /etc/init.d/mysqld restart
  	else if test -x /etc/init.d/mysql ; then
      /etc/init.d/mysql restart
  	else
      echo_failure "We don't find Mysql server. OREON will not run." "FAILURE"
      exit
  	fi
 	fi
}

function config_sudo(){
    echo ""
    echo "------------------------------------------------------------------------"
	echo "                            Configure Sudo"
	echo "------------------------------------------------------------------------"
	echo ""
	echo ""
	# Find Nagios Init Script
	check_nagios_init_script

  sudo=`cat $SUDO_FILE | grep OREON > /dev/null; echo $?`

  if [ $sudo == '1' ]; then
      echo "#Add by OREON installation script" >> $SUDO_FILE
      echo "User_Alias      OREON= $WEB_USER" >> $SUDO_FILE
      echo "## Nagios Restart" >> $SUDO_FILE
      echo "OREON   ALL = NOPASSWD: $NAGIOS_INIT_SCRIPT restart" >> $SUDO_FILE
      echo "## Nagios reload" >> $SUDO_FILE
      echo "OREON   ALL = NOPASSWD: $NAGIOS_INIT_SCRIPT reload" >> $SUDO_FILE
      echo "## Snmptrapd Restart" >> $SUDO_FILE
      echo "OREON   ALL = NOPASSWD: /etc/init.d/snmptrapd restart" >> $SUDO_FILE
      echo "" >> $SUDO_FILE
      echo_success "Configuring Sudo" "OK"
  else
      echo_passed "Sudo is already configurated" "PASSED"
  fi
}

function oreon_post_install()
{
	echo ""
	echo "------------------------------------------------------------------------"
	echo "                          Oreon Post Install"
	echo "------------------------------------------------------------------------"
	echo ""
	echo ""
      #BIN_MAIL=`whereis -b mail | cut -d : -f2`
      #BIN_MAIL=${BIN_MAIL# }
	  echo_success "Finding mail binary : $BIN_MAIL " "OK"

	  #BIN_RRDTOOL=`whereis -b rrdtool | cut -d : -f2 | cut -d " " -f2`
      #BIN_RRDTOOL=${BIN_RRDTOOL# }
	  echo_success "Finding rrdtool binary : $BIN_RRDTOOL " "OK"


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
      #echo "\$conf_installoreon['rrdtool_dir'] = \"$BIN_RRDTOOL\";" >> $INSTALL_DIR_OREON_CONF
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


##
## INSTALL
##
echo "------------------------------------------------------------------------"
echo "                           User Management"
echo "------------------------------------------------------------------------"
echo ""
echo ""
# check for httpd directory
check_httpd_directory
## group apache
check_group_apache
## user apache
check_user_apache
check_group_nagios
check_user_nagios

echo ""
echo "------------------------------------------------------------------------"
echo "                              Other Stuff"
echo "------------------------------------------------------------------------"
echo ""
echo ""

if test -d $NAGIOS_PLUGIN ; then
    echo_success "Nagios libexec directory" "OK"
else
    mkdir -p $NAGIOS_PLUGIN > /dev/null
    echo_success "Nagios libexec directory created" "OK"
fi

if test -d $TRUETYPE ; then
    cp truetype/verdanab.ttf $TRUETYPE/verdanab.ttf > /dev/null
    echo_success "TrueType verdana installed" "OK"
else
    mkdir -p $TRUETYPE > /dev/null
    echo_success "TrueType directory created" "OK"
    cp truetype/verdanab.ttf $TRUETYPE/verdanab.ttf > /dev/null
    echo_success "TrueType verdana installed" "OK"
fi

#PEAR_PATH=`whereis -b pear | cut -d : -f2 | cut -d " " -f4`
#PEAR_PATH=${PEAR_PATH# }
#PEAR_PATH=${PEAR_PATH%/}
echo_success "Finding PEAR Path : $PEAR_PATH " "OK"
if test -d "$PEAR_PATH/Image/Canvas/Fonts" ; then
	cp truetype/arial.ttf $PEAR_PATH/Image/Canvas/Fonts/arial.ttf > /dev/null
  	cp truetype/fontmap.txt $PEAR_PATH/Image/Canvas/Fonts/fontmap.txt > /dev/null
  	echo_success "PEAR Font installed" "OK"
else
	if [ -z $PEAR_PATH ];then
		echo_passed "PEAR directory not found" "PASSED"
	else
	    mkdir -p "$PEAR_PATH/Image/Canvas/Fonts" > /dev/null
	    echo_success "PEAR Font directory created" "OK"
	    cp truetype/arial.ttf $PEAR_PATH/Image/Canvas/Fonts/arial.ttf > /dev/null
  	  	cp truetype/fontmap.txt $PEAR_PATH/Image/Canvas/Fonts/fontmap.txt > /dev/null
	    echo_success "PEAR Font installed" "OK"
	 fi
fi

# installation script
#check_group_nagiocmd
configure_apache
confirm_oreon
oreon_post_install

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

