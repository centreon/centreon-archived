# -*-Shell-script-*-
# install centreon centstorage  

echo "------------------------------------------------------------------------"
echo -e "\t`gettext \"Start CentStorage Installation\"`"
echo "------------------------------------------------------------------------"

## Where is install_dir_centreon ?
locate_centreon_installdir

## locate or create Centreon log dir
locate_centreon_logdir
locate_centreon_etcdir
locate_centreon_generationdir

## Config pre-require
locate_rrd_perldir
locate_nagios_vardir
locate_init_d
locate_cron_d

locate_centstorage_bindir
locate_centstorage_rrddir
## Config Nagios
check_group_nagios
check_user_nagios

## Populate temporaty source directory
copyInTempFile

## Create temporary folder
log "INFO" "`gettext \"Create working directory\"`"
mkdir -p $TMPDIR/final/www/install
mkdir -p $TMPDIR/work/www/install
mkdir -p $TMPDIR/final/bin
mkdir -p $TMPDIR/work/bin
[ ! -d $INSTALL_DIR_CENTREON/examples ] && mkdir -p $INSTALL_DIR_CENTREON/examples

## Change Macro in working dir

sed -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' \
 -e 's|@CENTREON_VAR@|'"$CENTREON_GENDIR"'|g' \
 $TMPDIR/src/www/install/createTablesODS.sql > $TMPDIR/work/www/install/createTablesODS.sql

## Copy in final dir
log "INFO" "`gettext \"Copying www/install/createTablesODS.sql in final directory\"`"
cp $TMPDIR/work/www/install/createTablesODS.sql $TMPDIR/final/www/install/createTablesODS.sql 2>&1 >> $LOG_FILE

## Create CentStorage Status folder
if [ ! -d "$CENTSTORAGE_RRD/status" ] ; then
	log "INFO" "`gettext \"Create CentStorage status directory\"`"
	$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -d 775 \
		$CENTSTORAGE_RRD/status 2>&1 >> $LOG_FILE
	echo_success "`gettext \"Creating Centreon Directory\"` '$CENTSTORAGE_RRD/status'" "$ok"
else
	echo_passed "`gettext \"CentStorage status Directory already exists\"`" "$passed"
fi
## Create CentStorage metrics folder
if [ ! -d "$CENTSTORAGE_RRD/metrics" ] ; then
	log "INFO" "`gettext \"Create CentStorage metrics directory\"`"
	$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -d 775 \
		$CENTSTORAGE_RRD/metrics 2>&1 >> $LOG_FILE
	echo_success "`gettext \"Creating Centreon Directory\"` '$CENTSTORAGE_RRD/metrics'" "$ok"
else
	echo_passed "`gettext \"CentStorage metrics Directory already exists\"`" "$passed"
fi
    
    
## Change macros in CentStorage binary
sed -e 's|@CENTREON_PATH@|'"$INSTALL_DIR_CENTREON"'|g' \
	 -e 's|@RRD_PERL@|'"$RRD_PERL"'|g' \
	 $TMPDIR/src/bin/centstorage > $TMPDIR/work/bin/centstorage
	 
echo_success "`gettext \"Replace Centstorage Macro\"`" "$ok"
log "INFO" "`gettext \"Copying CentStorage binary in final directory\"`"
cp $TMPDIR/work/bin/centstorage $TMPDIR/final/bin/centstorage 2>&1 >> $LOG_FILE
$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -m 755\
	$TMPDIR/final/bin/centstorage $CENTSTORAGE_BINDIR/centstorage 2>&1 >> $LOG_FILE

#chown $NAGIOS_USER:$NAGIOS_GROUP $TMPDIR/final/bin/centstorage
#chmod 755 $TMPDIR/final/bin/centstorage
echo_success "`gettext \"Set CentStorage properties\"`" "$ok"

#cp -a $TMPDIR/final/bin/centstorage $CENTSTORAGE_BINDIR/centstorage
 	
## Change macros in CentStorage init script
sed -e 's|@CENTREON_PATH@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' \
	-e 's|@NAGIOS_GROUP@|'"$NAGIOS_GROUP"'|g' \
	$TMPDIR/src/init.d.centstorage > $TMPDIR/work/init.d.centstorage

echo_success "`gettext \"Replace Centstorage init script Macro\"`" "$ok"
cp $TMPDIR/work/init.d.centstorage $TMPDIR/final/init.d.centstorage
cp $TMPDIR/final/init.d.centstorage $INSTALL_DIR_CENTREON/examples/init.d.centstorage
#chmod 755 $TMPDIR/final/init.d.centstorage

yes_no_default "`gettext \"Do you want I install CentStorage init script ?\"`"
if [ $? -eq 0 ] ; then 
	log "INFO" "`gettext \"CentStorage init script installed\"`"
	$INSTALL_DIR/cinstall -u root -g root -m 755 \
		$TMPDIR/final/init.d.centstorage \
		$INIT_D/centstorage 2>&1 >> $LOG_FILE
	#cp -a $TMPDIR/final/init.d.centstorage $INIT_D/centstorage
	yes_no_default "`gettext \"Do you want I install CentStorage run level ?\"`"
		if [ $? -eq 0 ] ; then
			install_init_service "centstorage"
		fi
else
	echo_passed "`gettext \"CentStorage init script not installed, please use \"`:\n $INSTALL_DIR_CENTREON/examples/init.d.centstorage" "$passed"
	log "INFO" "`gettext \"CentStorage init script not installed, please use \"`: $INSTALL_DIR_CENTREON/examples/init.d.centstorage"
fi

## Cron stuff
# Macro
### logAnalyser
sed -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	$TMPDIR/src/bin/logAnalyser > $TMPDIR/work/bin/logAnalyser

cp $TMPDIR/work/bin/logAnalyser $TMPDIR/final/bin/logAnalyser 2>&1 >> $LOG_FILE
$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -m 755 \
	$TMPDIR/final/bin/logAnalyser \
	$CENTSTORAGE_BINDIR/logAnalyser 2>&1 >> $LOG_FILE

#chown $NAGIOS_USER:$NAGIOS_GROUP $TMPDIR/final/bin/logAnalyser 2>&1 >> $LOG_FILE
#chmod 755 $TMPDIR/final/bin/logAnalyser 2>&1 >> $LOG_FILE
echo_success "`gettext \"Set logAnalyser properties\"`" "$ok"

#cp -a $TMPDIR/final/bin/logAnalyser $CENTSTORAGE_BINDIR/logAnalyser

### nagiosPerfTrace
sed -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	$TMPDIR/src/bin/nagiosPerfTrace > $TMPDIR/work/bin/nagiosPerfTrace

cp $TMPDIR/work/bin/nagiosPerfTrace $TMPDIR/final/bin/nagiosPerfTrace 2>&1 >> $LOG_FILE
$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -m 755 \
	$TMPDIR/final/bin/nagiosPerfTrace \
	$CENTSTORAGE_BINDIR/nagiosPerfTrace 2>&1 >> $LOG_FILE

#chown $NAGIOS_USER:$NAGIOS_GROUP $TMPDIR/final/bin/nagiosPerfTrace 2>&1 >> $LOG_FILE
#chmod 755 $TMPDIR/final/bin/nagiosPerfTrace 2>&1 >> $LOG_FILE
echo_success "`gettext \"Set nagiosPerfTrace properties\"`" "$ok"

#cp -a $TMPDIR/final/bin/nagiosPerfTrace $CENTSTORAGE_BINDIR/nagiosPerfTrace

### centreon.cron.conf
sed -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
	-e 's|@CENTSTORAGE_BINDIR@|'"$CENTSTORAGE_BINDIR"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	$BASE_DIR/tmpl/install/centstorage.cron > $TMPDIR/work/centstorage.cron
cp $TMPDIR/work/centstorage.cron $TMPDIR/final/centstorage.cron 2>&1 >> $LOG_FILE
$INSTALL_DIR/cinstall -u root -g root -m 644 \
	$TMPDIR/final/centstorage.cron \
	$CRON_D/centstorage 2>&1 >> $LOG_FILE

#chmod 755 $TMPDIR/final/centstorage.cron 2>&1 >> $LOG_FILE
#cp -a $TMPDIR/final/centstorage.cron $CRON_D/centstorage 2>&1 >> $LOG_FILE
echo_success "`gettext \"Install CentStorage cron\"`" "$ok"


## write install config file
createCentStorageInstallConf


## wait and see...
## sql console inject ?
