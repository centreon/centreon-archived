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
# Provides:       centstorage
# Required-Start:
# Required-Stop:
# Default-Start:  3 5
# Default-Stop: 0 1 6
# Description:    Start the CentStorage collector
### END INIT INFO

### BEGIN INIT INFO Redhat
# chkconfig: - 71 31
# description: Centreon Storage
# processname: centcore
# config:
# pidfile:
### END INIT INFO

status_centstorage() {
    if test ! -f $centstorageRunFile; then
		echo "No lock file found in $centstorageRunFile"
		return 1
    fi
    centstoragePID=`head -n 1 $centstorageRunFile`
    if ps -p $centstoragePID; then
		return 0
    else
		return 1
    fi
    return 1
}

killproc_centstorage() {
    if test ! -f $centstorageRunFile; then
		echo "No lock file found in $centstorageRunFile"
		return 1
    fi    
    centstoragePID=`head -n 1 $centstorageRunFile`
    kill -s INT $centstoragePID
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

prefix=@CENTREON_DIR@
centstorageBin=@CENTSTORAGE_BINDIR@/centstorage
centstorageCfgFile=@CENTREON_ETC@/conf.pm
centstorageLogDir=@CENTREON_LOG@
centstorageRunDir=@CENTREON_RUNDIR@
#centstorageVarDir=${prefix}/var/
centstorageRunFile=${centstorageRunDir}/centstorage.pid
centstorageDemLog=${centstorageLogDir}/centstorage.log
centstorageLockDir=/var/lock/subsys
centstorageLockFile=centstorage
NICE=5

# Check that centstorage exists.
if [ ! -f $centstorageBin ]; then
    echo "Executable file $centstorageBin not found.  Exiting."
    exit 1
fi

# Check that centstorage.cfg exists.
if [ ! -f $centstorageCfgFile ]; then
    echo "Configuration file $centstorageCfgFile not found.  Exiting."
    exit 1
fi
          
# See how we were called.
case "$1" in
    start)
		# Check lock file
	    if test -f $centstorageRunFile; then
			echo "Error : $centstorageRunFile already Exists."
			NDcentstorageRUNNING=`ps -edf | grep $centstorageBin | grep -v grep | wc -l `
			if [ $NDcentstorageRUNNING = 0 ] ; then
			    echo "But no centstorage process runnig"
			    rm -f $centstorageRunFile
			    echo "Removing centstorage pid file"
			else 
			    exit 1
			fi
	    fi
	    # Test if running directory exist.
	    rundir_exist
	    echo "Starting centstorage Collector : centstorage"
	    sudo - @NAGIOS_USER@ -c "$centstorageBin >> $centstorageDemLog 2>&1"
	    if [ -d $centstorageLockDir ]; then 
	    	touch $centstorageLockDir/$centstorageLockFile; 
	    fi
	    exit 0
    ;;
    
    stop)
		echo "Stopping centreon data collector Collector : centstorage"
		killproc_centstorage centstorage
		echo -n 'Waiting for centstorage to exit .'
		for i in `seq 20` ; do
		if status_centstorage > /dev/null; then
		    echo -n '.'
		    sleep 1
		else
		    break
		fi
		done
		if status_centstorage > /dev/null; then
			echo ''
			echo 'Warning - running centstorage did not exit in time'
			exit 1
		else
			echo ' done.'
			exit 0
		fi
    ;;
    
    status)
	    status_centstorage centstorage
    ;;
    
    restart)
	    $0 stop
	    $0 start
    ;;
    
    reload|force-reload)
	    if test ! -f $centstorageRunFile; then
			$0 start
		    else
			centstoragePID=`head -n 1 $centstorageRunFile`
			if status_centstorage > /dev/null; then
			    killproc_centstorage centstorage -HUP
			    echo "done"
			else
			    $0 stop
			    $0 start
			fi
	    fi
    ;;
    
    *)
	    echo "Usage: centstorage {start|stop|restart|reload|status}"
	    exit 1
    ;;
    
esac
# End of this script
