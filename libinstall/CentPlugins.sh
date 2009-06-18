#!/bin/bash	
#----
## @Synopsis	Install script for CentPlugins
## @Copyright	Copyright 2008, Guillaume Watteeux
## @license	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for CentPlugins
#----
# install script for CentPlugins
#################################
# SVN: $Id$

echo -e "\n$line"
echo -e "\t$(gettext "Start CentPlugins Installation")"
echo -e "$line"

###### Check disk space
check_tmp_disk_space
[ "$?" -eq 1 ] && purge_centreon_tmp_dir

## Where is nagios_pluginsdir
locate_nagios_plugindir

## Locale for sed
locate_nagios_vardir
locate_nagios_installdir
locate_nagios_etcdir
locate_rrd_perldir
locate_centplugins_tmpdir

## check nagios user and group
check_user_nagios
check_group_nagios


## Populate temporaty source directory
copyInTempFile 2>>$LOG_FILE

## Create temporary folder
log "INFO" "$(gettext "Create working directory")"
mkdir -p $TMP_DIR/final/plugins
mkdir -p $TMP_DIR/work/plugins

## Change Macro in working dir
flg_error=0
for FILE in `ls $TMP_DIR/src/plugins/src/check*centreon*` \
	$TMP_DIR/src/plugins/src/centreon.pm \
	$TMP_DIR/src/plugins/src/centreon.conf \
	$TMP_DIR/src/plugins/src/check_meta_service \
	`ls $TMP_DIR/src/plugins/src/check_snmp*` \
	$TMP_DIR/src/plugins/src/process-service-perfdata \
	$TMP_DIR/src/plugins/src/submit_host_check_result \
	$TMP_DIR/src/plugins/src/submit_service_check_result; do

	${SED} -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' \
		-e 's|@INSTALL_DIR_NAGIOS@|'"$INSTALL_DIR_NAGIOS"'|g' \
		-e 's|@NAGIOS_ETC@|'"$NAGIOS_ETC"'|g' \
		-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
		-e 's|@NAGIOS_PLUGINS@|'"$NAGIOS_PLUGIN"'|g' \
		-e 's|@RRDTOOL_PERL_LIB@|'"$RRD_PERL"'|g' \
		-e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' \
		-e 's|@CENTPLUGINS_TMP@|'"$CENTPLUGINS_TMP"'|g' \
		"$FILE" > "$TMP_DIR/work/plugins/`basename $FILE`"
	[ $? -ne 0 ] && flg_error=1
done
check_result $flg_error "$(gettext "Change macros for CentPlugins")"

## Copy in final dir
log "INFO" "$(gettext "Copying plugins in final directory")"
cp -r $TMP_DIR/work/plugins/* $TMP_DIR/final/plugins >> $LOG_FILE 2>&1

## Install the plugins
log "INFO" "$(gettext "Installing the plugins")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-m 755 -p $TMP_DIR/final/plugins \
	$TMP_DIR/final/plugins/* $NAGIOS_PLUGIN >> $LOG_FILE 2>&1
check_result $? "$(gettext "Installing the plugins")"

## change right for a specific file
$INSTALL_DIR/cinstall -f $cinstall_opts -g $NAGIOS_GROUP \
	-m 664 $TMP_DIR/final/plugins/centreon.conf \
	$NAGIOS_PLUGIN/centreon.conf >> $LOG_FILE 2>&1
check_result $? "$(gettext "Change right on") centreon.conf"

log "INFO" "$(gettext "Install temporary directory for plugins") : $CENTPLUGINS_TMP"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u $NAGIOS_USER -g $NAGIOS_GROUP -d 755 -v \
	$CENTPLUGINS_TMP >> $LOG_FILE 2>&1
echo_success "$(gettext "CentPlugins is installed")"

if [ "$PROCESS_CENTREON_SNMP_TRAPS" -eq 1 ] ; then
	. $INSTALL_DIR/CentPluginsTraps.sh
fi

###### Post Install
#################################
createCentPluginsInstallConf


