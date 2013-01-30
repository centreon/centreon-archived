#!/bin/sh
### BEGIN INIT INFO
# Provides:		centcore
# Required-Start:	$local_fs $network
# Required-Stop:	$local_fs $network
# Default-Start:	2 3 4 5
# Default-Stop:		0 1 6
# Should-Start:		mysql
# Should-Stop:
# Short-Description:	Start daemon centcore at boot
# Description:		Enable service provided CentCore : Manage pollers
### END INIT INFO

PKGNAME=centcore
DESC="Centcore"
DAEMON=@CENTCORE_BINDIR@/centcore
PIDFILE=@CENTREON_RUNDIR@/centcore.pid

if [ ! -x "${DAEMON}" ]; then
  echo "The program ${DAEMON} does not exists or is not executable"
  exit 3
fi

# Include the default user configuration if exists
[ -r /etc/default/${PKGNAME} ] && . /etc/default/${PKGNAME}

# Load the VERBOSE setting and other rcS variables
[ -f /etc/init/vars.sh ] && . /etc/init/vars.sh

# Define LSB log_* functions.
# Depend on lsb-base (>= 3.0-6) to ensure that this file is present.
. /lib/lsb/init-functions

if [ -z "${RUN_AT_STARTUP}" -o "${RUN_AT_STARTUP}" != "YES" ]; then
    log_warning_msg "Not starting $PKGNAME, edit /etc/default/$PKGNAME to start it."
    exit 0
fi

if [ -z "${CENTREON_USER}" ]; then
    log_warning_msg "Not starting $PKGNAME, CENTREON_USER not set in /etc/default/$PKGNAME."
    exit 0
fi

do_start()
{
  start-stop-daemon --start --background --quiet --pidfile ${PIDFILE} --exec ${DAEMON} \
    --chuid ${CENTREON_USER} --user ${CENTREON_USER} --test
  [ "$?" = "0" ] || return 1
  start-stop-daemon --start --background --quiet --pidfile ${PIDFILE} --exec ${DAEMON} \
    --make-pidfile --chuid ${CENTREON_USER} --user ${CENTREON_USER}
  [ "$?" = "0" ] || return 2
  return 0
}

do_stop()
{
  start-stop-daemon --stop --quiet --retry=TERM/30/KILL/5 --user ${CENTREON_USER} --pidfile ${PIDFILE}
  [ "$?" = "2" ] && return 2
  rm -rf ${PIDFILE}
  [ "$?" = 0 ] && return 0 || return 1
}

case "$1" in
  start)
    [ "${VERBOSE}" != "no" ] && log_daemon_msg "Starting ${DESC}" "${PKGNAME}"
    do_start
    case "$?" in
      0|1) [ "${VERBOSE}" != "no" ] && log_end_msg 0 ;;
      2) [ "${VERBOSE}" != "no" ] && log_end_msg 1 ;;
    esac
    ;;
   stop)
     [ "${VERBOSE}" != no ] && log_daemon_msg "Stopping ${DESC}" "${PKGNAME}"
     do_stop
     case "$?" in
       0|1) [ "${VERBOSE}" != no ] && log_end_msg 0 ;;
       2) [ "${VERBOSE}" != no ] && log_end_msg 1 ;;
     esac
     ;;
   status)
     status_of_proc ${DAEMON} ${PKGNAME} -p ${PIDFILE}
     ;;
   restart|force-reload)
     [ "${VERBOSE}" != no ] && log_daemon_msg "Restarting ${DESC}" "${PKGNAME}"
     do_stop
     case "$?" in
       0|1)
        do_start
        case "$?" in
          0) log_end_msg 0 ;;
          1) log_end_msg 1 ;;
          *) log_end_msg 1 ;;
        esac
        ;;
       *) log_end_msg 1 ;;
     esac
     ;;
   *)
     echo "Usage: ${SCRIPTNAME} (start|stop|status|restart|force-reload)" >&2
     exit 3
esac
