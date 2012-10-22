#!/bin/bash
# init file for snmptt
# Alex Burger - 08/29/02
# 	      - 09/08/03 - Added snmptt.pid support to Stop function
#             - 05/17/09 - Added LSB init keywords, change priority, add
#                          INIT INFO.
# chkconfig: - 49 51
# description: SNMP Trap Translator daemon
#
# processname: /usr/bin/snmptt
# pidfile: /var/run/snmptt.pid

### BEGIN INIT INFO
# Provides: snmptt
# Default-Stop: 0 1 6
# Required-Start: $syslog $local_fs
# Required-Stop: $syslog $local_fs
# Should-Start: $network snmptrapd
# Should-Stop: $network snmptrapd
# Short-Description: SNMP Trap Translator daemon
### END INIT INFO

# source function library
. /etc/init.d/functions

OPTIONS="--daemon --ini=@SNMPTT_INI_FILE@"
RETVAL=0
prog="snmptt"

start() {
	echo -n $"Starting $prog: "
        daemon /usr/bin/snmptt $OPTIONS
	RETVAL=$?
	echo
	touch /var/lock/subsys/snmptt
	return $RETVAL
}

stop() {
	echo -n $"Stopping $prog: "
	killproc /usr/bin/snmptt 2>/dev/null
	RETVAL=$?
	echo
	rm -f /var/lock/subsys/snmptt
	if test -f /var/run/snmptt.pid ; then
	  [ $RETVAL -eq 0 ] && rm -f /var/run/snmptt.pid
	fi
	return $RETVAL
}

reload(){
        echo -n $"Reloading config file: "
        killproc snmptt -HUP
        RETVAL=$?
        echo
        return $RETVAL
}

restart(){
	stop
	start
}

condrestart(){
    [ -e /var/lock/subsys/snmptt ] && restart
    return 0
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
  reload|force-reload)
	reload
        ;;
  try-restart|condrestart)
	condrestart
	;;
  status)
        status snmptt
	RETVAL=$?
        ;;
  *)
	echo $"Usage: $0 {start|stop|status|restart|try-restart|condrestart|reload|force-reload}"
	RETVAL=1
esac

exit $RETVAL
