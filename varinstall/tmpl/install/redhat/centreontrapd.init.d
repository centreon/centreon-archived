#! /bin/bash
#
# crond          Start/Stop the centreontrapd daemon.
#
# chkconfig: 2345 71 31
# description: centreontrapd is a Centreon program that manage traps
# processname: centreontrapd
# config: @CENTREON_ETC@/centreon-config.pm
# pidfile: ${centstorageRunDir}/centreontrapd.pid

# Source function library.
. /etc/init.d/functions

binary=@CENTREONTRAPD_BINDIR@/centreontrapd
servicename=centreontrapd
user=@CENTREON_USER@
timeout=60
start_timeout=5

pidfile=@CENTREON_RUNDIR@/centreontrapd.pid

[ -e /etc/sysconfig/$servicename ] && . /etc/sysconfig/$servicename

# Check if we can find the binary.
if [ ! -x $binary ]; then
    echo -n $"Starting $servicename.";
    failure $"Executable file $binary not found. Exiting."
    echo
    exit 2
fi

start() {
	echo -n $"Starting $servicename: "
        if [ -e "$pidfile" ] && [ -n "$(cat $pidfile)" ] && [ -e "/proc/`cat $pidfile`" ]; then
                echo -n $"cannot start $servicename: $servicename is already running.";
                failure $"cannot start $servicename: $servicename already running.";
                echo
                return 1
        fi
        if [ ! -e "$pidfile" ] ; then
                pid=$(pidofproc $binary)
                if [ -n "$pid" ] ; then
                        echo -n $"cannot start $servicename: $servicename is already running.";
                        failure $"cannot start $servicename: $servicename already running.";
                        echo
                        return 1
                fi
        fi

	if [ "$(id -u -n)" = "$user" ] ; then
		daemon ''$binary' '$OPTIONS' > /dev/null 2>&1 &'
	else
		daemon --user $user ''$binary' '$OPTIONS' > /dev/null 2>&1 &'
	fi

	i=0
	while : ; do
		if [ "$i" -gt $start_timeout ] ; then
			failure $"service not launched"
			echo
			return 1
		fi
		pid=$(pidofproc $binary)
		if [ -n "$pid" ] ; then
			echo $pid > $pidfile
			break
		fi
		sleep 1
		i=$(($i + 1))
	done
	success $"service launched"
	echo
	return 0
}

stop() {
	echo -n $"Stopping $servicename: "
	if [ ! -e "$pidfile" ] || [ -z "$(cat $pidfile)" ] ; then
		killproc -d $timeout "$binary"
	else
		killproc -p "$pidfile" -d $timeout "$binary"
	fi
	RETVAL=$?
	echo
	return $RETVAL
}	

rhstatus() {
	if [ ! -e "$pidfile" ] || [ -z "$(cat $pidfile)" ] ; then
		status "$binary"
	else
		status -p "$pidfile" "$binary"
	fi
}	

restart() {
  	stop
	start
}	

reload() {
	echo -n $"Reloading $servicename daemon configuration: "
	if [ ! -e "$pidfile" ] || [ -z "$(cat $pidfile)" ] ; then
		killproc "$binary" -HUP
	else
		killproc -p "$pidfile" "$binary" -HUP
	fi
    RETVAL=$?
    echo
    return $RETVAL
}	

case "$1" in
  start)
  	start
	;;
  stop)
  	stop
	;;
  restart)
  	restart
	;;
  reload)
  	reload
	;;
  status)
  	rhstatus
	;;
  condrestart)
  	[ -f /var/lock/subsys/centstorage ] && restart || :
	;;
  *)
	echo $"Usage: $0 {start|stop|status|reload|restart|condrestart}"
	exit 1
esac

