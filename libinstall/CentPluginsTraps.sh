#!/bin/bash
#----
## @Synopsis	Install script for CentPluginsTraps
## @Copyright	Copyright 2008, Guillaume Watteeux
## @license	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for CentPluginsTraps
#----
# install script for CentPlugins
#################################
# SVN: $Id$

echo -e "\n$line"
echo -e "\t$(gettext "Start CentPlugins Traps Installation")"
echo -e "$line"

###### Check disk space
check_tmp_disk_space
[ "$?" -eq 1 ] && purge_centreon_tmp_dir

## Where is nagios_pluginsdir
locate_plugindir

## Locate centreon etc_dir
locate_centreon_etcdir
locate_centreon_rundir
locate_centreon_logdir
locate_snmp_etcdir
locate_init_d
locate_centreontrapd_bindir

check_centreon_user
check_centreon_group
check_httpd_directory
check_user_apache

## Populate temporaty source directory
copyInTempFile 2>>$LOG_FILE

## Create temporary folder
log "INFO" "$(gettext "Create working directory")"
mkdir -p $TMP_DIR/final/bin
mkdir -p $TMP_DIR/work/bin
mkdir -p $TMP_DIR/work/snmptrapd
mkdir -p $TMP_DIR/final/snmptrapd
mkdir -p $TMP_DIR/work/centreontrapd
mkdir -p $TMP_DIR/final/centreontrapd

# Prepare init.d
DISTRIB=""
find_OS "DISTRIB"
if [ "$DISTRIB" = "DEBIAN" ]; then
	cp -f $BASE_DIR/tmpl/install/debian/centreontrapd.init.d $TMP_DIR/src
	cp -f $BASE_DIR/tmpl/install/debian/centreontrapd.default $TMP_DIR/src
elif [ "$DISTRIB" = "SUSE" ]; then
    cp -f $BASE_DIR/tmpl/install/suse/centreontrapd.init.d $TMP_DIR/src
else
	cp -f $BASE_DIR/tmpl/install/redhat/centreontrapd.init.d $TMP_DIR/src
	cp -f $BASE_DIR/tmpl/install/redhat/centreontrapd.sysconfig $TMP_DIR/src
fi

## Create centreon_traps directory
$INSTALL_DIR/cinstall $cinstall_opts \
	-u $WEB_USER -g $CENTREON_GROUP -d 775 \
	$SNMP_ETC/centreon_traps >> $LOG_FILE 2>&1

log "INFO" "$(gettext "Backup all your snmp files")"
# Backup snmptrapd.conf if exist
if [ -e "$SNMP_ETC/snmptrapd.conf" ] ; then
	log "INFO" "$(gettext "Backup") : $SNMP_ETC/snmptrapd.conf"
	\cp $SNMP_ETC/snmptrapd.conf $SNMP_ETC/snmptrapd.conf.bak-centreon
fi

# Backup snmp.conf if exist
if [ -e "$SNMP_ETC/snmp.conf" ] ; then
	log "INFO" "$(gettext "Backup") : $SNMP_ETC/snmp.conf"
	\cp $SNMP_ETC/snmp.conf $SNMP_ETC/snmp.conf.bak-centreon
fi

log "INFO" "$(gettext "Installing snmptt")"
# Change macros on snmptrapd.conf
${SED} -e 's|@CENTREONTRAPD_BINDIR@|'"$CENTREONTRAPD_BINDIR"'|g' \
	-e 's|@CENTREON_USER@|'"$CENTREON_USER"'|g' \
	$TMP_DIR/src/snmptrapd/snmptrapd.conf > \
	$TMP_DIR/work/snmptrapd/snmptrapd.conf 2>>$LOG_FILE
check_result $? "$(gettext "Change macros for snmptrapd.conf")"


###### CentreonTrapd init
#################################
## Change macros in CentTrapd init script
${SED} -e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_RUNDIR@|'"$CENTREON_RUNDIR"'|g' \
	-e 's|@CENTREONTRAPD_BINDIR@|'"$CENTREONTRAPD_BINDIR"'|g' \
	-e 's|@CENTREON_USER@|'"$CENTREON_USER"'|g' \
	$TMP_DIR/src/centreontrapd.init.d > $TMP_DIR/work/centreontrapd.init.d
check_result $? "$(gettext "Replace CentreonTrapd init script Macro")"

if [ "$DISTRIB" = "DEBIAN" ]; then
	${SED} -e 's|"NO"|"YES"|g' -e "s|@CENTREON_USER@|$CENTREON_USER|g" $TMP_DIR/src/centreontrapd.default > $TMP_DIR/work/centreontrapd.default
	check_result $? "$(gettext "Replace CentreonTrapd default script Macro")"
	cp $TMP_DIR/work/centreontrapd.default $TMP_DIR/final/centreontrapd.default
	cp $TMP_DIR/final/centreontrapd.default $INSTALL_DIR_CENTREON/examples/centreontrapd.default
elif [ "$DISTRIB" = "REDHAT" ]; then
	${SED} -e "s|@CENTREON_USER@|$CENTREON_USER|g" \
		-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
		$TMP_DIR/src/centreontrapd.sysconfig > $TMP_DIR/work/centreontrapd.sysconfig
	check_result $? "$(gettext "Replace CentreonTrapd sysconfig script Macro")"
	cp $TMP_DIR/work/centreontrapd.sysconfig $TMP_DIR/final/centreontrapd.sysconfig
	cp $TMP_DIR/final/centreontrapd.sysconfig $INSTALL_DIR_CENTREON/examples/centreontrapd.sysconfig
fi

cp $TMP_DIR/work/centreontrapd.init.d $TMP_DIR/final/centreontrapd.init.d
cp $TMP_DIR/final/centreontrapd.init.d $INSTALL_DIR_CENTREON/examples/centreontrapd.init.d

RC="1"
if [ ! "${CENTREONTRAPD_INSTALL_INIT}" ] ; then
	yes_no_default "$(gettext "Do you want me to install CentreonTrapd init script ?")"
	RC="$?"
elif [ "${CENTREONTRAPD_INSTALL_INIT}" -eq 1 ] ; then
	RC="0"
fi
if [ "$RC" -eq "0" ] ; then 
	log "INFO" "$(gettext "CentreonTrapd init script installed")"
	$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
				 $TMP_DIR/final/centreontrapd.init.d \
                 $INIT_D/centreontrapd >> $LOG_FILE 2>&1
	check_result $? "$(gettext "CentreonTrapd init script installed")"
	log "INFO" "$(gettext "CentreonTrapd init script installed")"
	RC="1"
	if [ "$DISTRIB" = "DEBIAN" ]; then
		log "INFO" "$(gettext "CentreonTrapd default script installed")"
			$INSTALL_DIR/cinstall $cinstall_opts -m 644 \
				 $TMP_DIR/final/centreontrapd.default \
				 /etc/default/centreontrapd >> $LOG_FILE 2>&1
		check_result $? "$(gettext "CentreonTrapd default script installed")"
		log "INFO" "$(gettext "CentreonTrapd default script installed")"
	elif [ "$DISTRIB" = "REDHAT" ]; then
		log "INFO" "$(gettext "CentreonTrapd sysconfig script installed")"
			$INSTALL_DIR/cinstall $cinstall_opts -m 644 \
				 $TMP_DIR/final/centreontrapd.sysconfig \
				 /etc/sysconfig/centreontrapd >> $LOG_FILE 2>&1
		check_result $? "$(gettext "CentreonTrapd sysconfig script installed")"
		log "INFO" "$(gettext "CentreonTrapd sysconfig script installed")"
	fi
	if [ ! "${CENTCORE_INSTALL_RUNLVL}" ] ; then
		yes_no_default "$(gettext "Do you want me to install CentreonTrapd run level ?")"
		RC="$?"
	elif [ "${CENTCORE_INSTALL_RUNLVL}" -eq 1 ] ; then
		RC="0"
	fi
	if [ "$RC" -eq "0" ] ; then
		install_init_service "centreontrapd" | tee -a $LOG_FILE
		#check_result $? "$(gettext "CentreonTrapd run level installed")"
		log "INFO" "$(gettext "CentreonTrapd run level installed")"
	else
		echo_passed "$(gettext "CentreonTrapd run level not installed")" "$passed"
		log "INFO" "$(gettext "CentreonTrapd run level not installed")"
	fi
else
	echo_passed "$(gettext "CentreonTrapd init script not installed, please use "):\n $INSTALL_DIR_CENTREON/examples/centreontrapd.init.d" "$passed"
	log "INFO" "$(gettext "CentreonTrapd init script not installed, please use "): $INSTALL_DIR_CENTREON/examples/centreontrapd.init.d"
fi


## Install all config file
write_snmp_conf="1"
if [ "$upgrade" = "1" ]; then
    yes_no_default "$(gettext "Should I overwrite all your SNMP configuration files?")"
    if [ "$?" -eq 0 ] ; then
        write_snmp_conf="1"
	log "INFO" "$(gettext "SNMP configuration will be overwritten")"
    else
        write_snmp_conf="0"
        log "INFO" "$(gettext "Keeping configuration files")"
    fi
fi
if [ "$write_snmp_conf" = "1" ]; then
    log "INFO" "$(gettext "Install") : snmptrapd.conf"
    $INSTALL_DIR/cinstall $cinstall_opts -m 644 \
            $TMP_DIR/work/snmptrapd/snmptrapd.conf \
            $SNMP_ETC/snmptrapd.conf >> $LOG_FILE 2>&1
    check_result $? "$(gettext "Install") : snmptrapd.conf"

    log "INFO" "$(gettext "Install") : snmp.conf"
    $INSTALL_DIR/cinstall $cinstall_opts -m 644 \
            $TMP_DIR/work/snmptrapd/snmp.conf \
            $SNMP_ETC/snmp.conf >> $LOG_FILE 2>&1
    check_result $? "$(gettext "Install") : snmp.conf"
fi
## End ##

## Copy Binaries
log "INFO" "$(gettext "Install : centreontrapdforward")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-m 755 $TMP_DIR/src/bin/centreontrapdforward $CENTREONTRAPD_BINDIR/centreontrapdforward >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install") : centreontrapdforward"

log "INFO" "$(gettext "Install : centreontrapd")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-m 755 $TMP_DIR/src/bin/centreontrapd $CENTREONTRAPD_BINDIR/centreontrapd >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install") : centreontrapd"

log "INFO" "$(gettext "Install") : spool directory"
$INSTALL_DIR/cinstall $cinstall_opts -d 775 \
	/var/spool/centreontrapd


# Create traps directory in nagios pluginsdir
#$INSTALL_DIR/cinstall $cinstall_opts -d 664 \
#	-g $WEB_GROUP \
#	$NAGIOS_PLUGIN/traps

#echo_success "$(gettext "Install SNMPTT")" "$ok"
## TODO : comment ^^ , log and echo_*
#	: copy centreon.pm and centreon.conf if not exist


