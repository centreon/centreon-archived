#!/usr/bin/env bash
#----
## @Synopsis	Install script for CentStorage
## @Copyright	Copyright 2008, Guillaume Watteeux
## @license	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for CentStorage
#----
# install centreon centstorage  
#################################
# SVN: $Id$

echo -e "\n$line"
echo -e "\t$(gettext "Start CentStorage Installation")"
echo -e "$line"

###### Check disk space
check_tmp_disk_space
[ "$?" -eq 1 ] && purge_centreon_tmp_dir

###### Require
#################################
## Where is install_dir_centreon ?
locate_centreon_installdir

## locate or create Centreon log dir
locate_centreon_logdir
locate_centreon_etcdir
locate_centreon_rundir
locate_centreon_generationdir
locate_centreon_varlib

## Config pre-require
locate_init_d
locate_cron_d

locate_centstorage_bindir
#locate_centstorage_libdir
locate_centstorage_rrddir
## Config Nagios
check_centreon_group
check_centreon_user

## Populate temporaty source directory
copyInTempFile 2>>$LOG_FILE 

## Create temporary folder
log "INFO" "$(gettext "Create working directory")"
mkdir -p $TMP_DIR/final/www/install
mkdir -p $TMP_DIR/work/www/install
mkdir -p $TMP_DIR/final/bin
mkdir -p $TMP_DIR/work/bin
mkdir -p $TMP_DIR/final/cron
mkdir -p $TMP_DIR/work/cron
[ ! -d $INSTALL_DIR_CENTREON/examples ] && \
	mkdir -p $INSTALL_DIR_CENTREON/examples
DISTRIB=""
find_OS "DISTRIB"
if [ "$DISTRIB" = "DEBIAN" ]; then
    cp -f $BASE_DIR/tmpl/install/debian/centstorage.init.d $TMP_DIR/src
    cp -f $BASE_DIR/tmpl/install/debian/centstorage.default $TMP_DIR/src
elif [ "$DISTRIB" = "SUSE" ]; then
    cp -f $BASE_DIR/tmpl/install/suse/centstorage.init.d $TMP_DIR/src
    cp -f $BASE_DIR/tmpl/install/suse/centstorage.sysconfig $TMP_DIR/src
else
    cp -f $BASE_DIR/tmpl/install/redhat/centstorage.init.d $TMP_DIR/src
    cp -f $BASE_DIR/tmpl/install/redhat/centstorage.sysconfig $TMP_DIR/src
fi

###### DB script
#################################
## Change Macro in working dir
log "INFO" "$(gettext "Change macros for createTablesCentstorage.sql")"
${SED} -e 's|@CENTSTORAGE_RRD@|'"$CENTSTORAGE_RRD"'|g' \
	$TMP_DIR/src/www/install/createTablesCentstorage.sql \
	> $TMP_DIR/work/www/install/createTablesCentstorage.sql

## Copy in final dir
log "INFO" "$(gettext "Copying www/install/createTablesCentstorage.sql in final directory")"
cp $TMP_DIR/work/www/install/createTablesCentstorage.sql \
	$TMP_DIR/final/www/install/createTablesCentstorage.sql >> $LOG_FILE 2>&1

## Copy CreateTablesCentStorage.sql in INSTALL_DIR_CENTREON
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -g "$WEB_GROUP" -m 644 \
	$TMP_DIR/final/www/install/createTablesCentstorage.sql \
	$INSTALL_DIR_CENTREON/www/install/createTablesCentstorage.sql \
	>> $LOG_FILE 2>&1
check_result $?  "$(gettext "install") www/install/createTablesCentstorage.sql"

###### RRD directory
#################################
## Create CentStorage Status folder
if [ ! -d "$CENTSTORAGE_RRD/status" ] ; then
	log "INFO" "$(gettext "Create CentStorage status directory")"
	$INSTALL_DIR/cinstall $cinstall_opts \
		-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 755 \
		$CENTSTORAGE_RRD/status >> $LOG_FILE 2>&1
	check_result $? "$(gettext "Creating Centreon Directory") '$CENTSTORAGE_RRD/status'"
else
	echo_passed "$(gettext "CentStorage status Directory already exists")" "$passed"
fi
## Create CentStorage metrics folder
if [ ! -d "$CENTSTORAGE_RRD/metrics" ] ; then
	log "INFO" "$(gettext "Create CentStorage metrics directory")"
	$INSTALL_DIR/cinstall $cinstall_opts \
		-u "$CENTREON_USER" -g "$CENTREON_USER" -d 755 \
		$CENTSTORAGE_RRD/metrics >> $LOG_FILE 2>&1
	check_result $? "$(gettext "Creating Centreon Directory") '$CENTSTORAGE_RRD/metrics'"
else
	echo_passed "$(gettext "CentStorage metrics Directory already exists")" "$passed"
fi

## Change right to RRD directory
check_rrd_right

###### CentStorage binary
#################################

log "INFO" "$(gettext "Copying CentStorage binary in final directory")"
#cp $TMP_DIR/work/bin/centstorage $TMP_DIR/final/bin/centstorage >> $LOG_FILE 2>&1
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -m 755 \
	$TMP_DIR/src/bin/centstorage $CENTSTORAGE_BINDIR/centstorage \
	>> $LOG_FILE 2>&1
check_result $? "$(gettext "Install CentStorage binary")"

## Copy lib for CentStorage TODO
####
#log "INFO" "$(gettext "Install library for centstorage")"
#$INSTALL_DIR/cinstall $cinstall_opts \
#	-g "$CENTREON_GROUP" -m 644 \
#	$TMP_DIR/final/lib $INSTALL_DIR_CENTREON/lib >> $LOG_FILE 2>&1
#check_result $? "$(gettext "Install library for centstorage")"

## Change right on CENTREON_RUNDIR
log "INFO" "$(gettext "Change right") : $CENTREON_RUNDIR"
$INSTALL_DIR/cinstall $cinstall_opts -u "$CENTREON_USER" -d 750 \
	$CENTREON_RUNDIR >> $LOG_FILE 2>&1
check_result $? "$(gettext "Change right") : $CENTREON_RUNDIR"

###### CentStorate init
#################################
## Change macros in CentStorage init script
log "INFO" "$(gettext "Change macros for centstorage init script")"
${SED} -e 's|@CENTREON_DIR@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTREON_RUNDIR@|'"$CENTREON_RUNDIR"'|g' \
	-e 's|@CENTSTORAGE_BINDIR@|'"$CENTSTORAGE_BINDIR"'|g' \
	-e 's|@CENTREON_USER@|'"$CENTREON_USER"'|g' \
	$TMP_DIR/src/centstorage.init.d > $TMP_DIR/work/centstorage.init.d
check_result $? "$(gettext "Change macros for centstorage init script")"

if [ "$DISTRIB" = "DEBIAN" ]; then
	${SED} -e 's|"NO"|"YES"|g' -e "s|@CENTREON_LOG@|$CENTREON_LOG|g" -e "s|@CENTREON_ETC@|$CENTREON_ETC|g" -e "s|@CENTREON_USER@|$CENTREON_USER|g" $TMP_DIR/src/centstorage.default > $TMP_DIR/work/centstorage.default
	check_result $? "$(gettext "Replace Centstorage default script Macro")"
	cp $TMP_DIR/work/centstorage.default $TMP_DIR/final/centstorage.default
	cp $TMP_DIR/final/centstorage.default $INSTALL_DIR_CENTREON/examples/centstorage.default
elif [ "$DISTRIB" = "REDHAT" -o "$DISTRIB" = "SUSE" ]; then
	${SED} -e "s|@CENTREON_USER@|$CENTREON_USER|g" \
        -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
		-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
		$TMP_DIR/src/centstorage.sysconfig > $TMP_DIR/work/centstorage.sysconfig
	check_result $? "$(gettext "Replace CentStorage sysconfig script Macro")"
	cp $TMP_DIR/work/centstorage.sysconfig $TMP_DIR/final/centstorage.sysconfig
	cp $TMP_DIR/final/centstorage.sysconfig $INSTALL_DIR_CENTREON/examples/centstorage.sysconfig
fi

cp $TMP_DIR/work/centstorage.init.d \
	$TMP_DIR/final/centstorage.init.d
cp $TMP_DIR/final/centstorage.init.d \
	$INSTALL_DIR_CENTREON/examples/centstorage.init.d

RC="1"
if [ ! "${CENTSTORAGE_INSTALL_INIT}" ] ; then 
	yes_no_default "$(gettext "Do you want me to install CentStorage init script ?")"
	RC="$?"
elif [ "${CENTSTORAGE_INSTALL_INIT}" -eq 1 ] ; then 
	RC="0"
fi
if [ "$RC" -eq "0" ] ; then 
	log "INFO" "$(gettext "CentStorage init script installed")"
	$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
		$TMP_DIR/final/centstorage.init.d \
		$INIT_D/centstorage >> $LOG_FILE 2>&1
	check_result $? "$(gettext "CentStorage init script installed")"
	RC="1"
	if [ "$DISTRIB" = "DEBIAN" ]; then
	    log "INFO" "$(gettext "CentStorage default script installed")"
        $INSTALL_DIR/cinstall $cinstall_opts -m 644 \
            $TMP_DIR/final/centstorage.default \
            /etc/default/centstorage >> $LOG_FILE 2>&1
	    check_result $? "$(gettext "CentStorage default script installed")"
	    log "INFO" "$(gettext "CentStorage default script installed")"
	elif [ "$DISTRIB" = "REDHAT" -o "$DISTRIB" = "SUSE" ]; then
		log "INFO" "$(gettext "CentStorage sysconfig script installed")"
        $INSTALL_DIR/cinstall $cinstall_opts -m 644 \
            $TMP_DIR/final/centstorage.sysconfig \
            /etc/sysconfig/centstorage >> $LOG_FILE 2>&1
	    check_result $? "$(gettext "CentStorage sysconfig script installed")"
	    log "INFO" "$(gettext "CentStorage sysconfig script installed")"
    fi
	if [ ! "${CENTSTORAGE_INSTALL_RUNLVL}" ] ; then
		yes_no_default "$(gettext "Do you want me to install CentStorage run level ?")"
		RC="$?"
	elif [ "${CENTSTORAGE_INSTALL_RUNLVL}" -eq 1 ] ; then
		RC="0"
	fi
	if [ "$RC" -eq "0" ] ; then
		install_init_service "centstorage" | tee -a $LOG_FILE
		#check_result $? "$(gettext "CentStorage run level installed")"
		log "INFO" "$(gettext "CentStorage run level installed")"
	else
		echo_passed "$(gettext "CentStorage run level not installed")" "$passed"
		log "INFO" "$(gettext "CentStorage run level not installed")"
	fi
	if /etc/init.d/centstorage status >/dev/null; then
		log "INFO" "$(gettext "CentStorage stop")"
		/etc/init.d/centstorage stop
		check_result $? "$(gettext "CentStorage stop")"
	fi
    
        # Install centstorage perl lib
	$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/common/ \
        $PERL_LIB_DIR/centreon/common/ >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/script.pm \
        $PERL_LIB_DIR/centreon/script.pm >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/centstorage/ \
        $PERL_LIB_DIR/centreon/centstorage/ >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/script/centstorage.pm \
        $PERL_LIB_DIR/centreon/script/centstorage.pm >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/script/centstorage_purge.pm \
        $PERL_LIB_DIR/centreon/script/centstorage_purge.pm >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/script/centreon_check_perfdata.pm \
        $PERL_LIB_DIR/centreon/script/centreon_check_perfdata.pm >> $LOG_FILE 2>&1
    echo_success "$(gettext "CentStorage Perl lib installed")" "$ok"
    log "INFO" "$(gettext "CentStorage Perl lib installed")"
else
	echo_passed "$(gettext "CentStorage init script not installed, please use "):\n $INSTALL_DIR_CENTREON/INSTALL_DIR_CENTREONexamples/centstorage.init.d" "$passed"
	log "INFO" "$(gettext "CentStorage init script not installed, please use "): $INSTALL_DIR_CENTREON/examples/centstorage.init.d"
fi

###### Cron
#################################
### Macro
## logAnalyser

log "INFO" "$(gettext "Install logAnalyser")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -m 755 \
	$TMP_DIR/src/cron/logAnalyser \
	$INSTALL_DIR_CENTREON/cron/logAnalyser >> $LOG_FILE 2>&1
check_result $?  "$(gettext "Install logAnalyser")"

## logAnalyserBroker

log "INFO" "$(gettext "Install logAnalyserBroker")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -m 755 \
	$TMP_DIR/src/bin/logAnalyserBroker \
	$CENTSTORAGE_BINDIR/logAnalyserBroker >> $LOG_FILE 2>&1
check_result $?  "$(gettext "Install logAnalyserBroker")"

## nagiosPerfTrace

log "INFO" "$(gettext "Install nagiosPerfTrace")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -m 755 \
	$TMP_DIR/src/cron/nagiosPerfTrace \
	$INSTALL_DIR_CENTREON/cron/nagiosPerfTrace >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install nagiosPerfTrace")"

if [ -f "$CENTREON_LOG/nagiosPerfTrace.log" ]; then
    log "INFO" "$(gettext "Applying proper permissions to nagiosPerfTrace.log file")"
    $CHOWN $CENTREON_USER:$CENTREON_GROUP $CENTREON_LOG/nagiosPerfTrace.log
fi

## Purge

#cp $TMP_DIR/work/cron/centstorage_purge $TMP_DIR/final/cron/centstorage_purge >> $LOG_FILE 2>&1
#log "INFO" "$(gettext "Install centstorage_purge")"
#$INSTALL_DIR/cinstall $cinstall_opts \
#	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -m 755 \
#	$TMP_DIR/final/cron/centstorage_purge \
#	$INSTALL_DIR_CENTREON/cron/purgeLogs >> $LOG_FILE 2>&1
#check_result $? "$(gettext "Install centstorage_purge")"

## cron file
log "INFO" "$(gettext "Change macros for centstorage.cron")"
${SED} -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
	-e 's|@CENTSTORAGE_BINDIR@|'"$CENTSTORAGE_BINDIR"'|g' \
	-e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_USER@|'"$CENTREON_USER"'|g' \
	-e 's|@WEB_USER@|'"$WEB_USER"'|g' \
	$BASE_DIR/tmpl/install/centstorage.cron > $TMP_DIR/work/centstorage.cron
check_result $? "$(gettext "Change macros for centstorage.cron")"

cp $TMP_DIR/work/centstorage.cron $TMP_DIR/final/centstorage.cron >> $LOG_FILE 2>&1
log "INFO" "$(gettext "Install centstorage cron")"
$INSTALL_DIR/cinstall $cinstall_opts -m 644 \
	$TMP_DIR/final/centstorage.cron \
	$CRON_D/centstorage >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install CentStorage cron")"

## Install Logrotate
log "INFO" "$(gettext "Change macros for centstorage.logrotate")"
${SED} -e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
    $TMP_DIR/src/logrotate/centstorage > $TMP_DIR/work/centstorage.logrotate
check_result $? "$(gettext "Change macros for centstorage.logrotate")"
cp $TMP_DIR/work/centstorage.logrotate $TMP_DIR/final/centstorage.logrotate >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Install centstorage.logrotate")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 644 \
    $TMP_DIR/final/centstorage.logrotate $LOGROTATE_D/centstorage >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install Centreon Storage logrotate.d file")"

###### Post Install
#################################
createCentStorageInstallConf

## wait and see...
## sql console inject ?

