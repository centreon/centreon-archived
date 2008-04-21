# -*-Shell-script-*-
# install script for centcore

echo "------------------------------------------------------------------------"
echo -e "\t`gettext \"Start CentCore Installation\"`"
echo "------------------------------------------------------------------------"

## Where is install_dir_centreon ?
locate_centreon_installdir

## Config Nagios
check_group_nagios
check_user_nagios

## Populate temporaty source directory
copyInTempFile

## Create temporary folder
log "INFO" "`gettext \"Create working directory\"`"
mkdir -p $TMPDIR/final/bin 
mkdir -p $TMPDIR/work/bin
[ ! -d $INSTALL_DIR_CENTREON/examples ] && mkdir -p $INSTALL_DIR_CENTREON/examples

## Change macros 
sed -e 's|@CENTREON_PATH@|"$INSTALL_DIR_CENTREON"|g' \
 -e 's|@RRD_PERL@|"$RRD_PERL"|g' \
 $TMPDIR/src/bin/centcore > $TMPDIR/work/bin/centcore
echo_success "`gettext \"Replace CentCore Macro\"`" "$ok"
log "INFO" "`gettext \"Copying CentCore bianry in final directory\"`"
cp $TMPDIR/work/bin/centcore $TMPDIR/final/bin/centcore 2>&1  >> $LOG_FILE

chown $NAGIOS_USER:$NAGIOS_GROUP $TMPDIR/final/bin/centcore
chmod 7755 $TMPDIR/final/bin/centcore
echo_success "`gettext \"Set CentCore properties\"`" "$ok"

## Change macros in CentCore init script
sed -e 's|@CENTREON_PATH@|"$INSTALL_DIR_CENTREON"|g' \
 -e 's|@NAGIOS_USER@|"$NAGIOS_USER"|g' \
 -e 's|@NAGIOS_GROUP@|"$NAGIOS_GROUP"|g' \
 $TMPDIR/src/init.d.centcore > $TMPDIR/work/init.d.centcore
echo_success "`gettext \"Replace CentCore init script Macro\"`" "$ok"
cp $TMPDIR/work/init.d.centcore $TMPDIR/final/init.d.centcore
cp $TMPDIR/final/init.d.centcore $INSTALL_DIR_CENTREON/examples/init.d.centcore
chmod 755 $TMPDIR/final/init.d.centcore

yes_no_default "`gettext \"Do you want I install CentCore init script ?\"`"
if [ $? -eq 0 ] ; then 
	log "INFO" "`gettext \"CentCore init script installed\"`"
	cp -a $TMPDIR/final/init.d.centcore $INIT_D/centcore
	yes_no_default "`gettext \"Do you want I install CentCore run level ?\"`"
		if [ $? -eq 0 ] ; then
			install_init_service "centcore"
		fi
else
	echo_passed "`gettext \"CentCore init script not installed, please use \"`:\n $INSTALL_DIR_CENTREON/examples/init.d.centcore" "$passed"
	log "INFO" "`gettext \"CentCore init script not installed, please use \"`: $INSTALL_DIR_CENTREON/examples/init.d.centcore"
fi

## wait and see...
## sql console inject ?
