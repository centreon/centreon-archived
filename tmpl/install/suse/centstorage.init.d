#!/usr/bin/env bash
### BEGIN INIT INFO
# Provides:            centstorage
# Required-Start:   $syslog $remote_fs
# Should-Start:     centstorage
# Required-Stop:    $syslog $remote_fs
# Should-Stop:      centstorage
# Default-Start:       2 3 5
# Default-Stop:        0 1 6
# Description:         centstorage is a Centreon program that manage perfs
# Short-Description:   centstorage is a Centreon program that manage perfs
### END INIT INFO

# Source function library.
. /lib/lsb/init-functions
  
binary=@CENTSTORAGE_BINDIR@/centstorage
servicename=$(basename "$0")
user=@CENTREON_USER@
timeout=60
start_timeout=5
logfile=@CENTREON_LOG@/centstorage.log
config=@CENTREON_ETC@/conf.pm

FOLDER=/var/run/centreon/
if [ ! -d "$FOLDER" ]; then # Control will enter here if $DIRECTORY doesn't exist.
    mkdir $FOLDER
fi

# Add optionnal option for centstorage daemon
opt_daemon=""
if [ -n "${logfile}" ]; then
    opt_daemon=" --logfile=${logfile}"
fi

if [ -n "${config}" ]; then
    opt_daemon="${opt_daemon} --config=${config}"
fi

pidfile=@CENTREON_RUNDIR@/centstorage.pid

# Check if we can find the binary.
if [ ! -x $binary ]; then
    echo -n $"Starting $servicename.";
    log_failure_msg $"Executable file $binary not found. Exiting."
    echo
    exit 2
fi

start() {
	echo -n $"Starting $servicename: "
        if [ -e "$pidfile" ] && [ -n "$(cat $pidfile)" ] && [ -e "/proc/`cat $pidfile`" ]; then
                echo -n $"cannot start $servicename: $servicename is already running.";
                log_failure_msg $"cannot start $servicename: $servicename already running.";
                echo
                return 1
        fi
        if [ ! -e "$pidfile" ] ; then
                pid=$(pidofproc $binary)
                if [ -n "$pid" ] ; then
                        echo -n $"cannot start $servicename: $servicename is already running.";
                        log_failure_msg $"cannot start $servicename: $servicename already running.";
                        echo
                        return 1
                fi
        fi

	if [ "$(id -u -n)" = "$user" ] ; then
		startproc $binary ${opt_daemon}
	else
		startproc -u $user $binary ${opt_daemon}
	fi

	i=0
	while : ; do
		if [ "$i" -gt $start_timeout ] ; then
			log_failure_msg $"service not launched"
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
	log_success_msg $"service launched"
	echo
	return 0
}

stop() {
	echo -n $"Stopping $servicename: "
	if [ ! -e "$pidfile" ] || [ -z "$(cat $pidfile)" ] ; then
		killproc -t$timeout "$binary" 
	else
		killproc -p "$pidfile" -t$timeout "$binary" 
	fi
	RETVAL=$?
	echo
	return $RETVAL
}	

rhstatus() {
	if [ ! -e "$pidfile" ] || [ -z "$(cat $pidfile)" ] ; then
		echo -n "centstorage is "
                checkproc "$binary"
                rc_status -v
	else
		echo -n "centstorage is "
                checkproc -p "$pidfile" "$binary"
                rc_status -v
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

