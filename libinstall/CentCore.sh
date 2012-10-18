#!/bin/bash
#----
## @Synopsis	Install script for CentCore
## @Copyright	Copyright 2008, Guillaume Watteeux
## @license	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for CentCore
#----
# install script for centcore
#################################
# SVN: $Id$

echo -e "\n$line"
echo -e "\t$(gettext "Start CentCore Installation")"
echo -e "$line"

###### Check disk space
check_tmp_disk_space
[ "$?" -eq 1 ] && purge_centreon_tmp_dir

###### Require
#################################
## Where is install_dir_centreon ?
locate_centreon_installdir
locate_centreon_etcdir
locate_centreon_rundir
locate_centreon_logdir
locate_centreon_varlib
locate_centreon_generationdir
locate_centcore_bindir

## locate binaries
locate_ssh
locate_scp

## Config Nagios
check_centreon_group
check_centreon_user

## Other requirement
locate_init_d

## Populate temporaty source directory
copyInTempFile 2>>$LOG_FILE

## Create temporary folder
log "INFO" "$(gettext "Create working directory")"
mkdir -p $TMP_DIR/{work,final}/bin 
mkdir -p $TMP_DIR/{work,final}/www/include/configuration/configGenerate
mkdir -p $TMP_DIR/{work,final}/www/include/monitoring/external_cmd
[ ! -d $INSTALL_DIR_CENTREON/examples ] && mkdir -p $INSTALL_DIR_CENTREON/examples
# Copy init.d template in src
DISTRIB=""
find_OS "DISTRIB"
if [ "$DISTRIB" = "DEBIAN" ]; then
    cp -f $BASE_DIR/tmpl/install/debian/centcore.init.d $TMP_DIR/src
    cp -f $BASE_DIR/tmpl/install/debian/centcore.default $TMP_DIR/src
else
    cp -f $BASE_DIR/tmpl/install/redhat/centcore.init.d $TMP_DIR/src
fi

###### CentCore binary
#################################
## Change macros for CentCore binary
${SED} -e 's|@CENTREON_DIR@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTCORE_BINDIR@|'"$CENTCORE_BINDIR"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_RUNDIR@|'"$CENTREON_RUNDIR"'|g' \
	-e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	-e 's|@RRD_PERL@|'"$RRD_PERL"'|g' \
	-e 's|@BIN_SSH@|'"$BIN_SSH"'|g' \
	-e 's|@BIN_SCP@|'"$BIN_SCP"'|g' \
	$TMP_DIR/src/bin/centcore > $TMP_DIR/work/bin/centcore
check_result $? "$(gettext "Change CentCore Macro")"

log "INFO" "$(gettext "Copying CentCore binary in final directory")"
cp $TMP_DIR/work/bin/centcore $TMP_DIR/final/bin/centcore 2>&1  >> $LOG_FILE

log "INFO" "$(gettext "Copying CentCore in binary directory")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -m 755 \
	$TMP_DIR/final/bin/centcore $CENTCORE_BINDIR/centcore >> $LOG_FILE 2>&1
check_result $? "$(gettext "Copy CentCore in binary directory")"

## Change CentCore link in CentWeb
${SED} -e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	$TMP_DIR/src/www/include/configuration/configGenerate/formGenerateFiles.php \
	> $TMP_DIR/work/www/include/configuration/configGenerate/formGenerateFiles.php

${SED} -e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	$TMP_DIR/src/www/include/monitoring/external_cmd/functions.php \
	> $TMP_DIR/work/www/include/monitoring/external_cmd/functions.php \

cp $TMP_DIR/work/www/include/configuration/configGenerate/formGenerateFiles.php\
	$TMP_DIR/final/www/include/configuration/configGenerate/ \
	>> $LOG_FILE 2>&1
cp $TMP_DIR/work/www/include/monitoring/external_cmd/functions.php \
	$TMP_DIR/final/www/include/monitoring/external_cmd/ \
	>> $LOG_FILE 2>&1

$INSTALL_DIR/cinstall $cinstall_opts -f \
	-u "$WEB_USER" -g "$WEB_GROUP" -m 644 \
	$TMP_DIR/final/www/include/configuration/configGenerate/formGenerateFiles.php \
	$INSTALL_DIR_CENTREON/www/include/configuration/configGenerate/formGenerateFiles.php \
	>> $LOG_FILE 2>&1


$INSTALL_DIR/cinstall $cinstall_opts -f \
	-u "$WEB_USER" -g "$WEB_GROUP" -m 644 \
	$TMP_DIR/final/www/include/monitoring/external_cmd/functions.php \
	$INSTALL_DIR_CENTREON/www/include/monitoring/external_cmd/functions.php \
	>> $LOG_FILE 2>&1


## Change right on CENTREON_RUNDIR
log "INFO" "$(gettext "Change right") : $CENTREON_RUNDIR"
$INSTALL_DIR/cinstall $cinstall_opts -u "$CENTREON_USER" -d 750 \
	$CENTREON_RUNDIR >> $LOG_FILE 2>&1
check_result $? "$(gettext "Change right") : $CENTREON_RUNDIR"

## Change tight on CENTREON_VARLIB
log "INFO" "$(gettext "Change right") : $CENTREON_VARLIB"
$INSTALL_DIR/cinstall $cinstall_opts -g "$CENTREON_USER" -d 775 \
	$CENTREON_VARLIB >> $LOG_FILE 2>&1
check_result $? "$(gettext "Change right") : $CENTREON_VARLIB"

## Add logrotate
log "INFO" "$(gettext "Change macros for centcore.logrotate")"
${SED} -e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
        $TMP_DIR/src/logrotate/centcore > $TMP_DIR/work/centcore.logrotate
check_result $? "$(gettext "Change macros for centcore.logrotate")"
cp $TMP_DIR/work/centcore.logrotate $TMP_DIR/final/centcore.logrotate >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Install centcore.logrotate")"
$INSTALL_DIR/cinstall $cinstall_opts \
        -m 644 \
        $TMP_DIR/final/centcore.logrotate $LOGROTATE_D/centcore >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install Centreon Core logrotate.d file")"

###### CentCore init
#################################
## Change macros in CentCore init script
${SED} -e 's|@CENTREON_DIR@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_RUNDIR@|'"$CENTREON_RUNDIR"'|g' \
	-e 's|@CENTCORE_BINDIR@|'"$CENTCORE_BINDIR"'|g' \
	-e 's|@CENTREON_USER@|'"$CENTREON_USER"'|g' \
	$TMP_DIR/src/centcore.init.d > $TMP_DIR/work/centcore.init.d
check_result $? "$(gettext "Replace CentCore init script Macro")"

if [ "$DISTRIB" = "DEBIAN" ]; then
  ${SED} -e 's|"NO"|"YES"|g' -e "s|@CENTREON_USER@|$CENTREON_USER|g" $TMP_DIR/src/centcore.default > $TMP_DIR/work/centcore.default
  check_result $? "$(gettext "Replace CentCore default script Macro")"
  cp $TMP_DIR/work/centcore.default $TMP_DIR/final/centcore.default
  cp $TMP_DIR/final/centcore.default $INSTALL_DIR_CENTREON/examples/centcore.default
fi

cp $TMP_DIR/work/centcore.init.d $TMP_DIR/final/centcore.init.d
cp $TMP_DIR/final/centcore.init.d $INSTALL_DIR_CENTREON/examples/centcore.init.d

RC="1"
if [ ! "${CENTCORE_INSTALL_INIT}" ] ; then
	yes_no_default "$(gettext "Do you want me to install CentCore init script ?")"
	RC="$?"
elif [ "${CENTCORE_INSTALL_INIT}" -eq 1 ] ; then
	RC="0"
fi
if [ "$RC" -eq "0" ] ; then 
	log "INFO" "$(gettext "CentCore init script installed")"
	$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
		$TMP_DIR/final/centcore.init.d \
                 $INIT_D/centcore >> $LOG_FILE 2>&1
	check_result $? "$(gettext "CentCore init script installed")"
	log "INFO" "$(gettext "CentCore init script installed")"
	RC="1"
        if [ "$DISTRIB" = "DEBIAN" ]; then
	    log "INFO" "$(gettext "CentCore default script installed")"
            $INSTALL_DIR/cinstall $cinstall_opts -m 644 \
                 $TMP_DIR/final/centcore.default \
                 /etc/default/centcore >> $LOG_FILE 2>&1
	    check_result $? "$(gettext "CentCore default script installed")"
	    log "INFO" "$(gettext "CentCore default script installed")"
	    RC="1"
        fi
	if [ ! "${CENTCORE_INSTALL_RUNLVL}" ] ; then
		yes_no_default "$(gettext "Do you want me to install CentCore run level ?")"
		RC="$?"
	elif [ "${CENTCORE_INSTALL_RUNLVL}" -eq 1 ] ; then
		RC="0"
	fi
	if [ "$RC" -eq "0" ] ; then
		install_init_service "centcore" | tee -a $LOG_FILE
		#check_result $? "$(gettext "CentCore run level installed")"
		log "INFO" "$(gettext "CentCore run level installed")"
	else
		echo_passed "$(gettext "CentCore run level not installed")" "$passed"
		log "INFO" "$(gettext "CentCore run level not installed")"
	fi
else
	echo_passed "$(gettext "CentCore init script not installed, please use "):\n $INSTALL_DIR_CENTREON/examples/centcore.init.d" "$passed"
	log "INFO" "$(gettext "CentCore init script not installed, please use "): $INSTALL_DIR_CENTREON/examples/centcore.init.d"
fi

###### Post Install
#################################
createCentCoreInstallConf

## wait and see...
## sql console inject ?

