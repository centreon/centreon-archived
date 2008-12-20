#!/bin/bash
#----
## @Synopsis	Install script for CentStorage
## @Copyright	Copyright 2008, Guillaume Watteeux
## @license	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for CentStorage
#----
# install centreon centstorage  
#################################
# SVN: $Id$

echo "$line"
echo -e "\t$(gettext "Start CentStorage Installation")"
echo "$line"

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
locate_rrd_perldir
locate_nagios_vardir
locate_init_d
locate_cron_d

locate_centstorage_bindir
#locate_centstorage_libdir
locate_centstorage_rrddir
## Config Nagios
check_group_nagios
check_user_nagios

## Populate temporaty source directory
copyInTempFile 2>>$LOG_FILE 

## Create temporary folder
log "INFO" "$(gettext "Create working directory")"
mkdir -p $TMPDIR/final/www/install
mkdir -p $TMPDIR/work/www/install
mkdir -p $TMPDIR/final/bin
mkdir -p $TMPDIR/work/bin
[ ! -d $INSTALL_DIR_CENTREON/examples ] && \
	mkdir -p $INSTALL_DIR_CENTREON/examples
cp -f $BASE_DIR/tmpl/install/centstorage.init.d $TMPDIR/src
cp -rf $TMPDIR/src/lib $TMPDIR/final

###### DB script
#################################
## Change Macro in working dir
log "INFO" "$(gettext "Change macros for createTablesCentstorage.sql")"
${SED} -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' \
	-e 's|@CENTSTORAGE_RRD@|'"$CENTSTORAGE_RRD"'|g' \
	$TMPDIR/src/www/install/createTablesCentstorage.sql \
	> $TMPDIR/work/www/install/createTablesCentstorage.sql

## Copy in final dir
log "INFO" "$(gettext "Copying www/install/createTablesCentstorage.sql in final directory")"
cp $TMPDIR/work/www/install/createTablesCentstorage.sql \
	$TMPDIR/final/www/install/createTablesCentstorage.sql >> $LOG_FILE 2>&1

## Copy CreateTablesCentStorage.sql in INSTALL_DIR_CENTREON
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -g "$WEB_GROUP" -m 755 \
	$TMPDIR/final/www/install/createTablesCentstorage.sql \
	$INSTALL_DIR_CENTREON/www/install/createTablesCentstorage.sql \
	>> $LOG_FILE 2>&1
check_result $?  "$(gettext "install") www/install/createTablesCentstorage.sql"

###### RRD directory
#################################
## Create CentStorage Status folder
if [ ! -d "$CENTSTORAGE_RRD/status" ] ; then
	log "INFO" "$(gettext "Create CentStorage status directory")"
	$INSTALL_DIR/cinstall $cinstall_opts \
		-u "$NAGIOS_USER" -g "$NAGIOS_GROUP" -d 755 \
		$CENTSTORAGE_RRD/status >> $LOG_FILE 2>&1
	check_result $? "$(gettext "Creating Centreon Directory") '$CENTSTORAGE_RRD/status'"
else
	echo_passed "$(gettext "CentStorage status Directory already exists")" "$passed"
fi
## Create CentStorage metrics folder
if [ ! -d "$CENTSTORAGE_RRD/metrics" ] ; then
	log "INFO" "$(gettext "Create CentStorage metrics directory")"
	$INSTALL_DIR/cinstall $cinstall_opts \
		-u "$NAGIOS_USER" -g "$NAGIOS_GROUP" -d 755 \
		$CENTSTORAGE_RRD/metrics >> $LOG_FILE 2>&1
	check_result $? "$(gettext "Creating Centreon Directory") '$CENTSTORAGE_RRD/metrics'"
else
	echo_passed "$(gettext "CentStorage metrics Directory already exists")" "$passed"
fi
    
    
###### CentStorage binary
#################################
## Change macros in CentStorage binary
log "INFO" "$(gettext "Change macros for centstorage binary")"
${SED} -e 's|@CENTREON_PATH@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTREON_RUNDIR@|'"$CENTREON_RUNDIR"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@RRD_PERL@|'"$RRD_PERL"'|g' \
	 $TMPDIR/src/bin/centstorage > $TMPDIR/work/bin/centstorage
check_result $? "$(gettext "Change macros for centstorage binary")"
	 
log "INFO" "$(gettext "Copying CentStorage binary in final directory")"
cp $TMPDIR/work/bin/centstorage $TMPDIR/final/bin/centstorage >> $LOG_FILE 2>&1
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$NAGIOS_USER" -g "$NAGIOS_GROUP" -m 755 \
	$TMPDIR/final/bin/centstorage $CENTSTORAGE_BINDIR/centstorage \
	>> $LOG_FILE 2>&1
check_result $? "$(gettext "Install CentStorage binary")"

#echo_success "$(gettext "Set CentStorage properties")" "$ok"

## Copy lib for CentStorage
log "INFO" "$(gettext "Install library for centstorage")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-g "$NAGIOS_GROUP" -m 766 \
	$TMPDIR/final/lib $INSTALL_DIR_CENTREON/lib >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install library for centstorage")"

## Change right on CENTREON_RUNDIR
log "INFO" "$(gettext "Change right") : $CENTREON_RUNDIR"
$INSTALL_DIR/cinstall $cinstall_opts -u "$NAGIOS_USER" -d 750 \
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
	-e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' \
	$TMPDIR/src/centstorage.init.d > $TMPDIR/work/centstorage.init.d
check_result $? "$(gettext "Change macros for centstorage init script")"

cp $TMPDIR/work/centstorage.init.d \
	$TMPDIR/final/centstorage.init.d
cp $TMPDIR/final/centstorage.init.d \
	$INSTALL_DIR_CENTREON/examples/centstorage.init.d

RC="1"
if [ "${CENTSTORAGE_INSTALL_INIT:-0}" -eq 1 ] ; then 
	RC="0"	
else
	yes_no_default "$(gettext "Do you want me to install CentStorage init script ?")"
	RC="$?"
fi
if [ "$RC" -eq "0" ] ; then 
	log "INFO" "$(gettext "CentStorage init script installed")"
	$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
		$TMPDIR/final/centstorage.init.d \
		$INIT_D/centstorage >> $LOG_FILE 2>&1
	check_result $? "$(gettext "CentStorage init script installed")"
	RC="1"
	if [ "${CENTSTORAGE_INSTALL_RUNLVL:-0}" -eq 1 ] ; then
		RC="1"
	else
		yes_no_default "$(gettext "Do you want me to install CentStorage run level ?")"
		RC="$?"
	fi
	if [ "$RC" -eq "0" ] ; then
		install_init_service "centstorage" | tee -a $LOG_FILE
		#check_result $? "$(gettext "CentStorage run level installed")"
		log "INFO" "$(gettext "CentStorage run level installed")"
	else
		echo_passed "$(gettext "CentStorage run level not installed")" "$passed"
		log "INFO" "$(gettext "CentStorage run level not installed")"
	fi
else
	echo_passed "$(gettext "CentStorage init script not installed, please use "):\n $INSTALL_DIR_CENTREON/examples/centstorage.init.d" "$passed"
	log "INFO" "$(gettext "CentStorage init script not installed, please use "): $INSTALL_DIR_CENTREON/examples/centstorage.init.d"
fi

###### Cron
#################################
### Macro
## logAnalyser
log "INFO" "$(gettext "Change macros for logAnalyser")"
${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
        -e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	$TMPDIR/src/bin/logAnalyser > $TMPDIR/work/bin/logAnalyser
check_result $? "$(gettext "Change macros for logAnalyser")"

cp $TMPDIR/work/bin/logAnalyser $TMPDIR/final/bin/logAnalyser >> $LOG_FILE 2>&1
log "INFO" "$(gettext "Install logAnalyser")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$NAGIOS_USER" -g "$NAGIOS_GROUP" -m 755 \
	$TMPDIR/final/bin/logAnalyser \
	$CENTSTORAGE_BINDIR/logAnalyser >> $LOG_FILE 2>&1
check_result $?  "$(gettext "Install logAnalyser")"

#echo_success "$(gettext "Set logAnalyser properties")" "$ok"

## nagiosPerfTrace
log "INFO" "$(gettext "Change macros for nagiosPerfTrace")"
${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTSTORAGE_LIB@|'"$CENTSTORAGE_RRD"'|g' \
	-e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' \
	-e 's|@NAGIOS_GROUP@|'"$NAGIOS_GROUP"'|g' \
	$TMPDIR/src/bin/nagiosPerfTrace > $TMPDIR/work/bin/nagiosPerfTrace
check_result $? "$(gettext "Change macros for nagiosPerfTrace")"

cp $TMPDIR/work/bin/nagiosPerfTrace $TMPDIR/final/bin/nagiosPerfTrace >> $LOG_FILE 2>&1
log "INFO" "$(gettext "Install nagiosPerfTrace")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$NAGIOS_USER" -g "$NAGIOS_GROUP" -m 755 \
	$TMPDIR/final/bin/nagiosPerfTrace \
	$CENTSTORAGE_BINDIR/nagiosPerfTrace >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install nagiosPerfTrace")"

#echo_success "$(gettext "Set nagiosPerfTrace properties")" "$ok"

## cron file 
log "INFO" "$(gettext "Change macros for centstorage.cron")"
${SED} -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
	-e 's|@CENTSTORAGE_BINDIR@|'"$CENTSTORAGE_BINDIR"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CRONUSER@|'"$NAGIOS_USER"'|g' \
	$BASE_DIR/tmpl/install/centstorage.cron > $TMPDIR/work/centstorage.cron
check_result $? "$(gettext "Change macros for centstorage.cron")"

cp $TMPDIR/work/centstorage.cron $TMPDIR/final/centstorage.cron >> $LOG_FILE 2>&1
log "INFO" "$(gettext "Install centstorage cron")"
$INSTALL_DIR/cinstall $cinstall_opts -m 644 \
	$TMPDIR/final/centstorage.cron \
	$CRON_D/centstorage >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install CentStorage cron")"

###### Post Install
#################################
createCentStorageInstallConf

## wait and see...
## sql console inject ?

