#!/bin/sh
################################################################################
# Copyright 2005-2009 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
# For more information : contact@centreon.com
# 
# SVN : $URL$
# SVN : $Id$
#
####################################################################################
#
# Script init
#
### BEGIN INIT INFO Suse
# Provides:       centcore
# Required-Start:
# Required-Stop:
# Default-Start:  3 5
# Default-Stop: 0 1 6
# Description:    Start the centore high-Availability Engine
### END INIT INFO

### BEGIN INIT INFO Redhat
# chkconfig: - 70 30
# description: Centreon Core
# processname: centcore
# config:
# pidfile:
### END INIT INFO

status_centcore () {
    if test ! -f $RunFile; then
		echo "No lock file found in $RunFile"
		return 1
    fi
    PID=`head -n 1 $RunFile`
    if ps -p $PID; then
		return 0
    else
		return 1
    fi
    return 1
}


killproc_centcore () {
    if test ! -f $RunFile; then
		echo "No lock file found in $RunFile"
		return 1
    fi    
    PID=`head -n 1 $RunFile`
    kill -s INT $PID
}

# Create RunDir if not exit
rundir_exist() {
[ -e ${centstorageRunDir} ] || \
        install -d -o@NAGIOS_USER@ -m750 ${centstorageRunDir}
}

# Source function library
# Solaris doesn't have an rc.d directory, so do a test first
if [ -f /etc/rc.d/init.d/functions ]; then
    . /etc/rc.d/init.d/functions
elif [ -f /etc/init.d/functions ]; then
    . /etc/init.d/functions
fi

prefix=@CENTREON_DIR@/
exec_prefix=${prefix}
Bin=@CENTCORE_BINDIR@/centcore
CfgFile=@CENTREON_ETC@/conf.pm
VarDir=${prefix}var/
LogDir=@CENTREON_LOG@
RunDir=@CENTREON_RUNDIR@
RunFile=${RunDir}/centcore.pid
DemLog=${LogDir}/centcore.log
LockDir=/var/lock/subsys
LockFile=centcore
NICE=5    

# Check that centcore exists.
if [ ! -f $Bin ]; then
    echo "Executable file $Bin not found.  Exiting."
    exit 1
fi

# Check that centcore.cfg exists.
if [ ! -f $CfgFile ]; then
    echo "Configuration file $CfgFile not found.  Exiting."
    exit 1
fi
          
# See how we were called.
case "$1" in

    start)
	    # Check lock file
	    if test -f $RunFile; then
		echo "Error : $RunFile already Exists."
		ISRUNNING=`ps -edf | grep $Bin | grep -v grep | wc -l `
		if [ $ISRUNNING = 0 ]
		    then
		    echo "But no centcore process runnig"
		    rm -f $RunFile
		    echo "Removing centcore pid file"
		else 
		    exit 1
		fi
	    fi
	    # Test if running directory exist.
	    rundir_exist
	    echo "Starting Centcore"
	    sudo - @NAGIOS_USER@ -c "nice -n $NICE $Bin >> $DemLog 2>&1"
	    if [ -d $LockDir ]; then 
	    	touch $LockDir/$LockFile; 
	    fi
	    exit 0
    ;;
    
    stop)
	    echo "Stopping Centcore"
	    killproc_centcore centcore
	    
	    echo -n 'Waiting for centcore to exit .'
	    for i in `seq 20` ; do
			if status_centcore > /dev/null; then
			    echo -n '.'
			    sleep 1
			else
			    break
			fi
	    done
	    if status_centcore > /dev/null; then
			echo ''
			echo 'Warning - running centcore did not exit in time'
		    else
			echo ' done.'
	    fi
    ;;
    
    status)
	    status_centcore centcore
    ;;
    
    restart)
	    $0 stop
	    $0 start
    ;;
    
    reload|force-reload)
    if test ! -f $RunFile; then
		$0 start
    else
		PID=`head -n 1 $RunFile`
		if status_centcore > /dev/null; then
		    killproc_centcore centcore -HUP
		    echo "done"
		else
		    $0 stop
		    $0 start
		fi
    fi
    ;;
    
    *)
	    echo "Usage: centcore {start|stop|restart|reload|force-reload|status}"
    	exit 1
    ;;
    
esac
# End of this script
