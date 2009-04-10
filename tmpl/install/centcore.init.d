#!/bin/sh
###################################################################
# Centreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
#
# Developped by : Julien Mathis - Romain Le Merlus
#
#                 jmathis@merethis.com
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
#    For information : contact@merethis.com
####################################################################
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
# description: Centreon Data Storage
# processname: centcore
# config:
# pidfile:
### END INIT INFO


status_centcore ()
{
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


killproc_centcore ()
{
    if test ! -f $RunFile; then
	echo "No lock file found in $RunFile"
	return 1
    fi    
    PID=`head -n 1 $RunFile`
    kill -s INT $PID
}

# Create RunDir if not exit
rundir_exist()
{
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
    echo "Starting Centcore : centcore"
    su - @NAGIOS_USER@ -c "nice -n $NICE $Bin >> $DemLog 2>&1 &"
    if [ -d $LockDir ]; then touch $LockDir/$LockFile; fi
    exit 0
    ;;
    
    stop)
    echo "Stopping Centcore : centcore"
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

