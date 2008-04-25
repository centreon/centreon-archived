# -*-Shell-script-*-
# install script for CentPlugins

echo "------------------------------------------------------------------------"
echo -e "\t`gettext \"Start CentPlugins Installation\"`"
echo "------------------------------------------------------------------------"

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
copyInTempFile >> $LOG_FILE 2>&1

## Create temporary folder
log "INFO" "`gettext \"Create working directory\"`"
mkdir -p $TMPDIR/final/plugins
mkdir -p $TMPDIR/work/plugins

## Change Macro in working dir
for FILE in `ls $TMPDIR/src/plugins/src/check*centreon*` \
	$TMPDIR/src/plugins/src/check_meta_service \
	`ls $TMPDIR/src/plugins/src/check_snmp*` \
	$TMPDIR/src/plugins/src/process-service-perfdata \
	$TMPDIR/src/plugins/src/submit_host_check_result \
	$TMPDIR/src/plugins/src/submit_service_check_result; do

	sed -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' \
		-e 's|@INSTALL_DIR_NAGIOS@|'"$INSTALL_DIR_NAGIOS"'|g' \
		-e 's|@NAGIOS_ETC@|'"$NAGIOS_ETC"'|g' \
		-e 's|@NAGIOS_PLUGINS@|'"$NAGIOS_PLUGIN"'|g' \
		-e 's|@RRDTOOL_PERL_LIB@|'"$RRD_PERL"'|g' \
		-e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' \
		-e 's|@CENTPLUGINS_TMP@|'"$CENTPLUGINS_TMP"'|g' \
		"$FILE" > "$TMPDIR/work/plugins/`basename $FILE`"
done

## Copy in final dir
log "INFO" "`gettext \"Copying plugins in final directory\"`"
cp -r $TMPDIR/work/plugins/* $TMPDIR/final/plugins >> $LOG_FILE 2>&1

## Install the plugins
log "INFO" "`gettext \"Installing the plugins\"`"
$INSTALL_DIR/cinstall -u root -g root -m 755 \
	-p "$TMPDIR/final/plugins" "$TMPDIR/final/plugins/*" "$NAGIOS_PLUGIN" >> $LOG_FILE 2>&1

log "INFO" "`gettext \"Install temporary directory for plugins\"` : $CENTPLUGINS_TMP"
$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -d 775 \
	$CENTPLUGINS_TMP >> $LOG_FILE 2>&1
echo_success "`gettext \"CentPlugins is installed\"`"
