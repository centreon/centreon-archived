#!/usr/bin/env bash
#----
## @Synopsis	Install script for CentStorage
## @Copyright	Copyright 2008, Guillaume Watteeux
## @Copyright	Copyright 2008-2020, Centreon
## @license	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for CentStorage
#----
## Centreon is developed with GPL Licence 2.0
##
## GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
##
## Developed by : Julien Mathis - Romain Le Merlus
## Contributors : Guillaume Watteeux - Maximilien Bersoult
##
## This program is free software; you can redistribute it and/or
## modify it under the terms of the GNU General Public License
## as published by the Free Software Foundation; either version 2
## of the License, or (at your option) any later version.
##
## This program is distributed in the hope that it will be useful,
## but WITHOUT ANY WARRANTY; without even the implied warranty of
## MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
## GNU General Public License for more details.
##
##    For information : infos@centreon.com

echo -e "\n$line"
echo -e "\t$(gettext "Starting CentStorage Installation")"
echo -e "$line"

###### Check disk space
check_tmp_disk_space
if [ "$?" -eq 1 ] ; then
  if [ "$silent_install" -eq 1 ] ; then
    purge_centreon_tmp_dir "silent"
  else
    purge_centreon_tmp_dir
  fi
fi

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

# Install centstorage perl lib
$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
    $TMP_DIR/src/lib/perl/centreon/common/ \
    $PERL_LIB_DIR/centreon/common/ >> $LOG_FILE 2>&1
$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
    $TMP_DIR/src/lib/perl/centreon/script.pm \
    $PERL_LIB_DIR/centreon/script.pm >> $LOG_FILE 2>&1
$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
    $TMP_DIR/src/lib/perl/centreon/script/centstorage_purge.pm \
    $PERL_LIB_DIR/centreon/script/centstorage_purge.pm >> $LOG_FILE 2>&1

###### Cron
#################################
### Macro
## logAnalyserBroker

log "INFO" "$(gettext "Install logAnalyserBroker")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -m 755 \
	$TMP_DIR/src/bin/logAnalyserBroker \
	$CENTSTORAGE_BINDIR/logAnalyserBroker >> $LOG_FILE 2>&1
check_result $?  "$(gettext "Install logAnalyserBroker")"

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

###### Post Install
#################################
createCentStorageInstallConf

## wait and see...
## sql console inject ?

