#!/bin/bash
# install script for centcore
#################################
# SVN: $Id$

echo "------------------------------------------------------------------------"
echo -e "\t`gettext \"Start CentCore Installation\"`"
echo "------------------------------------------------------------------------"

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
check_group_nagios
check_user_nagios

## Populate temporaty source directory
copyInTempFile 2>>$LOG_FILE

## Create temporary folder
log "INFO" "`gettext \"Create working directory\"`"
mkdir -p $TMPDIR/final/bin 
mkdir -p $TMPDIR/work/bin
[ ! -d $INSTALL_DIR_CENTREON/examples ] && mkdir -p $INSTALL_DIR_CENTREON/examples
# Copy init.d template in src
cp -f $BASE_DIR/tmpl/install/centcore.init.d $TMPDIR/src

###### CentCore binary
#################################
## Change macros for CentCore binary
sed -e 's|@CENTREON_DIR@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTCORE_BINDIR@|'"$CENTCORE_BINDIR"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_RUNDIR@|'"$CENTREON_RUNDIR"'|g' \
	-e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	-e 's|@RRD_PERL@|'"$RRD_PERL"'|g' \
	-e 's|@BIN_SSH@|'"$BIN_SSH"'|g' \
	-e 's|@BIN_SCP@|'"$BIN_SCP"'|g' \
	$TMPDIR/src/bin/centcore > $TMPDIR/work/bin/centcore

echo_success "`gettext \"Replace CentCore Macro\"`" "$ok"
log "INFO" "`gettext \"Copying CentCore binary in final directory\"`"
cp $TMPDIR/work/bin/centcore $TMPDIR/final/bin/centcore 2>&1  >> $LOG_FILE

$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -m 755 -v \
	$TMPDIR/final/bin/centcore $CENTCORE_BINDIR/centcore >> $LOG_FILE 2>&1
echo_success "`gettext \"Copy CentCore in binary directory\"`" "$ok"
log "INFO" "`gettext \"Copying CentCore in binary directory\"`"

## Change right on CENTREON_RUNDIR
log "INFO" "`gettext \"Change right\"` : $CENTREON_RUNDIR"
$INSTALL_DIR/cinstall -g $NAGIOS_USER -d 775 -v \
	$CENTREON_RUNDIR >> $LOG_FILE 2>&1

## Change tight on CENTREON_VARLIB
log "INFO" "`gettext \"Change right\"` : $CENTREON_VARLIB"
$INSTALL_DIR/cinstall -g $NAGIOS_USER -d 775 -v \
	$CENTREON_VARLIB >> $LOG_FILE 2>&1

###### CentCore init
#################################
## Change macros in CentCore init script
sed -e 's|@CENTREON_DIR@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_RUNDIR@|'"$CENTREON_RUNDIR"'|g' \
	-e 's|@CENTCORE_BINDIR@|'"$CENTCORE_BINDIR"'|g' \
	-e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' \
	$TMPDIR/src/centcore.init.d > $TMPDIR/work/centcore.init.d

echo_success "`gettext \"Replace CentCore init script Macro\"`" "$ok"
cp $TMPDIR/work/centcore.init.d $TMPDIR/final/centcore.init.d
cp $TMPDIR/final/centcore.init.d $INSTALL_DIR_CENTREON/examples/centcore.init.d

yes_no_default "`gettext \"Do you want I install CentCore init script ?\"`"
if [ $? -eq 0 ] ; then 
	$INSTALL_DIR/cinstall -m 755 -v \
		$TMPDIR/final/centcore.init.d $INIT_D/centcore >> $LOG_FILE 2>&1
	log "INFO" "`gettext \"CentCore init script installed\"`"

	yes_no_default "`gettext \"Do you want me to install CentCore run level ?\"`"
		if [ $? -eq 0 ] ; then
			install_init_service "centcore"
			log "INFO" "`gettext \"CentCore run level installed\"`"
		else
			echo_passed "`gettext \"CentCore run level not installed\"`" "$passed"
			log "INFO" "`gettext \"CentCore run level not installed\"`"
		fi
else
	echo_passed "`gettext \"CentCore init script not installed, please use \"`:\n $INSTALL_DIR_CENTREON/examples/centcore.init.d" "$passed"
	log "INFO" "`gettext \"CentCore init script not installed, please use \"`: $INSTALL_DIR_CENTREON/examples/centcore.init.d"
fi

###### Post Install
#################################
createCentCoreInstallConf

## wait and see...
## sql console inject ?

